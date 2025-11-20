<?php

namespace App\Support\Plans;

use App\Models\User;

class PlanLimits
{
    public const FREE_LIMITS = [
        'campaigns' => 2,
        'sources'   => 4,
        'links'     => 10,
    ];

    public static function campaignsLimit(User $user): ?int
    {
        return $user->plan === 'free' ? self::FREE_LIMITS['campaigns'] : null;
    }

    public static function sourcesLimit(User $user): ?int
    {
        return $user->plan === 'free' ? self::FREE_LIMITS['sources'] : null;
    }

    public static function linksLimit(User $user): ?int
    {
        return $user->plan === 'free' ? self::FREE_LIMITS['links'] : null;
    }
}
