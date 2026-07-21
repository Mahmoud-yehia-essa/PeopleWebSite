<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupCallInitiated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callerId;
    public $callerName;
    public $callerAvatar;
    public $groupId;
    public $groupName;
    public $channelName;
    public $receiverId;

    public function __construct($callerId, $callerName, $callerAvatar, $groupId, $groupName, $channelName, $receiverId)
    {
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->callerAvatar = $callerAvatar;
        $this->groupId = $groupId;
        $this->groupName = $groupName;
        $this->channelName = $channelName;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'GroupCallInitiated';
    }

    public function broadcastWith(): array
    {
        return [
            'caller_id' => $this->callerId,
            'caller_name' => $this->callerName,
            'caller_avatar' => $this->callerAvatar,
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'channel_name' => $this->channelName,
            'receiver_id' => $this->receiverId,
            'agora_app_id' => env('AGORA_APP_ID')
        ];
    }
}
