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
    public function it_redirects_and_pushes_tracking_job_to_queue()
    {
        \Illuminate\Support\Facades\Queue::fake();

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

        // 3) Vérifier que le Job a été dispatché
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\TrackClickJob::class, function ($job) use ($tracked) {
            return $job->trackedLinkId === $tracked->id;
        });

        // 4) Vérifier que la DB est vide (Fire & Forget)
        $this->assertDatabaseCount('clicks', 0);
    }

    #[Test]
    public function it_returns_404_when_tracking_key_does_not_exist()
    {
        $response = $this->get('/l/INVALID123');

        $response->assertStatus(404);
    }
}
