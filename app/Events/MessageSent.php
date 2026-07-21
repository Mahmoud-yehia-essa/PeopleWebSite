<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// يجب التأكد من عمل implements ShouldBroadcastNow ليعمل البث فوريًا دون الحاجة لتشغيل الـ Queue Worker
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        // نمرر الرسالة المحفوظة للحدث
        $this->message = $message;
    }

    // تحديد القناة الخاصة التي سيتم البث عبرها (قناة خاصة بالمستقبل فقط)
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->receiver_id),
        ];
    }

    // تحديد اسم الحدث المخصص للبث
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'image_url' => $this->message->image ? asset('new_wiselook/uploads/' . basename($this->message->image)) : null,
            'video_url' => $this->message->video ? asset('new_wiselook/uploads/' . basename($this->message->video)) : null,
            'audio_url' => $this->message->audio ? asset('new_wiselook/uploads/' . basename($this->message->audio)) : null,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'sender_avatar' => $this->message->sender->avatar_url,
            'created_at' => $this->message->created_at->toIso8601String(),
            'parent' => $this->message->parent ? [
                'id' => $this->message->parent->id,
                'message' => $this->message->parent->message,
                'image' => $this->message->parent->image,
                'video' => $this->message->parent->video,
                'audio' => $this->message->parent->audio,
                'sender' => [
                    'name' => $this->message->parent->sender->name,
                ],
            ] : null,
        ];
    }
}