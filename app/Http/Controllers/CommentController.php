<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Comment;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Define who is allowed to participate in this ticket
        $isCreator = $ticket->user_id === $user->id;
        $isAssignee = $ticket->assigned_to === $user->id;
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super-admin');
        $isCollaborator = $ticket->collaborators->contains($user->id);

        // 2. The Bouncer: Kick them out if they don't have clearance
        if (!$isCreator && !$isAssignee && !$isAdmin && !$isCollaborator) {
            abort(403, 'You are strictly viewing this ticket in Read-Only mode and cannot leave comments.');
        }

        // 3. Prevent comments if the ticket is permanently Closed
        if ($ticket->status === 'Closed' && !$isAdmin) {
            return back()->withErrors(['error' => 'This ticket is closed. Further comments are disabled.']);
        }

        $request->validate([
            'content' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // 2MB Max
            'is_internal' => 'nullable|boolean'
        ]);

        // 4. Handle the file attachment securely
        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('comments', 'local');
        }

        // 5. Handle the Internal Note flag securely
        $isInternal = false;
        if ($user->hasRole('admin') || $user->hasRole('agent')) {
            $isInternal = $request->has('is_internal');
        }

        // 6. Save the comment
        $ticket->comments()->create([
            'content' => $request->content,
            'user_id' => $user->id,
            'attachment_path' => $path,
            'is_internal' => $isInternal,
        ]);

        // 7. THE PING-PONG AUTOMATION
        $oldStatus = $ticket->status;
        $newStatus = $oldStatus;

        // Only automate status changes for public replies
        if (!$isInternal) {
            if ($user->hasRole('admin') || $user->hasRole('agent')) {
                // Agent replied -> Ball is in the Customer's court
                $newStatus = 'Pending Customer';
            } else {
                // Customer replied -> Ball is in the Agent's court
                $newStatus = 'Pending Technician';
            }
        }

        // If the automation determined a new status, update it and log it!
        if ($oldStatus !== $newStatus) {
            $ticket->update(['status' => $newStatus]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Updated Status (Auto)',
                'target_type' => 'Ticket',
                'target_id' => $ticket->id,
                'old_value' => $oldStatus,
                'new_value' => $newStatus,
            ]);
        }

        // 8. Global Audit Log for the comment itself
        AuditLog::create([
            'user_id' => $user->id,
            'action' => $isInternal ? 'Added Internal Note' : 'Added Public Reply',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => $path ? 'Included attachment' : 'Text only',
        ]);

        return back()->with('success', $isInternal ? 'Internal note added.' : 'Reply sent successfully.');
    }
}
