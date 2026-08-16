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

    // تحديد القنوات الخاصة التي سيتم البث عبرها (للمستقبل والمرسل لضمان التزامن اللحظي على كافة الأجهزة والتطبيق)
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.' . $this->message->receiver_id),
            new PrivateChannel('chat.' . $this->message->sender_id),
        ];

        if ($this->message->receiver_id != $this->message->sender_id) {
            $channels[] = new PrivateChannel('private-chat.' . $this->message->receiver_id);
            $channels[] = new PrivateChannel('private-chat.' . $this->message->sender_id);
        }

        return $channels;
    }

    // تحديد اسم الحدث المخصص للبث
    public function broadcastAs(): string
    {
        return 'MessageSent';
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

        $tempId = $this->message->temp_id 
            ?? $this->message->client_id 
            ?? $this->message->uuid 
            ?? $this->message->local_id 
            ?? $this->message->temporary_id 
            ?? $this->message->request_id 
            ?? null;

        $imageUrl = $this->message->image ? (str_starts_with($this->message->image, 'http') ? $this->message->image : asset('new_wiselook/uploads/' . basename($this->message->image))) : null;
        $videoUrl = $this->message->video ? (str_starts_with($this->message->video, 'http') ? $this->message->video : asset('new_wiselook/uploads/' . basename($this->message->video))) : null;
        $audioUrl = $this->message->audio ? (str_starts_with($this->message->audio, 'http') ? $this->message->audio : asset('new_wiselook/uploads/' . basename($this->message->audio))) : null;
        $primaryUrl = $imageUrl ?? $videoUrl ?? $audioUrl;

        return [
            'id' => $this->message->id,
            '_id' => (string)$this->message->id,
            'temp_id' => $tempId,
            'client_id' => $tempId,
            'uuid' => $tempId,
            'local_id' => $tempId,
            'temporary_id' => $tempId,
            'request_id' => $tempId,
            'message' => $this->message->message,
            'image' => $imageUrl ?? $this->message->image,
            'video' => $videoUrl ?? $this->message->video,
            'audio' => $audioUrl ?? $this->message->audio,
            'image_url' => $imageUrl,
            'video_url' => $videoUrl,
            'audio_url' => $audioUrl,
            'uri' => $primaryUrl,
            'url' => $primaryUrl,
            'file' => $primaryUrl,
            'path' => $primaryUrl,
            'media' => $primaryUrl,
            'status' => 'sent',
            'is_sent' => true,
            'sender_id' => (int)$this->message->sender_id,
            'receiver_id' => (int)$this->message->receiver_id,
            'user_id' => (int)$this->message->sender_id,
            'recipient_id' => (int)$this->message->receiver_id,
            'to_id' => (int)$this->message->receiver_id,
            'from_id' => (int)$this->message->sender_id,
            'sender_name' => $senderName,
            'sender_avatar' => $senderAvatar,
            'created_at' => $this->message->created_at ? $this->message->created_at->toIso8601String() : null,
            'timestamp' => $this->message->created_at ? $this->message->created_at->timestamp : null,
            'createdAt' => $this->message->created_at ? $this->message->created_at->timestamp * 1000 : null,
            'parent' => $parentData,
        ];
    }
}