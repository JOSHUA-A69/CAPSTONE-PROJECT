<?php

namespace App\Mail;

use App\Models\OrganizationBookingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationBookingApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $bookingRequest;
    public $comments;

    /**
     * Create a new message instance.
     */
    public function __construct(OrganizationBookingRequest $bookingRequest, $comments = null)
    {
        $this->bookingRequest = $bookingRequest;
        $this->comments = $comments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Organization Booking Request Approved - ' . $this->bookingRequest->activity_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.organization-booking.approval-notification',
            with: [
                'request' => $this->bookingRequest,
                'organization' => $this->bookingRequest->organization,
                'adviser' => $this->bookingRequest->organization?->adviser,
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