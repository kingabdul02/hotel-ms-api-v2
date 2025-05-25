<?php

namespace Database\Seeders;

use App\Models\InAppNotification;
use Illuminate\Database\Seeder;

class InAppNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InAppNotification::factory()->count(5)->create();
    }
}
