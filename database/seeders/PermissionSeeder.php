<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'user_management_access',
            'permission_access',
            'permission_create',
            'permission_edit',
            'permission_show',
            'permission_delete',

            'role_access',
            'role_create',
            'role_edit',
            'role_show',
            'role_delete',

            'user_access',
            'user_create',
            'user_edit',
            'user_show',
            'user_delete',
        ];

        $admin_role = Role::findByName('Admin');

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission
            ]);

            $admin_role->givePermissionTo($permission);
        }
    }
}
