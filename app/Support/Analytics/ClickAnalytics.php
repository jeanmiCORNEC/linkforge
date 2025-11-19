<?php

namespace App\Support\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClickAnalytics
{
    /**
     * Calcule les stats analytiques sur une fenêtre de $days jours
     * à partir d'une requête de clics déjà contextualisée
     * (lien, source, campagne, etc.).
     *
     * Convention LinkForge :
     * - totalClicks       : nombre total de clics sur toute la durée de vie (sans filtre de période)
     * - uniqueVisitors    : nb de visitor_hash distincts sur la fenêtre de $days jours
     * - clicksPerDay      : série journalière sur $days jours
     * - devicesBreakdown  : distribution device sur la fenêtre
     * - browsersBreakdown : distribution browser sur la fenêtre
     */
    public static function forPeriod(Builder|Relation $clicks, int $days = 7): array
    {
        $now   = Carbon::now();
        $since = (clone $now)->subDays($days);
        $previousStart = (clone $since)->subDays($days);

        // Normaliser : si on reçoit une Relation, on récupère son Builder sous-jacent
        $query = self::normalizeQuery($clicks);

        // On part toujours d'un builder filtré par période
        // ⚠️ IMPORTANT : bien préfixer par clicks.created_at pour éviter l'ambiguïté avec tracked_links
        $base = (clone $query)->where('clicks.created_at', '>=', $since);

        // Visiteurs uniques
        $uniqueVisitors = (clone $base)
            ->distinct('visitor_hash')
            ->count('visitor_hash');

        $currentClicks = (clone $base)->count();

        // Fenêtre précédente pour la comparaison
        $previousWindow = (clone $query)
            ->whereBetween('clicks.created_at', [$previousStart, $since]);

        $previousClicks = (clone $previousWindow)->count();
        $previousUnique = (clone $previousWindow)
            ->distinct('visitor_hash')
            ->count('visitor_hash');

        // Breakdown devices
        $devices = (clone $base)
            ->select('device', DB::raw('count(*) as total'))
            ->groupBy('device')
            ->pluck('total', 'device');

        // Breakdown browsers
        $browsers = (clone $base)
            ->select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->pluck('total', 'browser');

        // Clics par jour
        $clicksPerDay = (clone $base)
            ->select(
                DB::raw('DATE(clicks.created_at) as day'),
                DB::raw('count(*) as total')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'date'  => $row->day,
                'total' => (int) $row->total,
            ])
            ->values();

        return [
            'totalClicks'    => $currentClicks,
            'uniqueVisitors' => $uniqueVisitors,
            'devices'        => $devices,
            'browsers'       => $browsers,
            'clicksPerDay'   => $clicksPerDay,
            'period'         => [
                'days'  => $days,
                'since' => $since->toDateString(),
                'until' => $now->toDateString(),
            ],
            'delta' => [
                'totalClicks'    => self::deltaPercent($currentClicks, $previousClicks),
                'uniqueVisitors' => self::deltaPercent($uniqueVisitors, $previousUnique),
            ],
        ];
    }

    /**
     * Variante pour les campagnes : ajoute top sources / liens / jours.
     */
    public static function forCampaign(Builder|Relation $clicks, int $days = 7, array $insights = ['sources', 'links', 'days', 'heatmap']): array
    {
        return self::withInsights($clicks, $days, $insights);
    }

    public static function forSource(Builder|Relation $clicks, int $days = 7, array $insights = ['links', 'days', 'heatmap']): array
    {
        return self::withInsights($clicks, $days, $insights);
    }

    public static function forLink(Builder|Relation $clicks, int $days = 7, array $insights = ['sources', 'days', 'heatmap']): array
    {
        return self::withInsights($clicks, $days, $insights);
    }

    /**
     * Ajoute des insights spécifiques (tops, heatmap...) à la base de stats.
     */
    public static function withInsights(Builder|Relation $clicks, int $days = 7, array $insights = []): array
    {
        $stats       = self::forPeriod($clicks, $days);
        $since       = Carbon::now()->subDays($days);
        $query       = self::normalizeQuery($clicks);
        $windowQuery = (clone $query)->where('clicks.created_at', '>=', $since);
        $windowTotal = collect($stats['clicksPerDay'])->sum('total');

        if (in_array('sources', $insights, true)) {
            $stats['topSources'] = self::topSources($windowQuery, $windowTotal);
        }

        if (in_array('links', $insights, true)) {
            $stats['topLinks'] = self::topLinks($windowQuery, $windowTotal);
        }

        if (in_array('days', $insights, true)) {
            $stats['topDays'] = self::topDays($stats['clicksPerDay'], $windowTotal);
        }

        if (in_array('heatmap', $insights, true)) {
            $stats['hourlyHeatmap'] = self::hourlyHeatmap($windowQuery);
        }

        return $stats;
    }

    protected static function normalizeQuery(Builder|Relation $clicks): Builder
    {
        return $clicks instanceof Relation
            ? $clicks->getQuery()
            : $clicks;
    }

    /**
     * Sources les plus performantes sur la période.
     */
    protected static function topSources(Builder $windowQuery, int $total, int $limit = 5): array
    {
        $results = (clone $windowQuery)
            ->join('tracked_links as tl_sources', 'tl_sources.id', '=', 'clicks.tracked_link_id')
            ->join('sources', 'sources.id', '=', 'tl_sources.source_id')
            ->select('sources.id', 'sources.name', DB::raw('count(*) as total'))
            ->groupBy('sources.id', 'sources.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $results
            ->map(fn ($row) => [
                'id'         => $row->id,
                'name'       => $row->name,
                'total'      => (int) $row->total,
                'percentage' => $total > 0 ? round(($row->total / $total) * 100) : 0,
            ])
            ->toArray();
    }

    /**
     * Liens (tracked_links -> links) les plus performants.
     */
    protected static function topLinks(Builder $windowQuery, int $total, int $limit = 5): array
    {
        $results = (clone $windowQuery)
            ->join('tracked_links as tl_links', 'tl_links.id', '=', 'clicks.tracked_link_id')
            ->join('links', 'links.id', '=', 'tl_links.link_id')
            ->select('links.id', 'links.title', 'links.destination_url', DB::raw('count(*) as total'))
            ->groupBy('links.id', 'links.title', 'links.destination_url')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $results
            ->map(fn ($row) => [
                'id'            => $row->id,
                'title'         => $row->title,
                'destination'   => $row->destination_url,
                'total'         => (int) $row->total,
                'percentage'    => $total > 0 ? round(($row->total / $total) * 100) : 0,
            ])
            ->toArray();
    }

    /**
     * Classement des journées qui performent le plus.
     */
    protected static function topDays(iterable $clicksPerDay, int $total, int $limit = 5): array
    {
        return collect($clicksPerDay)
            ->sortByDesc('total')
            ->take($limit)
            ->values()
            ->map(fn ($row) => [
                'date'       => $row['date'],
                'total'      => $row['total'],
                'percentage' => $total > 0 ? round(($row['total'] / $total) * 100) : 0,
            ])
            ->toArray();
    }

    /**
     * Heatmap horaire : chaque cellule = nb de clics pour un jour donné et une heure.
     */
    protected static function hourlyHeatmap(Builder $windowQuery): array
    {
        $connection    = $windowQuery->getConnection();
        $driver        = $connection->getDriverName();
        $dateExpr      = 'DATE(clicks.created_at)';
        $hourExpr      = $driver === 'sqlite'
            ? "CAST(strftime('%H', clicks.created_at) AS INTEGER)"
            : 'HOUR(clicks.created_at)';

        return (clone $windowQuery)
            ->selectRaw("$dateExpr as date, $hourExpr as hour, COUNT(*) as total")
            ->groupByRaw("$dateExpr, $hourExpr")
            ->orderBy('date')
            ->orderBy('hour')
            ->get()
            ->map(function ($row) {
                $day = Carbon::parse($row->date);

                return [
                    'date'          => $row->date,
                    'weekday'       => $day->dayOfWeek,
                    'weekdayLabel'  => $day->translatedFormat('D'),
                    'hour'          => (int) $row->hour,
                    'total'         => (int) $row->total,
                ];
            })
            ->toArray();
    }

    protected static function deltaPercent(int $current, int $previous): int
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
