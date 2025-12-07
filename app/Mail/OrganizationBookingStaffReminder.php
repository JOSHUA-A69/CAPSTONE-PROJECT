<?php

namespace App\Mail;

use App\Models\OrganizationBookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationBookingStaffReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingRequest;

    /**
     * Create a new message instance.
     */
    public function __construct(OrganizationBookingRequest $bookingRequest)
    {
        $this->bookingRequest = $bookingRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'REMINDER: Overdue Organization Booking Request - ' . $this->bookingRequest->activity_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-booking.staff-reminder',
            with: [
                'request' => $this->bookingRequest,
                'organization' => $this->bookingRequest->organization,
                'adviser' => $this->bookingRequest->organization->adviser,
                'requestor' => $this->bookingRequest->requestor,
                'daysPending' => $this->bookingRequest->adviser_notified_at ? $this->bookingRequest->adviser_notified_at->diffInDays(now()) : 0,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}