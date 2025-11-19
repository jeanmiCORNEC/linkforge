<?php

namespace App\Http\Controllers\Campaigns;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Click;
use App\Support\Analytics\ClickAnalytics;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CampaignAnalyticsController extends Controller
{
    /**
     * Affiche les stats pour une campagne.
     */
    public function show(Request $request, Campaign $campaign)
    {
        $user = $request->user();

        // Sécurité : la campagne doit appartenir à l'utilisateur
        if ($campaign->user_id !== $user->id) {
            abort(403);
        }

        $days = (int) $request->get('days', 7);
        if ($days <= 0) {
            $days = 7;
        }

        // On récupère tous les clics liés à cette campagne via:
        // Click -> trackedLink -> source -> campaign
        $clicksQuery = Click::query()
            ->whereHas('trackedLink.source', function ($query) use ($campaign) {
                $query->where('campaign_id', $campaign->id);
            });

        // Stats génériques + insights campagne (tops sources/liens/jours)
        $stats = ClickAnalytics::forCampaign($clicksQuery, $days);

        // Même fenêtre temporelle que forPeriod()
        $since = now()->subDays($days);

        // Répartition par device
        $stats['devices'] = (clone $clicksQuery)
            ->where('created_at', '>=', $since)
            ->selectRaw("COALESCE(device, 'Unknown') as device, COUNT(*) as total")
            ->groupBy('device')
            ->pluck('total', 'device')
            ->toArray();

        // Répartition par navigateur
        $stats['browsers'] = (clone $clicksQuery)
            ->where('created_at', '>=', $since)
            ->selectRaw("COALESCE(browser, 'Unknown') as browser, COUNT(*) as total")
            ->groupBy('browser')
            ->pluck('total', 'browser')
            ->toArray();

        return Inertia::render('Campaigns/Analytics', [
            'campaign' => [
                'id'   => $campaign->id,
                'name' => $campaign->name,
            ],
            'stats'   => $stats,
            'filters' => [
                'days' => $days,
            ],
        ]);
    }
}
