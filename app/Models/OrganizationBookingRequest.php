<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Organization Booking Request Model
 * 
 * Handles requests for organization-based activities where:
 * - Requestors choose an organization and submit booking details
 * - Organization advisers review and approve/reject requests
 * - Staff monitors and sends reminders for pending requests
 * - Admins can oversee all organizational activities
 */
class OrganizationBookingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'requestor_id',
        'organization_id',
        'activity_name',
        'purpose',
        'requested_date',
        'requested_venue',
        'estimated_participants',
        'special_requirements',
        'status',
        'rejection_reason',
        'adviser_comments',
        'submitted_at',
        'adviser_notified_at',
        'adviser_responded_at',
        'staff_reminded_at',
        'approved_by',
        'rejected_by',
    ];

    protected $casts = [
        'requested_date' => 'datetime',
        'submitted_at' => 'datetime',
        'adviser_notified_at' => 'datetime',
        'adviser_responded_at' => 'datetime',
        'staff_reminded_at' => 'datetime',
        'estimated_participants' => 'integer',
    ];

    // ===========================
    // Relationships
    // ===========================

    /**
     * The user who submitted this booking request
     */
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    /**
     * The organization being requested for the activity
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'org_id');
    }

    /**
     * The adviser who approved this request
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * The adviser who rejected this request
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // ===========================
    // Scopes
    // ===========================

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for requests needing staff reminders (pending > 1 day)
     */
    public function scopeNeedingReminder($query)
    {
        return $query->where('status', 'pending')
                    ->where('adviser_notified_at', '<', now()->subDay())
                    ->whereNull('staff_reminded_at');
    }

    /**
     * Scope for requests by organization
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    // ===========================
    // Accessors & Mutators
    // ===========================

    /**
     * Get formatted requested date
     */
    public function getFormattedRequestedDateAttribute()
    {
        return $this->requested_date ? $this->requested_date->format('F j, Y \a\t g:i A') : null;
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

    /**
     * Check if request is overdue (pending > 1 day)
     */
    public function getIsOverdueAttribute()
    {
        return $this->status === 'pending' && 
               $this->adviser_notified_at && 
               $this->adviser_notified_at < now()->subDay();
    }

    // ===========================
    // Methods
    // ===========================

    /**
     * Mark request as adviser notified
     */
    public function markAdviserNotified()
    {
        if (!$this->adviser_notified_at) {
            $this->update(['adviser_notified_at' => now()]);
        }
        return $this;
    }

    /**
     * Approve the request
     */
    public function approve($adviser, $comments = null)
    {
        $adviserId = is_object($adviser) ? $adviser->id : $adviser;
        
        return $this->update([
            'status' => 'approved',
            'approved_by' => $adviserId,
            'adviser_comments' => $comments,
            'adviser_responded_at' => now(),
        ]);
    }

    /**
     * Reject the request
     */
    public function reject($adviser, $reason, $comments = null)
    {
        $adviserId = is_object($adviser) ? $adviser->id : $adviser;
        
        return $this->update([
            'status' => 'rejected',
            'rejected_by' => $adviserId,
            'rejection_reason' => $reason,
            'adviser_comments' => $comments,
            'adviser_responded_at' => now(),
        ]);
    }

    /**
     * Mark that staff has sent reminder
     */
    public function markStaffReminded()
    {
        return $this->update(['staff_reminded_at' => now()]);
    }
}