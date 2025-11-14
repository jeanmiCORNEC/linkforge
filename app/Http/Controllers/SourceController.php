<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
            'name'        => ['required', 'string', 'max:255'],
            'platform'    => ['nullable', 'string', 'max:255'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'notes'       => ['nullable', 'string', 'max:2000'],
        ]);

        // On s’assure que la campagne appartient bien à l’utilisateur
        $campaign = Campaign::where('id', $validated['campaign_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $source = $campaign->sources()->create([
            'user_id'     => $user->id,
            'name'        => $validated['name'],
            'platform'    => $validated['platform'] ?? null,
            'external_id' => $validated['external_id'] ?? null,
            'notes'       => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Source créée.',
                'source'  => $source,
            ], 201);
        }

        return back()->with('success', 'Source créée.');
    }
}
