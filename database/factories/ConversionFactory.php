<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Conversion;
use App\Models\Link;
use App\Models\Source;
use App\Models\TrackedLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversion>
 */
class ConversionFactory extends Factory
{
    protected $model = Conversion::class;

    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'tracked_link_id' => TrackedLink::factory(),
            'link_id'         => Link::factory(),
            'source_id'       => Source::factory(),
            'campaign_id'     => Campaign::factory(),
            'order_id'        => fake()->uuid(),
            'status'          => 'pending',
            'currency'        => 'EUR',
            'revenue'         => fake()->randomFloat(2, 5, 150),
            'commission'      => fake()->randomFloat(2, 1, 50),
            'visitor_hash'    => base64_encode(fake()->sha256()),
            'metadata'        => null,
        ];
    }
}
