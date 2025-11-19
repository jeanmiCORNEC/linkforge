<?php

namespace Tests\Feature\Integrations;

use App\Models\AffiliateIntegration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AffiliateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function pro_user_can_create_integration(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);

        $response = $this->actingAs($user)->post(route('integrations.affiliate.store'), [
            'platform' => 'impact',
            'label'    => 'Impact - Ebook',
            'credentials' => [
                'api_key' => 'key',
                'account_sid' => 'sid',
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('affiliate_integrations', [
            'user_id'  => $user->id,
            'platform' => 'impact',
            'label'    => 'Impact - Ebook',
        ]);
    }

    #[Test]
    public function free_plan_cannot_create_integration(): void
    {
        $user = User::factory()->create(['plan' => 'free']);

        $this->actingAs($user)
            ->post(route('integrations.affiliate.store'), [
                'platform' => 'impact',
                'label'    => 'Test',
                'credentials' => ['api_key' => 'x'],
            ])
            ->assertStatus(403);
    }

    #[Test]
    public function user_can_delete_own_integration(): void
    {
        $user = User::factory()->create(['plan' => 'pro']);
        $integration = AffiliateIntegration::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('integrations.affiliate.destroy', $integration))
            ->assertRedirect();

        $this->assertDatabaseMissing('affiliate_integrations', [
            'id' => $integration->id,
        ]);
    }
}
