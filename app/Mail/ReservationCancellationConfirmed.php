<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCancellationConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $requestor;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->requestor = $reservation->user;
    }

    public function build()
    {
        return $this->subject("✓ Cancellation Confirmed")
                    ->view('emails.reservations.cancellation-confirmed');
    }
}
