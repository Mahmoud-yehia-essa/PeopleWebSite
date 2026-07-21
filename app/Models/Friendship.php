<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

#[Fillable([
    'sender_id',
    'receiver_id',
    'is_active'
])]
class Friendship extends Model
{
    use HasFactory;

    /**
     * العلاقة الأولى: الصداقة تنتمي إلى مُرسل الطلب
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * العلاقة الثانية: الصداقة تنتمي إلى مُستقبل الطلب
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}