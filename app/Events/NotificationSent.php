<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public $userId;
    public $notification;
    public $unreadCount;

    public function __construct(int $userId, array $notification, int $unreadCount = 0)
    {
        $this->userId = $userId;
        $this->notification = $notification;
        $this->unreadCount = $unreadCount;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->userId),
            new PrivateChannel('private-chat.' . $this->userId),
            new PrivateChannel('notifications.' . $this->userId),
            new PrivateChannel('private-notifications.' . $this->userId),
        ];
    }
    public function broadcastAs(): string
    {
        return 'NotificationSent';
    }

    public function broadcastWith(): array
    {
        return [
            'notification' => $this->notification,
            'unread_count' => $this->unreadCount,
        ];
    }
}
