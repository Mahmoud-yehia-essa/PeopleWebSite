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
    public ?int $receiverId;
    public ?int $senderId;
    public ?int $groupId;
    public array $memberIds;

    public function __construct(int $messageId, ?int $receiverId = null, ?int $senderId = null, ?int $groupId = null, array $memberIds = [])
    {
        $this->messageId  = $messageId;
        $this->receiverId = $receiverId;
        $this->senderId   = $senderId;
        $this->groupId    = $groupId;
        $this->memberIds  = $memberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        if ($this->receiverId) {
            $channels[] = new PrivateChannel('chat.' . $this->receiverId);
            $channels[] = new PrivateChannel('private-chat.' . $this->receiverId);
        }
        if ($this->senderId) {
            $channels[] = new PrivateChannel('chat.' . $this->senderId);
            $channels[] = new PrivateChannel('private-chat.' . $this->senderId);
        }
        if ($this->groupId) {
            $channels[] = new PrivateChannel('chat.group.' . $this->groupId);
            $channels[] = new PrivateChannel('group.' . $this->groupId);
        }
        if (!empty($this->memberIds)) {
            foreach ($this->memberIds as $mId) {
                $channels[] = new PrivateChannel('chat.' . $mId);
                $channels[] = new PrivateChannel('private-chat.' . $mId);
            }
        }
        return array_values(array_unique($channels));
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
            'receiver_id' => $this->receiverId ? (int)$this->receiverId : null,
            'sender_id'   => $this->senderId ? (int)$this->senderId : null,
            'group_id'    => $this->groupId ? (int)$this->groupId : null,
        ];
    }
}

