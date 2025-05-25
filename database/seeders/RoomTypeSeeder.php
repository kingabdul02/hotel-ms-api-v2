<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoomType::factory()->create([
            'hotel_id' => 1,
            'name' => 'Chalet',
            'description' => 'The chalet has a sitting room and bedrooms attached to it',
            'chart_color_code' => '#2f4860',
            'image_url' => 'chalet.jpg',
        ]);

        RoomType::factory()->create([
            'hotel_id' => 1,
            'name' => 'Studio',
            'description' => 'The studio configuration is a single room from studio 1 to Studio 6',
            'chart_color_code' => '#00bb7e',
            'image_url' => 'studio.jpg',
        ]);
    }
}
