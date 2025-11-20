<?php

namespace Tests\Feature\Quotas;

use App\Models\Campaign;
use App\Models\Link;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotaMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_free_user_cannot_create_more_than_10_links(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Link::factory()->count(10)->create(['user_id' => $user->id]);

        $response = $this
            ->actingAs($user)
            ->post(route('links.store'), [
                'title'           => 'Dépassement',
                'destination_url' => 'https://example.com',
            ]);

        $response->assertSessionHasErrors(['link']);
    }

    public function test_free_user_cannot_create_more_than_2_campaigns(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        Campaign::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this
            ->actingAs($user)
            ->post(route('campaigns.store'), [
                'name'  => 'Dépassement',
                'notes' => '',
            ]);

        $response->assertSessionHasErrors(['campaign']);
    }

    public function test_free_user_cannot_create_more_than_4_sources(): void
    {
        $user = User::factory()->create(['plan' => 'free']);
        $campaign = Campaign::factory()->create(['user_id' => $user->id]);
        Source::factory()->count(4)->create([
            'user_id'     => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('sources.store'), [
                'name'        => 'Dépassement',
                'platform'    => 'tiktok',
                'campaign_id' => $campaign->id,
            ]);

        $response->assertSessionHasErrors(['source']);
    }
}
