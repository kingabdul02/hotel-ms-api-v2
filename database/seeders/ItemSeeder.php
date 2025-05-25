<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::create([
            'name' => 'Smartphone',
            'description' => 'Latest model smartphone with high-end features.',
            'category_id' => 1, // Assuming 1 is the ID for Electronics
            'unit_price' => 699.99,
            'reorder_level' => 10,
        ]);

        Item::create([
            'name' => 'Office Chair',
            'description' => 'Ergonomic office chair with adjustable settings.',
            'category_id' => 2, // Assuming 2 is the ID for Furniture
            'unit_price' => 149.99,
            'reorder_level' => 5,
        ]);

        Item::create([
            'name' => 'Organic Apples',
            'description' => 'Fresh organic apples from local farms.',
            'category_id' => 3, // Assuming 3 is the ID for Groceries
            'unit_price' => 2.99,
            'reorder_level' => 50,
        ]);
    }
}
