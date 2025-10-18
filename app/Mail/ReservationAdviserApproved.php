<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationAdviserApproved extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $remarks;

    public function __construct(Reservation $reservation, string $remarks = '')
    {
        $this->reservation = $reservation;
        $this->remarks = $remarks;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reservation Approved by Adviser - ' . $this->reservation->service->service_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.adviser-approved',
            with: [
                'reservation' => $this->reservation,
                'requestor' => $this->reservation->user,
                'adviser' => $this->reservation->organization->adviser,
                'remarks' => $this->remarks,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
