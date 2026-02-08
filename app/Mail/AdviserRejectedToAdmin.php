<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdviserRejectedToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, string $reason = '')
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Adviser Rejected: ' . ($this->reservation->service?->service_name ?? 'Unknown Service'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.adviser-rejected-admin',
            with: [
                'reservation' => $this->reservation,
                'reason' => $this->reason,
                'requestor' => $this->reservation->user,
                'adviser' => $this->reservation->organization?->adviser,
            ],
        );
    }
}
