<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
        'attachment_path',
        'attachment_name',
        'attachment_type',
        'attachment_size',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $appends = ['attachment_url'];

    /**
     * Get the attachment URL.
     */
    public function getAttachmentUrlAttribute()
    {
        if ($this->attachment_path) {
            // Add a cache-busting query param based on last update to avoid stale caches
            $url = asset('storage/' . $this->attachment_path);
            $version = optional($this->updated_at)->timestamp ?? time();
            return $url . '?v=' . $version;
        }
        return null;
    }

    /**
     * Check if message has attachment.
     */
    public function hasAttachment(): bool
    {
        return !is_null($this->attachment_path);
    }

    /**
     * Check if attachment is an image.
     */
    public function isImage(): bool
    {
        if (!$this->attachment_type) {
            return false;
        }
        return str_starts_with($this->attachment_type, 'image/');
    }

    /**
     * Get the sender of the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver of the message.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for messages between two users.
     */
    public function scopeConversation($query, $userId1, $userId2)
    {
        return $query->where(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId1)->where('receiver_id', $userId2);
        })->orWhere(function ($q) use ($userId1, $userId2) {
            $q->where('sender_id', $userId2)->where('receiver_id', $userId1);
        })->orderBy('created_at', 'asc');
    }

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }
}
