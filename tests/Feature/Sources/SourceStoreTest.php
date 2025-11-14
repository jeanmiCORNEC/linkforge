<?php

namespace Tests\Feature\Sources;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_source_for_his_campaign(): void
    {
        $user     = User::factory()->create();
        $campaign = Campaign::factory()->create([
            'user_id' => $user->id,
        ]);

        $payload = [
            'campaign_id' => $campaign->id,
            'name'        => 'TikTok bio',
            'platform'    => 'TikTok',
            'notes'       => 'Lien principal',
        ];

        $response = $this->actingAs($user)->post('/sources', $payload);

        $response->assertRedirect(); // back()
        $this->assertDatabaseHas('sources', [
            'campaign_id' => $campaign->id,
            'user_id'     => $user->id,
            'name'        => 'TikTok bio',
        ]);
    }

    public function test_user_cannot_create_source_on_someone_else_campaign(): void
    {
        $owner    = User::factory()->create();
        $intruder = User::factory()->create();

        $campaign = Campaign::factory()->create([
            'user_id' => $owner->id,
        ]);

        $payload = [
            'campaign_id' => $campaign->id,
            'name'        => 'Newsletter',
        ];

        $response = $this->actingAs($intruder)->post('/sources', $payload);

        // Notre contrôleur fait un firstOrFail => 404
        $response->assertNotFound();

        $this->assertDatabaseMissing('sources', [
            'campaign_id' => $campaign->id,
            'name'        => 'Newsletter',
        ]);
    }
}
