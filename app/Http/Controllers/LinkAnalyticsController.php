<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Support\Analytics\ClickAnalytics;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkAnalyticsController extends Controller
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

    public function show(Request $request, Link $link)
    {
        // Sécurité : propriétaire du lien
        $this->ensureOwner($request, $link);

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }

        // On part de la relation hasManyThrough déjà définie sur Link
        $clicksQuery = $link->clicks();

        $stats = ClickAnalytics::forPeriod($clicksQuery, $days);

        return Inertia::render('Links/Analytics', [
            'link' => [
                'id'    => $link->id,
                'title' => $link->title,
                'slug'  => $link->slug,
            ],
            'stats' => $stats,
            'filters' => [
                'days' => $days,
            ],
        ]);
    }
}
