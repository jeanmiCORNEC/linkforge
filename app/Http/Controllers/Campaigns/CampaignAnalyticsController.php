<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Click;
use App\Support\Analytics\ClickAnalytics;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CampaignAnalyticsController extends Controller
{
    /**
     * Affiche les stats pour une campagne.
     */
    public function show(Request $request, Campaign $campaign)
    {
        $user = $request->user();

        // Sécurité : la campagne doit appartenir à l'utilisateur
        if ($campaign->user_id !== $user->id) {
            abort(403);
        }

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        // On récupère tous les clics liés à cette campagne via:
        // Click -> trackedLink -> source -> campaign
        $clicksQuery = Click::query()
            ->whereHas('trackedLink.source', function ($query) use ($campaign) {
                $query->where('campaign_id', $campaign->id);
            });

        // Stats génériques + insights campagne (tops sources/liens/jours)
        $stats = ClickAnalytics::forCampaign($clicksQuery, $days);

        // Même fenêtre temporelle que forPeriod()
        $since = now()->subDays($days);

        // Répartition par device
        $stats['devices'] = (clone $clicksQuery)
            ->where('created_at', '>=', $since)
            ->selectRaw("COALESCE(device, 'Unknown') as device, COUNT(*) as total")
            ->groupBy('device')
            ->pluck('total', 'device')
            ->toArray();

        // Répartition par navigateur
        $stats['browsers'] = (clone $clicksQuery)
            ->where('created_at', '>=', $since)
            ->selectRaw("COALESCE(browser, 'Unknown') as browser, COUNT(*) as total")
            ->groupBy('browser')
            ->pluck('total', 'browser')
            ->toArray();

        return Inertia::render('Campaigns/Analytics', [
            'campaign' => [
                'id'   => $campaign->id,
                'name' => $campaign->name,
            ],
            'stats'   => $stats,
            'filters' => [
                'days' => $days,
            ],
        ]);
    }

    public function export(Request $request, Campaign $campaign)
    {
        $user = $request->user();

        if ($campaign->user_id !== $user->id) {
            abort(403);
        }

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        $clicksQuery = Click::query()
            ->whereHas('trackedLink.source', function ($query) use ($campaign) {
                $query->where('campaign_id', $campaign->id);
            });

        $stats   = ClickAnalytics::forCampaign($clicksQuery, $days);
        $devices = is_array($stats['devices']) ? $stats['devices'] : $stats['devices']->toArray();
        $period  = $stats['period'] ?? ['since' => now()->subDays($days)->toDateString(), 'until' => now()->toDateString()];
        $since   = Carbon::parse($period['since'])->startOfDay();
        $until   = Carbon::parse($period['until'] ?? now()->toDateString())->endOfDay();

        $mobile  = (int) ($devices['mobile'] ?? 0);
        $desktop = (int) ($devices['desktop'] ?? 0);
        $tablet  = (int) ($devices['tablet'] ?? 0);
        $unknown = max($stats['totalClicks'] - ($mobile + $desktop + $tablet), 0);

        $totalSources = $campaign->sources()->whereNull('deleted_at')->count();
        $totalLinks   = DB::table('tracked_links')
            ->join('sources', 'sources.id', '=', 'tracked_links.source_id')
            ->where('sources.campaign_id', $campaign->id)
            ->distinct('tracked_links.link_id')
            ->count('tracked_links.link_id');

        $columns = [
            'campaign_id',
            'campaign_name',
            'campaign_slug',
            'status',
            'created_at',
            'archived_at',
            'user_id',
            'total_links',
            'total_sources',
            'period_days',
            'period_since',
            'period_until',
            'total_clicks',
            'unique_visitors',
            'clicks_mobile',
            'clicks_desktop',
            'clicks_tablet',
            'clicks_unknown_device',
            'conversions',
            'approved_conversions',
            'pending_conversions',
            'revenue',
            'commission',
            'epc',
        ];

        $row = [
            'campaign_id'           => $campaign->id,
            'campaign_name'         => $campaign->name,
            'campaign_slug'         => '',
            'status'                => $campaign->status,
            'created_at'            => optional($campaign->created_at)->toDateTimeString(),
            'archived_at'           => optional($campaign->deleted_at)->toDateTimeString(),
            'user_id'               => $campaign->user_id,
            'total_links'           => $totalLinks,
            'total_sources'         => $totalSources,
            'period_days'           => $stats['period']['days'] ?? $days,
            'period_since'          => $period['since'] ?? '',
            'period_until'          => $period['until'] ?? '',
            'total_clicks'          => $stats['totalClicks'],
            'unique_visitors'       => $stats['uniqueVisitors'],
            'clicks_mobile'         => $mobile,
            'clicks_desktop'        => $desktop,
            'clicks_tablet'         => $tablet,
            'clicks_unknown_device' => $unknown,
            'conversions'           => 0,
            'approved_conversions'  => 0,
            'pending_conversions'   => 0,
            'revenue'               => 0,
            'commission'            => 0,
            'epc'                   => 0,
        ];

        $csv = CsvExporter::build($columns, [$row]);
        $filename = sprintf('campaign-%s-analytics.csv', $campaign->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
