<?php

namespace App\Console\Commands;

use App\Models\TrackedLink;
use App\Support\Links\ShortCode;
use Illuminate\Console\Command;

class BackfillTrackedLinkShortCodes extends Command
{
    protected $signature = 'tracked-links:backfill-short-codes';

    protected $description = 'Génère un short_code pour tous les tracked_links qui n’en ont pas.';

    public function handle(): int
    {
        $this->info('Backfill des short codes manquants…');

        $updated = 0;

        TrackedLink::query()
            ->whereNull('short_code')
            ->orderBy('id')
            ->chunkById(500, function ($links) use (&$updated) {
                foreach ($links as $trackedLink) {
                    $trackedLink->short_code = ShortCode::encode($trackedLink->id);
                    $trackedLink->saveQuietly();
                    $updated++;
                }
            });

        $this->info("Short codes ajoutés : {$updated}");

        return self::SUCCESS;
    }
}
