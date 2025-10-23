<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationCancelled extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $reason;
    public string $cancelledBy;

    public function __construct(Reservation $reservation, string $reason, string $cancelledBy)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
        $this->cancelledBy = $cancelledBy;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reservation Cancelled - ' . $this->reservation->service->service_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.cancelled',
            with: [
                'reservation' => $this->reservation,
                'reason' => $this->reason,
                'cancelledBy' => $this->cancelledBy,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
