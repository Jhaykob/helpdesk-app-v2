<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // <-- ADDED THIS IMPORT

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
        // FIX: Using Gate makes the IDE happy!
        if (Gate::denies('claim', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->update([
            'assigned_to' => Auth::id(),
            'status' => 'Open',
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'You have successfully claimed this ticket.');
    }

    public function show(Ticket $ticket)
    {
        if (Gate::denies('view', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->load(['comments.user', 'user', 'assignedTo']);
        return view('tickets.show', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        if (Gate::denies('update', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Open,Pending Customer,Pending Technician,Resolved,Closed',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket status updated successfully.');
    }

    public function destroy(Ticket $ticket)
    {
        if (Gate::denies('delete', $ticket)) {
            abort(403, 'Unauthorized action.');
        }

        $ticket->delete();
        return redirect()->route('tickets.index')->with('success', 'Ticket deleted successfully.');
    }
}
