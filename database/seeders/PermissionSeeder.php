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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // permissions array with names underscored
        $permissions = [
            'user_access',
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
            'role_access',
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            'permission_access',
            'permission_view',
            'permission_create',
            'permission_edit',
            'permission_delete',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::create(['name' => 'Super Admin']);

        $role = Role::create(['name' => 'Admin']);


        $role->givePermissionTo([
            'user_access',
            'user_view',
            'user_create',
            'user_edit',
            'user_delete',
        ]);

        // create user role and assign some permissions
        $role = Role::create(['name' => 'User']);
        $role->givePermissionTo(['user_access']);
    }
}
