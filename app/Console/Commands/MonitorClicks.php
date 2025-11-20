<?php

namespace App\Console\Commands;

use App\Models\Click;
use App\Models\TrackedLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MonitorClicks extends Command
{
    protected $signature = 'clicks:monitor {--since= : Lookback en minutes (défaut: env CLICK_MONITOR_LOOKBACK_MINUTES=60)}';

    protected $description = 'Alerte si aucun clic récent n’est enregistré (surveillance de la collecte).';

    public function handle(): int
    {
        $lookbackMinutes = (int) ($this->option('since') ?: config('tracking.click_monitor_lookback', 60));
        $threshold       = Carbon::now()->subMinutes($lookbackMinutes);

        $trackedLinks = TrackedLink::count();
        if ($trackedLinks === 0) {
            $this->info('Aucun tracked_link : pas de monitoring nécessaire.');
            return self::SUCCESS;
        }

        $lastClick = Click::latest('created_at')->first();
        if (! $lastClick) {
            $this->warn('ALERTE: aucun clic en base alors que des tracked_links existent.');
            Log::warning('Clicks monitor: no clicks yet but tracked_links exist', [
                'tracked_links' => $trackedLinks,
            ]);
            return self::SUCCESS;
        }

        if ($lastClick->created_at < $threshold) {
            $this->warn("ALERTE: aucun nouveau clic depuis {$lastClick->created_at->diffForHumans()}");
            Log::warning('Clicks monitor: no recent clicks', [
                'tracked_links' => $trackedLinks,
                'last_click_at' => $lastClick->created_at,
                'lookback_minutes' => $lookbackMinutes,
            ]);
        } else {
            $this->info('Rien à signaler : des clics récents sont présents.');
        }

        return self::SUCCESS;
    }
}
