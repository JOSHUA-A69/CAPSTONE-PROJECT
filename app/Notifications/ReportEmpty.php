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
            ->view('emails.reports.empty', [
                'result' => $this->result,
            ]);
    }
}
