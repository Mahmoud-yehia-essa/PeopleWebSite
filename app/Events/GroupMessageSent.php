<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $memberIds;

    public function __construct(Message $message, array $memberIds)
    {
        $this->message = $message;
        $this->memberIds = $memberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->memberIds as $memberId) {
            $channels[] = new PrivateChannel('chat.' . $memberId);
            $channels[] = new PrivateChannel('private-chat.' . $memberId);
        }
        if (!empty($this->message->group_id)) {
            $channels[] = new PrivateChannel('group.' . $this->message->group_id);
            $channels[] = new PrivateChannel('chat.group.' . $this->message->group_id);
        }
        return array_values(array_unique($channels));
    }

    public function broadcastAs(): string
    {
        return 'GroupMessageSent';
    }

    public function broadcastWith(): array
    {
        $senderName = '';
        $senderAvatar = null;
        if ($this->message->sender) {
            $senderName = $this->message->sender->name ?? trim(($this->message->sender->first_name ?? '') . ' ' . ($this->message->sender->last_name ?? ''));
            $senderAvatar = $this->message->sender->avatar_url ?? null;
        }

        $parentData = null;
        if ($this->message->parent) {
            $parentSenderName = '';
            if ($this->message->parent->sender) {
                $parentSenderName = $this->message->parent->sender->name ?? trim(($this->message->parent->sender->first_name ?? '') . ' ' . ($this->message->parent->sender->last_name ?? ''));
            }
            $parentData = [
                'id' => $this->message->parent->id,
                'message' => $this->message->parent->message,
                'image' => $this->message->parent->image,
                'video' => $this->message->parent->video,
                'audio' => $this->message->parent->audio,
                'sender' => [
                    'name' => $parentSenderName,
                ],
            ];
        }

        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'image' => $this->message->image,
            'video' => $this->message->video,
            'audio' => $this->message->audio,
            'image_url' => $this->message->image ? asset('new_wiselook/uploads/' . basename($this->message->image)) : null,
            'video_url' => $this->message->video ? asset('new_wiselook/uploads/' . basename($this->message->video)) : null,
            'audio_url' => $this->message->audio ? asset('new_wiselook/uploads/' . basename($this->message->audio)) : null,
            'sender_id' => (int)$this->message->sender_id,
            'user_id' => (int)$this->message->sender_id,
            'from_id' => (int)$this->message->sender_id,
            'sender_name' => $senderName,
            'sender_avatar' => $senderAvatar,
            'group_id' => (int)$this->message->group_id,
            'created_at' => $this->message->created_at ? $this->message->created_at->toIso8601String() : null,
            'timestamp' => $this->message->created_at ? $this->message->created_at->timestamp : null,
            'parent' => $parentData,
        ];
    }
}
