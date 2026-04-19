<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReopenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("URGENT: Ticket #{$this->ticket->id} Reopened by Customer")
            ->greeting("Hello {$notifiable->name},")
            ->line("The customer for Ticket #{$this->ticket->id} ({$this->ticket->title}) has indicated that their issue is not completely fixed.")
            ->line("The ticket status has been automatically changed back to Open.")
            ->action('View Ticket', route('tickets.show', $this->ticket))
            ->line('Please review the ticket and reach out to the customer as soon as possible.');
    }
}
