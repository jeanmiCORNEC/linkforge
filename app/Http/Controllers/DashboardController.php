<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Link;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $links = Link::withCount('trackedLinks')
            ->where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get();

        return Inertia::render('Dashboard', [
            'links' => $links,
        ]);
    }
}
