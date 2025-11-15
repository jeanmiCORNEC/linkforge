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
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CampaignAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_campaign_analytics(): void
    {
        $user = User::factory()->create();

        $campaign = Campaign::factory()->for($user)->create();

        $source = Source::factory()
            ->for($user)
            ->for($campaign)
            ->create();

        $link = Link::factory()
            ->for($user)
            ->create();

        $trackedLink = TrackedLink::factory()
            ->for($user)
            ->for($source)
            ->for($link)
            ->create();

        // 2 clics dont 1 visiteur unique
        Click::factory()->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'abc',
        ]);

        Click::factory()->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'abc', // même visiteur
        ]);

        $this->actingAs($user);

        $response = $this->get(route('campaigns.analytics.show', [
            'campaign' => $campaign->id,
            'days'     => 7,
        ]));

        $response->assertOk();

                $response->assertInertia(fn (Assert $page) => $page
            ->component('Campaigns/Analytics')
            ->where('campaign.id', $campaign->id)
            ->where('stats.totalClicks', 2)
            ->where('stats.uniqueVisitors', 1)
        );

    }

    #[Test]
    public function user_cannot_view_someone_else_campaign_analytics(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();

        $campaign = Campaign::factory()->for($other)->create();

        $this->actingAs($user);

        $response = $this->get(route('campaigns.analytics.show', $campaign));

        $response->assertStatus(403);
    }
}
