<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCancellationUnresponsive extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $role;
    public $contactInfo;

    public function __construct(Reservation $reservation, string $role, array $contactInfo)
    {
        $this->reservation = $reservation;
        $this->role = $role;
        $this->contactInfo = $contactInfo;
    }

    public function build()
    {
        return $this->subject("⚠️ Unresponsive " . ucfirst($this->role) . " - Follow-up Needed")
                    ->view('emails.reservations.cancellation-unresponsive');
    }
}
