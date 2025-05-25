<?php

namespace Database\Seeders;

use App\Models\HotelPolicy;
use Illuminate\Database\Seeder;

class HotelPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HotelPolicy::factory()->count(5)->create();
    }
}
