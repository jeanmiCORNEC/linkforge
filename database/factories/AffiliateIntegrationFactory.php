<?php

namespace Database\Factories;

use App\Models\AffiliateIntegration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffiliateIntegrationFactory extends Factory
{
    protected $model = AffiliateIntegration::class;

    public function definition(): array
    {
        return [
            'user_id'    => User::factory(),
            'platform'   => 'impact',
            'label'      => fake()->company(),
            'status'     => fake()->randomElement(AffiliateIntegration::STATUSES),
            'credentials'=> [
                'api_key'     => fake()->sha1(),
                'account_sid' => fake()->bothify('MP########'),
            ],
            'last_synced_at' => null,
            'last_error'     => null,
        ];
    }
}
