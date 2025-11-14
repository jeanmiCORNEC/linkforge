<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $campaigns = Campaign::query()
            ->where('user_id', $user->id)
            ->with([
                'sources' => function ($query) {
                    $query->orderBy('name');
                },
            ])
            ->withCount('sources')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'notes'     => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $user->campaigns()->create($validated);

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campagne créée.');
    }
}
