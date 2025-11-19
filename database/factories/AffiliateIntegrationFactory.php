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
            'platform'   => fake()->randomElement(['impact', 'awin', 'hotmart']),
            'label'      => fake()->company(),
            'status'     => fake()->randomElement(AffiliateIntegration::STATUSES),
            'credentials'=> [
                'api_key' => fake()->sha1(),
                'account_id' => fake()->bothify('AFF-####'),
            ],
        ];
    }
}
