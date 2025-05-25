<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Hotel::factory()->create([
            'name' => 'NBTE Hotel',
            'description' => '',
            'location' => 'Kaduna',
            'phone' => '08099887777',
        ]);
    }
}
