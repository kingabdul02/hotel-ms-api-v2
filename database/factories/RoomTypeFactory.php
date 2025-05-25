<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RoomType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'image_url' => $this->faker->name(),
            'description' => $this->faker->text(),
            'chart_color_code' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
            'hotel_id' => Hotel::factory(),
        ];
    }
}
