<?php

namespace App\Http\Controllers\Sources;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Support\Analytics\ClickAnalytics;
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
        $rawStats = ClickAnalytics::forSource($clicksQuery, $days);

        // Mapping en snake_case pour rester cohérent avec LinkAnalytics + tests
        $stats = [
            'total_clicks'       => $rawStats['totalClicks'],
            'unique_visitors'    => $rawStats['uniqueVisitors'],
            'devices_breakdown'  => $rawStats['devices'],
            'browsers_breakdown' => $rawStats['browsers'],
            'clicks_per_day'     => $rawStats['clicksPerDay'],
            'top_links'          => $rawStats['topLinks'] ?? [],
            'top_days'           => $rawStats['topDays'] ?? [],
            'hourly_heatmap'     => $rawStats['hourlyHeatmap'] ?? [],
            'delta'              => [
                'total_clicks'    => $rawStats['delta']['totalClicks'] ?? 0,
                'unique_visitors' => $rawStats['delta']['uniqueVisitors'] ?? 0,
            ],
            'period'             => $rawStats['period'],
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
        ]);
    }

    public function export(Request $request, Source $source)
    {
        $user = $request->user();

        if ($source->user_id !== $user->id) {
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

        $topCountry = $source->clicks()
            ->whereBetween('clicks.created_at', [$since, $until])
            ->whereNotNull('country')
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->orderByDesc('total')
            ->value('country');

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
            'conversions'           => 0,
            'revenue'               => 0,
            'commission'            => 0,
            'epc'                   => 0,
        ];

        $csv = CsvExporter::build($columns, [$row]);

        $filename = sprintf('source-%s-analytics.csv', $source->id);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
