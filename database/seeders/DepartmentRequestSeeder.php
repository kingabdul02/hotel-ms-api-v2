<?php

namespace Database\Seeders;

use App\Models\DepartmentRequest;
use Illuminate\Database\Seeder;

class DepartmentRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DepartmentRequest::factory()->count(5)->create();
    }
}
