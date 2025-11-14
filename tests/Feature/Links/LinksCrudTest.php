<?php

namespace Tests\Feature\Links;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinksCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_links_index(): void
    {
        $response = $this->get('/links');

        $response->assertRedirect('/login');
    }

    public function test_user_can_create_link(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title'           => 'Lien TikTok bio',
            'destination_url' => 'https://example.com/produit',
        ];

        $response = $this->actingAs($user)->post('/links', $payload);

        // store redirige vers links.index
        $response->assertRedirect(route('links.index'));

        $this->assertDatabaseHas('links', [
            'user_id'         => $user->id,
            'title'           => 'Lien TikTok bio',
            'destination_url' => 'https://example.com/produit',
            'is_active'       => true,
        ]);
    }

    public function test_user_can_update_his_link(): void
    {
        $user = User::factory()->create();

        /** @var Link $link */
        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'title'           => 'Ancien titre',
            'destination_url' => 'https://old.example.com',
            'is_active'       => true,
        ]);

        $payload = [
            'title'           => 'Nouveau titre',
            'destination_url' => 'https://new.example.com',
            'is_active'       => false, // on teste aussi le booléen
        ];

        $response = $this->actingAs($user)->put("/links/{$link->id}", $payload);

        $response->assertRedirect(); // back()

        $this->assertDatabaseHas('links', [
            'id'             => $link->id,
            'title'          => 'Nouveau titre',
            'destination_url'=> 'https://new.example.com',
            'is_active'      => false,
        ]);
    }

    public function test_user_cannot_update_someone_else_link(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $link = Link::factory()->create([
            'user_id' => $owner->id,
        ]);

        $payload = [
            'title'           => 'Hack',
            'destination_url' => 'https://evil.example.com',
            'is_active'       => false,
        ];

        $response = $this->actingAs($other)->put("/links/{$link->id}", $payload);

        $response->assertStatus(403);

        // rien n’a changé
        $this->assertDatabaseMissing('links', [
            'id'    => $link->id,
            'title' => 'Hack',
        ]);
    }

    public function test_user_can_soft_delete_his_link(): void
    {
        $user = User::factory()->create();

        /** @var Link $link */
        $link = Link::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete("/links/{$link->id}");

        $response->assertRedirect();

        // soft delete => deleted_at non null
        $this->assertSoftDeleted('links', [
            'id' => $link->id,
        ]);
    }

    public function test_user_cannot_delete_someone_else_link(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $link = Link::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($other)->delete("/links/{$link->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('links', [
            'id'        => $link->id,
            'deleted_at'=> null,
        ]);
    }
}
