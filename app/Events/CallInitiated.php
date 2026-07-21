<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callerId;
    public $callerName;
    public $callerAvatar;
    public $receiverId;
    public $channelName;
    public $token;

    public function __construct($callerId, $callerName, $callerAvatar, $receiverId, $channelName, $token)
    {
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->callerAvatar = $callerAvatar;
        $this->receiverId = $receiverId;
        $this->channelName = $channelName;
        $this->token = $token;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'CallInitiated';
    }

    public function broadcastWith(): array
    {
        return [
            'caller_id' => $this->callerId,
            'caller_name' => $this->callerName,
            'caller_avatar' => $this->callerAvatar,
            'receiver_id' => $this->receiverId,
            'channel_name' => $this->channelName,
            'token' => $this->token,
            'agora_app_id' => env('AGORA_APP_ID')
        ];
    }
}
