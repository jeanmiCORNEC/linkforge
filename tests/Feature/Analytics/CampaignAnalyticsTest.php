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
        $user = User::factory()->create(['plan' => 'pro']);

        $campaign = Campaign::factory()->for($user)->create();

        $firstSource = Source::factory()
            ->for($user)
            ->for($campaign)
            ->create(['name' => 'Bio TikTok']);

        $secondSource = Source::factory()
            ->for($user)
            ->for($campaign)
            ->create(['name' => 'Newsletter']);

        $firstLink = Link::factory()
            ->for($user)
            ->create(['title' => 'Lien TikTok']);

        $secondLink = Link::factory()
            ->for($user)
            ->create(['title' => 'Lien Newsletter']);

        $firstTracked = TrackedLink::factory()
            ->for($user)
            ->for($firstSource)
            ->for($firstLink)
            ->create();

        $secondTracked = TrackedLink::factory()
            ->for($user)
            ->for($secondSource)
            ->for($secondLink)
            ->create();

        // Source 1 : 2 clics sur 2 jours différents
        Click::factory()->create([
            'tracked_link_id' => $firstTracked->id,
            'visitor_hash'    => 'hash-a1',
            'created_at'      => now(),
        ]);

        Click::factory()->create([
            'tracked_link_id' => $firstTracked->id,
            'visitor_hash'    => 'hash-a2',
            'created_at'      => now()->subDay(),
        ]);

        // Source 2 : 3 clics -> doit être en tête
        Click::factory()->create([
            'tracked_link_id' => $secondTracked->id,
            'visitor_hash'    => 'hash-b1',
            'created_at'      => now(),
        ]);

        Click::factory()->create([
            'tracked_link_id' => $secondTracked->id,
            'visitor_hash'    => 'hash-b2',
            'created_at'      => now(),
        ]);

        Click::factory()->create([
            'tracked_link_id' => $secondTracked->id,
            'visitor_hash'    => 'hash-b3',
            'created_at'      => now()->subDays(2),
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
            ->where('stats.totalClicks', 5)
            ->where('stats.uniqueVisitors', 5)
            ->has('stats.delta')
            ->has('stats.topSources')
            ->where('stats.topSources.0.name', $secondSource->name)
            ->where('stats.topSources.0.total', 3)
            ->has('stats.topLinks')
            ->where('stats.topLinks.0.title', $secondLink->title)
            ->has('stats.topDays')
            ->where('stats.topDays.0.total', 3)
            ->has('stats.hourlyHeatmap')
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

    #[Test]
    public function user_can_export_campaign_analytics_csv(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        $campaign = Campaign::factory()->for($user)->create();

        $source = Source::factory()->for($user)->for($campaign)->create();

        $link = Link::factory()->for($user)->create();

        $trackedLink = TrackedLink::factory()
            ->for($user)
            ->for($source)
            ->for($link)
            ->create();

        Click::factory()->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'csv-campaign',
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($user)->get(route('campaigns.analytics.export', [
            'campaign' => $campaign->id,
            'days'     => 7,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = array_map('str_getcsv', array_filter(explode("\n", trim($response->getContent()))));
        $headers = array_shift($rows);
        $data = array_combine($headers, $rows[0]);

        $this->assertArrayNotHasKey('conversions', $data);
    }

    #[Test]
    public function user_can_export_campaign_raw_clicks(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        $campaign = Campaign::factory()->for($user)->create();
        $source   = Source::factory()->for($user)->for($campaign)->create();
        $link     = Link::factory()->for($user)->create();
        $tracked  = TrackedLink::factory()->for($user)->for($source)->for($link)->create();

        Click::factory()->create([
            'tracked_link_id' => $tracked->id,
            'visitor_hash'    => 'raw-campaign',
            'created_at'      => now(),
        ]);

        $response = $this->actingAs($user)->get(route('campaigns.analytics.export-raw', [
            'campaign' => $campaign->id,
            'days'     => 7,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('raw-campaign', $response->getContent());
    }

    #[Test]
    public function free_plan_cannot_export_campaign_raw_clicks(): void
    {
        $user     = User::factory()->create(['plan' => 'free']);
        $campaign = Campaign::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('campaigns.analytics.export-raw', [
                'campaign' => $campaign->id,
                'days'     => 7,
            ]))
            ->assertForbidden();
    }
}
