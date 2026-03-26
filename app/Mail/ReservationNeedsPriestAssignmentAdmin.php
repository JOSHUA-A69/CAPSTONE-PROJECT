<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationNeedsPriestAssignmentAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $serviceName = $this->reservation->service?->service_name ?? 'Unknown Service';

        return new Envelope(
            subject: 'Priest Assignment Needed: ' . $serviceName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.needs-priest-assignment-admin',
            with: [
                'reservation' => $this->reservation,
                'requestor' => $this->reservation->user,
            ],
        );
    }
}
