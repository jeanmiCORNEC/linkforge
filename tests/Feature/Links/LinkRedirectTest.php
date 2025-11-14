<?php

namespace Tests\Feature\Links;

use App\Models\User;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_link_redirects_and_tracks_click(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'destination_url' => 'https://example.com/product',
            'is_active'       => true,
        ]);

        $tracked = TrackedLink::create([
            'user_id'      => $user->id,
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'ABC123TEST',
        ]);

        $response = $this->get('/l/'.$tracked->tracking_key, [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Safari',
            'HTTP_REFERER'    => 'https://tiktok.com/',
        ]);

        // Redirect vers la destination
        $response->assertRedirect('https://example.com/product');

        // Un clic est bien enregistré
        $this->assertDatabaseHas('clicks', [
            'tracked_link_id' => $tracked->id,
            'referrer'        => 'https://tiktok.com/',
        ]);
    }

    public function test_inactive_link_returns_404_and_no_click_logged(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'destination_url' => 'https://example.com/product',
            'is_active'       => false,
        ]);

        $tracked = TrackedLink::create([
            'user_id'      => $user->id,
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'INACTIVE123',
        ]);

        $response = $this->get('/l/'.$tracked->tracking_key, [
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $response->assertStatus(404);

        $this->assertDatabaseMissing('clicks', [
            'tracked_link_id' => $tracked->id,
        ]);
    }

    public function test_bot_user_agent_is_not_tracked(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id'         => $user->id,
            'destination_url' => 'https://example.com/product',
            'is_active'       => true,
        ]);

        $tracked = TrackedLink::create([
            'user_id'      => $user->id,
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'BOTTEST123',
        ]);

        $response = $this->get('/l/'.$tracked->tracking_key, [
            'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ]);

        $response->assertRedirect('https://example.com/product');

        $this->assertDatabaseMissing('clicks', [
            'tracked_link_id' => $tracked->id,
        ]);
    }
}
