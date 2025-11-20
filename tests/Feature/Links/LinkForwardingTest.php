<?php

namespace Tests\Feature\Links;

use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkForwardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_query_params_are_forwarded_on_redirect(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'destination_url' => 'https://example.com/page',
            'is_active'       => true,
        ]);

        $tracked = TrackedLink::factory()->create([
            'user_id'      => $user->id,
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'FORWARD123',
        ]);

        $response = $this->get('/l/'.$tracked->tracking_key.'?utm_source=instagram&utm_campaign=test');

        $response->assertRedirect('https://example.com/page?utm_source=instagram&utm_campaign=test');
    }
}
