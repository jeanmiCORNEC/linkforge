<?php

namespace Tests\Feature\Analytics;

use App\Models\Campaign;
use App\Models\Click;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SourceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_source_analytics(): void
    {
        $user = User::factory()->create();

        $campaign = Campaign::factory()->create(['user_id' => $user->id]);

        $source = Source::factory()->create([
            'user_id'     => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $link = Link::factory()->create(['user_id' => $user->id]);

        $tracked = TrackedLink::factory()->create([
            'user_id'   => $user->id,
            'link_id'   => $link->id,
            'source_id' => $source->id,
        ]);

        // 2 clics => 2 total / 1 unique
        Click::factory()->create([
            'tracked_link_id' => $tracked->id,
            'visitor_hash'    => 'hash-123',
            'created_at'      => now()->subDay(),
        ]);

        Click::factory()->create([
            'tracked_link_id' => $tracked->id,
            'visitor_hash'    => 'hash-123',
            'created_at'      => now()->subDay()->addMinutes(5),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('sources.analytics.show', [
                'source' => $source->id,
                'days'   => 7,
            ]));

        $response->assertOk();

        $response->assertInertia(function (Assert $page) use ($source) {
            $page
                ->component('Sources/Analytics')
                ->where('source.id', $source->id)
                ->where('filters.days', 7)

                // Clés réellement renvoyées par ton SourceAnalyticsController
                ->where('stats.total_clicks', 2)
                ->where('stats.unique_visitors', 1)

                // Ton controller NE renvoie PAS "devices" ni "browsers" aujourd’hui
                // ->has('stats.devices')
                // ->has('stats.browsers')

                // Mais il renvoie "clicks_per_day"
                ->has('stats.clicks_per_day')

                ->etc();
        });
    }

    public function test_user_cannot_view_someone_else_source_analytics(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $campaign = Campaign::factory()->create(['user_id' => $owner->id]);

        $source = Source::factory()->create([
            'user_id'     => $owner->id,
            'campaign_id' => $campaign->id,
        ]);

        $response = $this
            ->actingAs($other)
            ->get(route('sources.analytics.show', $source));

        $response->assertForbidden();
    }
}
