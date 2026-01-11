<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestorPriestReassigned extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $oldPriestName;
    public $newPriestName;
    public $requestorName;

    public function __construct(Reservation $reservation, string $oldPriestName, string $newPriestName)
    {
        $this->reservation = $reservation;
        $this->oldPriestName = $oldPriestName;
        $this->newPriestName = $newPriestName;
        $this->requestorName = $reservation->user->first_name;
    }

    public function build()
    {
        return $this->subject("Priest Reassignment - Reservation #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.requestor-priest-reassigned');
    }
}
