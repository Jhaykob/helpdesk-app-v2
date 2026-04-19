<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Action Required] New Unassigned Ticket: #{$this->ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new ticket has been submitted by {$this->ticket->user->name} and is waiting to be claimed.")
            ->line("**Priority:** {$this->ticket->priority}")
            ->line("**Title:** {$this->ticket->title}")
            ->action('Claim Ticket', route('tickets.show', $this->ticket))
            ->line('Please log in to review and claim this ticket.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => 'New Unassigned Ticket',
            'message' => "{$this->ticket->user->name} submitted: {$this->ticket->title}",
            // The 'false' parameter forces a relative URL (e.g., /tickets/8)
            'url' => route('tickets.show', $this->ticket->id)
        ];
    }
}
