<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservationAssignment extends Model
{
    use HasFactory;

    protected $table = 'reservation_assignments';
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'reservation_id',
        'priest_id',
        'status',
        'notified_at',
        'confirmed_at',
        'declined_at',
        'decline_reason',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    public function priest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'priest_id', 'id');
    }
}
