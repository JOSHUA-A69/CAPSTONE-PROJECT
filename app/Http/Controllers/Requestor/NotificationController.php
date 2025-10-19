<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecent()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();

        $html = '';
        if ($notifications->isEmpty()) {
            $html = '<div class="px-4 py-3 text-sm text-gray-500">No new notifications</div>';
        } else {
            foreach ($notifications as $notification) {
                $html .= '<a href="' . route('requestor.notifications.show', $notification->notification_id) . '" class="block px-4 py-3 hover:bg-gray-50 border-b">';
                $html .= '<div class="text-sm font-medium text-gray-900">' . strip_tags($notification->message) . '</div>';
                $html .= '<div class="text-xs text-gray-500 mt-1">' . $notification->sent_at->diffForHumans() . '</div>';
                $html .= '</a>';
            }
        }

        return response()->json(['html' => $html]);
    }

    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('sent_at', 'desc')
            ->paginate(20);

        return view('requestor.notifications.index', compact('notifications'));
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
            return redirect()->route('requestor.reservations.show', $notification->reservation_id);
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
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
