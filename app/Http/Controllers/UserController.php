<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Helper to verify permissions and satisfy IDE type-hinting.
     */
    private function authorizeUserManagement(): void
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->hasRole('admin') && !$currentUser->can('manage_users')) {
            abort(403, 'You do not have permission to manage users.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeUserManagement();

        $query = User::with('roles'); // Eager load Spatie roles

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created User',
            'target_type' => 'User',
            'target_id' => $user->id,
            'new_value' => "Created {$user->name} with role {$validated['role']}",
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8']);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$validated['role']]);

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated User',
            'target_type' => 'User',
            'target_id' => $user->id,
            'new_value' => "Updated {$user->name} (Role: {$validated['role']})",
        ]);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeUserManagement();

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted User',
            'target_type' => 'User',
            'target_id' => $user->id,
            'old_value' => $user->email,
        ]);

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
