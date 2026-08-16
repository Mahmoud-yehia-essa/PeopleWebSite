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
    public ?int $senderId;

    public function __construct(int $messageId, int $receiverId, ?int $senderId = null)
    {
        $this->messageId  = $messageId;
        $this->receiverId = $receiverId;
        $this->senderId   = $senderId;
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
        if ($this->senderId) {
            $channels[] = new PrivateChannel('chat.' . $this->senderId);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'MessageDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'  => (int)$this->messageId,
            'id'          => (int)$this->messageId,
            'receiver_id' => (int)$this->receiverId,
            'sender_id'   => (int)$this->senderId,
        ];
    }
}
