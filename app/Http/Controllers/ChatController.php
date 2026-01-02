<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display chat interface.
     */
    public function index()
    {
        $user = Auth::user();

        // Get list of users the current user can chat with
        if ($user->role === 'admin') {
            // Admin can see all requestors they've chatted with
            $conversations = $this->getAdminConversations();
        } else {
            // Requestors can only chat with admins
            $conversations = $this->getRequestorConversations();
        }

        return view('chat.index', compact('conversations'));
    }

    /**
     * Get conversation with a specific user.
     */
    public function show($userId)
    {
        $user = Auth::user();
        $otherUser = User::findOrFail($userId);

        // Authorization: Requestors can only chat with admins, admins can chat with requestors
        if ($user->role === 'requestor' && $otherUser->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }
        if ($user->role === 'admin' && $otherUser->role !== 'requestor') {
            abort(403, 'Unauthorized access');
        }

        // Get messages between these two users
        $messages = Message::conversation($user->id, $userId)
            ->with(['sender', 'receiver'])
            ->get();

        // Mark messages as read
        Message::where('sender_id', $userId)
            ->where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('chat.show', compact('messages', 'otherUser'));
    }

    /**
     * Send a new message.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx',
        ]);

        $user = Auth::user();
        $receiverId = $request->receiver_id;
        $receiver = User::findOrFail($receiverId);

        // Authorization check
        if ($user->role === 'requestor' && $receiver->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        if ($user->role === 'admin' && $receiver->role !== 'requestor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Prepare message data
        $messageData = [
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->message ?? '',
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('chat_attachments', $fileName, 'public');

            $messageData['attachment_path'] = $filePath;
            $messageData['attachment_name'] = $file->getClientOriginalName();
            $messageData['attachment_type'] = $file->getMimeType();
            $messageData['attachment_size'] = $file->getSize();
        }

        // Create message
        $message = Message::create($messageData);

        // Load relationships
        $message->load(['sender', 'receiver']);

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'attachment_url' => $message->attachment_url,
                'attachment_name' => $message->attachment_name,
                'attachment_type' => $message->attachment_type,
                'attachment_size' => $message->attachment_size,
                'is_image' => $message->isImage(),
                'sender' => [
                    'id' => $message->sender->id,
                    'name' => $message->sender->first_name ?? $message->sender->name,
                    'profile_picture' => $message->sender->profile_picture_url,
                ],
                'created_at' => $message->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * Send FAQ auto-reply (question + automated response).
     */
    public function sendFaqAutoReply(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'question' => 'required|string|max:500',
            'answer' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $receiverId = $request->receiver_id;

        // Only requestors can use this endpoint
        if ($user->role !== 'requestor') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 1. Send the question first
        $questionMessage = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $request->question,
            'is_auto_reply' => false,
        ]);
        $questionMessage->load(['sender', 'receiver']);

        // Small delay to ensure auto-reply has a later timestamp
        usleep(100000); // 100ms delay

        // 2. Send the automated reply (from admin to requestor)
        $autoReplyMessage = Message::create([
            'sender_id' => $receiverId, // Admin/receiver sends the auto-reply
            'receiver_id' => $user->id, // To the requestor
            'message' => "🤖 **Automated FAQ Response:**\n\n" . $request->answer . "\n\n_If you need further assistance, please type your question below and an admin will respond shortly._",
            'is_auto_reply' => true,
            'read_at' => now(), // Mark as read immediately since it's automated
        ]);
        $autoReplyMessage->load(['sender', 'receiver']);

        return response()->json([
            'success' => true,
            'question' => [
                'id' => $questionMessage->id,
                'message' => $questionMessage->message,
                'sender_id' => $questionMessage->sender_id,
                'receiver_id' => $questionMessage->receiver_id,
                'is_auto_reply' => false,
                'sender' => [
                    'id' => $questionMessage->sender->id,
                    'name' => $questionMessage->sender->first_name ?? $questionMessage->sender->name,
                    'profile_picture' => $questionMessage->sender->profile_picture_url,
                ],
                'created_at' => $questionMessage->created_at->toISOString(),
            ],
            'auto_reply' => [
                'id' => $autoReplyMessage->id,
                'message' => $autoReplyMessage->message,
                'sender_id' => $autoReplyMessage->sender_id,
                'receiver_id' => $autoReplyMessage->receiver_id,
                'is_auto_reply' => true,
                'sender' => [
                    'id' => $autoReplyMessage->sender->id,
                    'name' => $autoReplyMessage->sender->first_name ?? $autoReplyMessage->sender->name,
                    'profile_picture' => $autoReplyMessage->sender->profile_picture_url,
                ],
                'created_at' => $autoReplyMessage->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Get messages for a conversation (AJAX).
     */
    public function getMessages($userId)
    {
        $user = Auth::user();

        $lastClearedAt = DB::table('chat_resets')
            ->where('user_id', $user->id)
            ->where('other_user_id', $userId)
            ->value('cleared_at');

        $messages = Message::conversation($user->id, $userId)
            ->when($lastClearedAt, function ($query) use ($lastClearedAt) {
                $query->where('created_at', '>', $lastClearedAt);
            })
            ->with(['sender', 'receiver'])
            ->get();

        return response()->json([
            'messages' => $messages->map(function ($message) {
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
                    'is_auto_reply' => $message->is_auto_reply ?? false,
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->first_name ?? $message->sender->name,
                        'profile_picture' => $message->sender->profile_picture_url,
                    ],
                    'created_at' => $message->created_at->toISOString(),
                    'read_at' => $message->read_at,
                ];
                        })
                ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                    ->header('Pragma', 'no-cache');
    }

    /**
     * Get unread message count.
     */
    public function unreadCount()
    {
        $userId = Auth::id();

        // Exclude messages cleared via chat resets (per-conversation clear)
        $count = DB::table('messages')
            ->leftJoin('chat_resets as cr', function ($join) use ($userId) {
                $join->on('cr.other_user_id', '=', 'messages.sender_id')
                     ->where('cr.user_id', '=', $userId);
            })
            ->where('messages.receiver_id', $userId)
            // Only count messages sent by other users
            ->where('messages.sender_id', '<>', $userId)
            ->whereNull('messages.read_at')
            ->where(function ($q) {
                $q->whereNull('cr.cleared_at')
                  ->orWhereColumn('messages.created_at', '>', 'cr.cleared_at');
            })
            // Use DISTINCT to avoid any accidental duplicates from joins
            ->distinct()
            ->count('messages.id');

        return response()->json(['count' => $count])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Mark messages as read.
     */
    public function markAsRead($userId)
    {
        Message::where('sender_id', $userId)
            ->where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Clear all messages in a conversation.
     */
    public function clearConversation($userId)
    {
        $user = Auth::user();

        DB::table('chat_resets')->upsert([
            [
                'user_id' => $user->id,
                'other_user_id' => $userId,
                'cleared_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['user_id', 'other_user_id'], ['cleared_at', 'updated_at']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation cleared for current user.',
        ]);
    }

    /**
     * Debug unread messages for current user (admin-only).
     * Returns the distinct IDs counted plus raw unread rows for comparison.
     */
    public function debugUnread()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $userId = $user->id;

        $base = DB::table('messages')
            ->leftJoin('chat_resets as cr', function ($join) use ($userId) {
                $join->on('cr.other_user_id', '=', 'messages.sender_id')
                     ->where('cr.user_id', '=', $userId);
            })
            ->where('messages.receiver_id', $userId)
            ->where('messages.sender_id', '<>', $userId)
            ->whereNull('messages.read_at')
            ->where(function ($q) {
                $q->whereNull('cr.cleared_at')
                  ->orWhereColumn('messages.created_at', '>', 'cr.cleared_at');
            })
            ->select([
                'messages.id',
                'messages.sender_id',
                'messages.receiver_id',
                'messages.created_at',
                'cr.cleared_at',
            ]);

        $count = (clone $base)->distinct()->count('messages.id');
        $counted = (clone $base)->distinct()->orderBy('messages.id')->get();

        $rawUnread = DB::table('messages')
            ->where('receiver_id', $userId)
            ->where('sender_id', '<>', $userId)
            ->whereNull('read_at')
            ->orderBy('id')
            ->get(['id', 'sender_id', 'receiver_id', 'created_at']);

        $senderIds = $counted->pluck('sender_id')->merge($rawUnread->pluck('sender_id'))->unique()->values();
        $senders = DB::table('users')
            ->whereIn('id', $senderIds)
            ->get(['id', 'first_name', 'last_name', 'email']);

        return response()->json([
            'count' => $count,
            'counted' => $counted,
            'raw_unread_total' => $rawUnread->count(),
            'raw_unread' => $rawUnread,
            'senders' => $senders,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    /**
     * Debug action: mark all unread messages for the current user as read (admin-only).
     */
    public function debugMarkAllUnreadAsRead()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $affected = DB::table('messages')
            ->where('receiver_id', $user->id)
            ->where('sender_id', '<>', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'updated' => $affected,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache');
    }

    /**
     * Get admin's conversations.
     */
    private function getAdminConversations()
    {
        $userId = Auth::id();

        // Get all requestors who have messaged this admin or whom this admin has messaged
        $conversations = DB::table('messages')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture')
            ->selectRaw('MAX(CASE WHEN cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at THEN messages.created_at END) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN (cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at) AND messages.receiver_id = ? AND messages.read_at IS NULL THEN 1 END) as unread_count', [$userId])
            ->selectRaw('SUM(CASE WHEN (cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at) THEN 1 ELSE 0 END) as message_count')
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->where('messages.receiver_id', '=', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->on('users.id', '=', 'messages.receiver_id')
                            ->where('messages.sender_id', '=', $userId);
                    });
            })
            ->leftJoin('chat_resets as cr', function ($join) use ($userId) {
                $join->on('cr.other_user_id', '=', 'users.id')
                    ->where('cr.user_id', '=', $userId);
            })
            ->where('users.role', 'requestor')
            ->where(function ($query) use ($userId) {
                $query->where('messages.sender_id', $userId)
                    ->orWhere('messages.receiver_id', $userId);
            })
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture')
            ->orderBy('last_message_at', 'desc')
            ->get();

        return $conversations;
    }

    /**
     * Get requestor's conversations (with admins).
     */
    private function getRequestorConversations()
    {
        $userId = Auth::id();

        // Get all admins with their conversation stats
        $adminsWithConversations = DB::table('messages')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture', 'users.role')
            ->selectRaw('MAX(CASE WHEN cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at THEN messages.created_at END) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN (cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at) AND messages.receiver_id = ? AND messages.read_at IS NULL THEN 1 END) as unread_count', [$userId])
            ->selectRaw('SUM(CASE WHEN (cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at) THEN 1 ELSE 0 END) as message_count')
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->where('messages.receiver_id', '=', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->on('users.id', '=', 'messages.receiver_id')
                            ->where('messages.sender_id', '=', $userId);
                    });
            })
            ->leftJoin('chat_resets as cr', function ($join) use ($userId) {
                $join->on('cr.other_user_id', '=', 'users.id')
                    ->where('cr.user_id', '=', $userId);
            })
            ->where('users.role', 'admin')
            ->where(function ($query) use ($userId) {
                $query->where('messages.sender_id', $userId)
                    ->orWhere('messages.receiver_id', $userId);
            })
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture', 'users.role')
            ->get()
            ->keyBy('id');

        // Get ALL admins (including those without conversations)
        $allAdmins = User::where('role', 'admin')
            ->select('id', 'first_name', 'last_name', 'email', 'profile_picture', 'role')
            ->get();

        // Merge: use conversation data if exists, otherwise use defaults
        $conversations = $allAdmins->map(function ($admin) use ($adminsWithConversations) {
            if ($adminsWithConversations->has($admin->id)) {
                return $adminsWithConversations->get($admin->id);
            }
            
            // Admin with no conversation yet
            return (object) [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'email' => $admin->email,
                'profile_picture' => $admin->profile_picture,
                'role' => $admin->role,
                'last_message_at' => null,
                'unread_count' => 0,
                'message_count' => 0,
            ];
        });

        // Sort: admins with recent messages first, then alphabetically
        $conversations = $conversations->sortBy([
            ['last_message_at', 'desc'],
            ['first_name', 'asc'],
        ])->values();

        return $conversations;
    }
}
