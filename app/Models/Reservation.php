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
        'cancellation_reason',
        'cancelled_by',
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

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'service_id');
    }

    /**
     * The priest/officiant assigned to this reservation
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

    public function history()
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
     */
    public function scopeAwaitingPriestConfirmation(Builder $query): Builder
    {
        return $query->where('status', 'admin_approved')
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
     */
    public function scopeForPriest(Builder $query, int $priestId): Builder
    {
        return $query->where('officiant_id', $priestId);
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
}

