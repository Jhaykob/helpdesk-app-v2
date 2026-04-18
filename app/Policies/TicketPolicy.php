<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    // Who can view a specific ticket?
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->role->name === 'admin' || $user->role->name === 'agent') {
            return true; // Admins and Agents can view all tickets
        }

        // Regular users can only view their own tickets
        return $user->id === $ticket->user_id;
    }

    // Who can update a ticket's status/priority?
    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role->name === 'admin') {
            return true;
        }

        // Agents can only update tickets that are assigned to them
        if ($user->role->name === 'agent') {
            return $user->id === $ticket->assigned_to;
        }

        return false; // Users cannot update tickets after creating them
    }

    // Who can completely delete a ticket?
    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->role->name === 'admin'; // Strict: Only Admins
    }

    // Custom Method: Who can claim an unassigned ticket?
    public function claim(User $user, Ticket $ticket): bool
    {
        return in_array($user->role->name, ['admin', 'agent']) && $ticket->status === 'Unassigned';
    }
}
