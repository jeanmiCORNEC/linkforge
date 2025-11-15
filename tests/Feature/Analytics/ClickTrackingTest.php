<?php

namespace Tests\Feature\Analytics;

use App\Models\Click;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClickTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_logs_enriched_click(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'destination_url' => 'https://example.com',
            'is_active'       => true,
        ]);

        $tracked = TrackedLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $ip        = '1.2.3.4';
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120.0';

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR'      => $ip,
                'HTTP_USER_AGENT'  => $userAgent,
            ])
            ->get(route('links.redirect', ['tracking_key' => $tracked->tracking_key]));

        $response->assertStatus(302);

        $this->assertDatabaseCount('clicks', 1);

        /** @var Click $click */
        $click = Click::first();

        $this->assertEquals($tracked->id, $click->tracked_link_id);
        $this->assertEquals($ip, $click->ip_address);
        $this->assertEquals($userAgent, $click->user_agent);
        $this->assertNotNull($click->device);
        $this->assertNotNull($click->browser);
        $this->assertNotNull($click->visitor_hash);
    }
}
