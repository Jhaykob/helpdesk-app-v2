<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define the Granular Permissions Dictionary
        $permissions = [
            'manage_users',          // Can create/edit/suspend standard users & agents
            'manage_roles',          // Can create new dynamic roles and assign permissions (Super Admin usually)
            'manage_global_macros',  // Can create/edit global canned responses
            'manage_kb_articles',    // Can create/edit knowledge base articles
            'manage_settings',       // Can access the Global App Settings page
            'delete_tickets',        // Can permanently delete tickets
            'view_all_tickets',      // Can see tickets unassigned or assigned to others
        ];

        // Safely create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 2. Create Base Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 3. Assign default permissions to the standard Admin
        // Notice we DO NOT give them 'manage_roles'. Only the Super Admin gets everything via the Gate::before!
        $adminRole->syncPermissions([
            'manage_users',
            'manage_global_macros',
            'manage_kb_articles',
            'manage_settings',
            'delete_tickets',
            'view_all_tickets'
        ]);

        // 4. Assign default permissions to standard Agents
        $agentRole->syncPermissions([
            'view_all_tickets' // Standard agents shouldn't manage users or global settings
        ]);

        // Standard users get no special backend permissions by default.
    }
}
