<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\TicketUpdatedNotification; // <-- ADDED IMPORT

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

        // The Ping-Pong Auto-Status & Notification Logic
        if (!$isInternal) {
            if (Auth::user()->role->name === 'admin' || Auth::user()->role->name === 'agent') {
                $ticket->update(['status' => 'Pending Customer']);

                // NOTIFY THE CUSTOMER
                $ticket->user->notify(new TicketUpdatedNotification($ticket, Auth::user()->name . ' replied to your ticket.'));
            } elseif (Auth::id() === $ticket->user_id) {
                $ticket->update(['status' => 'Pending Technician']);

                // NOTIFY THE ASSIGNED TECHNICIAN (if one exists)
                if ($ticket->assignedTo) {
                    $ticket->assignedTo->notify(new TicketUpdatedNotification($ticket, 'The customer replied to ticket #' . $ticket->id));
                }
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Comment added successfully.');
    }
}
