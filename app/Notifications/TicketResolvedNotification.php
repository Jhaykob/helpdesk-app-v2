<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketResolvedNotification extends Notification
{
    use Queueable;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function via($notifiable)
    {
        // This triggers BOTH the Email and the in-app Bell Icon!
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // 1. Fetch the dynamic template from your System Settings
        $template = Cache::get('email_template_resolved', 'Hello {user_name}, your ticket #{ticket_id} has been marked as resolved.');

        // 2. Swap out the variables dynamically
        $message = str_replace(
            ['{user_name}', '{ticket_id}', '{status}'],
            [$notifiable->name, $this->ticket->id, $this->ticket->status],
            $template
        );

        return (new MailMessage)
            ->subject('Ticket Resolved: #' . $this->ticket->id)
            ->line($message)
            ->action('Confirm Resolution & Rate Support', route('tickets.show', $this->ticket->id))
            ->line('Please review the ticket and let us know if your issue is fully fixed by submitting your feedback.');
    }

    public function toArray($notifiable)
    {
        // This array is what gets saved to the database for the Notification Bell
        return [
            'message' => 'Ticket #' . $this->ticket->id . ' has been marked as resolved. Please confirm!',
            'ticket_id' => $this->ticket->id,
            'type' => 'ticket_resolved'
        ];
    }
}
