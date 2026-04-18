<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use App\Models\AuditLog; // <-- ADDED
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TicketUpdatedNotification;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // 2MB Max
        ]);

        // 1. Handle the file attachment
        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('comments', 'public');
        }

        // 2. Handle the Internal Note flag securely
        $isInternal = false;
        if (Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent') {
            $isInternal = $request->has('is_internal');
        }

        // 3. Save the comment
        $ticket->comments()->create([
            'content' => $request->content,
            'user_id' => Auth::id(),
            'attachment_path' => $path,
            'is_internal' => $isInternal,
        ]);

        // Optional: Global Audit Log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $isInternal ? 'Added Internal Note' : 'Added Public Reply',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => $path ? 'Included attachment' : 'Text only',
        ]);

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply sent successfully.');
    }
}
