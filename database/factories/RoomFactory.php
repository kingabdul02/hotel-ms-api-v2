<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Room::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'room_type_id' => RoomType::factory(),
            'hotel_id' => Hotel::factory(),
            'price' => $this->faker->randomFloat(0, 0, 99999.),
            'is_available' => true,
            'no_of_guests' => $this->faker->numberBetween(1, 5),
            'no_of_bedrooms' => $this->faker->numberBetween(1, 5),
            'no_of_beds' => $this->faker->numberBetween(1, 5),
            'no_of_baths' => $this->faker->numberBetween(1, 5),
            'is_feature' => $this->faker->boolean(),
        ];
    }
}
