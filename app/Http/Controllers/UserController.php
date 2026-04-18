<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Show the list of users
    public function index()
    {
        // Security: Only Admins can access this page
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with('role')->get();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    // Update a user's role
    public function updateRole(Request $request, User $user)
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        // Safety check: Prevent the admin from accidentally removing their own admin access
        if ($user->id === Auth::id() && $request->role_id != $user->role_id) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        $user->update([
            'role_id' => $request->role_id,
        ]);

        return back()->with('success', "{$user->name}'s role has been updated.");
    }
}
