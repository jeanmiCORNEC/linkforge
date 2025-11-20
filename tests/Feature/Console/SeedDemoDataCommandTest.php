<?php

namespace Tests\Feature\Console;

use App\Models\Click;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedDemoDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_demo_user_and_clicks(): void
    {
        $this->artisan('demo:seed --clicks=5')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'demo@linkforge.test', 'plan' => 'pro']);
        $this->assertDatabaseCount('clicks', 5);

        $user = User::where('email', 'demo@linkforge.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->campaigns()->exists());
        $this->assertTrue($user->sources()->exists());
        $this->assertTrue($user->links()->exists());
    }
}
