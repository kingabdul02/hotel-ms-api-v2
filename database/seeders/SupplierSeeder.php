<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::create([
            'supplier_name' => 'NBTE Consult',
            'address' => 'Kaduna Nigeria',
            'phone' => '08099887777',
            'email' => 'contact@nbteconsult.com',
        ]);

        Supplier::create([
            'supplier_name' => 'TechSupplier Inc.',
            'address' => '123 Tech Lane, Silicon Valley, CA',
            'phone' => '123-456-7890',
            'email' => 'contact@techsupplier.com',
        ]);

        Supplier::create([
            'supplier_name' => 'FurnitureCo',
            'address' => '456 Comfort St, Suite 100, Chicago, IL',
            'phone' => '987-654-3210',
            'email' => 'sales@furnitureco.com',
        ]);

        Supplier::create([
            'supplier_name' => 'FarmFresh Goods',
            'address' => '789 Greenway Blvd, Portland, OR',
            'phone' => '555-123-4567',
            'email' => 'info@farmfreshgoods.com',
        ]);
    }
}
