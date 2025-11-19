<?php

namespace Tests\Feature\Conversions;

use App\Models\Conversion;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConversionManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pro_user_can_view_conversions(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $link = Link::factory()->for($user)->create(['title' => 'Landing Ventes']);
        $tracked = TrackedLink::factory()->for($user)->for($link)->create();

        $conversion = Conversion::factory()->create([
            'user_id'         => $user->id,
            'tracked_link_id' => $tracked->id,
            'link_id'         => $link->id,
            'source_id'       => null,
            'campaign_id'     => null,
            'order_id'        => 'ORDER-555',
            'status'          => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('conversions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Conversions/Index')
                ->where('canAccess', true)
                ->where('conversions.0.order_id', $conversion->order_id)
                ->where('conversions.0.status', 'pending')
            );
    }

    #[Test]
    public function free_plan_sees_locked_message(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->get(route('conversions.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Conversions/Index')
                ->where('canAccess', false)
                ->where('conversions', [])
            );
    }

    #[Test]
    public function user_can_update_conversion_status(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $link = Link::factory()->for($user)->create();
        $tracked = TrackedLink::factory()->for($user)->for($link)->create();

        $conversion = Conversion::factory()->create([
            'user_id'         => $user->id,
            'tracked_link_id' => $tracked->id,
            'link_id'         => $link->id,
            'source_id'       => null,
            'campaign_id'     => null,
            'status'          => 'pending',
        ]);

        $this->actingAs($user)
            ->patch(route('conversions.status', $conversion), ['status' => 'approved'])
            ->assertSessionHas('status', 'conversion-updated');

        $this->assertDatabaseHas('conversions', [
            'id'     => $conversion->id,
            'status' => 'approved',
        ]);
    }

    #[Test]
    public function user_cannot_update_conversion_he_does_not_own(): void
    {
        $owner = User::factory()->create(['plan' => 'pro']);
        $other = User::factory()->create(['plan' => 'pro']);

        $link = Link::factory()->for($owner)->create();
        $tracked = TrackedLink::factory()->for($owner)->for($link)->create();

        $conversion = Conversion::factory()->create([
            'user_id'         => $owner->id,
            'tracked_link_id' => $tracked->id,
            'link_id'         => $link->id,
            'source_id'       => null,
            'campaign_id'     => null,
        ]);

        $this->actingAs($other)
            ->patch(route('conversions.status', $conversion), ['status' => 'approved'])
            ->assertStatus(403);
    }
}
