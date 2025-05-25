<?php

namespace Database\Seeders;

use App\Models\RoomImage;
use Illuminate\Database\Seeder;

class RoomImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoomImage::factory()->count(5)->create();
    }
}
