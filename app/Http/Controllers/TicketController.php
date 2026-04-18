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
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            $tickets = Ticket::with(['user', 'assignedTo'])->latest()->get();
        } elseif ($user->role->name === 'agent') {
            $tickets = Ticket::where('status', 'Unassigned')
                ->orWhere('assigned_to', $user->id)
                ->with(['user', 'assignedTo'])
                ->latest()
                ->get();
        } else {
            $tickets = Ticket::where('user_id', $user->id)->with(['assignedTo'])->latest()->get();
        }

        return view('tickets.index', compact('tickets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:Low,Medium,High',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        $ticket = Ticket::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'Unassigned',
            'user_id' => Auth::id(),
        ]);

        // LOCAL LOGGING: Ticket History
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'created the ticket',
        ]);

        // GLOBAL LOGGING: Enterprise Audit Trail
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'new_value' => 'Status: Unassigned',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attachments', $fileName, 'public');

            $ticket->attachments()->create([
                'file_name' => $fileName,
                'file_path' => $filePath,
            ]);
        }

        return redirect()->route('tickets.index')->with('success', 'Ticket created successfully.');
    }

    public function claim(Ticket $ticket)
    {
        if (Gate::denies('claim', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->update([
            'assigned_to' => Auth::id(),
            'status' => 'Open',
        ]);

        // LOCAL LOGGING: Ticket History
        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'claimed the ticket',
            'new_value' => 'Open',
        ]);

        // GLOBAL LOGGING: Enterprise Audit Trail
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Claimed Ticket',
            'target_type' => 'Ticket',
            'target_id' => $ticket->id,
            'old_value' => 'Unassigned',
            'new_value' => 'Open',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'You have successfully claimed this ticket.');
    }

    public function show(Ticket $ticket)
    {
        if (Gate::denies('view', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['comments.user', 'user', 'assignedTo', 'activities.user']);

        // Fetch macros so the view can use them
        $macros = Macro::all();
        return view('tickets.show', compact('ticket', 'macros'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (Gate::denies('update', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Open,Pending Customer,Pending Technician,Resolved,Closed',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update($validated);

        if ($oldStatus !== $validated['status']) {
            // LOCAL LOGGING: Ticket History
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'changed status',
                'old_value' => $oldStatus,
                'new_value' => $validated['status'],
            ]);

            // GLOBAL LOGGING: Enterprise Audit Trail
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Changed Ticket Status',
                'target_type' => 'Ticket',
                'target_id' => $ticket->id,
                'old_value' => $oldStatus,
                'new_value' => $validated['status'],
            ]);
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket status updated successfully.');
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
}
