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
        $count = Message::where('receiver_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
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

        // Get all admins
        $conversations = DB::table('messages')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture')
            ->selectRaw('MAX(CASE WHEN cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at THEN messages.created_at END) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN (cr.cleared_at IS NULL OR messages.created_at > cr.cleared_at) AND messages.receiver_id = ? AND messages.read_at IS NULL THEN 1 END) as unread_count', [$userId])
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
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture')
            ->orderBy('last_message_at', 'desc')
            ->get();

        // If no conversations yet, get all admins
        if ($conversations->isEmpty()) {
            $conversations = User::where('role', 'admin')
                ->select('id', 'first_name', 'last_name', 'email', 'profile_picture')
                ->selectRaw('NULL as last_message_at')
                ->selectRaw('0 as unread_count')
                ->get();
        }

        return $conversations;
    }
}
