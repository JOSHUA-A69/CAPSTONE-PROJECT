<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestorConfirmedToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $requestorName;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->requestorName = $reservation->user->first_name . ' ' . $reservation->user->last_name;
    }

    public function build()
    {
        return $this->subject('Requestor Confirmed - Reservation #' . $this->reservation->reservation_id)
                    ->view('emails.reservations.requestor-confirmed-admin');
    }
}
