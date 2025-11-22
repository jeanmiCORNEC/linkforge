<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tracked_link_id' => null, // on associera dans les tests

            'ip_address'   => $this->faker->ipv4(),
            'user_agent'   => $this->faker->userAgent(),
            'referrer'     => $this->faker->url(),

            'device'       => 'desktop',
            'browser'      => 'Chrome',
            'os'           => 'macOS',

            'visitor_hash' => \Illuminate\Support\Str::random(40),

            'country'      => null,
            'city'         => null,
        ];
    }
}
