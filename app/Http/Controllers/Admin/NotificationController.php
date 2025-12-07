<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':admin,staff']);
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
            'role' => 'admin',
        ])->render();
        return response()->json(['html' => $html]);
    }



    /**
     * Get recent notifications
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->notArchived()
            ->with('reservation')
            ->orderByRaw('COALESCE(sent_at, created_at) DESC')
            ->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    /**
     * Show a specific notification
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        // If it's an Assignment notification (admin assigned as priest), redirect to service details
        if ($notification->type === 'Assignment' && $notification->reservation_id) {
            return redirect()->route('admin.services.show', $notification->reservation_id);
        }

        // If it's an Edit Request notification, redirect to change request details
        if ($notification->type === 'Edit Request') {
            $data = $notification->data;
            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            $changeRequestId = $data['change_request_id'] ?? null;
            if ($changeRequestId) {
                return redirect()->route('admin.change-requests.show', $changeRequestId);
            }
        }

        // If it's a priest declined notification, show special view
        if ($notification->type === 'Priest Declined' && $notification->reservation_id) {
            return redirect()->route('admin.notifications.priest-declined', $notification->notification_id);
        }

        // If it's a priest confirmation/cancellation notification, show detailed view
        if (in_array($notification->type, ['Update', 'Urgent']) && $notification->reservation_id) {
            $data = $notification->data;
            if (is_string($data)) {
                $data = json_decode($data, true);
            }
            $action = $data['action'] ?? null;

            if (in_array($action, ['priest_confirmed', 'priest_cancelled_confirmation', 'priest_undeclined'])) {
                return redirect()->route('admin.notifications.priest-action', $notification->notification_id);
            }
        }

        // For other types, redirect to reservation
        if ($notification->reservation_id) {
            return redirect()->route('admin.reservations.show', $notification->reservation_id);
        }

        return redirect()->route('admin.notifications.index');
    }

    /**
     * Show priest declined notification detail
     */
    public function showPriestDeclined($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->where('type', 'Priest Declined')
            ->with(['reservation.service', 'reservation.venue', 'reservation.user'])
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        // Get available priests for the reservation date/time
        $availablePriests = $this->getAvailablePriests(
            $notification->reservation->schedule_date,
            $notification->reservation_id
        );

        return view('admin.notifications.priest-declined', compact('notification', 'availablePriests'));
    }

    /**
     * Show priest action notification detail (confirmed/cancelled/undeclined)
     */
    public function showPriestAction($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->whereIn('type', ['Update', 'Urgent'])
            ->with(['reservation.service', 'reservation.venue', 'reservation.user', 'reservation.officiant'])
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        // Parse notification data
        $data = $notification->data;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return view('admin.notifications.priest-action', compact('notification', 'data'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
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
     * Get available priests for a specific date/time
     */
    private function getAvailablePriests($scheduleDate, $excludeReservationId = null)
    {
        // Get all priests
        $allPriests = User::where('role', 'priest')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        // Get priests already assigned at this time
        $assignedPriestIds = Reservation::where('schedule_date', $scheduleDate)
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->when($excludeReservationId, function ($q) use ($excludeReservationId) {
                $q->where('reservation_id', '!=', $excludeReservationId);
            })
            ->pluck('officiant_id')
            ->filter()
            ->toArray();

        // Mark availability
        return $allPriests->map(function ($priest) use ($assignedPriestIds) {
            $priest->is_available = !in_array($priest->id, $assignedPriestIds);
            return $priest;
        });
    }

    /**
     * Archive a notification
     */
    public function archive($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $notificationId)
            ->firstOrFail();

        $notification->update(['archived_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Notification archived successfully']);
    }

    /**
     * Show archived notifications
     */
    public function archived()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->archived()
            ->with('reservation')
            ->orderBy('archived_at', 'desc')
            ->paginate(20);

        return view('admin.notifications.archived', compact('notifications'));
    }

    /**
     * Restore an archived notification
     */
    public function restore($notificationId)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $notificationId)
            ->firstOrFail();

        $notification->update(['archived_at' => null]);

        return response()->json(['success' => true, 'message' => 'Notification restored successfully']);
    }
}
