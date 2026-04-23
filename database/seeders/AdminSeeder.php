<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'view-roles', 'assign-roles',
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            'view-vendors', 'create-vendors', 'edit-vendors', 'delete-vendors',
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::create(['name' => $permission]);
        }

        // Create Roles
        $adminRole = \App\Models\Role::create(['name' => 'admin']);
        $customerRole = \App\Models\Role::create(['name' => 'customer']);
        $vendorRole = \App\Models\Role::create(['name' => 'vendor']);

        // Assign all permissions to admin
        $adminRole->permissions()->attach(\App\Models\Permission::all()->pluck('id'));

        // Create Admin User
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@vmspro.com',
            'password' => bcrypt('password'),
        ]);

        // Assign admin role to admin user
        $admin->roles()->attach($adminRole->id);

        // Create sample customers and vendors
        \App\Models\Customer::factory(5)->create();
        \App\Models\Vendor::factory(5)->create();
    }
}
