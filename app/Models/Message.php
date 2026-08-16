<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;


class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'group_id', 'message', 'image', 'video', 'audio', 'parent_id', 'is_read'];

    protected $appends = ['image_url', 'video_url', 'audio_url', 'uri', 'url', 'file', 'path', 'media', 'status', 'is_sent', 'createdAtMs'];

    public function getImageUrlAttribute()
    {
        if (empty($this->attributes['image'])) {
            return null;
        }
        $val = $this->attributes['image'];
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }
        return asset('new_wiselook/uploads/' . basename($val));
    }

    public function getVideoUrlAttribute()
    {
        if (empty($this->attributes['video'])) {
            return null;
        }
        $val = $this->attributes['video'];
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }
        return asset('new_wiselook/uploads/' . basename($val));
    }

    public function getAudioUrlAttribute()
    {
        if (empty($this->attributes['audio'])) {
            return null;
        }
        $val = $this->attributes['audio'];
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://')) {
            return $val;
        }
        return asset('new_wiselook/uploads/' . basename($val));
    }

    public function getUriAttribute()
    {
        return $this->getImageUrlAttribute() ?? $this->getVideoUrlAttribute() ?? $this->getAudioUrlAttribute();
    }

    public function getUrlAttribute()
    {
        return $this->getUriAttribute();
    }

    public function getFileAttribute()
    {
        return $this->getUriAttribute();
    }

    public function getPathAttribute()
    {
        return $this->getUriAttribute();
    }

    public function getMediaAttribute()
    {
        return $this->getUriAttribute();
    }

    public function getStatusAttribute()
    {
        return 'sent';
    }

    public function getIsSentAttribute()
    {
        return true;
    }

    public function getCreatedAtMsAttribute()
    {
        return $this->created_at ? $this->created_at->timestamp * 1000 : null;
    }

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
