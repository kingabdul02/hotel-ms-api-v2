<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Hotel;
use App\Models\HotelPolicy;
use App\Models\PolicyType;

class HotelPolicyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HotelPolicy::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'policy' => $this->faker->word(),
            'policy_type_id' => PolicyType::factory(),
            'hotel_id' => Hotel::factory(),
        ];
    }
}
