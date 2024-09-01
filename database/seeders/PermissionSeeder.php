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

        // create permissions
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'publish users']);
        Permission::create(['name' => 'unpublish users']);

        // create roles and assign existing permissions
        $employee = Role::create(['name' => 'employee']);
        $employee->givePermissionTo('edit users');
        $employee->givePermissionTo('delete users');

        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo('publish users');
        $admin->givePermissionTo('unpublish users');

        $super = Role::create(['name' => 'Super-Admin']);
        // gets all permissions via Gate::before rule; see AuthServiceProvider

        // create demo users
        $user = \App\Models\User::factory()->create([
            'firstname' => 'Example User',
            'lastname' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),

        ]);
        $user->assignRole($employee);

        $user = \App\Models\User::factory()->create([
            'firstname' => 'Example Admin User',
            'lastname' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),

        ]);

        $user->assignRole($admin);

        $user = \App\Models\User::factory()->create([
            'firstname' => 'Example super Admin User',
            'lastname' => 'User',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($super);
    }
}
