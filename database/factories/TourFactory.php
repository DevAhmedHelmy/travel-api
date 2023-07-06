<?php

namespace Database\Factories;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'travel_id' => Travel::factory()->create()->id,
            'price' => $this->faker->randomFloat(2, 10, 999),
            'starting_date' => now(),
            'ending_date' => now()->addDays(rand(1, 10)),
        ];
    }
}
