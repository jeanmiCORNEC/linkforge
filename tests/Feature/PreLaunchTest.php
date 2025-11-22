<?php

namespace Tests\Feature;

use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PreLaunchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the Welcome Email is sent when a user registers.
     */
    public function test_welcome_email_is_sent_upon_registration(): void
    {
        Mail::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        Mail::assertSent(WelcomeMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /**
     * Test that the Zero Data Dashboard (Checklist) is displayed for new users.
     * We verify that the backend sends the correct stats (0 links, 0 campaigns)
     * which triggers the frontend state.
     */
    public function test_zero_data_dashboard_is_displayed_for_new_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('stats.links_count', 0)
            ->where('stats.campaigns_count', 0)
        );
    }

    /**
     * Test that the Annual Plan checkout logic attempts to use the yearly price ID.
     * We test this by ensuring it fails with a specific error when the config is missing,
     * which confirms the controller is looking for the yearly key.
     */
    public function test_annual_plan_checkout_looks_for_yearly_config(): void
    {
        $user = User::factory()->create();

        // 1. Simulate missing yearly price config
        Config::set('services.stripe.price_pro_yearly', null);

        $response = $this->actingAs($user)->get(route('subscription.checkout', ['interval' => 'yearly']));

        // Should redirect to profile with error about missing configuration
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Stripe configuration error: Price ID is missing.');

        // 2. Simulate missing monthly price config (default)
        Config::set('services.stripe.price_pro', null);
        
        $response = $this->actingAs($user)->get(route('subscription.checkout', ['interval' => 'monthly']));
        
        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Stripe configuration error: Price ID is missing.');
    }
}
