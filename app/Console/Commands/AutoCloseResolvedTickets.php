<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class AutoCloseResolvedTickets extends Command
{
    // The terminal command signature
    protected $signature = 'tickets:auto-close {--days=3 : The number of days to wait}';

    protected $description = 'Automatically close tickets that have been resolved for a specific number of days without user feedback.';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        // Find all tickets that are Resolved, and haven't been updated in X days
        $staleTickets = Ticket::where('status', 'Resolved')
            ->where('updated_at', '<', $cutoffDate)
            ->get();

        if ($staleTickets->isEmpty()) {
            $this->info("No stale resolved tickets found.");
            return;
        }

        // Grab a system admin to attribute the Audit Log to (since no human clicked a button)
        $systemUser = User::role('admin')->first();
        $systemUserId = $systemUser ? $systemUser->id : 1;

        $count = 0;
        foreach ($staleTickets as $ticket) {
            $ticket->update(['status' => 'Closed']);

            // Create a clear audit trail for why this happened
            AuditLog::create([
                'user_id' => $systemUserId,
                'action' => 'Auto-Closed Ticket',
                'target_type' => 'Ticket',
                'target_id' => $ticket->id,
                'old_value' => 'Resolved',
                'new_value' => 'Closed (72-hour inactivity timeout)',
            ]);

            $count++;
        }

        $this->info("Successfully auto-closed {$count} stale tickets.");
    }
}
