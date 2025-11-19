<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\Click;
use App\Models\Campaign;
use App\Models\Source;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_dashboard_is_displayed_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $campaign = Campaign::factory()->for($user)->create();
        $source = Source::factory()->for($user)->for($campaign)->create();
        $link = Link::factory()->for($user)->create();
        $tracked = TrackedLink::factory()->for($user)->for($link)->for($source)->create();

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '127.0.0.1',
            'user_agent'      => 'Mozilla/5.0',
            'referrer'        => null,
            'device'          => 'desktop',
            'browser'         => 'Chrome',
            'visitor_hash'    => 'hash-dashboard',
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('stats')
            ->has('dailyClicks')
            ->has('hourlyHeatmap')
            ->has('topCampaigns')
            ->has('topSources')
            ->has('topLinks')
            ->has('globalDelta'));
    }
}
