<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'FrontDesk']);
        Role::create(['name' => 'Guest']);
        Role::create(['name' => 'InventoryStaff']);
        Role::create(['name' => 'Housekeeper']);
    }
}
