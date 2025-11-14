<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LinkController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing publique (Inertia)
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Routes d'auth Breeze
require __DIR__.'/auth.php';

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

    Route::put('/links/{link}', [LinkController::class, 'update'])
        ->name('links.update');

    Route::delete('/links/{link}', [LinkController::class, 'destroy'])
        ->name('links.destroy');

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

