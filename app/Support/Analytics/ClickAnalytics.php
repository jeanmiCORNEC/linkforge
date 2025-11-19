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
        $since = Carbon::now()->subDays($days);

        // Normaliser : si on reçoit une Relation, on récupère son Builder sous-jacent
        $query = $clicks instanceof Relation
            ? $clicks->getQuery()
            : $clicks;

        // Total clics "lifetime" : pas de filtre de période
        $totalClicks = (clone $query)->count();

        // On part toujours d'un builder filtré par période
        // ⚠️ IMPORTANT : bien préfixer par clicks.created_at pour éviter l'ambiguïté avec tracked_links
        $base = (clone $query)->where('clicks.created_at', '>=', $since);

        // Visiteurs uniques
        $uniqueVisitors = (clone $base)
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
            'totalClicks'    => $totalClicks,
            'uniqueVisitors' => $uniqueVisitors,
            'devices'        => $devices,
            'browsers'       => $browsers,
            'clicksPerDay'   => $clicksPerDay,
            'period'         => [
                'days'  => $days,
                'since' => $since->toDateString(),
            ],
        ];
    }
}
