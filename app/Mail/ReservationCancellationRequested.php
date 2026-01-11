<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\ReservationCancellation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationCancellationRequested extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $cancellation;
    public $requestorName;
    public $recipientName;
    public $reason;

    public function __construct(Reservation $reservation, ReservationCancellation $cancellation, string $requestorName, ?string $recipientName = null)
    {
        $this->reservation = $reservation;
        $this->cancellation = $cancellation;
        $this->requestorName = $requestorName;
        $this->recipientName = $recipientName;
        $this->reason = $cancellation->reason;
    }

    public function build()
    {
        return $this->subject("🚫 Cancellation Request from {$this->requestorName}")
                    ->view('emails.reservations.cancellation-requested');
    }
}
