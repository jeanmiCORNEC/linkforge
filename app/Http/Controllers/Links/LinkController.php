<?php

namespace App\Http\Controllers\Links;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\Click;
use App\Support\Geo\GeoLocator;
use App\Support\Links\ShortCode;
use App\Support\Plans\PlanLimits;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Jobs\TrackClickJob;

class LinkController extends Controller
{
    public function __construct(
        protected readonly GeoLocator $geoLocator,
    ) {
    }
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

        if ($limit = PlanLimits::linksLimit($user)) {
            $current = $user->links()->count();
            if ($current >= $limit) {
                return back()->withErrors(['link' => "Limite atteinte : {$limit} liens actifs sur le plan Free."]);
            }
        }

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

        // 2bis) Génération d'un short_code lisible (base62)
        $trackedLink->short_code = ShortCode::encode($trackedLink->id);
        $trackedLink->save();

        // 3) Si c'est une requête API (Accept: application/json)
        if ($request->wantsJson()) {
            return response()->json([
                'message'      => 'Link created',
                'tracked_link' => $trackedLink,
            ], 201);
        }

        // 4) Si c'est une requête Inertia (notre cas)
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

        $link->is_active = ! $link->is_active;
        $link->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message'   => 'Statut mis à jour',
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

    public function redirect(Request $request, string $code)
    {
        $cacheKey = "link_redirect_{$code}";

        // 1. Retrouver la tracked_link + le lien associé (short_code prioritaire)
        // On cache le résultat de la requête DB pendant 24h
        // On charge aussi 'link.user' pour récupérer le code de parrainage du créateur
        $tracked = Cache::remember($cacheKey, now()->addDay(), function () use ($code) {
            return TrackedLink::with('link.user')
                ->where('short_code', $code)
                ->orWhere('tracking_key', $code)
                ->first();
        });

        if (! $tracked) {
            abort(404);
        }

        // 2. Si le lien est inactif -> 404
        if (! $tracked->link || ! $tracked->link->is_active) {
            abort(404);
        }

        // 2bis. Attribution du parrainage (Cookie)
        // Si le créateur du lien a un code parrain, on le stocke dans le cookie du visiteur.
        // Ainsi, si le visiteur s'inscrit sur LinkForge après avoir cliqué sur ce lien, le créateur sera son parrain.
        if ($tracked->link->user && $tracked->link->user->referral_code) {
            \Illuminate\Support\Facades\Cookie::queue('linkforge_ref', $tracked->link->user->referral_code, 43200);
        }

        // 3. Décider si on trace ce clic (bots + anti-spam)
        if ($this->shouldTrackClick($request, $tracked)) {
            try {
                // Fire & Forget : On pousse le job dans la queue
                TrackClickJob::dispatch(
                    $tracked->id,
                    $request->ip() ?? '0.0.0.0',
                    (string) $request->userAgent(),
                    $request->headers->get('referer')
                );
            } catch (\Throwable $e) {
                // Si Redis est down, on ne veut pas bloquer la redirection
                Log::error('Failed to dispatch TrackClickJob', [
                    'tracked_link_id' => $tracked->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 4. Redirection finale avec propagation des paramètres GET
        $destination = $tracked->link->destination_url;
        if ($request->query()) {
            $destination = $this->appendQueryParameters($destination, $request->query());
        }

        return redirect()->away($destination);
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

        // 3) Anti-spam : Cache Redis au lieu de DB
        $ip = $request->ip() ?? 'unknown';
        $spamCacheKey = "click_spam:{$tracked->id}:{$ip}";

        if (Cache::has($spamCacheKey)) {
            return false;
        }

        // On marque l'IP comme ayant cliqué pour 5 minutes
        Cache::put($spamCacheKey, true, now()->addMinutes(5));

        return true;
    }

    /**
     * Détection très basique des bots via user-agent.
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

    protected function appendQueryParameters(string $url, array $params): string
    {
        if (empty($params)) {
            return $url;
        }

        $fragment = null;
        if (str_contains($url, '#')) {
            [$url, $fragment] = explode('#', $url, 2);
        }

        $existingQuery = [];
        if (str_contains($url, '?')) {
            [$base, $queryString] = explode('?', $url, 2);
            $url = $base;
            parse_str($queryString, $existingQuery);
        }

        $merged = array_merge($existingQuery, $params);
        $query  = Arr::query($merged);

        $rebuilt = $url;
        if ($query) {
            $rebuilt .= '?' . $query;
        }

        if ($fragment !== null) {
            $rebuilt .= '#' . $fragment;
        }

        return $rebuilt;
    }
}
