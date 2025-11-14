<?php

namespace Tests\Feature\Sources;

use App\Models\Campaign;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourcesCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_sources_index(): void
    {
        $response = $this->get('/sources');

        $response->assertRedirect('/login');
    }

    public function test_user_can_update_source(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->for($user)->create();

        $source = Source::factory()->create([
            'user_id'     => $user->id,
            'campaign_id' => $campaign->id,
            'name'        => 'Ancien nom',
        ]);

        $payload = [
            'name'     => 'Nouveau nom',
            'platform' => 'YouTube',
            'notes'    => 'Note modifiée',
        ];

        $response = $this
            ->actingAs($user)
            ->put(route('sources.update', $source), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('sources', [
            'id'        => $source->id,
            'user_id'   => $user->id,
            'name'      => 'Nouveau nom',
            'platform'  => 'YouTube',
            'notes'     => 'Note modifiée',
            'deleted_at'=> null,
        ]);
    }

    public function test_user_can_soft_delete_source(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->for($user)->create();

        $source = Source::factory()->create([
            'user_id'     => $user->id,
            'campaign_id' => $campaign->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('sources.destroy', $source));

        $response->assertRedirect();

        $this->assertSoftDeleted('sources', [
            'id'      => $source->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_sees_only_his_sources_via_campaigns(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $campaignUser = Campaign::factory()->for($user)->create();
        $campaignOther = Campaign::factory()->for($other)->create();

        Source::factory()->create([
            'user_id'     => $user->id,
            'campaign_id' => $campaignUser->id,
            'name'        => 'Ma source',
        ]);

        Source::factory()->create([
            'user_id'     => $other->id,
            'campaign_id' => $campaignOther->id,
            'name'        => 'Source Autre',
        ]);

        $response = $this->actingAs($user)->get('/sources');

        $response->assertOk();
        $response->assertSee('Ma source');
        $response->assertDontSee('Source Autre');
    }
}
