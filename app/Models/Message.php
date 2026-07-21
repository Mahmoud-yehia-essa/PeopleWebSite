<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;


class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'group_id', 'message', 'image', 'video', 'audio', 'parent_id', 'is_read'];

    // علاقة الرسالة بالمجموعة (إذا كانت رسالة جماعية)
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    // علاقة الرسالة بالمرسل
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // علاقة الرسالة بالمستقبل
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // العلاقة بالرسالة الأب (التي يتم الرد عليها)
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }
}
