<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Hall>
 */
class HallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'capacity' => $this->faker->numberBetween(50, 500),
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'image' => $this->faker->imageUrl(),
        ];
    }
}
