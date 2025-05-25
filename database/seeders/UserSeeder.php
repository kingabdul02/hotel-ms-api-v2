<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'email' => 'admin@hotelms.com',
            'name' => 'Joe Doe',
            'password' => bcrypt('password'),
            'phone' => '08099887766',
            'address' => 'no 7 wuye, Abuja',
        ])->assignRole('Admin');

        User::create([
            'email' => 'manager@hotelms.com',
            'name' => 'John Smith',
            'password' => bcrypt('password'),
            'phone' => '08099889999',
            'address' => 'no 7 wuye, Abuja',

        ])->assignRole('Manager');

        User::create([
            'email' => 'staff@hotelms.com',
            'name' => 'John Carter',
            'password' => bcrypt('password'),
            'phone' => '08122334455',
            'address' => 'no 7 wuye, Abuja',
        ])->assignRole('FrontDesk');

        User::create([
            'email' => 'guest@hotelms.com',
            'name' => 'John Snow',
            'password' => bcrypt('password'),
            'phone' => '08099112233',
            'address' => 'no 7 wuye, Abuja',
        ])->assignRole('Guest');

        User::create([
            'email' => 'inventory@hotelms.com',
            'name' => 'John Hot',
            'password' => bcrypt('password'),
            'phone' => '08099112277',
            'address' => 'no 7 wuye, Abuja',
        ])->assignRole('InventoryStaff');
    }
}
