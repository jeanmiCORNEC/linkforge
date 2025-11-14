<?php

namespace Database\Factories;

use App\Models\Source;
use App\Models\User;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'campaign_id' => Campaign::factory(),
            'name'        => fake()->sentence(2),
            'platform'    => fake()->randomElement(['TikTok', 'YouTube', 'Instagram', 'Newsletter', null]),
            'notes'       => fake()->boolean(40) ? fake()->sentence(8) : null,
        ];
    }
}
