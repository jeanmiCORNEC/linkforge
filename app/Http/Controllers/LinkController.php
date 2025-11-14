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

    public function toggle(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $link->is_active = !$link->is_active;
        $link->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Statut mis à jour',
                'is_active' => $link->is_active,
            ]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }


    public function destroy(Request $request, Link $link)
    {
        $this->ensureOwner($request, $link);

        $link->delete(); // soft delete grâce au trait SoftDeletes

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Lien supprimé.'], 200);
        }

        return back()->with('success', 'Lien supprimé.');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Link::query()
            ->where('user_id', $user->id)
            ->with(['trackedLinks'])
            ->withCount('clicks')
            ->latest();

        // petit filtre statut 
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $links = $query->paginate(15)->withQueryString();

        return Inertia::render('Links/Index', [
            'links'   => $links,
            'filters' => [
                'status' => $status ?? 'all',
            ],
        ]);
    }

    public function redirect(Request $request, string $tracking_key)
    {
        // 1. Retrouver la tracked_link + son lien associé
        $tracked = TrackedLink::with(['link' => function ($q) {
            $q->withTrashed(); // au cas où, pour pouvoir tester is_active + deleted_at
        }])->where('tracking_key', $tracking_key)->firstOrFail();

        $link = $tracked->link;

        // 2. Si le lien n’existe plus, est soft-deleted ou inactif → 404
        if (! $link || $link->trashed() || ! $link->is_active) {
            abort(404); // plus tard on pourra faire une jolie page "Lien expiré"
        }

        // 3. Log du clic uniquement si ça vaut le coup (anti-bot / anti-spam)
        if ($this->shouldTrackClick($request, $tracked)) {
            Click::create([
                'tracked_link_id' => $tracked->id,
                'ip_address'      => $request->ip(),
                'user_agent'      => $request->userAgent(),
                'referrer'        => $request->headers->get('referer'),
            ]);
        }

        // 4. Redirection finale vers le lien d’affiliation
        return redirect()->away($link->destination_url);
    }

    /**
     * Détermine si on doit enregistrer ce clic ou l’ignorer
     * (pour éviter les bots et le spam de refresh).
     */
    protected function shouldTrackClick(Request $request, TrackedLink $tracked): bool
    {
        $userAgent = (string) $request->userAgent();

        // 1) UA vide = souvent suspect
        if ($userAgent === '') {
            return false;
        }

        // 2) Bots évidents
        if ($this->isBot($userAgent)) {
            return false;
        }

        // 3) Anti-spam : 1 clic par IP / tracking_key toutes les 5 minutes
        $ip = $request->ip() ?? 'unknown';

        $recentClickExists = Click::where('tracked_link_id', $tracked->id)
            ->where('ip_address', $ip)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        if ($recentClickExists) {
            return false;
        }

        return true;
    }

    /**
     * Détection très basique des bots via user-agent.
     * (on pourra raffiner plus tard si besoin).
     */
    protected function isBot(string $userAgent): bool
    {
        $ua = strtolower($userAgent);

        $botFragments = [
            'bot',
            'crawler',
            'crawl',
            'spider',
            'slurp',
            'facebookexternalhit',
            'pingdom',
            'discordbot',
            'twitterbot',
            'whatsapp',
        ];

        foreach ($botFragments as $fragment) {
            if (str_contains($ua, $fragment)) {
                return true;
            }
        }

        return false;
    }
}
