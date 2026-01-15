<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $reservation_id
 * @property int $user_id
 * @property int $org_id
 * @property int|null $venue_id
 * @property string|null $custom_venue_name
 * @property int $service_id
 * @property int|null $officiant_id
 * @property \Carbon\Carbon $schedule_date
 * @property string $schedule_time
 * @property string $status
 * @property string $purpose
 * @property string|null $details
 * @property int|null $participants_count
 * @property string|null $activity_name
 * @property string|null $theme
 * @property string|null $commentator
 * @property string|null $servers
 * @property string|null $readers
 * @property string|null $choir
 * @property string|null $psalmist
 * @property string|null $prayer_leader
 * @property \Carbon\Carbon|null $adviser_notified_at
 * @property \Carbon\Carbon|null $adviser_responded_at
 * @property \Carbon\Carbon|null $contacted_at
 * @property \Carbon\Carbon|null $requestor_confirmed_at
 * @property string|null $requestor_confirmation_token
 * @property \Carbon\Carbon|null $admin_notified_at
 * @property \Carbon\Carbon|null $staff_followed_up_at
 * @property \Carbon\Carbon|null $priest_notified_at
 * @property string|null $priest_confirmation
 * @property \Carbon\Carbon|null $priest_confirmed_at
 * @property string|null $cancellation_reason
 * @property int|null $cancelled_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read Organization $organization
 * @property-read Venue $venue
 * @property-read Service $service
 * @property-read User|null $officiant
 * @property-read User|null $cancelledByUser
 */
class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservations';
    protected $primaryKey = 'reservation_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'org_id',
        'venue_id',
        'custom_venue_name',
        'service_id',
        'officiant_id',
        'priest_selection_type',
        'external_priest_name',
        'external_priest_contact',
        'schedule_date',
        'status',
        'purpose',
        'activity_name',
        'theme',
        'details',
        'participants_count',
        'commentator',
        'servers',
        'readers',
        'choir',
        'psalmist',
        'prayer_leader',
        'adviser_notified_at',
        'adviser_responded_at',
        'contacted_at',
        'requestor_confirmed_at',
        'requestor_confirmation_token',
        'admin_notified_at',
        'staff_followed_up_at',
        'priest_notified_at',
        'priest_confirmation',
        'priest_confirmed_at',
        'approved_by',
        'rejected_by',
        'cancellation_reason',
        'cancelled_by',
    ];

    protected $appends = [
        'schedule_time',
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'participants_count' => 'integer',
        'adviser_notified_at' => 'datetime',
        'adviser_responded_at' => 'datetime',
        'contacted_at' => 'datetime',
        'requestor_confirmed_at' => 'datetime',
        'admin_notified_at' => 'datetime',
        'staff_followed_up_at' => 'datetime',
        'priest_notified_at' => 'datetime',
        'priest_confirmed_at' => 'datetime',
    ];

    // ===========================
    // Relationships
    // ===========================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'org_id');
    }

    /**
     * Many-to-many relationship: All organizations assigned to this reservation
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'reservation_organization', 'reservation_id', 'organization_id')
            ->withPivot('notified', 'notified_at', 'approval_status', 'rejection_reason', 'responded_by', 'responded_at')
            ->withTimestamps();
    }

    /**
     * Many-to-many relationship: All priests assigned to this reservation
     */
    public function priests()
    {
        return $this->belongsToMany(User::class, 'reservation_priest', 'reservation_id', 'priest_id')
            ->withPivot('confirmation_status', 'decline_reason', 'notified', 'notified_at', 'responded_at')
            ->withTimestamps();
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    /**
     * The priest/officiant assigned to this reservation (legacy single priest)
     */
    public function officiant()
    {
        return $this->belongsTo(User::class, 'officiant_id');
    }

    /**
     * User who cancelled this reservation (if applicable)
     */
    public function cancelledByUser()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * User who rejected this reservation
     */
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * User who approved this reservation
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function history()
    {
        return $this->hasMany(ReservationHistory::class, 'reservation_id', 'reservation_id')
                    ->whereNull('archived_at');
    }

    // All history including archived
    public function allHistory()
    {
        return $this->hasMany(ReservationHistory::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Priest decline records for this reservation
     */
    public function declines()
    {
        return $this->hasMany(PriestDecline::class, 'reservation_id', 'reservation_id');
    }

    // ===========================
    // Query Scopes
    // ===========================

    /**
     * Scope: Pending adviser approval
     */
    public function scopePendingAdviserApproval(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Approved by adviser, awaiting admin
     */
    public function scopePendingAdminApproval(Builder $query): Builder
    {
        return $query->where('status', 'adviser_approved');
    }

    /**
     * Scope: Waiting for priest confirmation
     * Includes: adviser_approved (after adviser approval), admin_approved (after admin assignment)
     * Per swimlane: Adviser approves → Priest confirms availability → Admin assigns
     */
    public function scopeAwaitingPriestConfirmation(Builder $query): Builder
    {
        return $query->whereIn('status', ['adviser_approved', 'admin_approved'])
            ->where(function ($q) {
                $q->whereNull('priest_confirmation')
                    ->orWhere('priest_confirmation', 'pending');
            });
    }

    /**
     * Scope: Fully approved and confirmed
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: Unnoticed requests (pending > 24 hours without adviser response)
     */
    public function scopeUnnoticedByAdviser(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('created_at', '<', now()->subDay())
            ->whereNull('adviser_responded_at')
            ->where(function ($q) {
                $q->whereNull('staff_followed_up_at')
                    ->orWhere('staff_followed_up_at', '<', now()->subDays(2));
            });
    }

    /**
     * Scope: Reservations for a specific organization
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope: Reservations assigned to a specific priest
     * Checks both many-to-many priests relationship and legacy officiant_id
     */
    public function scopeForPriest(Builder $query, int $priestId): Builder
    {
        return $query->where(function ($q) use ($priestId) {
            $q->where('officiant_id', $priestId)
              ->orWhereHas('priests', function ($priestQuery) use ($priestId) {
                  $priestQuery->where('users.id', $priestId);
              });
        });
    }

    /**
     * Scope: Upcoming reservations (future dates only)
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('schedule_date', '>=', now());
    }

    /**
     * Scope: Past reservations
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('schedule_date', '<', now());
    }

    // ===========================
    // Helper Methods
    // ===========================

    public function getScheduleTimeAttribute(): ?string
    {
        return $this->schedule_date ? $this->schedule_date->format('H:i:s') : null;
    }

    /**
     * Check if reservation is pending adviser approval
     */
    public function isPendingAdviser(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if reservation is pending admin approval
     */
    public function isPendingAdmin(): bool
    {
        return $this->status === 'adviser_approved';
    }

    /**
     * Check if reservation is fully approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if reservation was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if reservation was cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get human-readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Adviser Approval',
            'adviser_approved' => 'Pending Admin Approval',
            'admin_approved' => 'Awaiting Priest Confirmation',
            'approved' => 'Approved & Confirmed',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get human-readable priest confirmation status
     */
    public function getPriestConfirmationLabelAttribute(): string
    {
        return match ($this->priest_confirmation) {
            'pending' => 'Awaiting Confirmation',
            'confirmed' => 'Confirmed',
            'declined' => 'Declined',
            default => 'Not Yet Assigned',
        };
    }

    // ===========================
    // Multi-Approval Helper Methods
    // ===========================

    /**
     * Check if all organizations/advisers have approved
     */
    public function allAdvisersApproved(): bool
    {
        $orgs = $this->organizations;
        
        // If no organizations attached, check if single org_id is approved (legacy)
        if ($orgs->isEmpty()) {
            return $this->status !== 'pending';
        }

        // All organizations must have approval_status = 'approved'
        return $orgs->every(fn($org) => $org->pivot->approval_status === 'approved');
    }

    /**
     * Check if any adviser has rejected
     */
    public function anyAdviserRejected(): bool
    {
        return $this->organizations->contains(fn($org) => $org->pivot->approval_status === 'rejected');
    }

    /**
     * Get count of pending adviser approvals
     */
    public function pendingAdviserCount(): int
    {
        return $this->organizations->filter(fn($org) => $org->pivot->approval_status === 'pending')->count();
    }

    /**
     * Get count of approved advisers
     */
    public function approvedAdviserCount(): int
    {
        return $this->organizations->filter(fn($org) => $org->pivot->approval_status === 'approved')->count();
    }

    /**
     * Check if all selected priests have confirmed
     */
    public function allPriestsConfirmed(): bool
    {
        $priests = $this->priests;
        
        // If no priests attached via pivot, check legacy officiant
        if ($priests->isEmpty()) {
            return $this->priest_confirmation === 'confirmed';
        }

        // All priests must have confirmation_status = 'confirmed'
        return $priests->every(fn($priest) => $priest->pivot->confirmation_status === 'confirmed');
    }

    /**
     * Check if any priest has declined
     */
    public function anyPriestDeclined(): bool
    {
        return $this->priests->contains(fn($priest) => $priest->pivot->confirmation_status === 'declined');
    }

    /**
     * Get count of pending priest confirmations
     */
    public function pendingPriestCount(): int
    {
        return $this->priests->filter(fn($priest) => $priest->pivot->confirmation_status === 'pending')->count();
    }

    /**
     * Get count of confirmed priests
     */
    public function confirmedPriestCount(): int
    {
        return $this->priests->filter(fn($priest) => $priest->pivot->confirmation_status === 'confirmed')->count();
    }

    /**
     * Get the display status for requestor view
     * This shows user-friendly status based on the workflow state
     */
    public function getRequestorDisplayStatusAttribute(): string
    {
        // Check for cancelled/rejected first
        if (in_array($this->status, ['cancelled', 'rejected'])) {
            return ucfirst($this->status);
        }

        // Completed
        if ($this->status === 'completed') {
            return 'Completed';
        }

        // Final approved
        if ($this->status === 'approved') {
            return 'Approved by Admin';
        }

        // Check multi-adviser approval status
        if ($this->status === 'pending') {
            $totalOrgs = $this->organizations->count();
            if ($totalOrgs > 1) {
                $approved = $this->approvedAdviserCount();
                return "Awaiting Adviser ({$approved}/{$totalOrgs} approved)";
            }
            return 'Awaiting Adviser';
        }

        // After all advisers approve, waiting for priests
        if ($this->status === 'adviser_approved') {
            $totalPriests = $this->priests->count();
            if ($totalPriests > 1) {
                $confirmed = $this->confirmedPriestCount();
                if ($confirmed < $totalPriests) {
                    return "Awaiting Priest ({$confirmed}/{$totalPriests} confirmed)";
                }
            }
            // Single priest or external - check priest confirmation status
            if ($this->priest_selection_type === 'external') {
                return 'Awaiting Admin';
            }
            if ($this->priest_confirmation === 'confirmed' || $this->allPriestsConfirmed()) {
                return 'Awaiting Admin';
            }
            return 'Awaiting Priest';
        }

        // Admin has approved, waiting for final priest confirmation
        if ($this->status === 'admin_approved') {
            if ($this->priest_confirmation === 'confirmed' || $this->allPriestsConfirmed()) {
                return 'Awaiting Admin';
            }
            return 'Awaiting Priest';
        }

        return ucwords(str_replace('_', ' ', $this->status));
    }
}

