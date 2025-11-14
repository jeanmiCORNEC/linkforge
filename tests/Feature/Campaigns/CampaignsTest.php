<?php

namespace Tests\Feature\Campaigns;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_campaigns_index(): void
    {
        $response = $this->get('/campaigns');

        $response->assertRedirect('/login');
    }

    public function test_user_sees_only_his_campaigns_on_index(): void
    {
        $user   = User::factory()->create();
        $other  = User::factory()->create();

        $mine   = Campaign::factory()->create([
            'user_id' => $user->id,
            'name'    => 'Ma campagne',
        ]);

        $others = Campaign::factory()->create([
            'user_id' => $other->id,
            'name'    => 'Campagne de quelqu’un d’autre',
        ]);

        $response = $this->actingAs($user)->get('/campaigns');

        $response->assertOk();
        $response->assertSee('Ma campagne');
        $response->assertDontSee('Campagne de quelqu’un d’autre');
    }

    public function test_user_can_create_campaign(): void
    {
        $user = User::factory()->create();

        $payload = [
            'name'      => 'Campagne ebook',
            'notes'     => 'Test notes',
            'starts_at' => now()->toDateString(),
            'ends_at'   => now()->addDays(7)->toDateString(),
        ];

        $response = $this->actingAs($user)->post('/campaigns', $payload);

        $response->assertRedirect(route('campaigns.index'));

        $this->assertDatabaseHas('campaigns', [
            'user_id' => $user->id,
            'name'    => 'Campagne ebook',
        ]);
    }
    public function test_user_can_archive_campaign(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->for($user)->create(['status' => 'active']);

        $this->actingAs($user)
            ->patch(route('campaigns.archive', $campaign))
            ->assertRedirect();

        $this->assertEquals('archived', $campaign->fresh()->status);
    }
}
