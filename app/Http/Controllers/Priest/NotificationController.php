<?php

namespace App\Http\Controllers\Priest;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':priest']);
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
        $notifications = Notification::where('user_id', Auth::id())
            ->unread()
            ->notArchived()
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();

        $html = view('components.notifications.recent-list', [
            'notifications' => $notifications,
            'role' => 'priest',
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Display all notifications
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->notArchived()
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('priest.notifications.index', compact('notifications'));
    }

    /**
     * Display archived notifications
     */
    public function archived()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->archived()
            ->with('reservation')
            ->orderBy('archived_at', 'desc')
            ->paginate(20);

        return view('priest.notifications.archived', compact('notifications'));
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

        // If it's an assignment notification, show special view
        if ($notification->type === 'Assignment' && $notification->reservation_id) {
            return redirect()->route('priest.notifications.assignment', $notification->notification_id);
        }

        // For other types, redirect to reservation with notification_read parameter
        if ($notification->reservation_id) {
            return redirect()->route('priest.reservations.show', [
                'reservation_id' => $notification->reservation_id,
                'notification_read' => 1
            ]);
        }

        return redirect()->route('priest.notifications.index');
    }

    /**
     * Show assignment notification detail
     */
    public function showAssignment($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->where('type', 'Assignment')
            ->with(['reservation.service', 'reservation.venue', 'reservation.user'])
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        return view('priest.notifications.assignment', compact('notification'));
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