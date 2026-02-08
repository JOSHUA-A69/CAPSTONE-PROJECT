<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriestCancelledConfirmationToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $priestName;
    public $reason;
    public $requestorName;

    public function __construct(Reservation $reservation, string $priestName, string $reason)
    {
        $this->reservation = $reservation;
        $this->priestName = $priestName;
        $this->reason = $reason;
        $this->requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
    }

    public function build()
    {
        return $this->subject("⚠️ URGENT: {$this->priestName} Cancelled Confirmed Reservation #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.priest-cancelled-confirmation-admin');
    }
}
