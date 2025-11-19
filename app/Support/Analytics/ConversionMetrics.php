<?php

namespace App\Support\Analytics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

class ConversionMetrics
{
    /**
     * @return array{total:int, revenue:float, commission:float, by_status:array<string,int>}
     */
    public static function summary(Builder|Relation $query, Carbon $since, Carbon $until): array
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;

        $window = (clone $builder)->whereBetween('conversions.created_at', [$since, $until]);

        $totals = (clone $window)
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(revenue),0) as revenue, COALESCE(SUM(commission),0) as commission')
            ->first();

        $byStatus = (clone $window)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return [
            'total'      => (int) ($totals->total ?? 0),
            'revenue'    => (float) ($totals->revenue ?? 0),
            'commission' => (float) ($totals->commission ?? 0),
            'by_status'  => $byStatus,
        ];
    }
}
