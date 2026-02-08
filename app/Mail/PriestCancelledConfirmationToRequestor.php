<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriestCancelledConfirmationToRequestor extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $priestName;
    public $reason;
    public $requestor;
    public $venueName;

    public function __construct(Reservation $reservation, string $priestName, string $reason)
    {
        $this->reservation = $reservation;
        $this->priestName = $priestName;
        $this->reason = $reason;
        $this->requestor = $reservation->user;
        $this->venueName = $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A';
    }

    public function build()
    {
        return $this->subject("Reservation Cancellation - {$this->priestName} Cancelled Assignment")
                    ->view('emails.reservations.priest-cancelled-confirmation-requestor');
    }
}
