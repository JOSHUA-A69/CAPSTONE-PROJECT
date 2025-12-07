<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':requestor']);
    }

    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->notArchived()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->notArchived()
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();

        $html = view('components.notifications.recent-list', [
            'notifications' => $notifications,
            'role' => 'requestor',
        ])->render();

        return response()->json(['html' => $html]);
    }

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->notArchived()
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('requestor.notifications.index', compact('notifications'));
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

        return view('requestor.notifications.archived', compact('notifications'));
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        if (!$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        if ($notification->reservation_id) {
            return redirect()->route('requestor.reservations.show', [
                'reservation_id' => $notification->reservation_id,
                'notification_read' => 1
            ]);
        }

        return redirect()->route('requestor.notifications.index');
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->firstOrFail();

        $notification->read_at = now();
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
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