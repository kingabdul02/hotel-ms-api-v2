<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Department;
use App\Models\DepartmentRequest;
use App\Models\Item;

class DepartmentRequestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DepartmentRequest::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'item_id' => Item::factory(),
            'quantity' => $this->faker->word(),
            'request_date' => $this->faker->date(),
            'status' => $this->faker->randomElement(["Pending","Approved","Rejected","Fulfilled"]),
        ];
    }
}
