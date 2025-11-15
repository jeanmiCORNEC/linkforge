<?php

namespace App\Support\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClickAnalytics
{
    /**
     * Calcule les stats de base pour un jeu de clics donné.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation  $clicks
     * @param  int  $days  Période en jours (ex : 7 ou 30)
     * @return array
     */
    public static function forPeriod(Builder|Relation $clicks, int $days = 7): array
    {
        $since = Carbon::now()->subDays($days);

        // Normaliser : si on reçoit une Relation, on récupère son Builder sous-jacent
        $query = $clicks instanceof Relation
            ? $clicks->getQuery()
            : $clicks;

        // On part toujours d'un builder filtré par période
        // ⚠️ IMPORTANT : bien préfixer par clicks.created_at pour éviter l'ambiguïté avec tracked_links
        $base = (clone $query)->where('clicks.created_at', '>=', $since);

        // Total clics
        $totalClicks = (clone $base)->count();

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
