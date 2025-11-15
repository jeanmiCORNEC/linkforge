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

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Safari',
                'HTTP_REFERER'    => 'https://tiktok.com/',
            ]);

        // Redirect vers la destination
        $response->assertRedirect('https://example.com/product');

        // Un clic est bien enregistré avec les bonnes infos de base
        $this->assertDatabaseHas('clicks', [
            'tracked_link_id' => $tracked->id,
            'referrer'        => 'https://tiktok.com/',
            'ip_address'      => '127.0.0.1',
            'device'          => 'desktop',
            'browser'         => 'Safari', // deviné par guessBrowserFromUserAgent
        ]);

        $this->assertEquals(1, Click::count());
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

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0',
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseMissing('clicks', [
            'tracked_link_id' => $tracked->id,
        ]);
        $this->assertEquals(0, Click::count());
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

        $response = $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
            ]);

        // Même si on ne track pas, on redirige vers la cible
        $response->assertRedirect('https://example.com/product');

        $this->assertDatabaseMissing('clicks', [
            'tracked_link_id' => $tracked->id,
        ]);
        $this->assertEquals(0, Click::count());
    }

    public function test_multiple_clicks_from_same_ip_within_5_minutes_are_not_tracked_twice(): void
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
            'tracking_key' => 'ANTISPAM123',
        ]);

        // 1er clic
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
            ])
            ->assertRedirect('https://example.com/product');

        // 2e clic dans la foulée, même IP, même UA => ne doit PAS être loggé
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
            ])
            ->assertRedirect('https://example.com/product');

        // Toujours 1 seul click en base
        $this->assertEquals(1, Click::where('tracked_link_id', $tracked->id)->count());
    }

    public function test_clicks_from_different_ips_are_tracked_separately(): void
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
            'tracking_key' => 'MULTIIP123',
        ]);

        // Clic IP #1
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '127.0.0.1',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
            ])
            ->assertRedirect('https://example.com/product');

        // Clic IP #2
        $this
            ->withServerVariables([
                'REMOTE_ADDR' => '192.168.0.42',
            ])
            ->get('/l/'.$tracked->tracking_key, [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/120.0',
            ])
            ->assertRedirect('https://example.com/product');

        // Deux clics doivent être enregistrés, un par IP
        $this->assertEquals(2, Click::where('tracked_link_id', $tracked->id)->count());

        $this->assertDatabaseHas('clicks', [
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '127.0.0.1',
        ]);

        $this->assertDatabaseHas('clicks', [
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '192.168.0.42',
        ]);
    }
}
