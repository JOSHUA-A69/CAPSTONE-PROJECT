<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationFinalApprovalToAdviser extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $requestorName;
    public $venueName;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->requestorName = $reservation->user->first_name . ' ' . $reservation->user->last_name;
        $this->venueName = $reservation->custom_venue_name ?? $reservation->venue->name ?? 'N/A';
    }

    public function build()
    {
        return $this->subject("Reservation Approved - #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.final-approval-adviser');
    }
}
