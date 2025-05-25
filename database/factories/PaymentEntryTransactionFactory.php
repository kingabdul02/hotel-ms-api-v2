<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\PaymentEntry;
use App\Models\PaymentEntryTransaction;
use App\Models\PaymentMethod;

class PaymentEntryTransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentEntryTransaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'payment_method_id' => PaymentMethod::factory(),
            'amount' => $this->faker->randomFloat(10, 0, 99999999999999999999.9999999999),
            'status' => $this->faker->word(),
            'payment_date' => $this->faker->dateTime(),
            'raw_data' => $this->faker->word(),
            'host_trx_ref' => $this->faker->word(),
            'payment_entry_id' => PaymentEntry::factory(),
        ];
    }
}
