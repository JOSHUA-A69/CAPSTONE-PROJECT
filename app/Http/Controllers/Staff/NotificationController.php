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
                ->with(['reservation.officiant'])
                ->orderByRaw('COALESCE(sent_at, created_at) DESC')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to load notifications: ' . $e->getMessage());
            return response()->json(['html' => '<div class="px-5 py-12 text-center bg-white dark:bg-gray-800"><p class="text-red-600">Error loading notifications. Please refresh.</p></div>']);
        }

        $html = '';
        if ($notifications->isEmpty()) {
            $html = '<div class="px-6 py-12 text-center bg-white dark:bg-gray-800">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No new notifications</p>
                    </div>';
        } else {
            foreach ($notifications as $notification) {
                $bgClass = 'bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30';
                $iconBg = 'bg-blue-100 dark:bg-blue-900';
                $iconColor = 'text-blue-600 dark:text-blue-400';

                if (str_contains($notification->message, 'approved') || str_contains($notification->message, 'confirmed')) {
                    $bgClass = 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30';
                    $iconBg = 'bg-green-100 dark:bg-green-900';
                    $iconColor = 'text-green-600 dark:text-green-400';
                } elseif (str_contains($notification->message, 'cancelled') || str_contains($notification->message, 'declined')) {
                    $bgClass = 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30';
                    $iconBg = 'bg-red-100 dark:bg-red-900';
                    $iconColor = 'text-red-600 dark:text-red-400';
                }

                $timeAgo = $this->getTimeAgo($notification->sent_at ?? $notification->created_at);

                // Sanitize message while allowing minimal formatting
                $safeMessage = strip_tags((string) $notification->message, '<strong><b><em><i><br>');

                $html .= '<a href="' . route('staff.notifications.index') . '" class="block px-6 py-4 transition-colors duration-150 ' . $bgClass . ' border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-full ' . $iconBg . ' flex items-center justify-center">
                                        <svg class="w-5 h-5 ' . $iconColor . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">' . $safeMessage . '</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">' . $timeAgo . '</p>
                                </div>
                            </div>
                        </a>';
            }
        }

        return response()->json(['html' => $html]);
    }

    /**
     * Display all notifications page
     */
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->with(['reservation.officiant'])
            ->orderByRaw('COALESCE(sent_at, created_at) DESC')
            ->paginate(20);

        return view('staff.notifications.index', compact('notifications'));
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
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()->back()->with('status', 'all-notifications-read');
    }

    /**
     * Helper function to get human-readable time ago
     */
    private function getTimeAgo($datetime)
    {
        $now = now();
        $diff = $now->diffInSeconds($datetime);

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return $datetime->format('M j, Y');
        }
    }
}
