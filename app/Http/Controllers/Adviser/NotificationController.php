<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\RoleMiddleware::class . ':adviser']);
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
            'role' => 'adviser',
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Get all notifications
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->notArchived()
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('adviser.notifications.index', compact('notifications'));
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

        return view('adviser.notifications.archived', compact('notifications'));
    }

    /**
     * Show a specific notification
     */
    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->where('notification_id', $id)
            ->with(['reservation.user', 'reservation.service', 'reservation.organization', 'reservation.venue'])
            ->firstOrFail();

        // Mark as read
        $notification->markAsRead();

        // Check if this is an unnoticed reservation reminder for the adviser
        $isUnnoticedReminder = false;
        $adviserContactInfo = null;
        
        if ($notification->data) {
            $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
            if (isset($data['action']) && $data['action'] === 'unnoticed_reservation_reminder') {
                $isUnnoticedReminder = true;
                $adviserContactInfo = [
                    'email' => $data['your_contact_email'] ?? Auth::user()->email ?? 'N/A',
                    'phone' => $data['your_contact_phone'] ?? Auth::user()->phone ?? 'N/A',
                    'hours_pending' => $data['hours_pending'] ?? 0,
                    'requestor_name' => $data['requestor_name'] ?? 'Unknown',
                    'service_name' => $data['service_name'] ?? 'N/A',
                    'schedule_date' => $data['schedule_date'] ?? 'N/A',
                    'organization_name' => $data['organization_name'] ?? 'N/A',
                ];
                
                // Show the unnoticed reservation reminder view
                return view('adviser.notifications.unnoticed-reminder', compact('notification', 'adviserContactInfo'));
            }
        }

        // Route to appropriate view based on type
        if ($notification->type === 'Cancellation Request') {
            // Get cancellation_id from notification data
            $data = $notification->data;
            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            $cancellationId = $data['cancellation_id'] ?? null;

            if ($cancellationId) {
                return redirect()->route('adviser.cancellations.show', $cancellationId);
            }
        }

        // For other types, redirect to reservation or show generic view
        if ($notification->reservation_id) {
            return redirect()->route('adviser.reservations.show', [
                'reservation_id' => $notification->reservation_id,
                'notification_read' => 1
            ]);
        }

        return redirect()->route('adviser.notifications.index');
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