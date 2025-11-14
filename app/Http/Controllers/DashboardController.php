<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\Link;
use App\Models\Source;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // --- Stats globales du compte ---
        $stats = [
            'links_count'     => Link::where('user_id', $user->id)->count(),
            'campaigns_count' => Campaign::where('user_id', $user->id)->count(),
            'sources_count'   => Source::where('user_id', $user->id)->count(),
            'clicks_count'    => Click::whereHas('trackedLink', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
        ];

        // --- Clics des 7 derniers jours (aujourd'hui inclus) ---
        $start = Carbon::now()->subDays(6)->startOfDay();
        $end   = Carbon::now()->endOfDay();

        // SELECT DATE(created_at), COUNT(*) ... GROUP BY DATE(created_at)
        $raw = Click::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('trackedLink', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date'); // ['2025-11-09' => 12, ...]

        $dailyClicks = [];

        for ($i = 0; $i < 7; $i++) {
            $day = (clone $start)->addDays($i);
            $key = $day->toDateString();

            $dailyClicks[] = [
                'date'   => $day->format('Y-m-d'),
                'label'  => $day->format('d/m'),
                'count'  => (int) ($raw[$key] ?? 0),
            ];
        }

        return Inertia::render('Dashboard', [
            'stats'       => $stats,
            'dailyClicks' => $dailyClicks,
        ]);
    }
}
