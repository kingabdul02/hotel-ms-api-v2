<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\PaymentEntry;

class PaymentEntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentEntry::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'payment_amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'payment_method' => $this->faker->randomElement(["online","cash","pos","transfer"]),
            'transaction_id' => $this->faker->word(),
            'payment_status' => $this->faker->randomElement(["pending","successful","failed"]),
            'payment_date' => $this->faker->dateTime(),
        ];
    }
}
