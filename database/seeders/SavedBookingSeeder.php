<?php

namespace Database\Seeders;

use App\Models\SavedBooking;
use Illuminate\Database\Seeder;

class SavedBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SavedBooking::factory()->count(5)->create();
    }
}
