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
     * Get messages for a conversation (AJAX).
     */
    public function getMessages($userId)
    {
        $user = Auth::user();

        $messages = Message::conversation($user->id, $userId)
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
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->first_name ?? $message->sender->name,
                        'profile_picture' => $message->sender->profile_picture_url,
                    ],
                    'created_at' => $message->created_at->toISOString(),
                    'read_at' => $message->read_at,
                ];
            })
        ]);
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
     * Get admin's conversations.
     */
    private function getAdminConversations()
    {
        $userId = Auth::id();

        // Get all requestors who have messaged this admin or whom this admin has messaged
        $conversations = DB::table('messages')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.email', 'users.profile_picture')
            ->selectRaw('MAX(messages.created_at) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN messages.receiver_id = ? AND messages.read_at IS NULL THEN 1 END) as unread_count', [$userId])
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->where('messages.receiver_id', '=', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->on('users.id', '=', 'messages.receiver_id')
                            ->where('messages.sender_id', '=', $userId);
                    });
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
            ->selectRaw('MAX(messages.created_at) as last_message_at')
            ->selectRaw('COUNT(CASE WHEN messages.receiver_id = ? AND messages.read_at IS NULL THEN 1 END) as unread_count', [$userId])
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'messages.sender_id')
                    ->where('messages.receiver_id', '=', $userId)
                    ->orWhere(function ($query) use ($userId) {
                        $query->on('users.id', '=', 'messages.receiver_id')
                            ->where('messages.sender_id', '=', $userId);
                    });
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
