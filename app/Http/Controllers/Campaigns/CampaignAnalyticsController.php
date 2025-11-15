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

        // 🔑 On part de Click et on filtre via trackedLink -> source -> campaign
        $clicksQuery = Click::query()
            ->whereHas('trackedLink.source', function ($query) use ($campaign) {
                $query->where('campaign_id', $campaign->id);
            });

        $stats = ClickAnalytics::forPeriod($clicksQuery, $days);

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
