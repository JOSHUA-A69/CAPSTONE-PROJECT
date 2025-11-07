<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiturgicalSchedule extends Model
{
    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'title',
        'description',
        'schedule_date',
        'start_time',
        'end_time',
        'location',
        'venue_id',
        'priest_id',
        'event_type',
        'mass_subtype',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_public' => 'boolean',
    ];

    /**
     * Get the staff member who created this schedule
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the priest assigned to this schedule
     */
    public function priest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'priest_id');
    }

    /**
     * Get the venue for this schedule
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'venue_id');
    }

    /**
     * Scope to get only public schedules
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get schedules for a specific date range
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('schedule_date', [$start, $end]);
    }

    /**
     * Scope to get upcoming schedules
     */
    public function scopeUpcoming($query)
    {
        return $query->where('schedule_date', '>=', now()->toDateString())
                    ->orderBy('schedule_date')
                    ->orderBy('start_time');
    }
}
