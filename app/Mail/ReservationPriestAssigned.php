<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationPriestAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You Have Been Assigned to Officiate - ' . $this->reservation->service->service_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.priest-assigned',
            with: [
                'reservation' => $this->reservation,
                'priest' => $this->reservation->officiant,
                'service' => $this->reservation->service,
                'venue' => $this->reservation->venue,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
