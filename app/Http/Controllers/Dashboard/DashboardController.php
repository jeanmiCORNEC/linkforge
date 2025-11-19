<?php

namespace App\Http\Controllers\Dashboard;

use DB;
use App\Models\Link;
use Inertia\Inertia;
use App\Models\Click;
use App\Models\Source;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Support\Features\FeatureManager;
use App\Support\Analytics\ClickAnalytics;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Base query pour tous les clics de l'utilisateur
        $clicksQuery = Click::whereHas('trackedLink', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // --- Stats globales du compte ---
        $stats = [
            'links_count'     => Link::where('user_id', $user->id)->count(),
            'campaigns_count' => Campaign::where('user_id', $user->id)->count(),
            'sources_count'   => Source::where('user_id', $user->id)->whereNull('deleted_at')->count(),
            'clicks_count'    => (clone $clicksQuery)->count(),

            // visiteurs uniques = distinct visitor_hash non nul
            'unique_visitors' => (clone $clicksQuery)
                ->whereNotNull('visitor_hash')
                ->distinct('visitor_hash')
                ->count('visitor_hash'),

            // nombre de pays distincts (quand on aura la géoloc)
            'countries_count' => (clone $clicksQuery)
                ->whereNotNull('country')
                ->distinct('country')
                ->count('country'),
        ];

        // --- Clics des 7 derniers jours (aujourd'hui inclus) ---
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        $raw = (clone $clicksQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date'); // ['2025-11-09' => 12, ...]

        $dailyClicks = [];

        for ($i = 0; $i < 7; $i++) {
            $day = (clone $start)->addDays($i);
            $key = $day->toDateString();

            $dailyClicks[] = [
                'date'  => $day->format('Y-m-d'),
                'label' => $day->format('d/m'),
                'count' => (int) ($raw[$key] ?? 0),
            ];
        }

        $featureScope = FeatureManager::for($user);

        $rangeDays = 7;
        $insights = ClickAnalytics::withInsights($clicksQuery, $rangeDays, [
            ...($featureScope->allows('analytics.heatmap') ? ['heatmap'] : []),
            ...($featureScope->allows('analytics.top_lists') ? ['sources', 'links'] : []),
        ]);
        $deltas   = $featureScope->allows('analytics.deltas')
            ? ($insights['delta'] ?? ['totalClicks' => 0, 'uniqueVisitors' => 0])
            : ['totalClicks' => 0, 'uniqueVisitors' => 0];

        $topCampaigns = [];

        if ($featureScope->allows('analytics.top_lists')) {
            $topCampaigns = (clone $clicksQuery)
                ->join('tracked_links as tl_top', 'tl_top.id', '=', 'clicks.tracked_link_id')
                ->join('sources as s_top', 's_top.id', '=', 'tl_top.source_id')
                ->join('campaigns', 'campaigns.id', '=', 's_top.campaign_id')
                ->select('campaigns.id', 'campaigns.name', 'campaigns.status', \DB::raw('count(*) as total'))
                ->groupBy('campaigns.id', 'campaigns.name', 'campaigns.status')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
        }

        $stats['devices_breakdown'] = $insights['devices'] ?? [];
        $stats['browsers_breakdown'] = $insights['browsers'] ?? [];
        $stats['top_countries'] = $insights['topCountries'] ?? [];

        return Inertia::render('Dashboard', [
            'stats'          => $stats,
            'dailyClicks'    => $dailyClicks,
            'hourlyHeatmap'  => $featureScope->allows('analytics.heatmap')
                ? ($insights['hourlyHeatmap'] ?? [])
                : [],
            'topCampaigns'   => $topCampaigns,
            'topSources'     => $featureScope->allows('analytics.top_lists')
                ? ($insights['topSources'] ?? [])
                : [],
            'topLinks'       => $featureScope->allows('analytics.top_lists')
                ? ($insights['topLinks'] ?? [])
                : [],
            'globalDelta'    => [
                'clicks'          => $deltas['totalClicks'] ?? 0,
                'unique_visitors' => $deltas['uniqueVisitors'] ?? 0,
            ],
            'features' => [
                'exports'  => $featureScope->allows('analytics.exports'),
                'heatmap'  => $featureScope->allows('analytics.heatmap'),
                'topLists' => $featureScope->allows('analytics.top_lists'),
                'deltas'   => $featureScope->allows('analytics.deltas'),
            ],
        ]);
    }
}
