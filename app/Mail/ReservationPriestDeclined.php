<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationPriestDeclined extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public string $reason;
    public $priest;

    public function __construct(Reservation $reservation, string $reason)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
        $this->priest = $reservation->history()
            ->where('action', 'priest_declined')
            ->latest()
            ->first()
            ->performer ?? null;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Priest Declined - Reassignment Needed for Reservation #' . $this->reservation->reservation_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservations.priest-declined',
            with: [
                'reservation' => $this->reservation,
                'reason' => $this->reason,
                'priest' => $this->priest,
                'service' => $this->reservation->service,
                'venue' => $this->reservation->venue,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
