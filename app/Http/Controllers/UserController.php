<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // <-- ADDED FOR SECURE PASSWORDS

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Added latest() to show newest users first
        $users = User::with('role')->latest()->get();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    // NEW: Create User Method
    public function store(Request $request)
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        // WRITE TO GLOBAL AUDIT LOG
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created User',
            'target_type' => 'User',
            'target_id' => $user->id,
            'new_value' => 'Role: ' . ucfirst($user->role->name),
        ]);

        return back()->with('success', "User {$user->name} has been successfully created.");
    }

    public function updateRole(Request $request, User $user)
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate(['role_id' => 'required|exists:roles,id']);

        if ($user->id === Auth::id() && $request->role_id != $user->role_id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $oldRoleName = $user->role->name;

        $user->update(['role_id' => $request->role_id]);

        $newRoleName = Role::find($request->role_id)->name;

        // WRITE TO GLOBAL AUDIT LOG
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Changed User Role',
            'target_type' => 'User',
            'target_id' => $user->id,
            'old_value' => ucfirst($oldRoleName),
            'new_value' => ucfirst($newRoleName),
        ]);

        return back()->with('success', "{$user->name}'s role has been updated.");
    }
}
