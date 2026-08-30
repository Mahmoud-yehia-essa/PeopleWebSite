<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageId;
    public $message;
    public $userId;
    public $reaction;
    public $action; // 'added', 'removed', 'updated'
    public $reactionsSummary;
    public $allReactions;
    public $memberIds;

    public function __construct(Message $message, int $userId, ?string $reaction, string $action, array $reactionsSummary, array $allReactions, array $memberIds = [])
    {
        $this->messageId = $message->id;
        $this->message = $message;
        $this->userId = $userId;
        $this->reaction = $reaction;
        $this->action = $action;
        $this->reactionsSummary = $reactionsSummary;
        $this->allReactions = $allReactions;
        $this->memberIds = $memberIds;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        if (!empty($this->message->group_id)) {
            $channels[] = new PrivateChannel('chat.group.' . $this->message->group_id);
            $channels[] = new PrivateChannel('group.' . $this->message->group_id);
            $channels[] = new Channel('group.' . $this->message->group_id);
            $channels[] = new Channel('chat.group.' . $this->message->group_id);
            if (!empty($this->memberIds)) {
                foreach ($this->memberIds as $mId) {
                    $channels[] = new PrivateChannel('chat.' . $mId);
                    $channels[] = new PrivateChannel('private-chat.' . $mId);
                }
            }
        } else {
            if ($this->message->receiver_id) {
                $channels[] = new PrivateChannel('chat.' . $this->message->receiver_id);
                $channels[] = new PrivateChannel('private-chat.' . $this->message->receiver_id);
            }
            if ($this->message->sender_id) {
                $channels[] = new PrivateChannel('chat.' . $this->message->sender_id);
                $channels[] = new PrivateChannel('private-chat.' . $this->message->sender_id);
            }
        }

        return array_values(array_unique($channels));
    }

    public function broadcastAs(): string
    {
        return 'MessageReactionUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id'        => $this->messageId,
            'group_id'          => $this->message->group_id,
            'receiver_id'       => $this->message->receiver_id,
            'sender_id'         => $this->message->sender_id,
            'user_id'           => $this->userId,
            'reaction'          => $this->reaction,
            'action'            => $this->action,
            'reactions_summary' => $this->reactionsSummary,
            'reactions'         => $this->reactionsSummary,
            'all_reactions'     => $this->allReactions,
        ];
    }
}
