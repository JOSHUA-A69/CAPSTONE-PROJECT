<?php

namespace App\Observers;

use App\Models\Message;
use App\Services\SSEService;

class MessageObserver
{
    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        // Notify both sender and receiver
        $userIds = [$message->sender_id, $message->receiver_id];

        SSEService::triggerUpdate('chat', $userIds, [
            'action' => 'new_message',
            'message_id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
        ]);
    }

    /**
     * Handle the Message "updated" event.
     * This is mainly for when messages are marked as read.
     */
    public function updated(Message $message): void
    {
        // If read_at was just set, notify the sender
        if ($message->isDirty('read_at') && $message->read_at !== null) {
            SSEService::triggerUpdate('chat', [$message->sender_id], [
                'action' => 'message_read',
                'message_id' => $message->id,
            ]);
        }
    }
}
