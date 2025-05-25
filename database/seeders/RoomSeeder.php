<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Predefined room names based on structure
        $chaletRooms = [
            'A1', 'A2', 'A3', 'A4',
            'B1', 'B2', 'B3', 'B4',
            'C1', 'C2', 'C3',
            'D1', 'D2', 'D3',
        ];

        $studioRooms = [
            'Studio 1', 'Studio 2', 'Studio 3',
            'Studio 4', 'Studio 5', 'Studio 6',
        ];

        // Seed Chalets
        $chaletRoomTypeId = RoomType::where('name', 'Chalet')->first()->id;
        foreach ($chaletRooms as $roomName) {
            Room::factory()->create([
                'name' => $roomName,
                'room_type_id' => $chaletRoomTypeId,
                'price' => 20000,
                'no_of_guests' => 5,
                'has_sitting_room' => true,
                'no_of_bedrooms' => 1,
                'no_of_beds' => 1,
                'no_of_baths' => 1,
            ]);
        }

        // Seed Studios
        $studioRoomTypeId = RoomType::where('name', 'Studio')->first()->id;
        foreach ($studioRooms as $roomName) {
            Room::factory()->create([
                'name' => $roomName,
                'room_type_id' => $studioRoomTypeId,
                'price' => 15000,
                'no_of_guests' => 2,
                'has_sitting_room' => false,
                'no_of_bedrooms' => 1,
                'no_of_beds' => 1,
                'no_of_baths' => 1,
            ]);
        }
    }
}
