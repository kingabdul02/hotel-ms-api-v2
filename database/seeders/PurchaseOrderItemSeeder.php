<?php

namespace Database\Seeders;

use App\Models\PurchaseOrderItem;
use Illuminate\Database\Seeder;

class PurchaseOrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PurchaseOrderItem::factory()->count(5)->create();
    }
}
