<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\InventoryMovement;
use App\Models\Item;

class InventoryMovementFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InventoryMovement::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'transaction_type' => $this->faker->randomElement(["IN","OUT"]),
            'quantity' => $this->faker->word(),
            'transaction_date' => $this->faker->dateTime(),
            'remarks' => $this->faker->text(),
        ];
    }
}
