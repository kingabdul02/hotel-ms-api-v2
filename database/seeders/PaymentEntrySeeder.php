<?php

namespace Database\Seeders;

use App\Models\PaymentEntry;
use Illuminate\Database\Seeder;

class PaymentEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentEntry::factory()->count(5)->create();
    }
}
