<?php

namespace App\Support\Analytics;

use App\Models\Conversion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;

class RawClicksExporter
{
    public static function columns(): array
    {
        return [
            'click_id',
            'clicked_at',
            'campaign_id',
            'campaign_name',
            'source_id',
            'source_name',
            'platform',
            'link_id',
            'link_title',
            'tracked_link_id',
            'tracking_key',
            'ip_address',
            'visitor_hash',
            'device',
            'browser',
            'os',
            'country_code',
            'referrer',
            'order_id',
            'conversion_id',
            'revenue',
            'commission',
            'status',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function rows(Builder|Relation $query, Carbon $since, Carbon $until): array
    {
        $builder = $query instanceof Relation ? $query->getQuery() : $query;

        $clicks = $builder
            ->with([
                'trackedLink.link',
                'trackedLink.source.campaign',
            ])
            ->whereBetween('clicks.created_at', [$since, $until])
            ->orderBy('clicks.created_at')
            ->get();

        $trackedLinkIds = $clicks->pluck('tracked_link_id')->filter()->unique();

        $conversionLookup = [];
        $specificBuckets = [];
        $fallbackBuckets = [];
        $used = [];

        if ($trackedLinkIds->isNotEmpty()) {
            Conversion::whereIn('tracked_link_id', $trackedLinkIds)
                ->whereBetween('created_at', [$since, $until])
                ->orderBy('created_at')
                ->get()
                ->each(function (Conversion $conversion) use (&$conversionLookup, &$specificBuckets, &$fallbackBuckets) {
                    $conversionLookup[$conversion->id] = $conversion;

                    if ($conversion->visitor_hash) {
                        $key = self::specificKey($conversion->tracked_link_id, $conversion->visitor_hash);
                        $specificBuckets[$key][] = $conversion->id;
                    }

                    $fallbackBuckets[$conversion->tracked_link_id][] = $conversion->id;
                });
        }

        $rows = [];

        foreach ($clicks as $click) {
            $tracked  = $click->trackedLink;
            $link     = $tracked?->link;
            $source   = $tracked?->source;
            $campaign = $source?->campaign;

            $matched = null;

            if ($click->visitor_hash) {
                $key = self::specificKey($click->tracked_link_id, $click->visitor_hash);
                $matched = self::shiftConversion($specificBuckets[$key] ?? [], $conversionLookup, $used);
                if (isset($specificBuckets[$key])) {
                    $specificBuckets[$key] = array_values(array_diff($specificBuckets[$key], array_keys($used)));
                }
            }

            if (! $matched && $click->tracked_link_id) {
                $matched = self::shiftConversion($fallbackBuckets[$click->tracked_link_id] ?? [], $conversionLookup, $used);
                if (isset($fallbackBuckets[$click->tracked_link_id])) {
                    $fallbackBuckets[$click->tracked_link_id] = array_values(array_diff($fallbackBuckets[$click->tracked_link_id], array_keys($used)));
                }
            }

            $rows[] = [
                'click_id'        => $click->id,
                'clicked_at'      => optional($click->created_at)->toDateTimeString(),
                'campaign_id'     => $campaign?->id ?? '',
                'campaign_name'   => $campaign?->name ?? '',
                'source_id'       => $source?->id ?? '',
                'source_name'     => $source?->name ?? '',
                'platform'        => $source?->platform ?? '',
                'link_id'         => $link?->id ?? '',
                'link_title'      => $link?->title ?? '',
                'tracked_link_id' => $tracked?->id ?? '',
                'tracking_key'    => $tracked?->tracking_key ?? '',
                'ip_address'      => $click->ip_address,
                'visitor_hash'    => $click->visitor_hash,
                'device'          => $click->device,
                'browser'         => $click->browser,
                'os'              => $click->os,
                'country_code'    => $click->country,
                'referrer'        => $click->referrer,
                'order_id'        => $matched?->order_id ?? '',
                'conversion_id'   => $matched?->id ?? '',
                'revenue'         => $matched?->revenue ?? 0,
                'commission'      => $matched?->commission ?? 0,
                'status'          => $matched?->status ?? 'click',
            ];
        }

        return $rows;
    }

    protected static function specificKey(?int $trackedLinkId, ?string $visitorHash): string
    {
        return ($trackedLinkId ?? 0) . '|' . ($visitorHash ?? '');
    }

    /**
     * @param  array<int>  $bucket
     */
    protected static function shiftConversion(array $bucket, array $lookup, array &$used): ?Conversion
    {
        while (! empty($bucket)) {
            $conversionId = array_shift($bucket);

            if (! isset($lookup[$conversionId]) || isset($used[$conversionId])) {
                continue;
            }

            $used[$conversionId] = true;

            return $lookup[$conversionId];
        }

        return null;
    }
}
