<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestorConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $confirmationUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Please Confirm Your Reservation - ' . ($this->reservation->service?->service_name ?? 'Unknown Service'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.requestor-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
