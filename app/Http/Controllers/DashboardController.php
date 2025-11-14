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

        $links = Link::query()
            ->where('user_id', $user->id)
            ->with(['trackedLinks'])   // pour afficher le lien court
            ->withCount('clicks')      // => ajoute clicks_count
            ->latest()
            ->take(50)
            ->get();

        return Inertia::render('Dashboard', [
            'links' => $links,
        ]);
    }
}
