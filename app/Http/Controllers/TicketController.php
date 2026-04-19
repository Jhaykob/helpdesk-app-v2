<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\AuditLog; // <-- ADDED GLOBAL AUDIT LOG IMPORT
use App\Models\Macro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Start the query and eager load relationships for performance
        $query = Ticket::with(['user', 'assignedTo']);

        // SECURITY: If they are NOT an admin and NOT an agent, restrict to only their own tickets
        if (!$user->hasRole('admin') && !$user->hasRole('agent') && !$user->hasRole('super-admin')) {
            $query->where('user_id', $user->id);
        }
        // If they ARE an admin or agent, the block above is skipped, granting them global access!

        // Apply Search Filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', "%{$searchTerm}%")
                    ->orWhere('title', 'like', "%{$searchTerm}%");
            });
        }

        // Apply Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply Priority Filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Fetch the results, paginated
        $tickets = $query->latest()->paginate(10)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer', // Added to support your UI dropdown
            'priority' => 'required|in:Low,Medium,High',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'priority' => $validated['priority'],
            'status' => 'Unassigned',
            'user_id' => $user->id,
        ]);

        // LOCAL LOGGING: Ticket History
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'created the ticket',
        ]);

        // GLOBAL LOGGING: Enterprise Audit Trail
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Created Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => 'Status: Unassigned',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();

            // SECURITY UPDATE: Save to the private 'local' disk instead of 'public'
            $filePath = $file->storeAs('attachments', $fileName, 'local');

            $ticket->attachments()->create([
                'file_name' => $fileName,
                'file_path' => $filePath,
            ]);
        }

        // NEW: Alert all Admins and Agents about the new unassigned ticket
        $staffMembers = \App\Models\User::role(['admin', 'agent', 'super-admin'])->get();
        \Illuminate\Support\Facades\Notification::send($staffMembers, new \App\Notifications\NewTicketNotification($ticket));

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function claim(Request $request, Ticket $ticket)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security: Must be an admin or agent
        if (!$user->hasRole('admin') && !$user->hasRole('agent')) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent claiming if already assigned
        if (!is_null($ticket->assigned_to)) {
            return back()->withErrors(['error' => 'This ticket has already been claimed by another agent.']);
        }

        $ticket->update([
            'assigned_to' => $user->id,
            'status' => 'Open',
        ]);

        // Log the action securely
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Claimed Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'old_value' => 'Unassigned',
            'new_value' => 'Open (Claimed by ' . $user->name . ')',
        ]);

        return back()->with('success', 'You have successfully claimed this ticket.');
    }

    public function show(Ticket $ticket)
    {
        if (Gate::denies('view', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        // Maintained your original eager loading
        $ticket->load(['comments.user', 'user', 'assignedTo', 'activities.user']);

        // NEW: Fetch only Global macros, OR the user's personal macros
        // NEW: Fetch only Global macros, OR the user's personal macros
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $macros = \App\Models\Macro::where('is_global', true)
            ->orWhere('user_id', $user->id)
            ->orderBy('title')
            ->get();

        // NEW: Fetch agents for the "Assign To" dropdown in the sidebar
        $agents = [];
        if ($user->hasRole('admin') || $user->hasRole('agent')) {
            // SPATIE FIX: Use Spatie's elegant role() scope instead of whereHas!
            $agents = \App\Models\User::role(['admin', 'agent'])->get();
        }

        // Pass everything the updated view requires
        return view('tickets.show', compact('ticket', 'macros', 'agents'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        // 1. Security Check
        if (!in_array(Auth::user()->role->name, ['admin', 'agent'])) {
            abort(403, 'Unauthorized action.');
        }

        // Lock closed tickets from being edited by non-admins
        if ($ticket->status === 'Closed' && Auth::user()->role->name !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Closed tickets cannot be modified.'], 403);
            }
            abort(403, 'Closed tickets cannot be modified.');
        }

        // 2. Validate incoming data
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assigned_to' => 'sometimes|nullable',
        ]);

        // 3. Process changes and write to Audit Log
        foreach ($validated as $key => $newValue) {
            $oldValue = $ticket->{$key};

            if ($oldValue != $newValue) {
                // Update the ticket in the database
                $ticket->update([$key => $newValue]);

                // Format values nicely for the Audit Log
                $friendlyKey = ucfirst(str_replace('_', ' ', $key));
                $friendlyOld = $oldValue;
                $friendlyNew = $newValue;

                // If updating assigned agent, fetch their names instead of showing raw IDs
                if ($key === 'assigned_to') {
                    $friendlyOld = $oldValue ? \App\Models\User::find($oldValue)->name : 'Unassigned';
                    $friendlyNew = $newValue ? \App\Models\User::find($newValue)->name : 'Unassigned';
                }

                \App\Models\AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => "Updated {$friendlyKey}",
                    'target_type' => 'Ticket',
                    'target_id' => $ticket->id,
                    'old_value' => (string) $friendlyOld,
                    'new_value' => (string) $friendlyNew,
                ]);

                // NEW: CSAT Reset Logic
                // If the ticket WAS Resolved/Closed, and is NOW being reopened...
                if ($key === 'status' && in_array($oldValue, ['Resolved', 'Closed']) && !in_array($newValue, ['Resolved', 'Closed'])) {

                    // Only run the reset if a rating actually existed
                    if ($ticket->rating !== null) {
                        // 1. Wipe the scores from the database
                        $ticket->update([
                            'rating' => null,
                            'csat_feedback' => null
                        ]);

                        // 2. Add a specific Audit Log entry explaining why the score vanished
                        \App\Models\AuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'Reset CSAT Score',
                            'target_type' => 'Ticket',
                            'target_id' => $ticket->id,
                            'old_value' => "Previous Rating: {$ticket->rating} Stars",
                            'new_value' => 'Cleared due to ticket reopen'
                        ]);
                    }
                }

                // NEW: Resolution Email Trigger
                // If the status is changing TO Resolved...
                if ($key === 'status' && $newValue === 'Resolved' && $oldValue !== 'Resolved') {
                    // Check if Global Settings allow emails to be sent
                    if (\App\Models\Setting::get('send_email_notifications', '1') == '1') {
                        $ticket->user->notify(new \App\Notifications\TicketResolvedNotification($ticket));
                    }
                }
            }
        }

        // 4. Return JSON if it's an AJAX request (Our Alpine logic)
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Ticket updated successfully!']);
        }

        // Fallback for standard form submissions
        return back()->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        if (Gate::denies('delete', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticketId = $ticket->id; // Grab ID before soft-deleting
        $ticket->delete();

        // GLOBAL LOGGING: Enterprise Audit Trail
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Deleted Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticketId,
            'new_value' => 'Soft Deleted',
        ]);

        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }

    public function submitRating(Request $request, Ticket $ticket)
    {
        // Only the ticket creator can rate it
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Only the ticket creator can leave feedback.');
        }

        // Only allow rating if resolved or closed, and not already rated
        if (!in_array($ticket->status, ['Resolved', 'Closed']) || $ticket->rating) {
            abort(400, 'This ticket cannot be rated at this time.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'csat_feedback' => 'nullable|string|max:1000',
        ]);

        $ticket->update($validated);

        // Log the CSAT score in our Audit Logs
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Submitted CSAT Rating',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => "Rated {$validated['rating']} Stars",
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }

    public function confirmResolution(Ticket $ticket)
    {
        // Security: Only the ticket owner can do this
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Redirect them to the ticket page, but append a special anchor
        // to jump them straight down to the rating box!
        return redirect()->route('tickets.show', $ticket)->with('success', 'Please rate your experience below!');
    }

    public function rejectResolution(Ticket $ticket)
    {
        // Security: Only the ticket owner can do this
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        // Only process this if it's actually in a Resolved state
        if ($ticket->status === 'Resolved') {
            $ticket->update(['status' => 'Open']);

            // 1. Log it in the Global Audit Trail
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Rejected Resolution',
                'target_type' => 'Ticket',
                'target_id' => $ticket->id,
                'old_value' => 'Resolved',
                'new_value' => 'Open (Reopened via Email)',
            ]);

            // 2. Alert the Assigned Technician!
            if ($ticket->assignedTo) {
                $ticket->assignedTo->notify(new \App\Notifications\TicketReopenedNotification($ticket));
            }

            return redirect()->route('tickets.show', $ticket)->with('error', 'Ticket reopened. We have alerted the technician that you still need help.');
        }

        return redirect()->route('tickets.show', $ticket);
    }

    public function addCollaborator(Request $request, Ticket $ticket)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();

        // Security: Only the assigned agent or an admin can invite collaborators
        if ($ticket->assigned_to !== $currentUser->id && !$currentUser->hasRole('admin')) {
            abort(403, 'Only the assigned agent or an admin can invite collaborators.');
        }

        $invitedUser = \App\Models\User::findOrFail($request->user_id);

        // Prevent inviting the assigned agent or someone already collaborating
        if ($ticket->assigned_to === $invitedUser->id || $ticket->collaborators->contains($invitedUser->id)) {
            return back()->withErrors(['error' => 'This user is already working on this ticket.']);
        }

        // Attach the user to the ticket via the pivot table
        $ticket->collaborators()->attach($invitedUser->id);

        // Global Audit Logging
        \App\Models\AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Added Collaborator',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => 'Added: ' . $invitedUser->name,
        ]);

        // Send the notification
        $invitedUser->notify(new \App\Notifications\TicketCollaboratorNotification($ticket, $currentUser));

        return back()->with('success', $invitedUser->name . ' has been invited to collaborate.');
    }

    public function downloadAttachment(Ticket $ticket, \App\Models\Attachment $attachment)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Security check: Only people involved with the ticket can download this file
        $isCreator = $ticket->user_id === $user->id;
        $isAssignee = $ticket->assigned_to === $user->id;
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super-admin');
        $isCollaborator = $ticket->collaborators->contains($user->id);

        if (!$isCreator && !$isAssignee && !$isAdmin && !$isCollaborator) {
            abort(403, 'You do not have permission to view this attachment.');
        }

        // Ensure the requested file actually belongs to this specific ticket
        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $localDisk */
        $localDisk = \Illuminate\Support\Facades\Storage::disk('local');

        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

        // Fetch the file securely from the private disk
        if (!$localDisk->exists($attachment->file_path)) {

            // Fallback just in case you are testing with your older 'public' files
            if ($publicDisk->exists($attachment->file_path)) {
                // Use ->response() instead of ->download() for inline preview
                return $publicDisk->response($attachment->file_path);
            }

            abort(404, 'File not found on the server.');
        }

        // Use ->response() instead of ->download() for inline preview
        return $localDisk->response($attachment->file_path);
    }
}
