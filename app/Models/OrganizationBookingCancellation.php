<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Organization Booking Cancellation Model
 *
 * Handles cancellation requests for confirmed organization bookings
 * that require adviser approval before cancellation.
 */
class OrganizationBookingCancellation extends Model
{
    use HasFactory;

    protected $table = 'organization_booking_cancellations';

    protected $fillable = [
        'booking_request_id',
        'requested_by',
        'reason',
        'status',
        'adviser_response',
        'responded_by',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    // ===========================
    // Relationships
    // ===========================

    /**
     * The booking request this cancellation is for
     */
    public function bookingRequest()
    {
        return $this->belongsTo(OrganizationBookingRequest::class, 'booking_request_id');
    }

    /**
     * The user who requested the cancellation
     */
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by')->withTrashed();
    }

    /**
     * The adviser who responded to this cancellation request
     */
    public function respondedBy()
    {
        return $this->belongsTo(User::class, 'responded_by')->withTrashed();
    }

    // ===========================
    // Scopes
    // ===========================

    /**
     * Scope for pending cancellation requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved cancellation requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected cancellation requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // ===========================
    // Methods
    // ===========================

    /**
     * Approve the cancellation request
     */
    public function approve($adviser, $response = null)
    {
        $adviserId = is_object($adviser) ? $adviser->id : $adviser;

        $this->update([
            'status' => 'approved',
            'adviser_response' => $response,
            'responded_by' => $adviserId,
            'responded_at' => now(),
        ]);

        // Also update the booking request status to cancelled
        $this->bookingRequest->update([
            'status' => 'cancelled',
        ]);

        return $this;
    }

    /**
     * Reject the cancellation request
     */
    public function reject($adviser, $response)
    {
        $adviserId = is_object($adviser) ? $adviser->id : $adviser;

        return $this->update([
            'status' => 'rejected',
            'adviser_response' => $response,
            'responded_by' => $adviserId,
            'responded_at' => now(),
        ]);
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300'
        };
    }
}
