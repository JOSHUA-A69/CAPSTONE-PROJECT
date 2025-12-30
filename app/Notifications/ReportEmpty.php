<?php

namespace App\Notifications;

use App\Services\Reports\ReportResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReportEmpty extends Notification
{
    use Queueable;

    public function __construct(public ReportResult $result)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('No Data Found for Report')
            ->line('We generated your report but no matching data was found for the selected filters.')
            ->line('Try expanding the date range or removing filters.');
    }
}
