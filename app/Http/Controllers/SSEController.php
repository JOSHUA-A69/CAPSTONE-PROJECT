<?php

namespace App\Http\Controllers;

use App\Services\SSEService;
use App\Services\RealtimeUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SSEController extends Controller
{
    /**
     * Stream SSE events for the authenticated user
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $role = $user->role;

        return new StreamedResponse(function () use ($user, $role, $request) {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set initial timestamp
            $lastNotificationCheck = 0;
            $lastChatCheck = 0;
            $lastDashboardCheck = 0;
            $lastCrudCheck = 0;

            // Get client's last event ID if reconnecting
            $lastEventId = $request->header('Last-Event-ID', 0);

            // Track previous values to detect changes
            $prevNotificationCount = -1;
            $prevChatCount = -1;
            $prevDashboardStats = [];
            $prevCrudEvents = [];

            $iteration = 0;
            $maxIterations = 300; // 5 minutes at 1 second intervals

            while ($iteration < $maxIterations) {
                // Prevent PHP timeout
                if (connection_aborted()) {
                    break;
                }

                $currentTime = time();
                $updates = [];

                // Check notifications every iteration
                $notificationCount = SSEService::getNotificationCount($user->id);
                if ($notificationCount !== $prevNotificationCount) {
                    $updates['notifications'] = [
                        'count' => $notificationCount,
                        'timestamp' => $currentTime,
                    ];
                    $prevNotificationCount = $notificationCount;
                }

                // Check chat messages (for admin and requestor)
                if (in_array($role, ['admin', 'requestor'])) {
                    $chatCount = SSEService::getChatUnreadCount($user->id);
                    if ($chatCount !== $prevChatCount) {
                        $updates['chat'] = [
                            'unread_count' => $chatCount,
                            'timestamp' => $currentTime,
                        ];
                        $prevChatCount = $chatCount;
                    }
                }

                // Check for CRUD updates created by the RealtimeUpdateService
                $crudUpdates = $this->checkCrudUpdates($user, $role);
                if (!empty($crudUpdates)) {
                    $updates['crud'] = $crudUpdates;
                }

                // Check dashboard stats every 5 seconds
                if ($iteration % 5 === 0) {
                    $dashboardStats = $this->getDashboardStats($user, $role);
                    if ($dashboardStats !== $prevDashboardStats) {
                        $updates['dashboard'] = $dashboardStats;
                        $prevDashboardStats = $dashboardStats;
                    }
                }

                // Send updates if any
                if (!empty($updates)) {
                    echo "id: {$currentTime}\n";
                    echo SSEService::formatSSEMessage('update', $updates);
                }

                // Send keepalive every 15 seconds
                if ($iteration % 15 === 0 && empty($updates)) {
                    echo SSEService::keepalive();
                }

                // Flush output
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                // Sleep for 1 second
                sleep(1);
                $iteration++;
            }

            // Send reconnect instruction
            echo SSEService::formatSSEMessage('reconnect', ['timeout' => 1000]);
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }

    /**
     * Get dashboard stats based on user role
     */
    protected function getDashboardStats($user, string $role): array
    {
        return match ($role) {
            'admin' => SSEService::getAdminDashboardStats(),
            'staff' => SSEService::getStaffDashboardStats(),
            'adviser' => SSEService::getAdviserDashboardStats($user->id),
            'priest' => SSEService::getPriestDashboardStats($user->id),
            'requestor' => SSEService::getRequestorDashboardStats($user->id),
            default => [],
        };
    }

    /**
     * Check for new messages (polling fallback for chat page)
     */
    public function checkMessages(Request $request)
    {
        $user = Auth::user();
        $conversationWith = $request->input('conversation_with');
        $lastMessageId = $request->input('last_message_id', 0);

        $query = \App\Models\Message::where('id', '>', $lastMessageId)
            ->where(function ($q) use ($user, $conversationWith) {
                $q->where(function ($inner) use ($user, $conversationWith) {
                    $inner->where('sender_id', $user->id)
                        ->where('receiver_id', $conversationWith);
                })->orWhere(function ($inner) use ($user, $conversationWith) {
                    $inner->where('sender_id', $conversationWith)
                        ->where('receiver_id', $user->id);
                });
            })
            ->with('sender:id,first_name,last_name')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'messages' => $query,
            'hasNew' => $query->count() > 0,
        ]);
    }

    /**
     * Stream SSE for chat-specific updates
     */
    public function chatStream(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $conversationWith = $request->input('with');

        return new StreamedResponse(function () use ($user, $conversationWith) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            $lastMessageId = \App\Models\Message::where(function ($q) use ($user, $conversationWith) {
                $q->where(function ($inner) use ($user, $conversationWith) {
                    $inner->where('sender_id', $user->id)
                        ->where('receiver_id', $conversationWith);
                })->orWhere(function ($inner) use ($user, $conversationWith) {
                    $inner->where('sender_id', $conversationWith)
                        ->where('receiver_id', $user->id);
                });
            })->max('id') ?? 0;

            $iteration = 0;
            $maxIterations = 300;

            while ($iteration < $maxIterations) {
                if (connection_aborted()) {
                    break;
                }

                // Check for new messages
                $newMessages = \App\Models\Message::where('id', '>', $lastMessageId)
                    ->where(function ($q) use ($user, $conversationWith) {
                        $q->where(function ($inner) use ($user, $conversationWith) {
                            $inner->where('sender_id', $user->id)
                                ->where('receiver_id', $conversationWith);
                        })->orWhere(function ($inner) use ($user, $conversationWith) {
                            $inner->where('sender_id', $conversationWith)
                                ->where('receiver_id', $user->id);
                        });
                    })
                    ->with('sender')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($newMessages->count() > 0) {
                    $lastMessageId = $newMessages->last()->id;

                    // Format messages with complete sender data including profile pictures
                    $formattedMessages = $newMessages->map(function ($message) {
                        return [
                            'id' => $message->id,
                            'message' => $message->message,
                            'sender_id' => $message->sender_id,
                            'receiver_id' => $message->receiver_id,
                            'attachment_url' => $message->attachment_url,
                            'attachment_name' => $message->attachment_name,
                            'attachment_type' => $message->attachment_type,
                            'attachment_size' => $message->attachment_size,
                            'is_image' => $message->isImage(),
                            'is_auto_reply' => $message->is_auto_reply,
                            'created_at' => $message->created_at->toISOString(),
                            'sender' => [
                                'id' => $message->sender->id,
                                'name' => $message->sender->first_name . ' ' . $message->sender->last_name,
                                'first_name' => $message->sender->first_name,
                                'last_name' => $message->sender->last_name,
                                'profile_picture_url' => $message->sender->profile_picture_url,
                            ],
                        ];
                    })->toArray();

                    echo SSEService::formatSSEMessage('new_messages', [
                        'messages' => $formattedMessages,
                        'count' => count($formattedMessages),
                    ]);
                }

                // Check for unread count update
                $unreadCount = SSEService::getChatUnreadCount($user->id);
                if ($iteration % 5 === 0) {
                    echo SSEService::formatSSEMessage('unread_update', [
                        'count' => $unreadCount,
                    ]);
                }

                // Keepalive
                if ($iteration % 15 === 0 && $newMessages->count() === 0) {
                    echo SSEService::keepalive();
                }

                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                sleep(1);
                $iteration++;
            }

            echo SSEService::formatSSEMessage('reconnect', ['timeout' => 1000]);
            if (ob_get_level()) {
                ob_flush();
            }
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Check for real-time CRUD updates
     */
    protected function checkCrudUpdates($user, string $role): array
    {
        $events = [];

        $entityActionMap = [
            'Reservation' => ['create', 'update', 'delete'],
            'Notification' => ['create', 'update', 'delete'],
            'OrganizationBooking' => ['create', 'update', 'delete'],
            'User' => ['create', 'update', 'delete', 'restore'],
        ];

        foreach ($entityActionMap as $entity => $actions) {
            foreach ($actions as $action) {
                $update = $this->pullRealtimeEvent($user->id, $role, $action, $entity);
                if (!$update) {
                    continue;
                }

                $events[] = [
                    'type' => $action,
                    'entity' => $entity,
                    'data' => $update['data'] ?? [],
                ];
            }
        }

        // Check for list refresh triggers
        if (!empty($role)) {
            foreach (['Reservation', 'OrganizationBooking', 'User'] as $entity) {
                $listUpdate = $this->pullListRefreshEvent($user->id, $role, $entity);
                if (!$listUpdate) {
                    continue;
                }

                $events[] = [
                    'type' => 'refresh',
                    'entity' => $entity,
                ];
            }
        }

        return $events;
    }

    /**
     * Pull a single realtime CRUD event from either user-specific or role-specific channel.
     */
    protected function pullRealtimeEvent(int $userId, string $role, string $action, string $entity): ?array
    {
        foreach ([$userId, $role] as $channel) {
            $key = "realtime:{$channel}:{$action}:{$entity}";
            $event = Cache::get($key);
            if (!$event) {
                continue;
            }

            Cache::forget($key);
            return $event;
        }

        return null;
    }

    /**
     * Pull a list refresh marker from either user-specific or role-specific channel.
     */
    protected function pullListRefreshEvent(int $userId, string $role, string $entity): ?array
    {
        foreach ([$userId, $role] as $channel) {
            $key = "list:update:{$entity}:{$channel}";
            $event = Cache::get($key);
            if (!$event) {
                continue;
            }

            Cache::forget($key);
            return $event;
        }

        return null;
    }
}
