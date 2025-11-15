<?php

namespace App\Http\Controllers\Links;

use App\Models\Link;
use App\Support\Analytics\ClickAnalytics;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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

        // Nombre de jours : 1 à 365, valeur par défaut = 7
        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }
        if ($days > 365) {
            $days = 365;
        }

        // On part de la relation hasManyThrough déjà définie sur Link
        $clicksQuery = $link->clicks();

        $stats = ClickAnalytics::forPeriod($clicksQuery, $days);

        return Inertia::render('Links/Analytics', [
            'link' => [
                'id'               => $link->id,
                'title'            => $link->title,
                'destination_url'  => $link->destination_url,
                // on garde le slug si tu en as besoin ailleurs
                'slug'             => $link->slug,
            ],
            'stats' => $stats,
            'filters' => [
                'days' => $days,
            ],
        ]);
    }
}
