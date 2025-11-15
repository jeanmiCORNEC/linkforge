<?php

namespace Tests\Feature\Analytics;

use App\Models\Click;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_link_analytics(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id' => $user->id,
        ]);

        $trackedLink = TrackedLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        // Quelques clics sur plusieurs jours
        Click::factory()->count(3)->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'hash-1',
            'created_at'      => now()->subDays(1),
        ]);

        Click::factory()->count(2)->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'hash-2',
            'created_at'      => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)
            ->get(route('links.analytics.show', $link));

        $response->assertOk()
            ->assertInertia(fn ($page) =>
                $page
                    ->component('Links/Analytics')
                    ->where('link.id', $link->id)
                    ->has('stats.totalClicks')
                    ->has('stats.uniqueVisitors')
                    ->has('stats.devices')
                    ->has('stats.browsers')
                    ->has('stats.clicksPerDay')
            );
    }
}
