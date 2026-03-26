<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdviserPriestConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $priestName,
        public User $adviser
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✓ ' . $this->priestName . ' Confirmed - Reservation #' . $this->reservation->reservation_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.adviser-priest-confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
