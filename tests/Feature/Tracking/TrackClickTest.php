<?php

namespace Tests\Feature\Tracking;

use Tests\TestCase;
use App\Models\Link;
use App\Models\User;
use App\Models\Click;
use App\Models\TrackedLink;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TrackClickTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_redirects_and_logs_click_for_valid_tracking_key()
    {
        $user = User::factory()->create();

        $link = Link::factory()->for($user)->create([
            'destination_url' => 'https://example.com/landing',
            'is_active'       => true,
        ]);

        $tracked = TrackedLink::factory()
            ->for($user)
            ->for($link)
            ->create([
                'tracking_key' => 'ABC1234567',
            ]);

        // 1) Appel de la route publique
        $response = $this->get('/l/' . $tracked->tracking_key);

        // 2) Redirection OK
        $response->assertStatus(302);
        $response->assertRedirect('https://example.com/landing');

        // 3) Un clic bien loggé avec les champs réellement stockés
        $this->assertDatabaseHas('clicks', [
            'tracked_link_id' => $tracked->id,
        ]);

        $click = Click::first();

        $this->assertNotNull($click->ip_address);
        $this->assertNotNull($click->user_agent);
        $this->assertNotNull($click->visitor_hash);
        $this->assertNotNull($click->device);
        $this->assertNotNull($click->browser);
    }

    #[Test]
    public function it_returns_404_when_tracking_key_does_not_exist()
    {
        $response = $this->get('/l/INVALID123');

        $response->assertStatus(404);
    }
}
