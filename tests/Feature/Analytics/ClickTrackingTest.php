<?php

namespace Tests\Feature\Analytics;

use App\Models\Click;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\User;
use App\Support\Geo\GeoLocator;
use App\Support\Geo\GeoLookupResult;
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

        // On force ici notre GeoLocator pour contrôler la réponse MaxMind.
        $this->app->singleton(GeoLocator::class, fn () => new class extends GeoLocator {
            public function lookup(?string $ip): GeoLookupResult
            {
                return new GeoLookupResult('FR', 'France', 'Paris');
            }
        });

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR'      => $ip,
                'HTTP_USER_AGENT'  => $userAgent,
            ])
            ->get(route('links.redirect', ['code' => $tracked->short_code]));

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
        $this->assertEquals('FR', $click->country);
        $this->assertEquals('Paris', $click->city);
    }
}
