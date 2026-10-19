<?php

namespace Database\Factories;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Studio> */
class StudioFactory extends Factory
{
    protected $model = Studio::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'description' => fake()->sentence(),
            'price_per_hour' => '900.00',
            'has_piano' => false,
            'is_active' => true,
        ];
    }
}
