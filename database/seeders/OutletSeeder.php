<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Outlet;
use App\Models\OutletItemCategory;
use App\Models\OutletItem;
use Illuminate\Support\Str;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $outlets = [
            [
                'name' => 'Restaurant',
                'type' => 'dining',
                'status' => 'active',
                'categories' => [
                    'Starters' => ['Grilled Chicken' => 4500],
                    'Main Course' => ['Jollof Rice with Chicken' => 5500],
                    'Desserts' => ['Ice Cream' => 2000],
                ],
            ],
            [
                'name' => 'Bar',
                'type' => 'beverage',
                'status' => 'active',
                'categories' => [
                    'Cocktails' => ['Mojito' => 4000],
                    'Wines' => ['Red Wine' => 8500],
                    'Beers' => ['Heineken' => 1000],
                ],
            ],
            [
                'name' => 'Spa',
                'type' => 'wellness',
                'status' => 'active',
                'categories' => [
                    'Massage' => ['Swedish Massage' => 10000],
                    'Facial' => ['Classic Facial' => 7000],
                ],
            ],
            [
                'name' => 'Minibar',
                'type' => 'room-service',
                'status' => 'active',
                'categories' => [
                    'Snacks' => ['Chocolate Bar' => 700],
                    'Drinks' => ['Soft Drink' => 500],
                ],
            ],
            [
                'name' => 'Gift Shop',
                'type' => 'retail',
                'status' => 'active',
                'categories' => [
                    'Souvenirs' => ['Keychain' => 1200],
                    'Toiletries' => ['Toothbrush Kit' => 600],
                ],
            ],
        ];

        foreach ($outlets as $outletData) {
            $outlet = Outlet::create([
                'name' => $outletData['name'],
                'type' => $outletData['type'],
                'status' => $outletData['status'],
                'operating_hours' => [],
            ]);

            foreach ($outletData['categories'] as $categoryName => $items) {
                $category = OutletItemCategory::create([
                    'outlet_id' => $outlet->id,
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                ]);

                foreach ($items as $itemName => $price) {
                    OutletItem::create([
                        'outlet_id' => $outlet->id,
                        'category_id' => $category->id,
                        'name' => $itemName,
                        'price' => $price,
                        'description' => null,
                        'available' => true,
                        'tax_rate' => 0.075,
                        'image_url' => null,
                        'sku' => strtoupper(substr($itemName, 0, 3)) . rand(100, 999),
                    ]);
                }
            }
        }
    }
}
