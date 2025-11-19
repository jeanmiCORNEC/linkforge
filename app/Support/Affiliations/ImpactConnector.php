<?php

namespace App\Support\Affiliations;

use App\Models\AffiliateIntegration;
use App\Models\Conversion;
use App\Models\TrackedLink;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class ImpactConnector implements AffiliateConnector
{
    public function sync(AffiliateIntegration $integration, Carbon $since, Carbon $until): int
    {
        $credentials = $integration->credentials ?? [];
        $accountSid  = $credentials['account_sid'] ?? null;
        $apiKey      = $credentials['api_key'] ?? null;

        if (! $accountSid || ! $apiKey) {
            throw new \RuntimeException('Credentials Impact incomplets.');
        }

        $synced = 0;
        $page   = 1;

        do {
            $response = Http::withBasicAuth($accountSid, $apiKey)
                ->acceptJson()
                ->get("https://api.impact.com/Mediapartners/{$accountSid}/Reports/Performance.json", [
                    'Page'      => $page,
                    'PageSize'  => 200,
                    'StartDate' => $since->toDateString(),
                    'EndDate'   => $until->toDateString(),
                    'TimeFrame' => 'CUSTOM',
                ]);

            if ($response->failed()) {
                throw new \RuntimeException('Impact API error: ' . $response->body());
            }

            $data    = $response->json();
            $records = $data['Records'] ?? [];

            foreach ($records as $record) {
                $synced += $this->upsertConversion($integration, $record);
            }

            $page++;
            $totalPages = $data['Numpages'] ?? 1;
        } while ($page <= $totalPages);

        return $synced;
    }

    protected function upsertConversion(AffiliateIntegration $integration, array $record): int
    {
        $trackingKey = $record['SubId1'] ?? null;
        $orderId     = $record['OrderId'] ?? $record['Id'] ?? $record['TransactionId'] ?? null;

        if (! $trackingKey || ! $orderId) {
            return 0;
        }

        /** @var TrackedLink|null $trackedLink */
        $trackedLink = TrackedLink::query()
            ->with('source')
            ->where('user_id', $integration->user_id)
            ->where('tracking_key', $trackingKey)
            ->first();

        if (! $trackedLink) {
            return 0;
        }

        $occurredAt = $this->parseDate($record['EventDate'] ?? $record['EventTime'] ?? null);

        $payload = [
            'tracked_link_id' => $trackedLink->id,
            'link_id'         => $trackedLink->link_id,
            'source_id'       => $trackedLink->source_id,
            'campaign_id'     => $trackedLink->source?->campaign_id,
            'status'          => $this->mapStatus($record['Status'] ?? null),
            'currency'        => strtoupper($record['Currency'] ?? 'EUR'),
            'revenue'         => $this->formatAmount($record['AdvertiserRevenue'] ?? $record['SaleAmount'] ?? 0),
            'commission'      => $this->formatAmount($record['PartnerCommission'] ?? 0),
            'metadata'        => [
                'platform' => 'impact',
                'record'   => $record,
            ],
        ];

        $conversion = Conversion::query()
            ->where('user_id', $integration->user_id)
            ->where('order_id', $orderId)
            ->first();

        if ($conversion) {
            $conversion->fill($payload);
            $conversion->save();

            return 1;
        }

        $conversion = new Conversion(array_merge($payload, [
            'user_id'      => $integration->user_id,
            'order_id'     => $orderId,
            'visitor_hash' => null,
        ]));

        if ($occurredAt) {
            $conversion->created_at = $occurredAt;
            $conversion->updated_at = $occurredAt;
        }

        $conversion->save();

        return 1;
    }

    protected function mapStatus(?string $status): string
    {
        $value = strtolower($status ?? 'pending');

        return match ($value) {
            'approved', 'paid'   => 'approved',
            'rejected', 'declined', 'void' => 'rejected',
            default              => 'pending',
        };
    }

    protected function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function formatAmount(null|int|float|string $value): float
    {
        return round((float) $value, 2);
    }
}
