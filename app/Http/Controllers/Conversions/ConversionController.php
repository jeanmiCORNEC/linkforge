<?php

namespace App\Http\Controllers\Conversions;

use App\Http\Controllers\Controller;
use App\Models\Conversion;
use App\Support\Features\FeatureManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ConversionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $featureScope = FeatureManager::for($user);
        $canAccessConversions = $featureScope->allows('conversions.access');

        $filters = [
            'status' => $request->string('status')->lower()->value() ?: null,
            'days'   => (int) $request->input('days', 30),
        ];

        $filters['days'] = max(1, min(90, $filters['days']));

        $conversions = collect();

        if ($canAccessConversions) {
            $query = Conversion::query()
                ->with(['trackedLink.link', 'trackedLink.source.campaign'])
                ->where('user_id', $user->id)
                ->latest();

            if ($filters['status']) {
                $query->where('status', $filters['status']);
            }

            if ($filters['days']) {
                $query->where('created_at', '>=', now()->subDays($filters['days']));
            }

            $conversions = $query
                ->limit(50)
                ->get()
                ->map(function (Conversion $conversion) {
                    $tracked = $conversion->trackedLink;
                    $source = $tracked?->source;
                    $campaign = $source?->campaign;
                    $link = $tracked?->link;

                    return [
                        'id'           => $conversion->id,
                        'order_id'     => $conversion->order_id,
                        'status'       => $conversion->status,
                        'revenue'      => (float) $conversion->revenue,
                        'commission'   => (float) $conversion->commission,
                        'currency'     => $conversion->currency,
                        'tracking_key' => $tracked?->tracking_key,
                        'link_title'   => $link?->title,
                        'source_name'  => $source?->name,
                        'campaign'     => $campaign?->name,
                        'recorded_at'  => optional($conversion->created_at)->toDateTimeString(),
                    ];
                });
        }

        return Inertia::render('Conversions/Index', [
            'conversions'        => $conversions,
            'filters'            => $filters,
            'availableStatuses'  => Conversion::STATUSES,
            'canAccess'          => $canAccessConversions,
        ]);
    }

    public function updateStatus(Request $request, Conversion $conversion): RedirectResponse
    {
        $user = $request->user();

        if ($conversion->user_id !== $user->id) {
            abort(403);
        }

        if (! FeatureManager::for($user)->allows('conversions.access')) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(Conversion::STATUSES)],
        ]);

        $conversion->status = $data['status'];
        $conversion->save();

        return back()->with('status', 'conversion-updated');
    }
}
