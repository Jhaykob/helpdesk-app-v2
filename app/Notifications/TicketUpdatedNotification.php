<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketUpdatedNotification extends Notification
{
    use Queueable;

    public $ticket;
    public $message;

    public function __construct(Ticket $ticket, $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    // Tell Laravel to store this in the database
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    // Define the exact data we want to save
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'message' => $this->message,
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
