<?php

namespace Tests\Feature\Console;

use App\Models\Click;
use App\Models\TrackedLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MonitorClicksCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_warns_when_no_clicks_with_tracked_links(): void
    {
        TrackedLink::factory()->create();

        $this->artisan('clicks:monitor')
            ->assertExitCode(0)
            ->expectsOutputToContain('ALERTE');
    }

    public function test_warns_when_last_click_too_old(): void
    {
        $tracked = TrackedLink::factory()->create();
        Carbon::setTestNow(Carbon::now()->startOfDay());
        Click::factory()->create([
            'tracked_link_id' => $tracked->id,
            'created_at'      => Carbon::now()->subHours(2),
        ]);

        $this->artisan('clicks:monitor --since=30')
            ->assertExitCode(0)
            ->expectsOutputToContain('ALERTE');
    }

    public function test_ok_when_recent_click(): void
    {
        $tracked = TrackedLink::factory()->create();
        Click::factory()->create([
            'tracked_link_id' => $tracked->id,
        ]);

        $this->artisan('clicks:monitor --since=120')
            ->assertExitCode(0)
            ->expectsOutputToContain('Rien à signaler');
    }
}
