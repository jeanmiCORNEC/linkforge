<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly Digest: Every Monday at 9:00 AM
Schedule::command('emails:weekly-digest')->weeklyOn(1, '09:00');

// Zero Click Alert: Daily at 10:00 AM
Schedule::command('emails:zero-click-alert')->dailyAt('10:00');
