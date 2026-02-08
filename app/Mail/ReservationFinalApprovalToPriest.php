<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationFinalApprovalToPriest extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $priestName;
    public $requestorName;
    public $venueName;
    public $isMainPriest;

    public function __construct(Reservation $reservation, string $priestName, bool $isMainPriest = false)
    {
        $this->reservation = $reservation;
        $this->priestName = $priestName;
        $this->isMainPriest = $isMainPriest;
        $this->requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
        $this->venueName = $reservation->custom_venue_name ?? $reservation->venue?->name ?? 'N/A';
    }

    public function build()
    {
        return $this->subject("You have a confirmed assignment - #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.final-approval-priest');
    }
}
