<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\Click;
use Inertia\Inertia;

class LinkController extends Controller
{
    /**
     * Vérifie que le lien appartient bien à l'utilisateur connecté.
     */
    protected function ensureOwner(Request $request, Link $link): void
    {
        if ($link->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:2048'],
        ]);

        $user = $request->user();

        // 1) Création du lien
        $link = $user->links()->create([
            'title'           => $validated['title'],
            'destination_url' => $validated['destination_url'],
            'slug'            => Str::uuid()->toString(), // ou autre logique plus tard
            'is_active'       => true,
        ]);

        // 2) Création du tracked_link par défaut (sans source pour l’instant)
        $trackedLink = $user->trackedLinks()->create([
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => Str::random(10),
        ]);

        // 3) Si c'est une requête API (Accept: application/json) → on garde l’ancienne réponse JSON
        if ($request->wantsJson()) {
            return response()->json([
                'message'      => 'Link created',
                'tracked_link' => $trackedLink,
            ], 201);
        }

        // 4) Si c'est une requête Inertia (notre cas) → redirection vers la page Liens
        return redirect()
            ->route('links.index')
            ->with('success', 'Lien créé avec succès.');
    }

    public function update(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:2048'],
            'is_active'       => ['nullable', 'boolean'],
        ]);

        $link->update([
            'title'           => $validated['title'],
            'destination_url' => $validated['destination_url'],
            'is_active'       => $validated['is_active'] ?? true,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Lien mis à jour.',
                'link'    => $link->fresh('trackedLinks'),
            ]);
        }

        return back()->with('success', 'Lien mis à jour.');
    }
    
    public function destroy(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $link->delete(); // soft delete grâce au trait SoftDeletes

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Lien supprimé.'], 204);
        }

        return back()->with('success', 'Lien supprimé.');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $links = Link::query()
            ->where('user_id', $user->id)
            ->with(['trackedLinks'])
            ->withCount('clicks')      // clicks_count
            ->latest()
            ->paginate(15);

        return Inertia::render('Links/Index', [
            'links' => $links,
        ]);
    }

    public function redirect($tracking_key)
    {
        // 1. Retrouver la tracked_link
        $tracked = TrackedLink::with('link')->where('tracking_key', $tracking_key)->firstOrFail();

        // 2. Log du clic
        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => request()->ip(),
            'user_agent'      => request()->userAgent(),
            'referrer'        => request()->headers->get('referer'),
        ]);

        // 3. Redirection finale
        return redirect()->away($tracked->link->destination_url);
    }
}
