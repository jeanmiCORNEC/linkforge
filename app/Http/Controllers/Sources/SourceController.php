<?php

namespace App\Http\Controllers\Sources;

use App\Models\Campaign;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Controllers\Controller;

class SourceController extends Controller
{
    /**
     * Vérifie que la source appartient bien à l'utilisateur connecté.
     */
    protected function ensureOwner(Request $request, Source $source): void
    {
        if ($source->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    /**
     * Liste des campagnes + leurs sources (pour la page Sources).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $campaigns = $user->campaigns()
            ->with(['sources.trackedLinks.link'])
            ->latest()
            ->paginate(10);

        $links = $user->links()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title']);

        return Inertia::render('Sources/Index', [
            'campaigns' => $campaigns,
            'links'     => $links,
        ]);
    }

    /**
     * Création d'une source pour une campagne donnée.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'name'        => ['required', 'string', 'max:255'],
            'platform'    => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string'],
        ]);

        // On vérifie que la campagne appartient bien à l'utilisateur
        $campaign = Campaign::where('id', $validated['campaign_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $source = $campaign->sources()->create([
            'user_id'  => $user->id,
            'name'     => $validated['name'],
            'platform' => $validated['platform'] ?? null,
            'notes'    => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Source créée.',
                'source'  => $source,
            ], 201);
        }

        return back()->with('success', 'Source créée avec succès.');
    }

    /**
     * Mise à jour d'une source (via la modale d'édition).
     */
    public function update(Request $request, Source $source)
    {
        $this->ensureOwner($request, $source);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'platform' => ['nullable', 'string', 'max:255'],
            'notes'    => ['nullable', 'string'],
        ]);

        $source->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Source mise à jour.',
                'source'  => $source->fresh('campaign'),
            ]);
        }

        return back()->with('success', 'Source mise à jour.');
    }

    /**
     * Suppression (soft delete) d'une source.
     */
    public function destroy(Request $request, Source $source)
    {
        $this->ensureOwner($request, $source);

        $source->delete(); // SoftDeletes sur le modèle Source

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Source supprimée.',
            ]);
        }

        return back()->with('success', 'Source supprimée.');
    }
}
