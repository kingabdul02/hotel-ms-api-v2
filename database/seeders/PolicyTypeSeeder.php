<?php

namespace Database\Seeders;

use App\Models\PolicyType;
use Illuminate\Database\Seeder;

class PolicyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PolicyType::factory()->count(5)->create();
    }
}
