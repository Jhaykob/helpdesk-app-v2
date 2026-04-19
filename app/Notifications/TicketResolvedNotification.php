<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketResolvedNotification extends Notification implements ShouldQueue
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
        // 1. Fetch the dynamic template from your System Settings (Pillar 2)
        $template = Setting::get('email_template_resolved', 'Hello {user_name}, your ticket #{ticket_id} has been marked as resolved.');

        // 2. Swap out the placeholders with real data
        $bodyMessage = str_replace(
            ['{user_name}', '{ticket_id}', '{status}'],
            [$notifiable->name, $this->ticket->id, $this->ticket->status],
            $template
        );

        // 3. Render our custom Blade view, passing the required variables
        return (new MailMessage)
            ->subject("Update on Ticket #{$this->ticket->id}: Marked as Resolved")
            ->view('emails.ticket-resolved', [
                'ticket' => $this->ticket,
                'bodyMessage' => $bodyMessage
            ]);
    }
}
