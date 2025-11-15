<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SourceTrackedLinkController extends Controller
{
    public function store(Request $request, Source $source)
    {
        $user = $request->user();

        // Sécurité : la source doit appartenir à l'utilisateur
        if ($source->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'link_id' => ['required', 'integer', 'exists:links,id'],
        ]);

        // Le lien doit aussi appartenir à l'utilisateur
        $link = Link::where('user_id', $user->id)
            ->where('id', $validated['link_id'])
            ->firstOrFail();

        // Option : éviter les doublons (source + link)
        $existing = TrackedLink::where('user_id', $user->id)
            ->where('source_id', $source->id)
            ->where('link_id', $link->id)
            ->first();

        if ($existing) {
            return back()->with('success', 'Un lien tracké existe déjà pour cette source et ce lien.');
        }

        TrackedLink::create([
            'user_id'      => $user->id,
            'source_id'    => $source->id,
            'link_id'      => $link->id,
            'tracking_key' => Str::random(10),
        ]);

        return back()->with('success', 'Lien tracké créé pour cette source.');
    }

    public function destroy(Request $request, Source $source, TrackedLink $trackedLink)
    {
        $user = $request->user();

        if ($source->user_id !== $user->id || $trackedLink->user_id !== $user->id) {
            abort(403);
        }

        if ($trackedLink->source_id !== $source->id) {
            abort(403);
        }

        $trackedLink->delete();

        return back()->with('success', 'Lien tracké supprimé.');
    }
}
