<?php

namespace Database\Seeders;

use App\Models\CancelationRequest;
use Illuminate\Database\Seeder;

class CancelationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CancelationRequest::factory()->count(5)->create();
    }
}
