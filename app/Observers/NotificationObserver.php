<?php

namespace App\Observers;

use App\Models\Notification;
use App\Services\SSEService;
use App\Services\RealtimeUpdateService;

class NotificationObserver
{
    /**
     * Handle the Notification "created" event.
     */
    public function created(Notification $notification): void
    {
        // Trigger SSE update for the user who received the notification
        SSEService::triggerUpdate('notifications', [$notification->user_id], [
            'action' => 'new_notification',
            'notification_id' => $notification->id ?? null,
            'type' => $notification->type,
            'message' => $notification->message,
        ]);

        // Broadcast real-time update
        RealtimeUpdateService::broadcastNotificationUpdate('create', $notification, [$notification->user_id]);
    }

    /**
     * Handle the Notification "updated" event.
     * This is mainly for when notifications are marked as read.
     */
    public function updated(Notification $notification): void
    {
        // Trigger update when notification is read or archived
        if ($notification->isDirty('read_at') || $notification->isDirty('archived_at')) {
            SSEService::triggerUpdate('notifications', [$notification->user_id], [
                'action' => 'notification_updated',
                'notification_id' => $notification->id ?? null,
            ]);

            // Broadcast real-time update
            RealtimeUpdateService::broadcastNotificationUpdate('update', $notification, [$notification->user_id]);
        }
    }

    /**
     * Handle the Notification "deleted" event.
     */
    public function deleted(Notification $notification): void
    {
        SSEService::triggerUpdate('notifications', [$notification->user_id], [
            'action' => 'notification_deleted',
            'notification_id' => $notification->id ?? null,
        ]);

        // Broadcast real-time update
        RealtimeUpdateService::broadcast('delete', 'Notification', [
            'id' => $notification->id,
        ], [$notification->user_id]);
    }
}
