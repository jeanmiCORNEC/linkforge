<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Links\LinkController;
use App\Http\Controllers\Links\LinkAnalyticsController;
use App\Http\Controllers\Links\SourceTrackedLinkController;
use App\Http\Controllers\Sources\SourceController;
use App\Http\Controllers\Sources\SourceAnalyticsController;
use App\Http\Controllers\Campaigns\CampaignController;
use App\Http\Controllers\Campaigns\CampaignAnalyticsController;
use App\Http\Controllers\Profile\ProfileController;

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
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Liens
    Route::get('/links', [LinkController::class, 'index'])
        ->name('links.index');
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
    Route::get('/links/{link}/analytics/export', [LinkAnalyticsController::class, 'export'])
        ->name('links.analytics.export');
    Route::get('/links/{link}/analytics/export-raw', [LinkAnalyticsController::class, 'exportRaw'])
        ->name('links.analytics.export-raw');

    // Sources + analytics + tracked links
    Route::get('/sources', [SourceController::class, 'index'])
        ->name('sources.index');
    Route::post('/sources', [SourceController::class, 'store'])
        ->name('sources.store');
    Route::put('/sources/{source}', [SourceController::class, 'update'])
        ->name('sources.update');
    Route::delete('/sources/{source}', [SourceController::class, 'destroy'])
        ->name('sources.destroy');

    Route::get('/sources/{source}/analytics', [SourceAnalyticsController::class, 'show'])
        ->name('sources.analytics.show');
    Route::get('/sources/{source}/analytics/export', [SourceAnalyticsController::class, 'export'])
        ->name('sources.analytics.export');
    Route::get('/sources/{source}/analytics/export-raw', [SourceAnalyticsController::class, 'exportRaw'])
        ->name('sources.analytics.export-raw');

    Route::post('/sources/{source}/tracked-links', [SourceTrackedLinkController::class, 'store'])
        ->name('sources.tracked-links.store');
    Route::delete('/sources/{source}/tracked-links/{trackedLink}', [SourceTrackedLinkController::class, 'destroy'])
        ->name('sources.tracked-links.destroy');

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
    Route::get('/campaigns/{campaign}/analytics', [CampaignAnalyticsController::class, 'show'])
        ->name('campaigns.analytics.show');
    Route::get('/campaigns/{campaign}/analytics/export', [CampaignAnalyticsController::class, 'export'])
        ->name('campaigns.analytics.export');
    Route::get('/campaigns/{campaign}/analytics/export-raw', [CampaignAnalyticsController::class, 'exportRaw'])
        ->name('campaigns.analytics.export-raw');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Route publique de redirection courte
Route::get('/l/{tracking_key}', [LinkController::class, 'redirect'])
    ->name('links.redirect');
