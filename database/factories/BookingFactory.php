<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;

class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'check_in_date' => $this->faker->date(),
            'check_out_date' => $this->faker->date(),
            'special_requests' => $this->faker->text(),
            'total_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'payment_status' => $this->faker->randomElement(["pending","paid","cancelled"]),
            'is_confirmed' => $this->faker->boolean(),
            'is_checked_in' => $this->faker->boolean(),
            'is_checked_out' => $this->faker->boolean(),
            'no_of_guests' => $this->faker->word(),
            'no_of_nights' => $this->faker->word(),
            'booking_id' => $this->faker->word(),
        ];
    }
}
