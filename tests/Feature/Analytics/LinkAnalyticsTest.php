<?php

namespace Tests\Feature\Analytics;

use Tests\TestCase;
use App\Models\User;
use App\Models\Link;
use App\Models\TrackedLink;
use App\Models\Source;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;

class LinkAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_loads_link_analytics_with_basic_stats(): void
    {
        $user = User::factory()->create();

        $link = Link::factory()->create([
            'user_id' => $user->id,
        ]);

        $trackedLink = TrackedLink::factory()->create([
            'user_id' => $user->id,
            'link_id' => $link->id,
        ]);

        $source = Source::factory()->create([
            'user_id' => $user->id,
        ]);

        $trackedWithSource = TrackedLink::factory()->create([
            'user_id'   => $user->id,
            'link_id'   => $link->id,
            'source_id' => $source->id,
        ]);

        // Quelques clics pour remplir les stats
        Click::factory()->count(3)->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'hash-1',
            'created_at'      => now()->subDays(1),
        ]);

        Click::factory()->count(2)->create([
            'tracked_link_id' => $trackedLink->id,
            'visitor_hash'    => 'hash-2',
            'created_at'      => now()->subDays(2),
        ]);

        Click::factory()->create([
            'tracked_link_id' => $trackedWithSource->id,
            'visitor_hash'    => 'hash-source',
            'created_at'      => now()->setHour(10),
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('links.analytics.show', [
                'link' => $link->id,
                // days optionnel, mais on le force à 7 pour être explicite
                'days' => 7,
            ]));

        $response->assertOk();

        $response->assertInertia(function (Assert $page) use ($link, $source) {
            $page
                ->component('Links/Analytics')
                ->where('link.id', $link->id)

                // Clés CAMEL CASE, alignées sur le contrôleur/vue actuels
                ->has('stats.totalClicks')
                ->has('stats.uniqueVisitors')
                ->has('stats.devices')
                ->has('stats.browsers')
                ->has('stats.clicksPerDay')
                ->has('stats.topSources')
                ->has('stats.topDays')
                ->has('stats.hourlyHeatmap')

                ->where('filters.days', 7)
                ->etc();
        });
    }

    #[Test]
    public function it_displays_clicks_per_day_for_a_link_on_7_days_period(): void
    {
        $user = User::factory()->create();

        // On crée le lien via la relation pour rester cohérent avec le domaine
        $link = $user->links()->create([
            'title'           => 'Page de vente',
            'destination_url' => 'https://example.com/landing',
            'slug'            => 'page-vente',
            'is_active'       => true,
        ]);

        $tracked = $user->trackedLinks()->create([
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'ABC123XYZ',
        ]);

        // 2 clics aujourd'hui
        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '1.1.1.1',
            'user_agent'      => 'Mozilla/5.0 (Macintosh; Intel Mac OS X) Chrome/120.0',
            'referrer'        => 'https://google.com',
            'device'          => 'desktop',
            'browser'         => 'Chrome',
            'os'              => 'macOS',
            'visitor_hash'    => hash('sha256', '1.1.1.1|ua1'),
            'country'         => null,
            'city'            => null,
            'created_at'      => now(),
        ]);

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '2.2.2.2',
            'user_agent'      => 'Mozilla/5.0 (Windows NT) Chrome/120.0',
            'referrer'        => 'https://youtube.com',
            'device'          => 'desktop',
            'browser'         => 'Chrome',
            'os'              => 'windows',
            'visitor_hash'    => hash('sha256', '2.2.2.2|ua2'),
            'country'         => null,
            'city'            => null,
            'created_at'      => now(),
        ]);

        // 1 clic hier (dans la fenêtre 7 jours)
        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '3.3.3.3',
            'user_agent'      => 'Mozilla/5.0 (iPhone; CPU iPhone OS) Safari/605.1.15',
            'referrer'        => null,
            'device'          => 'mobile',
            'browser'         => 'Safari',
            'os'              => 'iOS',
            'visitor_hash'    => hash('sha256', '3.3.3.3|ua3'),
            'country'         => null,
            'city'            => null,
            'created_at'      => now()->subDay(),
        ]);

        // 1 clic hors période (J-10) -> actuellement ton code l’inclut dans totalClicks
        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '4.4.4.4',
            'user_agent'      => 'Mozilla/5.0',
            'referrer'        => null,
            'device'          => 'desktop',
            'browser'         => 'Other',
            'os'              => null,
            'visitor_hash'    => hash('sha256', '4.4.4.4|ua4'),
            'country'         => null,
            'city'            => null,
            'created_at'      => now()->subDays(10),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('links.analytics.show', [
            'link' => $link->id,
            'days' => 7,
        ]));

        $response->assertOk();

        $response->assertInertia(function (Assert $page) use ($link) {
            $page
                ->component('Links/Analytics')
                ->where('link.id', $link->id)
                // Désormais limité à la fenêtre analysée : 3 clics dans les 7 derniers jours
                ->where('stats.totalClicks', 3)
                // On NE force PAS la taille à 7, ton code renvoie seulement les jours présents
                ->has('stats.clicksPerDay')
                ->has('stats.delta')
                ->where('filters.days', 7)
                ->etc();
        });
    }

    #[Test]
    public function it_forbids_analytics_access_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $link = $owner->links()->create([
            'title'           => 'Lien du propriétaire',
            'destination_url' => 'https://example.com',
            'slug'            => 'owner-link',
            'is_active'       => true,
        ]);

        $this->actingAs($other);

        $response = $this->get(route('links.analytics.show', [
            'link' => $link->id,
        ]));

        $response->assertStatus(403);
    }

    #[Test]
    public function it_computes_unique_visitors_for_a_link_on_period(): void
    {
        $user = User::factory()->create();

        $link = $user->links()->create([
            'title'           => 'Page de vente',
            'destination_url' => 'https://example.com/landing',
            'slug'            => 'page-vente',
            'is_active'       => true,
        ]);

        $tracked = $user->trackedLinks()->create([
            'link_id'      => $link->id,
            'source_id'    => null,
            'tracking_key' => 'UNIQ123',
        ]);

        // Même visitor_hash, même jour -> 1 visiteur unique
        $hash1 = hash('sha256', '10.0.0.1|ua-1');

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '10.0.0.1',
            'user_agent'      => 'UA-1',
            'referrer'        => null,
            'device'          => 'desktop',
            'browser'         => 'Chrome',
            'os'              => null,
            'visitor_hash'    => $hash1,
            'country'         => null,
            'city'            => null,
            'created_at'      => now(),
        ]);

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '10.0.0.1',
            'user_agent'      => 'UA-1',
            'referrer'        => null,
            'device'          => 'desktop',
            'browser'         => 'Chrome',
            'os'              => null,
            'visitor_hash'    => $hash1,
            'country'         => null,
            'city'            => null,
            'created_at'      => now()->addMinutes(5),
        ]);

        // Deuxième visiteur (dans l’historique)
        $hash2 = hash('sha256', '20.0.0.1|ua-2');

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '20.0.0.1',
            'user_agent'      => 'UA-2',
            'referrer'        => null,
            'device'          => 'mobile',
            'browser'         => 'Safari',
            'os'              => null,
            'visitor_hash'    => $hash2,
            'country'         => null,
            'city'            => null,
            'created_at'      => now()->subDay(),
        ]);

        // Troisième visiteur hors fenêtre 7j -> ne doit PAS compter dans les uniques
        $hash3 = hash('sha256', '30.0.0.1|ua-3');

        Click::create([
            'tracked_link_id' => $tracked->id,
            'ip_address'      => '30.0.0.1',
            'user_agent'      => 'UA-3',
            'referrer'        => null,
            'device'          => 'desktop',
            'browser'         => 'Other',
            'os'              => null,
            'visitor_hash'    => $hash3,
            'country'         => null,
            'city'            => null,
            'created_at'      => now()->subDays(10),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('links.analytics.show', [
            'link' => $link->id,
            'days' => 7,
        ]));

        $response->assertOk();

        $response->assertInertia(function (Assert $page) use ($link) {
            $page
                ->component('Links/Analytics')
                ->where('link.id', $link->id)
                // Uniquement 2 visiteurs uniques dans la fenêtre des 7 derniers jours
                ->where('stats.uniqueVisitors', 2)
                ->where('filters.days', 7)
                ->etc();
        });
    }
}
