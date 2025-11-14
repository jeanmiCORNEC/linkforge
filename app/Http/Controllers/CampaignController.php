<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignController extends Controller
{
    /**
     * Vérifie que la campagne appartient bien à l'utilisateur.
     */
    protected function ensureOwner(Request $request, Campaign $campaign): void
    {
        if ($campaign->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $campaigns = Campaign::query()
            ->where('user_id', $user->id)
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
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $campaign = $request->user()->campaigns()->create([
            'name'    => $validated['name'],
            'notes'   => $validated['notes'] ?? null,
            'status'  => 'active',
            'starts_at' => null,
            'ends_at'   => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Campaign created',
                'campaign' => $campaign,
            ], 201);
        }

        return redirect()
            ->route('campaigns.index')
            ->with('success', 'Campagne créée avec succès.');
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->ensureOwner($request, $campaign);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'notes'     => ['nullable', 'string'],
            'status'    => ['required', 'in:active,archived'],
            'starts_at' => ['nullable', 'date'],
            'ends_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $campaign->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Campaign updated',
                'campaign' => $campaign->fresh('sources'),
            ]);
        }

        return back()->with('success', 'Campagne mise à jour.');
    }

    public function archive(Request $request, Campaign $campaign)
    {
        $this->ensureOwner($request, $campaign);

        $campaign->status = $campaign->status === 'archived' ? 'active' : 'archived';
        $campaign->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status toggled',
                'status'  => $campaign->status,
            ]);
        }

        return back()->with('success', 'Statut de la campagne mis à jour.');
    }

    public function destroy(Request $request, Campaign $campaign)
    {
        $this->ensureOwner($request, $campaign);

        $campaign->delete(); // Soft delete

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Campaign deleted'], 200);
        }

        return back()->with('success', 'Campagne supprimée.');
    }
}
