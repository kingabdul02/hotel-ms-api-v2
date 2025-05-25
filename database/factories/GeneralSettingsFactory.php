<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\GeneralSettings;

class GeneralSettingsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GeneralSettings::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'vat_rate' => 7.5,
            'cancelation_fees' => 5000,
        ];
    }
}
