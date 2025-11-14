<?php

namespace Database\Factories;

use App\Models\TrackedLink;
use App\Models\User;
use App\Models\Link;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrackedLinkFactory extends Factory
{
    protected $model = TrackedLink::class;

    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'link_id'     => Link::factory(),
            'source_id'   => Source::factory(),
            'tracking_key'=> fake()->bothify('??????##'),
        ];
    }
}
