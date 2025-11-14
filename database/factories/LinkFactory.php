<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'title'           => fake()->sentence(3),
            'destination_url' => fake()->url(),
            'slug'            => fake()->uuid(),
            'is_active'       => true,
        ];
    }
}
