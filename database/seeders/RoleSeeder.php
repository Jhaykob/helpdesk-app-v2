<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create the roles
        $adminRole = Role::create(['name' => 'admin']);
        $agentRole = Role::create(['name' => 'agent']); // NEW ROLE
        $userRole = Role::create(['name' => 'user']);

        // 2. Create default Admin
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@helpdesk.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
        ]);

        // 3. Create default Agent (NEW)
        User::create([
            'name' => 'IT Support Agent',
            'email' => 'agent@helpdesk.com',
            'password' => Hash::make('password'),
            'role_id' => $agentRole->id,
        ]);

        // 4. Create default Regular User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@helpdesk.com',
            'password' => Hash::make('password'),
            'role_id' => $userRole->id,
        ]);
    }
}
