<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $cancellation_id
 * @property int $reservation_id
 * @property int $requestor_id
 * @property string $reason
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $staff_confirmed_at
 * @property int|null $staff_confirmed_by
 * @property \Illuminate\Support\Carbon|null $admin_confirmed_at
 * @property int|null $admin_confirmed_by
 * @property \Illuminate\Support\Carbon|null $adviser_confirmed_at
 * @property int|null $adviser_confirmed_by
 * @property \Illuminate\Support\Carbon|null $priest_confirmed_at
 * @property int|null $priest_confirmed_by
 * @property \Illuminate\Support\Carbon|null $adviser_notified_at
 * @property \Illuminate\Support\Carbon|null $priest_notified_at
 * @property \Illuminate\Support\Carbon|null $staff_escalated_adviser_at
 * @property \Illuminate\Support\Carbon|null $staff_escalated_priest_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read \App\Models\Reservation $reservation
 * @property-read \App\Models\User $requestor
 * @property-read \App\Models\User|null $staffConfirmer
 * @property-read \App\Models\User|null $adminConfirmer
 * @property-read \App\Models\User|null $adviserConfirmer
 * @property-read \App\Models\User|null $priestConfirmer
 */
class ReservationCancellation extends Model
{
    use HasFactory;

    protected $table = 'reservation_cancellations';
    protected $primaryKey = 'cancellation_id';

    protected $fillable = [
        'reservation_id',
        'requestor_id',
        'reason',
        'status',
        'staff_confirmed_at',
        'staff_confirmed_by',
        'admin_confirmed_at',
        'admin_confirmed_by',
        'adviser_confirmed_at',
        'adviser_confirmed_by',
        'priest_confirmed_at',
        'priest_confirmed_by',
        'adviser_notified_at',
        'priest_notified_at',
        'staff_escalated_adviser_at',
        'staff_escalated_priest_at',
    ];

    protected $casts = [
        'staff_confirmed_at' => 'datetime',
        'admin_confirmed_at' => 'datetime',
        'adviser_confirmed_at' => 'datetime',
        'priest_confirmed_at' => 'datetime',
        'adviser_notified_at' => 'datetime',
        'priest_notified_at' => 'datetime',
        'staff_escalated_adviser_at' => 'datetime',
        'staff_escalated_priest_at' => 'datetime',
    ];

    // Relationships
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requestor_id');
    }

    public function staffConfirmer()
    {
        return $this->belongsTo(User::class, 'staff_confirmed_by');
    }

    public function adminConfirmer()
    {
        return $this->belongsTo(User::class, 'admin_confirmed_by');
    }

    public function adviserConfirmer()
    {
        return $this->belongsTo(User::class, 'adviser_confirmed_by');
    }

    public function priestConfirmer()
    {
        return $this->belongsTo(User::class, 'priest_confirmed_by');
    }

    // Helper methods
    public function isAdviserConfirmed(): bool
    {
        return !is_null($this->adviser_confirmed_at);
    }

    public function isPriestConfirmed(): bool
    {
        return !is_null($this->priest_confirmed_at);
    }

    public function isStaffConfirmed(): bool
    {
        return !is_null($this->staff_confirmed_at);
    }

    public function isAdminConfirmed(): bool
    {
        return !is_null($this->admin_confirmed_at);
    }

    public function isFullyConfirmed(): bool
    {
        return $this->isStaffConfirmed() || $this->isAdminConfirmed();
    }

    public function needsAdviserConfirmation(): bool
    {
        return !$this->isAdviserConfirmed() && !is_null($this->adviser_notified_at);
    }

    public function needsPriestConfirmation(): bool
    {
        return !$this->isPriestConfirmed() && !is_null($this->priest_notified_at);
    }
}
