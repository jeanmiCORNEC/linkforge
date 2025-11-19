<?php

namespace App\Support\Affiliations;

use App\Models\AffiliateIntegration;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AffiliateSyncService
{
    public function __construct(
        protected ConnectorRegistry $registry,
    ) {
    }

    public function syncIntegration(AffiliateIntegration $integration, Carbon $since, Carbon $until): int
    {
        $connector = $this->registry->resolve($integration->platform);

        try {
            $count = $connector->sync($integration, $since, $until);
            $integration->status = $count > 0 ? 'connected' : 'pending';
            $integration->last_error = null;
        } catch (\Throwable $exception) {
            $count = 0;
            $integration->status = 'error';
            $integration->last_error = $exception->getMessage();
            Log::warning('Affiliate sync failed', [
                'integration_id' => $integration->id,
                'platform'       => $integration->platform,
                'message'        => $exception->getMessage(),
            ]);
        }

        $integration->last_synced_at = now();
        $integration->save();

        return $count;
    }

    public function syncUser(User $user, Carbon $since, Carbon $until, ?string $platform = null): int
    {
        $integrations = $user->affiliateIntegrations()
            ->when($platform, fn ($query) => $query->where('platform', $platform))
            ->get();

        return $integrations->sum(fn (AffiliateIntegration $integration) => $this->syncIntegration($integration, $since, $until));
    }

    public function syncAll(Carbon $since, Carbon $until, ?string $platform = null): int
    {
        $query = AffiliateIntegration::query()
            ->with('user')
            ->when($platform, fn ($q) => $q->where('platform', $platform));

        $total = 0;

        $query->chunk(50, function (Collection $chunk) use (&$total, $since, $until) {
            $chunk->each(function (AffiliateIntegration $integration) use (&$total, $since, $until) {
                $total += $this->syncIntegration($integration, $since, $until);
            });
        });

        return $total;
    }
}
