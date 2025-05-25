<?php

namespace Database\Seeders;

use App\Models\MajorAttraction;
use Illuminate\Database\Seeder;

class MajorAttractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MajorAttraction::factory()->count(5)->create();
    }
}
