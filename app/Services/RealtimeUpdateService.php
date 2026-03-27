<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Broadcast;

class RealtimeUpdateService
{
    /**
     * Broadcast a CRUD event for real-time updates
     *
     * @param string $type Create|Update|Delete
     * @param string $entity Model name (Reservation, Notification, OrganizationBookingRequest, etc)
     * @param array $data Entity data
     * @param array|string $channels User IDs or roles to broadcast to
     */
    public static function broadcast(string $type, string $entity, array $data, $channels = []): void
    {
        $event = strtolower("{$type}:{$entity}");

        // Trigger SSE update for all users
        if (is_array($channels)) {
            foreach ($channels as $channel) {
                Cache::put("realtime:{$channel}:{$event}", [
                    'timestamp' => now()->timestamp,
                    'type' => $type,
                    'entity' => $entity,
                    'data' => $data,
                ], 30); // Cache for 30 seconds
            }
        } else {
            // Broadcast to role
            Cache::put("realtime:{$channels}:{$event}", [
                'timestamp' => now()->timestamp,
                'type' => $type,
                'entity' => $entity,
                'data' => $data,
            ], 30);
        }
    }

    /**
     * Broadcast reservation update
     */
    public static function broadcastReservationUpdate(string $type, $reservation, array $roles = []): void
    {
        self::broadcast($type, 'Reservation', [
            'id' => $reservation->id,
            'status' => $reservation->status,
            'service_id' => $reservation->service_id,
            'user_id' => $reservation->user_id,
            'reserved_date' => $reservation->reserved_date,
            'reserved_time_start' => $reservation->reserved_time_start,
            'reserved_time_end' => $reservation->reserved_time_end,
        ], $roles);
    }

    /**
     * Broadcast notification update
     */
    public static function broadcastNotificationUpdate(string $type, $notification, array $userIds = []): void
    {
        self::broadcast($type, 'Notification', [
            'id' => $notification->id,
            'user_id' => $notification->user_id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'read_at' => $notification->read_at,
        ], $userIds);
    }

    /**
     * Broadcast organization booking update
     */
    public static function broadcastOrgBookingUpdate(string $type, $booking, array $roles = []): void
    {
        self::broadcast($type, 'OrganizationBooking', [
            'id' => $booking->id,
            'status' => $booking->status,
            'organization_id' => $booking->organization_id,
            'service_id' => $booking->service_id,
            'booked_date' => $booking->booked_date,
            'booked_time_start' => $booking->booked_time_start,
            'booked_time_end' => $booking->booked_time_end,
        ], $roles);
    }

    /**
     * Trigger a collection update (list of items)
     */
    public static function triggerListUpdate(string $entity, int $userId, string $role = null): void
    {
        $key = "list:update:{$entity}:{$userId}";
        Cache::put($key, [
            'timestamp' => now()->timestamp,
            'entity' => $entity,
        ], 30);
    }

    /**
     * Invalidate cache for a specific entity
     */
    public static function invalidateCache(string $entity, int $id = null): void
    {
        if ($id) {
            Cache::forget("{$entity}:{$id}");
        }
        Cache::forget("{$entity}:list");
    }
}
