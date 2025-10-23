<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\User;
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
                // Determine background for unread/read notifications
                $bgColor = $notification->isUnread() ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-white dark:bg-gray-800';

                // Get priest data from notification
                $data = $notification->data;
                if (is_string($data)) {
                    $data = json_decode($data, true);
                }
                $priestName = $data['priest_name'] ?? 'Unknown';
                $timeAgo = $notification->sent_at->diffForHumans();

                // Generate avatar initials
                $nameParts = explode(' ', str_replace('Fr. ', '', $priestName));
                $initials = '';
                if (count($nameParts) >= 2) {
                    $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
                } else {
                    $initials = strtoupper(substr($priestName, 0, 2));
                }

                // Random avatar colors for variety
                $colors = [
                    'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-orange-500',
                    'bg-pink-500', 'bg-indigo-500', 'bg-teal-500', 'bg-red-500'
                ];
                $avatarColor = $colors[array_rand($colors)];

                if ($notification->type === 'Priest Declined') {
                    $url = route('admin.notifications.priest-declined', $notification->notification_id);
                } else {
                    $url = route('admin.notifications.show', $notification->notification_id);
                }

                $html .= '<div class="relative group ' . $bgColor . '">';
                $html .= '<a href="' . $url . '" class="block px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">';
                $html .= '<div class="flex items-start gap-4">';

                // Avatar
                $html .= '<div class="flex-shrink-0">';
                $html .= '<div class="w-11 h-11 rounded-full ' . $avatarColor . ' flex items-center justify-center text-white font-semibold text-sm">';
                $html .= $initials;
                $html .= '</div>';
                $html .= '</div>';

                // Content
                $html .= '<div class="flex-1 min-w-0">';
                $html .= '<p class="text-[15px] text-gray-900 dark:text-gray-100 leading-snug truncate">'
                    . $notification->message
                    . ' <span class="mx-2 text-gray-400">•</span><span class="text-xs text-gray-500 dark:text-gray-400">'
                    . $timeAgo
                    . '</span></p>';
                $html .= '</div>';

                $html .= '</div>';
                $html .= '</a>';

                // Three-dot menu
                $html .= '<button class="absolute top-4 right-5 opacity-0 group-hover:opacity-100 transition-opacity p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">';
                $html .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
                $html .= '<path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>';
                $html .= '</svg>';
                $html .= '</button>';

                $html .= '</div>';
            }
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Get recent notifications
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->with('reservation')
            ->orderBy('sent_at', 'desc')
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
}
