<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog; // <-- ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->role->name !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::with('role')->get();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
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
