<?php

namespace Tests\Feature\Console;

use App\Models\TrackedLink;
use App\Support\Links\ShortCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackfillShortCodesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_backfills_missing_short_codes(): void
    {
        $tracked = TrackedLink::factory()->create();
        // Simule un ancien enregistrement sans short_code
        $tracked->forceFill(['short_code' => null])->saveQuietly();

        $this->artisan('tracked-links:backfill-short-codes')
            ->assertExitCode(0)
            ->expectsOutputToContain('Backfill des short codes manquants…');

        $tracked->refresh();

        $this->assertNotNull($tracked->short_code);
        $this->assertEquals(ShortCode::encode($tracked->id), $tracked->short_code);
    }
}
