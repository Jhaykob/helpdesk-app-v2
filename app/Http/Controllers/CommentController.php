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
        if (Auth::user()->role->name !== 'admin' && Auth::user()->role->name !== 'agent' && Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'nullable|boolean',
        ]);

        $isInternal = $request->has('is_internal') && Auth::user()->role->name !== 'user';

        $ticket->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
            'is_internal' => $isInternal,
        ]);

        // WRITE TO GLOBAL AUDIT LOG
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Added Comment',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => $isInternal ? 'Internal Note' : 'Public Reply',
        ]);

        if (!$isInternal) {
            if (Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent') {
                $ticket->update(['status' => 'Pending Customer']);
                $ticket->user->notify(new TicketUpdatedNotification($ticket, Auth::user()->name . ' replied to your ticket.'));
            } elseif (Auth::id() === $ticket->user_id) {
                $ticket->update(['status' => 'Pending Technician']);
                if ($ticket->assignedTo) {
                    $ticket->assignedTo->notify(new TicketUpdatedNotification($ticket, 'The customer replied to ticket #' . $ticket->id));
                }
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Comment added successfully.');
    }
}
