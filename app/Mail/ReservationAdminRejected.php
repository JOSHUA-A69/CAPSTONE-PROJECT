<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationAdminRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $reason;
    public $adminName;
    public $requestor;

    public function __construct(Reservation $reservation, string $reason, string $adminName)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
        $this->adminName = $adminName;
        $this->requestor = $reservation->user;
    }

    public function build()
    {
        return $this->subject('Reservation Not Approved by Admin')
                    ->view('emails.reservations.admin-rejected');
    }
}
