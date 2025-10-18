<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationAdviserRejected extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $reason;

    public function __construct(Reservation $reservation, string $reason)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reservation Rejected by Adviser - ' . $this->reservation->service->service_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.adviser-rejected',
            with: [
                'reservation' => $this->reservation,
                'requestor' => $this->reservation->user,
                'adviser' => $this->reservation->organization->adviser,
                'reason' => $this->reason,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
