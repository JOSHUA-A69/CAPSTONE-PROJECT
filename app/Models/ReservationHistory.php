<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationHistory extends Model
{
    use HasFactory;

    protected $table = 'reservation_history';
    protected $primaryKey = 'history_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reservation_id', 'performed_by', 'action', 'remarks', 'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    // Allowed action values for reservation_history.action enum
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_SUBMITTED = 'submitted';
    public const ACTION_APPROVED = 'approved';
    public const ACTION_ADVISER_APPROVED = 'adviser_approved';
    public const ACTION_ADMIN_APPROVED = 'admin_approved';
    public const ACTION_REJECTED = 'rejected';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_CONTACTED_REQUESTOR = 'contacted_requestor';
    public const ACTION_REQUESTOR_CONFIRMED = 'requestor_confirmed';
    public const ACTION_APPROVED_BY_STAFF = 'approved_by_staff';
    public const ACTION_PRIEST_CONFIRMED = 'priest_confirmed';
    public const ACTION_PRIEST_DECLINED = 'priest_declined';
    public const ACTION_PRIEST_CANCELLED_CONFIRMATION = 'priest_cancelled_confirmation';
    public const ACTION_PRIEST_REASSIGNED = 'priest_reassigned';
    public const ACTION_STAFF_FOLLOWED_UP = 'staff_followed_up';
    public const ACTION_STATUS_UPDATED = 'status_updated';
    public const ACTION_CANCELLATION_REQUESTED = 'cancellation_requested';
    public const ACTION_CANCELLATION_CONFIRMED_BY_STAFF = 'cancellation_confirmed_by_staff';
    public const ACTION_CANCELLATION_CONFIRMED_BY_ADMIN = 'cancellation_confirmed_by_admin';
    public const ACTION_CANCELLATION_CONFIRMED_BY_ADVISER = 'cancellation_confirmed_by_adviser';
    public const ACTION_CANCELLATION_CONFIRMED_BY_PRIEST = 'cancellation_confirmed_by_priest';
    public const ACTION_CANCELLATION_COMPLETED = 'cancellation_completed';

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'id');
    }
}
