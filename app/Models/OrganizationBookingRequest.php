<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\Schema;

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

    protected static ?bool $supportsOrganizationServerQuantityColumn = null;

    protected $fillable = [
        'requestor_id',
        'organization_id',
        'activity_name',
        'purpose',
        'requested_date',
        'requested_venue',
        'estimated_participants',
        'servers_needed',
        'special_requirements',
        'status',
        'cancellation_restricted',
        'rejection_reason',
        'adviser_comments',
        'submitted_at',
        'session_started_at',
        'session_expires_at',
        'session_expired',
        'adviser_notified_at',
        'adviser_responded_at',
        'staff_reminded_at',
        'approved_by',
        'rejected_by',
    ];

    protected $casts = [
        'requested_date' => 'datetime',
        'submitted_at' => 'datetime',
        'session_started_at' => 'datetime',
        'session_expires_at' => 'datetime',
        'session_expired' => 'boolean',
        'cancellation_restricted' => 'boolean',
        'adviser_notified_at' => 'datetime',
        'adviser_responded_at' => 'datetime',
        'staff_reminded_at' => 'datetime',
        'estimated_participants' => 'integer',
        'servers_needed' => 'integer',
    ];

    /**
     * Ensure JSON serialization emits local, timezone-naive datetimes
     * to prevent UTC shifts on the client calendar.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->setTimezone(config('app.timezone'))->format('Y-m-d\TH:i:s');
    }

    // ===========================
    // Relationships
    // ===========================

    /**
     * The user who submitted this booking request
     */
    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id')->withTrashed();
    }

    /**
     * The organization being requested for the activity
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'org_id')->withTrashed();
    }

    /**
     * The adviser who approved this request
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by')->withTrashed();
    }

    /**
     * The adviser who rejected this request
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by')->withTrashed();
    }

    /**
     * Multiple organizations associated with this booking request
     */
    public function organizations()
    {
        $pivotColumns = [
            'is_primary',
            'notified',
            'notified_at',
            'approval_status',
            'rejection_reason',
            'responded_by',
            'responded_at',
        ];

        if (static::supportsOrganizationServerQuantityColumn()) {
            array_splice($pivotColumns, 1, 0, ['server_quantity']);
        }

        return $this->belongsToMany(
                Organization::class,
                'organization_booking_organizations',
                'booking_request_id',   // Foreign key on pivot table for this model
                'organization_id',      // Foreign key on pivot table for related model
                'id',                   // Local key on this model
                'org_id'                // Local key on related model (Organization uses org_id as primary key)
            )
            ->withPivot($pivotColumns)
            ->withTimestamps()
            ->withTrashed();
    }

    public static function supportsOrganizationServerQuantityColumn(): bool
    {
        if (static::$supportsOrganizationServerQuantityColumn !== null) {
            return static::$supportsOrganizationServerQuantityColumn;
        }

        static::$supportsOrganizationServerQuantityColumn = Schema::hasColumn(
            'organization_booking_organizations',
            'server_quantity'
        );

        return static::$supportsOrganizationServerQuantityColumn;
    }

    /**
     * Get the primary organization from the many-to-many relationship
     */
    public function primaryOrganization()
    {
        return $this->belongsToMany(
                Organization::class,
                'organization_booking_organizations',
                'booking_request_id',
                'organization_id',
                'id',
                'org_id'
            )
            ->withPivot('is_primary')
            ->wherePivot('is_primary', true)
            ->withTrashed();
    }

    /**
     * Cancellation requests for this booking
     */
    public function cancellationRequests()
    {
        return $this->hasMany(OrganizationBookingCancellation::class, 'booking_request_id');
    }

    /**
     * Get the pending cancellation request if any
     */
    public function pendingCancellation()
    {
        return $this->hasOne(OrganizationBookingCancellation::class, 'booking_request_id')
            ->where('status', 'pending');
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

    /**
     * Scope for expired sessions that need to be released
     */
    public function scopeExpiredSessions($query)
    {
        return $query->where('status', 'pending')
                    ->whereNotNull('session_expires_at')
                    ->where('session_expires_at', '<', now())
                    ->where('session_expired', false);
    }

    /**
     * Scope for requests with active sessions (not yet expired)
     */
    public function scopeActiveSessions($query)
    {
        return $query->where('status', 'pending')
                    ->whereNotNull('session_expires_at')
                    ->where('session_expires_at', '>', now())
                    ->where('session_expired', false);
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
            'cancelled' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
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

    // ===========================
    // Session Timeout Methods
    // ===========================

    /**
     * Start a booking session with timeout (default 30 minutes)
     */
    public function startSession($timeoutMinutes = 30)
    {
        return $this->update([
            'session_started_at' => now(),
            'session_expires_at' => now()->addMinutes($timeoutMinutes),
            'session_expired' => false,
        ]);
    }

    /**
     * Extend the session by additional minutes
     */
    public function extendSession($additionalMinutes = 15)
    {
        if ($this->session_expires_at && !$this->session_expired) {
            return $this->update([
                'session_expires_at' => max($this->session_expires_at, now())->addMinutes($additionalMinutes),
            ]);
        }
        return false;
    }

    /**
     * Mark session as expired
     */
    public function expireSession()
    {
        return $this->update([
            'session_expired' => true,
        ]);
    }

    /**
     * Check if session is still active
     */
    public function hasActiveSession(): bool
    {
        return $this->session_expires_at &&
               $this->session_expires_at > now() &&
               !$this->session_expired;
    }

    /**
     * Get remaining session time in seconds
     */
    public function getRemainingSessionTimeAttribute(): int
    {
        if (!$this->hasActiveSession()) {
            return 0;
        }
        return max(0, now()->diffInSeconds($this->session_expires_at, false));
    }

    // ===========================
    // Cancellation Methods
    // ===========================

    /**
     * Check if cancellation is allowed.
     * Business rule: once a booking is approved by advisers, it can no longer be cancelled by requestor.
     */
    public function canRequestCancellation(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if cancellation requires adviser approval
     */
    public function requiresCancellationApproval(): bool
    {
        return false;
    }

    /**
     * Check if there's a pending cancellation request
     */
    public function hasPendingCancellation(): bool
    {
        return $this->cancellationRequests()->where('status', 'pending')->exists();
    }

    // ===========================
    // Multi-Organization Methods
    // ===========================

    /**
     * Get all organization names formatted for display
     */
    public function getAllOrganizationNamesAttribute(): string
    {
        if ($this->organizations->isNotEmpty()) {
            return $this->organizations->pluck('org_name')->implode(', ');
        }

        if ($this->organization) {
            return $this->organization->org_name;
        }

        return 'No organization';
    }

    /**
     * Check if all organization advisers have approved
     */
    public function allOrganizationsApproved(): bool
    {
        $organizations = $this->organizations;

        if ($organizations->isEmpty()) {
            return $this->status !== 'pending';
        }

        return $organizations->every(fn($org) => $org->pivot->approval_status === 'approved');
    }

    /**
     * Check if any organization adviser has rejected
     */
    public function anyOrganizationRejected(): bool
    {
        return $this->organizations->contains(fn($org) => $org->pivot->approval_status === 'rejected');
    }

    /**
     * Get count of pending organization approvals
     */
    public function pendingOrganizationCount(): int
    {
        return $this->organizations->filter(fn($org) => $org->pivot->approval_status === 'pending')->count();
    }
}
