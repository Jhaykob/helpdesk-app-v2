<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Granular Permissions
        Permission::create(['name' => 'manage_system_settings']);
        Permission::create(['name' => 'manage_users']);
        Permission::create(['name' => 'manage_kb_articles']);
        Permission::create(['name' => 'manage_global_macros']);
        Permission::create(['name' => 'edit_all_tickets']);

        // 2. Create Roles and Assign Permissions

        // Super Admin gets everything
        $superAdminRole = Role::create(['name' => 'admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Agent gets ticket access and KB access
        $agentRole = Role::create(['name' => 'agent']);
        $agentRole->givePermissionTo(['edit_all_tickets', 'manage_kb_articles']);

        // Regular User gets nothing by default (handled by basic ticket policies)
        $userRole = Role::create(['name' => 'user']);

        // 3. Provision Default Users

        // Super Admin User
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@helpdesk.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $admin->assignRole($superAdminRole);

        // IT Support Agent
        $agent = User::create([
            'name' => 'IT Support Agent',
            'email' => 'agent@helpdesk.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $agent->assignRole($agentRole);

        // Regular User
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@helpdesk.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $user->assignRole($userRole);
    }
}
