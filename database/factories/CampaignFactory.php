<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'name'      => fake()->sentence(3),
            'status'    => 'draft',
            'notes'     => null,
            'starts_at' => null,
            'ends_at'   => null,
        ];
    }
}
