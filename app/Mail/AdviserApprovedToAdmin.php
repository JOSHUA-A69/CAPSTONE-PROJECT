<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdviserApprovedToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $remarks;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation, string $remarks = '')
    {
        $this->reservation = $reservation;
        $this->remarks = $remarks;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Adviser Approved: ' . ($this->reservation->service?->service_name ?? 'Unknown Service'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.adviser-approved-admin',
            with: [
                'reservation' => $this->reservation,
                'remarks' => $this->remarks,
                'requestor' => $this->reservation->user,
                'adviser' => $this->reservation->organization?->adviser,
            ],
        );
    }
}
