<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationPriestRestoredToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $priestName;
    public $requestorName;

    public function __construct(Reservation $reservation, string $priestName)
    {
        $this->reservation = $reservation;
        $this->priestName = $priestName;
        $this->requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
    }

    public function build()
    {
        return $this->subject("✓ {$this->priestName} Restored Assignment - Reservation #{$this->reservation->reservation_id}")
                    ->view('emails.reservations.priest-restored-admin');
    }
}
