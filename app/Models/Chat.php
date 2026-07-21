<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

#[Fillable([
    'sender_id',
    'receiver_id',
    'message',
    'media',
    'delivered_at',
    'is_active',
    'is_viewed'
])]
class Chat extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * الرسالة تنتمي إلى المستخدم الذي قام بإرسالها
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * الرسالة تنتمي إلى المستخدم الذي سوف يستقبلها
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}