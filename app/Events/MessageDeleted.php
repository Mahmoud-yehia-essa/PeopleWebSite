<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $messageId;
    public int $receiverId;

    public function __construct(int $messageId, int $receiverId)
    {
        $this->messageId  = $messageId;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn(): array
    {
        // Broadcast on the receiver's private channel so they see the deletion live
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'MessageDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
        ];
    }
}
