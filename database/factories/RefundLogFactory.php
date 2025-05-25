<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\RefundLog;

class RefundLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RefundLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'booking_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'refund_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'fees' => $this->faker->randomFloat(0, 0, 9999999999.),
        ];
    }
}
