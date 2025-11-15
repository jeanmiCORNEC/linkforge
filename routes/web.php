<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\LinkAnalyticsController;


// Landing publique (Inertia)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
})->name('welcome');

// Routes d'auth Breeze
require __DIR__ . '/auth.php';

// Routes auth + email vérifié
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (Inertia via contrôleur)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/links', [LinkController::class, 'index'])
        ->name('links.index');

    // Liens trackés (Inertia form)
    Route::post('/links', [LinkController::class, 'store'])
        ->name('links.store');
    Route::patch('/links/{link}/toggle', [LinkController::class, 'toggle'])
        ->name('links.toggle');
    Route::match(['put', 'patch'], '/links/{link}', [LinkController::class, 'update'])
        ->name('links.update');
    Route::delete('/links/{link}', [LinkController::class, 'destroy'])
        ->name('links.destroy');
        Route::get('/links/{link}/analytics', [LinkAnalyticsController::class, 'show'])
        ->name('links.analytics.show');

    // Campagnes
    Route::get('/campaigns', [CampaignController::class, 'index'])
        ->name('campaigns.index');
    Route::post('/campaigns', [CampaignController::class, 'store'])
        ->name('campaigns.store');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])
        ->name('campaigns.update');
    Route::patch('/campaigns/{campaign}/archive', [CampaignController::class, 'archive'])
        ->name('campaigns.archive');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])
        ->name('campaigns.destroy');


    // Sources (création)
    Route::get('/sources', [SourceController::class, 'index'])
        ->name('sources.index');
    Route::post('/sources', [SourceController::class, 'store'])
        ->name('sources.store');
    Route::put('/sources/{source}', [SourceController::class, 'update'])
    ->name('sources.update');
    Route::patch('/sources/{source}/toggle', [SourceController::class, 'toggle'])
    ->name('sources.toggle');
    Route::delete('/sources/{source}', [SourceController::class, 'destroy'])
    ->name('sources.destroy');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Route publique de redirection courte (pas Inertia, simple redirect HTTP)
Route::get('/l/{tracking_key}', [LinkController::class, 'redirect'])
    ->name('links.redirect');
