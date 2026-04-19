<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\AuditLog;
use App\Models\Macro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // ---------------------------------------------------------
        // MODAL DATA: Fetch users for the "On Behalf Of" agent tool
        // ---------------------------------------------------------
        $users = [];
        if ($user->hasRole('admin') || $user->hasRole('agent') || $user->hasRole('super-admin')) {
            $users = User::orderBy('name')->get();
        }

        return view('tickets.index', compact('tickets', 'users'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $isAgentOrAdmin = $user->hasRole('admin') || $user->hasRole('agent') || $user->hasRole('super-admin');

        // 1. Dynamic Validation Rules
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|integer',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ];

        // Add extra validation if the user submitting is an agent using the On Behalf Of tools
        if ($isAgentOrAdmin) {
            $rules['requester_type'] = 'required|in:self,existing,new';
            $rules['existing_user_id'] = 'required_if:requester_type,existing|nullable|exists:users,id';
            $rules['new_user_name'] = 'required_if:requester_type,new|nullable|string|max:255';
            $rules['new_user_email'] = 'required_if:requester_type,new|nullable|email|unique:users,email';
        }

        $validated = $request->validate($rules);

        // 2. Determine the Ticket Owner (Auto-Provisioning Logic)
        $ticketOwnerId = $user->id; // Default to the person submitting the form

        if ($isAgentOrAdmin) {
            if ($request->requester_type === 'existing') {
                $ticketOwnerId = $request->existing_user_id;
            } elseif ($request->requester_type === 'new') {
                // Auto-Provision the new user!
                $newUser = User::create([
                    'name' => $request->new_user_name,
                    'email' => $request->new_user_email,
                    'password' => Hash::make(Str::random(16)), // Secure random password
                ]);
                $newUser->assignRole('user'); // Give them standard user permissions
                $ticketOwnerId = $newUser->id;
            }
        }

        // 3. Determine Assignment & Status
        $assignedTo = null;
        $status = 'Unassigned';

        // Did the agent check "Assign to me"?
        if ($isAgentOrAdmin && $request->has('assign_to_me')) {
            $assignedTo = $user->id;
            $status = 'Open';
        }

        // 4. Handle the secure file attachment
        $path = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('attachments', $fileName, 'local');
        }

        // 5. Create the Ticket
        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'priority' => $validated['priority'],
            'status' => $status,
            'user_id' => $ticketOwnerId,
            'assigned_to' => $assignedTo,
            // I REMOVED the attachment_path line from here!
            'sla_deadline' => \Carbon\Carbon::now()->addHours(
                \Illuminate\Support\Facades\Cache::get('sla_' . strtolower($validated['priority']) . '_hours', 24)
            ),
        ]);

        if ($path) {
            $ticket->attachments()->create([
                'file_name' => $fileName,
                'file_path' => $path,
            ]);
        }

        // 6. Local Ticket Activity History
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'action' => 'created the ticket',
        ]);

        // 7. Global Audit Logging
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Created Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => $ticketOwnerId !== $user->id ? "Created on behalf of User ID: {$ticketOwnerId}" : 'Standard creation',
        ]);

        // 8. Notifications

        // A. Notify the Ticket Owner (The Customer) - Triggers Email AND Bell Icon
        if (\Illuminate\Support\Facades\Cache::get('send_email_notifications', '1') == '1') {
            $ticketOwner = User::find($ticketOwnerId);

            // Check if this was a brand new auto-provisioned user
            $isNewUser = $isAgentOrAdmin && $request->requester_type === 'new';

            // Fire the notification!
            $ticketOwner->notify(new \App\Notifications\TicketCreatedNotification($ticket, $isNewUser));
        }

        // B. Notify the Staff (Only if the ticket drops into the unassigned queue)
        if (is_null($assignedTo)) {
            $staffMembers = User::role(['admin', 'agent', 'super-admin'])->get();
            \Illuminate\Support\Facades\Notification::send($staffMembers, new \App\Notifications\NewTicketNotification($ticket));
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    // ... (Keep the rest of your methods exactly as they are: claim, show, update, destroy, submitRating, etc.)

    public function claim(Request $request, Ticket $ticket)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasRole('admin') && !$user->hasRole('agent')) {
            abort(403, 'Unauthorized action.');
        }

        if (!is_null($ticket->assigned_to)) {
            return back()->withErrors(['error' => 'This ticket has already been claimed by another agent.']);
        }

        $ticket->update([
            'assigned_to' => $user->id,
            'status' => 'Open',
        ]);

        AuditLog::create([
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

        $ticket->load(['comments.user', 'user', 'assignedTo', 'activities.user']);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $macros = Macro::where('is_global', true)
            ->orWhere('user_id', $user->id)
            ->orderBy('title')
            ->get();

        $agents = [];
        if ($user->hasRole('admin') || $user->hasRole('agent')) {
            $agents = User::role(['admin', 'agent'])->get();
        }

        return view('tickets.show', compact('ticket', 'macros', 'agents'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (!in_array(Auth::user()->role->name, ['admin', 'agent', 'super-admin'])) {
            abort(403, 'Unauthorized action.');
        }

        if ($ticket->status === 'Closed' && Auth::user()->role->name !== 'admin') {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Closed tickets cannot be modified.'], 403);
            }
            abort(403, 'Closed tickets cannot be modified.');
        }

        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|string',
            'assigned_to' => 'sometimes|nullable',
        ]);

        foreach ($validated as $key => $newValue) {
            $oldValue = $ticket->{$key};

            if ($oldValue != $newValue) {
                $ticket->update([$key => $newValue]);

                $friendlyKey = ucfirst(str_replace('_', ' ', $key));
                $friendlyOld = $oldValue;
                $friendlyNew = $newValue;

                if ($key === 'assigned_to') {
                    $friendlyOld = $oldValue ? User::find($oldValue)->name : 'Unassigned';
                    $friendlyNew = $newValue ? User::find($newValue)->name : 'Unassigned';
                }

                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => "Updated {$friendlyKey}",
                    'target_type' => 'Ticket',
                    'target_id' => $ticket->id,
                    'old_value' => (string) $friendlyOld,
                    'new_value' => (string) $friendlyNew,
                ]);

                if ($key === 'status' && in_array($oldValue, ['Resolved', 'Closed']) && !in_array($newValue, ['Resolved', 'Closed'])) {
                    if ($ticket->rating !== null) {
                        $ticket->update([
                            'rating' => null,
                            'csat_feedback' => null
                        ]);

                        AuditLog::create([
                            'user_id' => Auth::id(),
                            'action' => 'Reset CSAT Score',
                            'target_type' => 'Ticket',
                            'target_id' => $ticket->id,
                            'old_value' => "Previous Rating: {$ticket->rating} Stars",
                            'new_value' => 'Cleared due to ticket reopen'
                        ]);
                    }
                }

                if ($key === 'status' && $newValue === 'Resolved' && $oldValue !== 'Resolved') {
                    if (\Illuminate\Support\Facades\Cache::get('send_email_notifications', '1') == '1') {
                        $ticket->user->notify(new \App\Notifications\TicketResolvedNotification($ticket));
                    }
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Ticket updated successfully!']);
        }

        return back()->with('success', 'Ticket updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        if (Gate::denies('delete', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticketId = $ticket->id;
        $ticket->delete();

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
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'csat_feedback' => 'nullable|string|max:500',
        ]);

        if (Auth::id() !== $ticket->user_id) {
            abort(403);
        }

        $ticket->update([
            'rating' => $request->rating,
            'csat_feedback' => $request->csat_feedback,
            'status' => 'Closed',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Closed Ticket via CSAT',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => 'Status changed to Closed. Rating: ' . $request->rating . ' Stars',
        ]);

        return back()->with('success', 'Thank you for your feedback! This ticket has now been officially closed.');
    }

    public function confirmResolution(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Thank you for confirming!')
            ->with('resolution_confirmed', true);
    }

    public function rejectResolution(Ticket $ticket)
    {
        if (Auth::id() !== $ticket->user_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($ticket->status === 'Resolved') {
            $ticket->update(['status' => 'Open']);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Rejected Resolution',
                'target_type' => 'Ticket',
                'target_id' => $ticket->id,
                'old_value' => 'Resolved',
                'new_value' => 'Open (Reopened via Email)',
            ]);

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

        if ($ticket->assigned_to !== $currentUser->id && !$currentUser->hasRole('admin')) {
            abort(403, 'Only the assigned agent or an admin can invite collaborators.');
        }

        $invitedUser = User::findOrFail($request->user_id);

        if ($ticket->assigned_to === $invitedUser->id || $ticket->collaborators->contains($invitedUser->id)) {
            return back()->withErrors(['error' => 'This user is already working on this ticket.']);
        }

        $ticket->collaborators()->attach($invitedUser->id);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Added Collaborator',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => 'Added: ' . $invitedUser->name,
        ]);

        $invitedUser->notify(new \App\Notifications\TicketCollaboratorNotification($ticket, $currentUser));

        return back()->with('success', $invitedUser->name . ' has been invited to collaborate.');
    }

    public function downloadAttachment(Ticket $ticket, \App\Models\Attachment $attachment)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $isCreator = $ticket->user_id === $user->id;
        $isAssignee = $ticket->assigned_to === $user->id;
        $isAdmin = $user->hasRole('admin') || $user->hasRole('super-admin');
        $isCollaborator = $ticket->collaborators->contains($user->id);

        if (!$isCreator && !$isAssignee && !$isAdmin && !$isCollaborator) {
            abort(403, 'You do not have permission to view this attachment.');
        }

        if ($attachment->ticket_id !== $ticket->id) {
            abort(404);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $localDisk */
        $localDisk = \Illuminate\Support\Facades\Storage::disk('local');
        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
        $publicDisk = \Illuminate\Support\Facades\Storage::disk('public');

        if (!$localDisk->exists($attachment->file_path)) {
            if ($publicDisk->exists($attachment->file_path)) {
                return $publicDisk->response($attachment->file_path);
            }
            abort(404, 'File not found on the server.');
        }

        return $localDisk->response($attachment->file_path);
    }
}
