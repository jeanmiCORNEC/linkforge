<?php

namespace App\Support\Features;

use App\Models\User;

class FeatureManager
{
    /**
     * Liste des features par plan.
     */
    protected const PLAN_FEATURES = [
        'free' => [
            'analytics.deltas'    => false,
            'analytics.heatmap'   => false,
            'analytics.top_lists' => false,
            'analytics.exports'   => false,
            'analytics.raw_log'   => false,
        ],
        'pro' => [
            'analytics.deltas'    => true,
            'analytics.heatmap'   => true,
            'analytics.top_lists' => true,
            'analytics.exports'   => true,
            'analytics.raw_log'   => true,
        ],
    ];

    public static function for(User $user): FeatureScope
    {
        $plan = $user->plan ?? 'free';

        $features = self::PLAN_FEATURES[$plan] ?? self::PLAN_FEATURES['free'];

        return new FeatureScope($features);
    }
}
