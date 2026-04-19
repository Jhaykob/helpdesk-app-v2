<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('manage_roles');

        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage_roles');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // Format the role name to be lowercase and hyphenated for system consistency
        $roleName = strtolower(str_replace(' ', '-', $validated['name']));

        $role = Role::create(['name' => $roleName]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return back()->with('success', "Role '{$role->name}' created successfully.");
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('manage_roles');

        // Absolute Protection: Nobody can edit the super-admin role's permissions
        if ($role->name === 'super-admin') {
            abort(403, 'The super-admin role is immutable and cannot be modified.');
        }

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        // We only update permissions, we do not allow renaming roles to prevent breaking system logic
        $role->syncPermissions($validated['permissions'] ?? []);

        return back()->with('success', "Role '{$role->name}' updated successfully.");
    }

    public function destroy(Role $role)
    {
        Gate::authorize('manage_roles');

        // Absolute Protection: Prevent deletion of core system roles
        if (in_array($role->name, ['super-admin', 'admin', 'agent', 'user'])) {
            return back()->withErrors(['error' => "You cannot delete the core system role: {$role->name}."]);
        }

        $role->delete();

        return back()->with('success', 'Custom role deleted successfully.');
    }
}
