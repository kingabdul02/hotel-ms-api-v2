<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic items including gadgets, appliances, and accessories.',
            'chart_color' => '#495057',
        ]);

        Category::create([
            'name' => 'Furniture',
            'description' => 'Furniture items including chairs, tables, and sofas.',
            'chart_color' => '#2f4810',
        ]);

        Category::create([
            'name' => 'Groceries',
            'description' => 'Grocery items including food, beverages, and household supplies.',
            'chart_color' => '#ebedef',
        ]);
    }
}
