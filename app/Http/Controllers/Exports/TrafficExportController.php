<?php

namespace App\Http\Controllers\Exports;

use App\Http\Controllers\Controller;
use App\Models\Click;
use App\Support\CsvExporter;
use App\Support\Features\FeatureManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TrafficExportController extends Controller
{
    public function monthly(Request $request)
    {
        $user = $request->user();
        $featureScope = FeatureManager::for($user);

        if (! $featureScope->allows('analytics.exports')) {
            abort(403);
        }

        $monthParam = $request->get('month', now()->format('Y-m'));

        try {
            $start = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Exception) {
            abort(422, 'Invalid month format. Use YYYY-MM.');
        }

        $end = (clone $start)->endOfMonth();

        $type = strtolower((string) $request->get('type')) ?: null;
        $resourceId = $request->get('id');

        $clicksQuery = Click::query()
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('trackedLink', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });

        if ($type && $resourceId) {
            $this->applyResourceFilter($clicksQuery, $type, $resourceId, $user->id);
        }

        $clicks = $clicksQuery->get(['created_at', 'visitor_hash', 'device', 'country', 'referrer']);

        $grouped = $clicks->groupBy(fn (Click $click) => $click->created_at->toDateString());

        $columns = [
            'date',
            'total_clicks',
            'unique_visitors',
            'mobile_clicks',
            'desktop_clicks',
            'tablet_clicks',
            'unknown_device_clicks',
            'top_country',
            'top_referrer',
        ];

        $rows = [];

        $cursor = (clone $start);
        while ($cursor <= $end) {
            $day = $cursor->toDateString();
            $items = $grouped->get($day, collect());

            $rows[] = [
                'date'                   => $day,
                'total_clicks'           => $items->count(),
                'unique_visitors'        => $items->pluck('visitor_hash')->filter()->unique()->count(),
                'mobile_clicks'          => $this->countDevice($items, 'mobile'),
                'desktop_clicks'         => $this->countDevice($items, 'desktop'),
                'tablet_clicks'          => $this->countDevice($items, 'tablet'),
                'unknown_device_clicks'  => $this->countDevice($items, 'unknown'),
                'top_country'            => $this->topValue($items, 'country'),
                'top_referrer'           => $this->topValue($items, 'referrer'),
            ];

            $cursor->addDay();
        }

        $csv = CsvExporter::build($columns, $rows);
        $filename = sprintf('traffic-%s.csv', $start->format('Y-m'));

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    protected function applyResourceFilter($query, string $type, $resourceId, int $userId): void
    {
        switch ($type) {
            case 'link':
                $query->whereHas('trackedLink', function ($builder) use ($resourceId) {
                    $builder->where('link_id', $resourceId);
                });

                break;
            case 'source':
                $query->whereHas('trackedLink', function ($builder) use ($resourceId) {
                    $builder->where('source_id', $resourceId);
                });

                break;
            case 'campaign':
                $query->whereHas('trackedLink.source', function ($builder) use ($resourceId) {
                    $builder->where('campaign_id', $resourceId);
                });

                break;
            default:
                abort(422, 'Unsupported export type.');
        }
    }

    protected function countDevice(Collection $items, string $target): int
    {
        return $items->reduce(function ($carry, Click $click) use ($target) {
            $normalized = $this->normalizeDevice($click->device);

            return $carry + ($normalized === $target ? 1 : 0);
        }, 0);
    }

    protected function normalizeDevice(?string $device): string
    {
        $label = strtolower((string) $device);

        return match (true) {
            str_contains($label, 'mobile'),
            str_contains($label, 'android'),
            str_contains($label, 'iphone'),
            str_contains($label, 'phone') => 'mobile',
            str_contains($label, 'tablet'),
            str_contains($label, 'ipad') => 'tablet',
            str_contains($label, 'desktop'),
            str_contains($label, 'windows'),
            str_contains($label, 'mac') => 'desktop',
            default => 'unknown',
        };
    }

    protected function topValue(Collection $items, string $key): string
    {
        $result = $items
            ->pluck($key)
            ->filter()
            ->map(fn ($value) => strtolower(trim((string) $value)))
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        return $result ? ucfirst($result) : '';
    }
}
