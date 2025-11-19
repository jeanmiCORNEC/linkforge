<?php

namespace Tests\Feature\Integrations;

use App\Models\AffiliateIntegration;
use App\Models\Campaign;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_fetches_conversions_from_impact_and_creates_records(): void
    {
        Http::fake([
            'https://api.impact.com/*' => Http::response([
                'Records' => [
                    [
                        'SubId1'             => 'TRK123',
                        'OrderId'            => 'ORDER-100',
                        'Status'             => 'approved',
                        'Currency'           => 'EUR',
                        'AdvertiserRevenue'  => 150.5,
                        'PartnerCommission'  => 32.7,
                        'EventDate'          => now()->toIso8601String(),
                    ],
                ],
                'Numpages' => 1,
            ], 200),
        ]);

        $user = User::factory()->create(['plan' => 'pro']);
        $campaign = Campaign::factory()->for($user)->create();
        $source = Source::factory()->for($user)->for($campaign)->create();
        $link = Link::factory()->for($user)->create();
        $tracked = TrackedLink::factory()
            ->for($user)
            ->for($link)
            ->for($source)
            ->create([
                'tracking_key' => 'TRK123',
            ]);

        AffiliateIntegration::factory()->for($user)->create([
            'platform' => 'impact',
            'credentials' => [
                'api_key'     => 'key',
                'account_sid' => 'SID123',
            ],
        ]);

        Artisan::call('integrations:sync impact');

        $this->assertDatabaseHas('conversions', [
            'user_id'  => $user->id,
            'order_id' => 'ORDER-100',
            'status'   => 'approved',
        ]);
    }
}
