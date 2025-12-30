<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReportFailed extends Notification
{
    use Queueable;

    public function __construct(public string $message)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Report generation failed')
            ->line('The report could not be generated.')
            ->line('Reason: ' . $this->message);
    }
}
