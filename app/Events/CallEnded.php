<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $targetUserId;
    public $channelName;

    public function __construct($targetUserId, $channelName)
    {
        $this->targetUserId = $targetUserId;
        $this->channelName = $channelName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->targetUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CallEnded';
    }

    public function broadcastWith(): array
    {
        return [
            'target_user_id' => $this->targetUserId,
            'channel_name' => $this->channelName,
        ];
    }
}
