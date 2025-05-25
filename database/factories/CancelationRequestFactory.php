<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\CancelationRequest;

class CancelationRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CancelationRequest::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'fees' => $this->faker->randomFloat(0, 0, 9999999999.),
            'status' => $this->faker->randomElement(["approved","rejec","pending"]),
        ];
    }
}
