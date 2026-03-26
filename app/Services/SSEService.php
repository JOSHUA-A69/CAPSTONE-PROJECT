<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\ReservationCancellation;
use App\Models\OrganizationBookingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SSEService
{
    /**
     * Get the cache key for tracking updates
     */
    public static function getCacheKey(string $type, int $userId): string
    {
        return "sse:{$type}:{$userId}";
    }

    /**
     * Trigger an update notification for specific users
     */
    public static function triggerUpdate(string $type, array $userIds, array $data = []): void
    {
        foreach ($userIds as $userId) {
            $cacheKey = self::getCacheKey($type, $userId);
            Cache::put($cacheKey, [
                'timestamp' => now()->timestamp,
                'data' => $data,
            ], 60); // Cache for 60 seconds
        }
    }

    /**
     * Trigger update for all users with a specific role
     */
    public static function triggerRoleUpdate(string $type, string $role, array $data = []): void
    {
        $userIds = User::where('role', $role)->pluck('id')->toArray();
        self::triggerUpdate($type, $userIds, $data);
    }

    /**
     * Trigger update for multiple roles
     */
    public static function triggerMultiRoleUpdate(string $type, array $roles, array $data = []): void
    {
        $userIds = User::whereIn('role', $roles)->pluck('id')->toArray();
        self::triggerUpdate($type, $userIds, $data);
    }

    /**
     * Check if there are updates since last check
     */
    public static function hasUpdates(string $type, int $userId, int $lastCheck): bool
    {
        $cacheKey = self::getCacheKey($type, $userId);
        $cached = Cache::get($cacheKey);

        if ($cached && $cached['timestamp'] > $lastCheck) {
            return true;
        }

        return false;
    }

    /**
     * Get notification count for a user
     */
    public static function getNotificationCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get unread chat message count for a user
     */
    public static function getChatUnreadCount(int $userId): int
    {
        return Message::where('receiver_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get dashboard stats for Admin
     */
    public static function getAdminDashboardStats(): array
    {
        return [
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'pending_cancellations' => ReservationCancellation::where('status', 'pending')->count(),
            'pending_org_bookings' => OrganizationBookingRequest::where('status', 'pending')->count(),
            'pending_approvals' => User::where('status', 'pending')->count(),
            'total_users' => User::count(),
        ];
    }

    /**
     * Get dashboard stats for Staff
     */
    public static function getStaffDashboardStats(): array
    {
        return [
            'pending_reservations' => Reservation::where('status', 'pending')->count(),
            'pending_cancellations' => ReservationCancellation::where('status', 'pending')->count(),
            'pending_org_bookings' => OrganizationBookingRequest::where('status', 'pending')->count(),
            'today_reservations' => Reservation::whereDate('reservation_date', today())->count(),
        ];
    }

    /**
     * Get dashboard stats for Adviser
     */
    public static function getAdviserDashboardStats(int $userId): array
    {
        // Get organizations this adviser manages
        $orgIds = DB::table('organizations')
            ->where('adviser_id', $userId)
            ->pluck('org_id')
            ->toArray();

        return [
            'pending_bookings' => OrganizationBookingRequest::whereIn('org_id', $orgIds)
                ->where('status', 'pending')
                ->count(),
            'approved_bookings' => OrganizationBookingRequest::whereIn('org_id', $orgIds)
                ->where('status', 'approved')
                ->count(),
        ];
    }

    /**
     * Get dashboard stats for Priest
     */
    public static function getPriestDashboardStats(int $userId): array
    {
        return [
            'pending_reservations' => Reservation::where('priest_id', $userId)
                ->where('status', 'pending')
                ->count(),
            'upcoming_reservations' => Reservation::where('priest_id', $userId)
                ->whereIn('status', ['approved', 'confirmed'])
                ->where('reservation_date', '>=', today())
                ->count(),
            'today_reservations' => Reservation::where('priest_id', $userId)
                ->whereDate('reservation_date', today())
                ->count(),
        ];
    }

    /**
     * Get dashboard stats for Requestor
     */
    public static function getRequestorDashboardStats(int $userId): array
    {
        return [
            'pending_reservations' => Reservation::where('requestor_id', $userId)
                ->where('status', 'pending')
                ->count(),
            'approved_reservations' => Reservation::where('requestor_id', $userId)
                ->where('status', 'approved')
                ->count(),
            'upcoming_reservations' => Reservation::where('requestor_id', $userId)
                ->whereIn('status', ['approved', 'confirmed'])
                ->where('reservation_date', '>=', today())
                ->count(),
        ];
    }

    /**
     * Format SSE message
     */
    public static function formatSSEMessage(string $event, array $data): string
    {
        $json = json_encode($data);
        return "event: {$event}\ndata: {$json}\n\n";
    }

    /**
     * Send SSE keepalive comment
     */
    public static function keepalive(): string
    {
        return ": keepalive\n\n";
    }

    /**
     * Get unread messages for a specific conversation
     */
    public static function getConversationUnreadCount(int $userId, int $conversationWithId): int
    {
        return Message::where('receiver_id', $userId)
            ->where('sender_id', $conversationWithId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Get conversation list for a user with unread counts
     */
    public static function getConversationsList(int $userId, string $role)
    {
        if ($role === 'admin') {
            return DB::table('messages as m')
                ->selectRaw('
                    CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as conversation_with,
                    u.id, u.first_name, u.last_name, u.profile_picture,
                    MAX(m.created_at) as last_message_at,
                    (SELECT message FROM messages
                     WHERE (sender_id = ? AND receiver_id = u.id)
                        OR (sender_id = u.id AND receiver_id = ?)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages
                     WHERE receiver_id = ? AND sender_id = u.id AND read_at IS NULL) as unread_count
                ', [$userId, $userId, $userId, $userId])
                ->leftJoin('users as u', function($join) use ($userId) {
                    $join->on(DB::raw('CASE WHEN m.sender_id = ' . $userId . ' THEN m.receiver_id ELSE m.sender_id END'), '=', 'u.id');
                })
                ->where('u.role', 'requestor')
                ->groupByRaw('CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END', [$userId])
                ->orderBy('last_message_at', 'desc')
                ->get();
        }

        // For requestors, only show conversations with admins
        return DB::table('messages as m')
            ->selectRaw('
                CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END as conversation_with,
                u.id, u.first_name, u.last_name, u.profile_picture,
                MAX(m.created_at) as last_message_at,
                (SELECT message FROM messages
                 WHERE (sender_id = ? AND receiver_id = u.id)
                    OR (sender_id = u.id AND receiver_id = ?)
                 ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT COUNT(*) FROM messages
                 WHERE receiver_id = ? AND sender_id = u.id AND read_at IS NULL) as unread_count
            ', [$userId, $userId, $userId, $userId])
            ->leftJoin('users as u', function($join) use ($userId) {
                $join->on(DB::raw('CASE WHEN m.sender_id = ' . $userId . ' THEN m.receiver_id ELSE m.sender_id END'), '=', 'u.id');
            })
            ->where('u.role', 'admin')
            ->groupByRaw('CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END', [$userId])
            ->orderBy('last_message_at', 'desc')
            ->get();
    }
}
