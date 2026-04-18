<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'admin') {
            // Admin sees stats for the entire system
            $totalTickets = Ticket::count();
            $openTickets = Ticket::where('status', 'Open')->count();
            $inProgressTickets = Ticket::where('status', 'In Progress')->count();
            $resolvedTickets = Ticket::where('status', 'Resolved')->count();
        } else {
            // Regular user sees only their own stats
            $totalTickets = Ticket::where('user_id', $user->id)->count();
            $openTickets = Ticket::where('user_id', $user->id)->where('status', 'Open')->count();
            $inProgressTickets = Ticket::where('user_id', $user->id)->where('status', 'In Progress')->count();
            $resolvedTickets = Ticket::where('user_id', $user->id)->where('status', 'Resolved')->count();
        }

        return view('dashboard', compact(
            'totalTickets',
            'openTickets',
            'inProgressTickets',
            'resolvedTickets'
        ));
    }
}
