<?php

namespace Database\Seeders;

use App\Models\HotelImage;
use Illuminate\Database\Seeder;

class HotelImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HotelImage::factory()->count(5)->create();
    }
}
