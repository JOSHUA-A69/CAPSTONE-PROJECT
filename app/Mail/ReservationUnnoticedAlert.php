<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationUnnoticedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $hoursPending;
    public $serviceName;
    public $scheduleDate;
    public $requestorName;
    public $orgName;
    public $adviserName;
    public $adviserEmail;
    public $adviserPhone;

    public function __construct(Reservation $reservation, $adviser, $hoursPending)
    {
        $this->reservation = $reservation;
        $this->hoursPending = $hoursPending;
        $this->serviceName = $reservation->service?->service_name ?? 'Unknown Service';
        $this->scheduleDate = $reservation->schedule_date; // Keep as object for formatting in blade if handled there, or format here
        $this->requestorName = $reservation->user ? ($reservation->user->first_name . ' ' . $reservation->user->last_name) : 'Unknown User';
        $this->orgName = $reservation->organization?->org_name ?? 'N/A';
        
        $this->adviserName = $adviser ? ($adviser->full_name ?? $adviser->first_name . ' ' . $adviser->last_name) : 'Unknown';
        $this->adviserEmail = $adviser ? $adviser->email : 'N/A';
        $this->adviserPhone = $adviser ? ($adviser->phone ?? 'N/A') : 'N/A';
    }

    public function build()
    {
        return $this->subject("⚠️ Unnoticed Reservation #{$this->reservation->reservation_id} - Adviser No Response (24h+)")
                    ->view('emails.reservations.unnoticed-alert');
    }
}
