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
            "user_permaDelete",
            "user_restore",
            "user_changeRole",
            "user_changeStatus",
            'role_access',
            'role_view',
            'role_create',
            'role_edit',
            'role_delete',
            "activities_access",
            "activities_view",
            "category_access",
            "category_view",
            "category_create",
            "category_edit",
            "category_changeStatus",
            "category_delete",

        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        Role::create(['name' => 'Super Admin', 'color' => 'FABC3F', 'text_color' => '000000']);

        $role = Role::create(['name' => 'Admin', 'color' => 'FFEC9E', 'text_color' => '000000']);


        $role->givePermissionTo([
            'user_access',
            'user_view',
            'user_create',
            'user_edit',
            "user_changeStatus",
            'user_delete',
        ]);

        // create user role and assign some permissions
        $role = Role::create(['name' => 'User', 'color' => '606C5D', 'text_color' => 'ffffff']);
    }
}
