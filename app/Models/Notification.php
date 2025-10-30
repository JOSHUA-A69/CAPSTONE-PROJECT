<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'user_id',
        'reservation_id',
        'message',
        'type',
        'sent_at',
        'read_at',
        'data', // Additional JSON data
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'data' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Notification $model) {
            // Ensure sent_at is set for proper ordering and display
            if (empty($model->sent_at)) {
                $model->sent_at = now();
            }
        });
    }

    /**
     * Get the user that owns the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reservation associated with this notification
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'reservation_id');
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if notification is unread
     */
    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }
}
