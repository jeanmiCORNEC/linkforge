<?php

namespace App\Http\Controllers\Sources;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Support\Analytics\ClickAnalytics;
use App\Support\Analytics\ConversionMetrics;
use App\Support\Analytics\RawClicksExporter;
use App\Support\Features\FeatureManager;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SourceAnalyticsController extends Controller
{
    /**
     * Affiche les statistiques détaillées pour une source.
     */
    public function show(Request $request, Source $source)
    {
        $user = $request->user();

        // Sécurité : la source doit appartenir à l'utilisateur
        if ($source->user_id !== $user->id) {
            abort(403);
        }

        $featureScope = FeatureManager::for($user);

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        // On part de la relation clicks() définie sur Source
        $clicksQuery = $source->clicks();

        // Stats enrichies : top liens, meilleurs jours, heatmap…
        $insights = ['days'];
        if ($featureScope->allows('analytics.top_lists')) {
            $insights[] = 'links';
        }
        if ($featureScope->allows('analytics.heatmap')) {
            $insights[] = 'heatmap';
        }

        $rawStats = ClickAnalytics::forSource($clicksQuery, $days, $insights);
        $since = Carbon::parse($rawStats['period']['since'])->startOfDay();
        $until = Carbon::parse($rawStats['period']['until'] ?? now()->toDateString())->endOfDay();
        $conversionSummary = ConversionMetrics::summary($source->conversions(), $since, $until);

        if (! $featureScope->allows('analytics.top_lists')) {
            unset($rawStats['topLinks']);
        }
        if (! $featureScope->allows('analytics.heatmap')) {
            unset($rawStats['hourlyHeatmap']);
        }
        if (! $featureScope->allows('analytics.deltas')) {
            $rawStats['delta'] = [
                'totalClicks'    => 0,
                'uniqueVisitors' => 0,
            ];
        }

        // Mapping en snake_case pour rester cohérent avec LinkAnalytics + tests
        $stats = [
            'total_clicks'       => $rawStats['totalClicks'],
            'unique_visitors'    => $rawStats['uniqueVisitors'],
            'devices_breakdown'  => $rawStats['devices'],
            'browsers_breakdown' => $rawStats['browsers'],
            'top_countries'      => $rawStats['topCountries'] ?? [],
            'clicks_per_day'     => $rawStats['clicksPerDay'],
            'top_links'          => $rawStats['topLinks'] ?? [],
            'top_days'           => $rawStats['topDays'] ?? [],
            'hourly_heatmap'     => $rawStats['hourlyHeatmap'] ?? [],
            'delta'              => [
                'total_clicks'    => $rawStats['delta']['totalClicks'] ?? 0,
                'unique_visitors' => $rawStats['delta']['uniqueVisitors'] ?? 0,
            ],
            'period'             => $rawStats['period'],
            'conversions'        => $conversionSummary,
        ];

        return Inertia::render('Sources/Analytics', [
            'source' => [
                'id'       => $source->id,
                'name'     => $source->name,
                'platform' => $source->platform,
            ],
            'stats'   => $stats,
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

    public function export(Request $request, Source $source)
    {
        $user = $request->user();

        if ($source->user_id !== $user->id) {
            abort(403);
        }

        $featureScope = FeatureManager::for($user);

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

        $source->loadMissing('campaign');

        $stats   = ClickAnalytics::forSource($source->clicks(), $days);
        $devices = is_array($stats['devices']) ? $stats['devices'] : $stats['devices']->toArray();
        $period  = $stats['period'] ?? ['since' => now()->subDays($days)->toDateString(), 'until' => now()->toDateString()];
        $since   = Carbon::parse($period['since'])->startOfDay();
        $until   = Carbon::parse($period['until'] ?? now()->toDateString())->endOfDay();

        $mobile  = (int) ($devices['mobile'] ?? 0);
        $desktop = (int) ($devices['desktop'] ?? 0);
        $tablet  = (int) ($devices['tablet'] ?? 0);
        $unknown = max($stats['totalClicks'] - ($mobile + $desktop + $tablet), 0);

        $conversionSummary = ConversionMetrics::summary($source->conversions(), $since, $until);

        $topCountry = $source->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('country')
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->value('country');

        $topReferer = $source->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('referrer')
            ->select('referrer', DB::raw('count(*) as total'))
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->value('referrer');

        $topBrowser = $source->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('browser')
            ->select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->orderByDesc('total')
            ->value('browser');

        $columns = [
            'source_id',
            'source_name',
            'platform',
            'campaign_id',
            'campaign_name',
            'external_sub_id',
            'tracking_notes',
            'created_at',
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
            'top_browser',
            'top_referrer',
            'conversions',
            'revenue',
            'commission',
            'epc',
        ];

        $row = [
            'source_id'             => $source->id,
            'source_name'           => $source->name,
            'platform'              => $source->platform,
            'campaign_id'           => $source->campaign?->id ?? '',
            'campaign_name'         => $source->campaign?->name ?? '',
            'external_sub_id'       => $source->external_id ?? '',
            'tracking_notes'        => $source->notes ?? '',
            'created_at'            => optional($source->created_at)->toDateTimeString(),
            'period_days'           => $stats['period']['days'] ?? $days,
            'period_since'          => $period['since'] ?? '',
            'period_until'          => $period['until'] ?? '',
            'total_clicks'          => $stats['totalClicks'],
            'unique_visitors'       => $stats['uniqueVisitors'],
            'clicks_mobile'         => $mobile,
            'clicks_desktop'        => $desktop,
            'clicks_tablet'         => $tablet,
            'clicks_unknown_device' => $unknown,
            'top_country'           => $topCountry ?? '',
            'top_browser'           => $topBrowser ?? '',
            'top_referrer'          => $topReferer ?? '',
            'conversions'           => $conversionSummary['total'],
            'revenue'               => $conversionSummary['revenue'],
            'commission'            => $conversionSummary['commission'],
            'epc'                   => $stats['totalClicks'] > 0
                ? round($conversionSummary['revenue'] / $stats['totalClicks'], 2)
                : 0,
        ];

        $csv = CsvExporter::build($columns, [$row]);

        $filename = sprintf('source-%s-analytics.csv', $source->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function exportRaw(Request $request, Source $source)
    {
        $user = $request->user();

        if ($source->user_id !== $user->id) {
            abort(403);
        }

        $featureScope = FeatureManager::for($user);

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

        $period = ClickAnalytics::forPeriod($source->clicks(), $days)['period'] ?? [
            'since' => now()->subDays($days)->toDateString(),
            'until' => now()->toDateString(),
        ];

        $since = Carbon::parse($period['since'])->startOfDay();
        $until = Carbon::parse($period['until'] ?? now()->toDateString())->endOfDay();

        $rows = RawClicksExporter::rows($source->clicks(), $since, $until);

        $csv = CsvExporter::build(RawClicksExporter::columns(), $rows);

        $filename = sprintf('source-%s-raw-clicks.csv', $source->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
