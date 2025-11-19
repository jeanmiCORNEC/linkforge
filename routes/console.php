<?php

use App\Support\Affiliations\AffiliateSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('integrations:sync {platform?}', function (AffiliateSyncService $syncService) {
    $platform = $this->argument('platform');
    $window   = (int) config('affiliate.sync_window_days', 7);
    $since    = now()->subDays($window);
    $until    = now();

    $count = $syncService->syncAll($since, $until, $platform);

    $this->info("{$count} conversions synchronisées.");
})->purpose('Synchroniser les conversions provenant des plateformes affiliées');
