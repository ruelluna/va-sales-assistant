<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Products
            'view products',
            'create products',
            'edit products',
            'delete products',
            // Campaigns
            'view campaigns',
            'create campaigns',
            'edit campaigns',
            'delete campaigns',
            // Contacts
            'view contacts',
            'create contacts',
            'edit contacts',
            'delete contacts',
            // Calls
            'make calls',
            'view call history',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $agent = Role::firstOrCreate(['name' => 'agent']);

        // Assign all permissions to admin
        $admin->givePermissionTo(Permission::all());

        // Assign all permissions to manager
        $manager->givePermissionTo(Permission::all());

        // Assign only call-related permissions to agent
        $agent->givePermissionTo([
            'make calls',
            'view call history',
        ]);
    }
}
