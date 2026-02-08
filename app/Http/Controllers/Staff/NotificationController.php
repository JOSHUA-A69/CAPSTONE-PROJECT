<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':staff']);
    }

    /**
     * Get unread notification count for badge
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->unread()
            ->notArchived()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications for dropdown
     */
    public function getRecent()
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->unread()
                ->notArchived()
                ->with(['reservation.officiant'])
                ->orderByRaw('COALESCE(sent_at, created_at) DESC')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to load notifications: ' . $e->getMessage());
            return response()->json(['html' => '<div class="px-5 py-12 text-center bg-white dark:bg-gray-800"><p class="text-red-600">Error loading notifications. Please refresh.</p></div>']);
        }

        $html = view('components.notifications.recent-list', [
            'notifications' => $notifications,
            'role' => 'staff',
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Display all notifications page
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->notArchived()
            ->with(['reservation.officiant'])
            ->orderByRaw('COALESCE(sent_at, created_at) DESC')
            ->paginate(20);

        return view('staff.notifications.index', compact('notifications'));
    }

    /**
     * Display archived notifications
     */
    public function archived()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->archived()
            ->with(['reservation.officiant'])
            ->orderBy('archived_at', 'desc')
            ->paginate(20);

        return view('staff.notifications.archived', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

    // Use timestamp column read_at instead of non-existent boolean 'read'
    $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Show a single notification with full details
     * For unnoticed reservation notifications, displays adviser contact info
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->with(['reservation.user', 'reservation.service', 'reservation.organization.adviser', 'reservation.venue'])
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        // Check if this is an unnoticed reservation notification
        $isUnnoticedNotification = false;
        $adviserInfo = null;

        if ($notification->data) {
            $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
            if (isset($data['action']) && $data['action'] === 'unnoticed_reservation') {
                $isUnnoticedNotification = true;
                $adviserInfo = [
                    'name' => $data['adviser_name'] ?? 'Unknown',
                    'email' => $data['adviser_email'] ?? 'N/A',
                    'phone' => $data['adviser_phone'] ?? 'N/A',
                    'hours_pending' => $data['hours_pending'] ?? 0,
                    'requestor_name' => $data['requestor_name'] ?? 'Unknown',
                    'service_name' => $data['service_name'] ?? 'N/A',
                    'organization_name' => $data['organization_name'] ?? 'N/A',
                ];
            }
        }

        // If reservation exists, get fresh adviser info
        if ($notification->reservation && $notification->reservation->organization && $notification->reservation->organization->adviser) {
            $adviser = $notification->reservation->organization->adviser;
            $adviserInfo = [
                'name' => $adviser->full_name ?? $adviser->name ?? 'Unknown',
                'email' => $adviser->email ?? 'N/A',
                'phone' => $adviser->phone ?? $adviser->contact_number ?? 'N/A',
                'hours_pending' => $notification->reservation->created_at->diffInHours(now()),
                'requestor_name' => $notification->reservation->user?->full_name ?? 'Unknown',
                'service_name' => $notification->reservation->service?->service_name ?? 'N/A',
                'organization_name' => $notification->reservation->organization?->org_name ?? 'N/A',
            ];
        }

        return view('staff.notifications.show', compact('notification', 'isUnnoticedNotification', 'adviserInfo'));
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->notArchived()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Clear all notifications for the current user (archive them)
     */
    public function clearAll()
    {
        try {
            $updated = Notification::where('user_id', Auth::id())
                ->whereNull('archived_at')
                ->update(['archived_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => "Successfully archived {$updated} notification(s).",
                'count' => $updated
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear all notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear notifications. Please try again.'
            ], 500);
        }
    }

    /**
     * Archive a specific notification
     */
    public function archive($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        $notification->update(['archived_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Notification archived successfully']);
    }

    /**
     * Restore an archived notification
     */
    public function restore($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        $notification->update(['archived_at' => null]);

        return response()->json(['success' => true, 'message' => 'Notification restored successfully']);
    }
}