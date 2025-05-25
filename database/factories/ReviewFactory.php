<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Hotel;
use App\Models\Review;
use App\Models\User;

class ReviewFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Review::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'user_id' => User::factory(),
            'rating' => $this->faker->word(),
            'indoor_area' => $this->faker->word(),
            'out_door_area' => $this->faker->word(),
            'dining' => $this->faker->word(),
            'gym' => $this->faker->word(),
            'comment' => $this->faker->text(),
        ];
    }
}
