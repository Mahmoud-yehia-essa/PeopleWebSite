<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAccepted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callerId;
    public $receiverId;
    public $channelName;

    public function __construct($callerId, $receiverId, $channelName)
    {
        $this->callerId = $callerId;
        $this->receiverId = $receiverId;
        $this->channelName = $channelName;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->callerId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CallAccepted';
    }

    public function broadcastWith(): array
    {
        return [
            'caller_id' => $this->callerId,
            'receiver_id' => $this->receiverId,
            'channel_name' => $this->channelName,
        ];
    }
}
