<?php

namespace App\Support;

use App\Models\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * Centralized notification constants and factory to enforce defaults.
 */
class Notifications
{
    // Common notification types in the system
    public const TYPE_UPDATE = 'Update';
    public const TYPE_URGENT = 'Urgent';
    public const TYPE_ASSIGNMENT = 'Assignment';
    public const TYPE_PRIEST_DECLINED = 'Priest Declined';
    public const TYPE_CANCELLATION_REQUEST = 'Cancellation Request';
    public const TYPE_EDIT_REQUEST = 'Edit Request';
    public const TYPE_EDIT_APPROVED = 'Edit Approved';
    public const TYPE_EDIT_REJECTED = 'Edit Rejected';

    /**
     * Create a Notification record with sensible defaults.
     * - Ensures sent_at is set
     * - Defaults type to Update if not provided
     *
     * @param array $attributes keyed by Notification columns (user_id, message, etc.)
     */
    public static function make(array $attributes): Notification
    {
        // Default type and sent_at
        if (!isset($attributes['type']) || !is_string($attributes['type']) || $attributes['type'] === '') {
            $attributes['type'] = self::TYPE_UPDATE;
        }

        if (!isset($attributes['sent_at']) || empty($attributes['sent_at'])) {
            $attributes['sent_at'] = now();
        }

        // If data is array, encode to JSON
        if (isset($attributes['data']) && is_array($attributes['data'])) {
            $attributes['data'] = json_encode($attributes['data']);
        }

        return Notification::create($attributes);
    }
}
