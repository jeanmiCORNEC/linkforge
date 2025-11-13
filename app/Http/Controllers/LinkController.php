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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'destination_url' => ['required', 'url', 'max:2000'],
        ]);

        $user = $request->user();

        // 1. Créer le lien principal
        $link = Link::create([
            'user_id'        => $user->id,
            'title'          => $validated['title'],
            'destination_url'=> $validated['destination_url'],
            'slug'           => Str::slug($validated['title']),
            'is_active'      => true,
        ]);

        // 2. Créer la version trackée
        $tracked = TrackedLink::create([
            'user_id'      => $user->id,
            'link_id'      => $link->id,
            'source_id'    => null, // null = sans source pour simplifier le MVP
            'tracking_key' => Str::random(10),
        ]);

        return response()->json([
            'message' => 'Link created',
            'tracked_link' => $tracked,
        ]);
    }
    public function index(Request $request)
    {
        $links = $request->user()
            ->links()
            ->with(['trackedLinks' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->latest()
            ->paginate(10)
            ->through(function (Link $link) {
                $defaultTracked = $link->trackedLinks->first();

                $shortUrl = null;

                if ($defaultTracked) {
                    $shortUrl = route('links.redirect', [
                        'tracking_key' => $defaultTracked->tracking_key,
                    ]);
                }

                return [
                    'id'              => $link->id,
                    'title'           => $link->title,
                    'destination_url' => $link->destination_url,
                    'created_at'      => $link->created_at->format('Y-m-d H:i'),
                    'tracking_key'    => $defaultTracked?->tracking_key,
                    'short_url'       => $shortUrl,
                ];
            });

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
