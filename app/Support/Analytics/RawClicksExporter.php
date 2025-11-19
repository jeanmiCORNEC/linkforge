<?php

namespace App\Support\Analytics;

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

        return $builder
            ->with([
                'trackedLink.link',
                'trackedLink.source.campaign',
            ])
            ->whereBetween('clicks.created_at', [$since, $until])
            ->orderBy('clicks.created_at')
            ->get()
            ->map(function ($click) {
                $tracked  = $click->trackedLink;
                $link     = $tracked?->link;
                $source   = $tracked?->source;
                $campaign = $source?->campaign;

                return [
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
                    'order_id'        => '',
                    'conversion_id'   => '',
                    'revenue'         => 0,
                    'commission'      => 0,
                    'status'          => 'pending',
                ];
            })
            ->toArray();
    }
}
