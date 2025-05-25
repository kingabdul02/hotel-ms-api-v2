<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::factory()->create([
            'name' => 'NBTE Consult',
            'hotel_id' => 1,
        ]);

        Department::factory()->create([
            'name' => 'NBTE Anex',
            'hotel_id' => 1,
        ]);
    }
}
