<?php

namespace Tests\Feature\Sources;

use App\Models\Campaign;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SourceTrackedLinksTest extends TestCase
{
    use RefreshDatabase;

    public function guest_is_redirected_from_tracked_links_routes(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->for($user)->create();
        $source = Source::factory()->for($user)->for($campaign)->create();
        $link = Link::factory()->for($user)->create();

        // Store
        $this->post(route('sources.tracked-links.store', $source), [
            'link_id' => $link->id,
        ])->assertRedirect(route('login'));

        // Destroy
        $tracked = TrackedLink::factory()->for($user)->for($source)->for($link)->create();

        $this->delete(route('sources.tracked-links.destroy', [$source, $tracked]))
            ->assertRedirect(route('login'));
    }

   
    public function user_can_create_tracked_link_for_his_source_and_link(): void
    {
        $user = User::factory()->create();

        $campaign = Campaign::factory()->for($user)->create();
        $source = Source::factory()
            ->for($user)
            ->for($campaign)
            ->create();

        $link = Link::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('sources.tracked-links.store', $source), [
                'link_id' => $link->id,
            ])
            ->assertRedirect(); // back()

        $this->assertDatabaseHas('tracked_links', [
            'user_id'   => $user->id,
            'source_id' => $source->id,
            'link_id'   => $link->id,
        ]);
    }

    public function user_cannot_create_tracked_link_on_someone_else_source_or_link(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $campaign = Campaign::factory()->for($owner)->create();
        $source = Source::factory()
            ->for($owner)
            ->for($campaign)
            ->create();

        $linkOfOther = Link::factory()->for($other)->create();

        // Autre user qui tente de se greffer sur la source de quelqu'un d'autre
        $this->actingAs($other)
            ->post(route('sources.tracked-links.store', $source), [
                'link_id' => $linkOfOther->id,
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('tracked_links', 0);
    }

    public function user_can_delete_his_tracked_link_for_a_source(): void
    {
        $user = User::factory()->create();

        $campaign = Campaign::factory()->for($user)->create();
        $source = Source::factory()
            ->for($user)
            ->for($campaign)
            ->create();

        $link = Link::factory()->for($user)->create();

        $tracked = TrackedLink::factory()
            ->for($user)
            ->for($source)
            ->for($link)
            ->create();

        $this->actingAs($user)
            ->delete(route('sources.tracked-links.destroy', [$source, $tracked]))
            ->assertRedirect();

        $this->assertDatabaseMissing('tracked_links', [
            'id' => $tracked->id,
        ]);
    }

    public function user_cannot_delete_tracked_link_of_someone_else(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $campaign = Campaign::factory()->for($owner)->create();
        $source = Source::factory()
            ->for($owner)
            ->for($campaign)
            ->create();

        $link = Link::factory()->for($owner)->create();

        $tracked = TrackedLink::factory()
            ->for($owner)
            ->for($source)
            ->for($link)
            ->create();

        $this->actingAs($other)
            ->delete(route('sources.tracked-links.destroy', [$source, $tracked]))
            ->assertStatus(403);

        $this->assertDatabaseHas('tracked_links', [
            'id'        => $tracked->id,
            'user_id'   => $owner->id,
            'source_id' => $source->id,
            'link_id'   => $link->id,
        ]);
    }
}
