<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();

        // ========================================================================
        // 1. ADMIN / SUPER-ADMIN VIEW (The Command Center & Appraisals)
        // ========================================================================
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {

            // Tier 1: Global KPIs
            $kpis = [
                'total_open' => Ticket::whereNotIn('status', ['Resolved', 'Closed'])->count(),
                'unassigned' => Ticket::whereNull('assigned_to')->whereNotIn('status', ['Resolved', 'Closed'])->count(),
                'sla_breaches' => Ticket::where('is_breaching_sla', true)->whereNotIn('status', ['Resolved', 'Closed'])->count(),
                'global_csat' => round(Ticket::whereNotNull('rating')->avg('rating') ?? 0, 1),
            ];

            // Tier 2: Chart Data (Grouped by Status and Priority)
            $ticketsByStatus = Ticket::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status')->toArray();
            $ticketsByPriority = Ticket::selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority')->toArray();

            // Tier 3: Agent Appraisal Matrix (Using highly optimized sub-queries)
            $agents = User::role('agent')
                ->withCount(['assignedTickets as resolved_this_month' => function ($query) use ($startOfMonth) {
                    $query->whereIn('status', ['Resolved', 'Closed'])
                        ->where('updated_at', '>=', $startOfMonth);
                }])
                ->withAvg(['assignedTickets as csat_average' => function ($query) use ($startOfMonth) {
                    $query->whereNotNull('rating')
                        ->where('updated_at', '>=', $startOfMonth);
                }], 'rating')
                ->withCount(['assignedTickets as active_sla_breaches' => function ($query) {
                    $query->where('is_breaching_sla', true)
                        ->whereNotIn('status', ['Resolved', 'Closed']);
                }])
                ->orderByDesc('resolved_this_month')
                ->get();

            return view('dashboard', compact('kpis', 'ticketsByStatus', 'ticketsByPriority', 'agents', 'user'));
        }

        // ========================================================================
        // 2. AGENT VIEW (The Personal Mirror)
        // ========================================================================
        if ($user->hasRole('agent')) {

            // Tier 1: Personal Appraisals
            $personalKpis = [
                'resolved_this_month' => Ticket::where('assigned_to', $user->id)
                    ->whereIn('status', ['Resolved', 'Closed'])
                    ->where('updated_at', '>=', $startOfMonth)
                    ->count(),
                'csat_average' => round(Ticket::where('assigned_to', $user->id)
                    ->whereNotNull('rating')
                    ->where('updated_at', '>=', $startOfMonth)
                    ->avg('rating') ?? 0, 1),
                'active_sla_breaches' => Ticket::where('assigned_to', $user->id)
                    ->where('is_breaching_sla', true)
                    ->whereNotIn('status', ['Resolved', 'Closed'])
                    ->count(),
            ];

            // Tier 2: Active Personal Queue (Needs Attention)
            $activeTickets = Ticket::where('assigned_to', $user->id)
                ->whereNotIn('status', ['Resolved', 'Closed'])
                // Cross-database compatible custom sorting using CASE
                ->orderByRaw("
                    CASE status
                        WHEN 'Pending Technician' THEN 1
                        WHEN 'Open' THEN 2
                        WHEN 'Pending Customer' THEN 3
                        ELSE 4
                    END
                ")
                ->orderBy('sla_deadline', 'asc')
                ->take(10)
                ->get();

            // Tier 3: Recent Customer Feedback
            $recentFeedback = Ticket::where('assigned_to', $user->id)
                ->whereNotNull('rating')
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return view('dashboard', compact('personalKpis', 'activeTickets', 'recentFeedback', 'user'));
        }

        // ========================================================================
        // 3. CUSTOMER VIEW (The Self-Service Portal)
        // ========================================================================

        $customerKpis = [
            'open' => Ticket::where('user_id', $user->id)->whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'closed' => Ticket::where('user_id', $user->id)->whereIn('status', ['Resolved', 'Closed'])->count(),
        ];

        $myTickets = Ticket::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('customerKpis', 'myTickets', 'user'));
    }
}
