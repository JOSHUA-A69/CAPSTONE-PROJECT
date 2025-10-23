<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();

        $html = '';
        if ($notifications->isEmpty()) {
            $html = '<div class="px-5 py-12 text-center bg-white dark:bg-gray-800">';
            $html .= '<svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>';
            $html .= '</svg>';
            $html .= '<p class="mt-3 text-sm text-gray-500 dark:text-gray-400">No notifications yet</p>';
            $html .= '</div>';
        } else {
            foreach ($notifications as $notification) {
                $bgColor = $notification->isUnread() ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800';
                
                $data = $notification->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                
                $timeAgo = $notification->sent_at->diffForHumans();

                // Determine notification icon color based on type
                $iconColor = match($notification->type) {
                    'Cancellation Request' => 'bg-red-500',
                    'Update' => 'bg-green-500',
                    'Urgent' => 'bg-orange-500',
                    default => 'bg-blue-500',
                };

                $url = route('adviser.notifications.show', $notification->notification_id);

                $html .= '<div class="relative group ' . $bgColor . '">';
                $html .= '<a href="' . $url . '" class="block px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">';
                $html .= '<div class="flex items-start gap-4">';
                
                // Icon
                $html .= '<div class="flex-shrink-0">';
                $html .= '<div class="w-11 h-11 rounded-full ' . $iconColor . ' flex items-center justify-center text-white font-semibold text-sm">';
                $html .= '<svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>';
                $html .= '</div>';
                $html .= '</div>';
                
                // Content
                $html .= '<div class="flex-1 min-w-0">';
                $html .= '<p class="text-[15px] text-gray-900 dark:text-gray-100 leading-snug">'
                    . $notification->message
                    . '</p>';
                $html .= '<p class="text-xs text-gray-500 dark:text-gray-400 mt-1">' . $timeAgo . '</p>';
                $html .= '</div>';
                
                $html .= '</div>';
                $html .= '</a>';
                $html .= '</div>';
            }
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Get all notifications
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('adviser.notifications.index', compact('notifications'));
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
            return redirect()->route('adviser.reservations.show', $notification->reservation_id);
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
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
