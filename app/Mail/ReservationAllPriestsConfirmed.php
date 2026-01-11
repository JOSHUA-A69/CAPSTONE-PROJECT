<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationAllPriestsConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $requestorName;
    public $priestNames;

    public function __construct(Reservation $reservation, string $requestorName, string $priestNames)
    {
        $this->reservation = $reservation;
        $this->requestorName = $requestorName;
        $this->priestNames = $priestNames;
    }

    public function build()
    {
        return $this->subject("✓ All Priests Confirmed - Ready for Approval - Reservation #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.all-priests-confirmed');
    }
}
