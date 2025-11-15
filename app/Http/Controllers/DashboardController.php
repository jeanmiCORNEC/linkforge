<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\Link;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

            // breakdown devices
            'devices_breakdown' => (clone $clicksQuery)
                ->selectRaw('COALESCE(device, "unknown") as label, COUNT(*) as count')
                ->groupBy('label')
                ->pluck('count', 'label'),

            // breakdown navigateurs
            'browsers_breakdown' => (clone $clicksQuery)
                ->selectRaw('COALESCE(browser, "unknown") as label, COUNT(*) as count')
                ->groupBy('label')
                ->pluck('count', 'label'),
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

        return Inertia::render('Dashboard', [
            'stats'       => $stats,
            'dailyClicks' => $dailyClicks,
        ]);
    }
}
