<?php

namespace Tests\Feature\Links;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinksIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_links_index_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/links');

        $response->assertOk();
    }
}
