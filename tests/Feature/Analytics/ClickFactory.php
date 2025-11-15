<?php

namespace Database\Factories;

use App\Models\Click;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    protected $model = Click::class;

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

            'visitor_hash' => Str::random(40),

            'country'      => null,
            'city'         => null,
        ];
    }
}
