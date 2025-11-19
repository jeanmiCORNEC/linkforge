<?php

namespace App\Http\Controllers\Sources;

use App\Models\Source;
use App\Support\Analytics\ClickAnalytics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

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
}
