<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        App\Console\Commands\UpdateGeoDatabase::class,
        App\Console\Commands\BackfillTrackedLinkShortCodes::class,
        App\Console\Commands\MonitorClicks::class,
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('geo:maxmind-update')->monthlyOn(1, '03:00')->timezone('UTC');
        $schedule->command('clicks:monitor')->hourlyAt(5);
    })
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceHttps::class,
        ], append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
