<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public $ticket;
    public $isNewUser;

    public function __construct(Ticket $ticket, $isNewUser = false)
    {
        $this->ticket = $ticket;
        $this->isNewUser = $isNewUser; // Flag to check if we auto-provisioned them
    }

    public function via($notifiable)
    {
        // This triggers BOTH the Email and the in-app Bell Icon!
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // 1. Fetch the dynamic template from your System Settings
        $template = Cache::get('email_template_created', 'Hello {user_name}, your ticket #{ticket_id} has been successfully created.');

        // 2. Swap out the variables
        $message = str_replace(
            ['{user_name}', '{ticket_id}', '{status}'],
            [$notifiable->name, $this->ticket->id, $this->ticket->status],
            $template
        );

        $mail = (new MailMessage)
            ->subject('Support Ticket Created: #' . $this->ticket->id)
            ->line($message)
            ->action('View Ticket Details', route('tickets.show', $this->ticket->id));

        // 3. If they were auto-provisioned, tell them how to log in!
        if ($this->isNewUser) {
            $mail->line('---')
                ->line('An account has been automatically created for you to track this issue.')
                ->line('To access the portal, simply click "Forgot Password" on the login screen and use this email address to set your secure password.');
        }

        return $mail;
    }

    public function toArray($notifiable)
    {
        // This array is what gets saved to the database for the Notification Bell
        return [
            'message' => 'Ticket #' . $this->ticket->id . ' has been created.',
            'ticket_id' => $this->ticket->id,
            'type' => 'ticket_created'
        ];
    }
}
