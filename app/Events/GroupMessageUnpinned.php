<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageUnpinned implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public $groupId,
        public array $memberIds = []
    ) {}

    public function broadcastOn(): array
    {
        $channels = [];
        foreach ($this->memberIds as $memberId) {
            $channels[] = new PrivateChannel('chat.' . $memberId);
            $channels[] = new PrivateChannel('private-chat.' . $memberId);
        }
        $channels[] = new PrivateChannel('group.' . $this->groupId);
        $channels[] = new PrivateChannel('chat.group.' . $this->groupId);
        $channels[] = new Channel('group.' . $this->groupId);
        $channels[] = new Channel('chat.group.' . $this->groupId);

        return array_values(array_unique($channels));
    }

    public function broadcastAs(): string
    {
        return 'GroupMessageUnpinned';
    }

    public function broadcastWith(): array
    {
        return [
            'group_id' => (string) $this->groupId,
        ];
    }
}
