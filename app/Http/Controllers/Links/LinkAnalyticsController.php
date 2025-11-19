<?php

namespace App\Http\Controllers\Links;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Link;
use App\Support\Analytics\ClickAnalytics;
use App\Support\Analytics\ConversionMetrics;
use App\Support\Analytics\RawClicksExporter;
use App\Support\CsvExporter;
use App\Support\Features\FeatureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LinkAnalyticsController extends Controller
{
    /**
     * Vérifie que le lien appartient bien à l'utilisateur connecté.
     */
    protected function ensureOwner(Request $request, Link $link): void
    {
        if ($link->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function show(Request $request, Link $link)
    {
        // Sécurité : propriétaire du lien
        $this->ensureOwner($request, $link);

        // Nombre de jours : 1 à 365, valeur par défaut = 7
        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        $featureScope = FeatureManager::for($request->user());
        $insights      = ['days'];
        if ($featureScope->allows('analytics.top_lists')) {
            $insights[] = 'sources';
        }
        if ($featureScope->allows('analytics.heatmap')) {
            $insights[] = 'heatmap';
        }

        // On part de la relation hasManyThrough déjà définie sur Link
        $clicksQuery = $link->clicks();

        $stats = ClickAnalytics::forLink($clicksQuery, $days, $insights);

        $since = Carbon::parse($stats['period']['since'])->startOfDay();
        $until = Carbon::parse($stats['period']['until'] ?? now()->toDateString())->endOfDay();
        $conversions = ConversionMetrics::summary($link->conversions(), $since, $until);
        $stats['conversions'] = $conversions;

        if (! $featureScope->allows('analytics.top_lists')) {
            unset($stats['topSources']);
        }
        if (! $featureScope->allows('analytics.heatmap')) {
            unset($stats['hourlyHeatmap']);
        }
        if (! $featureScope->allows('analytics.deltas')) {
            $stats['delta'] = [
                'totalClicks'    => 0,
                'uniqueVisitors' => 0,
            ];
        }

        return Inertia::render('Links/Analytics', [
            'link' => [
                'id'               => $link->id,
                'title'            => $link->title,
                'destination_url'  => $link->destination_url,
                // on garde le slug si tu en as besoin ailleurs
                'slug'             => $link->slug,
            ],
            'stats' => $stats,
            'filters' => [
                'days' => $days,
            ],
            'features' => [
                'exports'  => $featureScope->allows('analytics.exports'),
                'heatmap'  => $featureScope->allows('analytics.heatmap'),
                'topLists' => $featureScope->allows('analytics.top_lists'),
                'deltas'   => $featureScope->allows('analytics.deltas'),
                'rawLog'   => $featureScope->allows('analytics.raw_log'),
            ],
        ]);
    }

    public function export(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $featureScope = FeatureManager::for($request->user());

        if (! $featureScope->allows('analytics.exports')) {
            abort(403);
        }

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        $stats     = ClickAnalytics::forLink($link->clicks(), $days);
        $devices   = is_array($stats['devices']) ? $stats['devices'] : $stats['devices']->toArray();
        $period    = $stats['period'] ?? ['since' => now()->subDays($days)->toDateString(), 'until' => now()->toDateString()];
        $since     = Carbon::parse($period['since'])->startOfDay();
        $until     = Carbon::parse($period['until'] ?? now()->toDateString())->endOfDay();

        $mobile  = (int) ($devices['mobile'] ?? 0);
        $desktop = (int) ($devices['desktop'] ?? 0);
        $tablet  = (int) ($devices['tablet'] ?? 0);
        $unknown = max($stats['totalClicks'] - ($mobile + $desktop + $tablet), 0);

        $lastClickAt = $link->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->max('clicks.created_at');

        $conversionSummary = ConversionMetrics::summary($link->conversions(), $since, $until);

        $sourcesCount = $link->trackedLinks()
            ->whereNotNull('source_id')
            ->distinct('source_id')
            ->count('source_id');

        $trackedLinksCount = $link->trackedLinks()->count();

        $defaultTracked = $link->trackedLinks()
            ->whereNull('source_id')
            ->first();

        $mainTrackingUrl = $defaultTracked
            ? route('links.redirect', ['tracking_key' => $defaultTracked->tracking_key])
            : '';

        $campaignIds = DB::table('sources')
            ->join('tracked_links', 'tracked_links.source_id', '=', 'sources.id')
            ->where('tracked_links.link_id', $link->id)
            ->whereNull('sources.deleted_at')
            ->pluck('sources.campaign_id')
            ->filter()
            ->unique()
            ->values();

        $campaignId   = '';
        $campaignName = '';

        if ($campaignIds->count() === 1) {
            $campaign = Campaign::find($campaignIds->first());
            $campaignId   = $campaign?->id ?? '';
            $campaignName = $campaign?->name ?? '';
        } elseif ($campaignIds->count() > 1) {
            $campaignName = 'Multiple';
        }

        $columns = [
            'link_id',
            'link_title',
            'destination_url',
            'slug',
            'is_active',
            'created_at',
            'campaign_id',
            'campaign_name',
            'sources_count',
            'tracked_links_count',
            'main_tracking_url',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'period_days',
            'period_since',
            'period_until',
            'total_clicks',
            'unique_visitors',
            'clicks_mobile',
            'clicks_desktop',
            'clicks_tablet',
            'clicks_unknown_device',
            'top_country',
            'top_referrer',
            'last_click_at',
            'conversions',
            'revenue',
            'commission',
            'epc',
        ];

        $topCountry = $link->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('country')
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->value('country');

        $topReferer = $link->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('count(*) as total'))
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->value('referrer');

        $row = [
            'link_id'              => $link->id,
            'link_title'           => $link->title,
            'destination_url'      => $link->destination_url,
            'slug'                 => $link->slug,
            'is_active'            => $link->is_active ? 'true' : 'false',
            'created_at'           => optional($link->created_at)->toDateTimeString(),
            'campaign_id'          => $campaignId,
            'campaign_name'        => $campaignName,
            'sources_count'        => $sourcesCount,
            'tracked_links_count'  => $trackedLinksCount,
            'main_tracking_url'    => $mainTrackingUrl,
            'utm_source'           => '',
            'utm_medium'           => '',
            'utm_campaign'         => '',
            'period_days'          => $stats['period']['days'] ?? $days,
            'period_since'         => $period['since'] ?? '',
            'period_until'         => $period['until'] ?? '',
            'total_clicks'         => $stats['totalClicks'],
            'unique_visitors'      => $stats['uniqueVisitors'],
            'clicks_mobile'        => $mobile,
            'clicks_desktop'       => $desktop,
            'clicks_tablet'        => $tablet,
            'clicks_unknown_device'=> $unknown,
            'top_country'          => $topCountry ?? '',
            'top_referrer'         => $topReferer ?? '',
            'last_click_at'        => $lastClickAt ? Carbon::parse($lastClickAt)->toDateTimeString() : '',
            'conversions'          => $conversionSummary['total'],
            'revenue'              => $conversionSummary['revenue'],
            'commission'           => $conversionSummary['commission'],
            'epc'                  => $stats['totalClicks'] > 0
                ? round($conversionSummary['revenue'] / $stats['totalClicks'], 2)
                : 0,
        ];

        $csv = CsvExporter::build($columns, [$row]);

        $filename = sprintf('link-%s-analytics.csv', $link->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportRaw(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $featureScope = FeatureManager::for($request->user());

        if (! $featureScope->allows('analytics.raw_log')) {
            abort(403);
        }

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        $period = ClickAnalytics::forPeriod($link->clicks(), $days)['period'] ?? [
            'since' => now()->subDays($days)->toDateString(),
            'until' => now()->toDateString(),
        ];

        $since = Carbon::parse($period['since'])->startOfDay();
        $until = Carbon::parse($period['until'] ?? now()->toDateString())->endOfDay();

        $rows = RawClicksExporter::rows($link->clicks(), $since, $until);

        $csv = CsvExporter::build(RawClicksExporter::columns(), $rows);

        $filename = sprintf('link-%s-raw-clicks.csv', $link->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
