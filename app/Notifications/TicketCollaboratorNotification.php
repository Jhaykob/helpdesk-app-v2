<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCollaboratorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;
    protected $inviter;

    public function __construct(Ticket $ticket, User $inviter)
    {
        $this->ticket = $ticket;
        $this->inviter = $inviter;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Collaboration Request] Ticket #{$this->ticket->id}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->inviter->name} has requested your assistance on a ticket.")
            ->line("**Title:** {$this->ticket->title}")
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('You now have access to view this ticket and leave internal notes.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title' => 'Collaboration Request',
            'message' => "{$this->inviter->name} invited you to assist on Ticket #{$this->ticket->id}",
            'url' => route('tickets.show', $this->ticket->id, false) // Using the safe relative URL!
        ];
    }
}
