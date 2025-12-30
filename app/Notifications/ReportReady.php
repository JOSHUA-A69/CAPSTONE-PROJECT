<?php

namespace App\Notifications;

use App\Services\Reports\ReportResult;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReportReady extends Notification
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
        $url = url('/reports/download?path=' . urlencode($this->result->path));
        return (new MailMessage)
            ->subject('Your report is ready')
            ->line('The report has been generated successfully.')
            ->action('Download Report', $url)
            ->line('Rows: ' . $this->result->rowsCount);
    }
}
