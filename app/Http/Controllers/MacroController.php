<?php

namespace App\Http\Controllers;

use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MacroController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Agents/Admins can see ALL Global macros PLUS their own personal macros
        $macros = Macro::where('is_global', true)
            ->orWhere('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('macros.index', compact('macros'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check if they are trying to make it global, and if they have permission
        $isGlobal = $request->has('is_global');
        if ($isGlobal && !$user->hasRole('admin') && !$user->can('manage_global_macros')) {
            $isGlobal = false; // Force it to personal if they lack permission
        }

        Macro::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'user_id' => $user->id,
            'is_global' => $isGlobal,
        ]);

        return back()->with('success', 'Canned response created successfully.');
    }

    public function update(Request $request, Macro $macro)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Can they edit this?
        if ($macro->is_global && !$user->hasRole('admin') && !$user->can('manage_global_macros')) {
            abort(403, 'You do not have permission to edit global macros.');
        }
        if (!$macro->is_global && $macro->user_id !== $user->id) {
            abort(403, 'You can only edit your own personal macros.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $isGlobal = $request->has('is_global');
        if ($isGlobal && !$user->hasRole('admin') && !$user->can('manage_global_macros')) {
            $isGlobal = $macro->is_global; // Keep it whatever it was
        }

        $macro->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'is_global' => $isGlobal,
        ]);

        return back()->with('success', 'Canned response updated successfully.');
    }

    public function destroy(Macro $macro)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($macro->is_global && !$user->hasRole('admin') && !$user->can('manage_global_macros')) {
            abort(403, 'You do not have permission to delete global macros.');
        }
        if (!$macro->is_global && $macro->user_id !== $user->id) {
            abort(403, 'You can only delete your own personal macros.');
        }

        $macro->delete();

        return back()->with('success', 'Canned response deleted successfully.');
    }
}
