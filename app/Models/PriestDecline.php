<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriestDecline extends Model
{
    protected $table = 'priest_declines';
    protected $primaryKey = 'decline_id';

    protected $fillable = [
        'reservation_id',
        'priest_id',
        'reason',
        'declined_at',
        'reservation_activity_name',
        'reservation_schedule_date',
        'reservation_venue',
    ];

    protected $casts = [
        'declined_at' => 'datetime',
        'reservation_schedule_date' => 'datetime',
    ];

    /**
     * Get the reservation that was declined
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Get the priest who declined
     */
    public function priest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'priest_id', 'id');
    }
}
