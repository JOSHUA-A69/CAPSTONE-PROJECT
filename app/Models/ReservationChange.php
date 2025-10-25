<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationChange extends Model
{
    protected $primaryKey = 'change_id';

    protected $fillable = [
        'reservation_id',
        'requested_by',
        'changes_requested',
        'requestor_notes',
        'status',
        'reviewed_by',
        'rejection_reason',
        'requested_at',
        'reviewed_at',
    ];

    protected $casts = [
        'changes_requested' => 'array',
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function requestor()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
