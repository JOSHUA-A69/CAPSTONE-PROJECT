<?php

namespace App\Mail;

use App\Models\OrganizationBookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationBookingRejectionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingRequest;
    public $reason;
    public $comments;

    /**
     * Create a new message instance.
     */
    public function __construct(OrganizationBookingRequest $bookingRequest, $reason, $comments = null)
    {
        $this->bookingRequest = $bookingRequest;
        $this->reason = $reason;
        $this->comments = $comments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organization Booking Request Update - ' . $this->bookingRequest->activity_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-booking.rejection-notification',
            with: [
                'request' => $this->bookingRequest,
                'organization' => $this->bookingRequest->organization,
                'adviser' => $this->bookingRequest->organization?->adviser,
                'reason' => $this->reason,
                'comments' => $this->comments,
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