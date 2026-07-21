<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Story;
use App\Models\User;

#[Fillable([
    'story_id',
    'user_id',
    'viewed_at',
    'is_active'
])]
class StorySeen extends Model
{
    use HasFactory;

    // تحديد اسم الجدول المخصص في قاعدة البيانات
    protected $table = 'story_seen';

    /**
     * المشاهدة تنتمي لقصة محددة
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id');
    }

    /**
     * المشاهدة تنتمي للمستخدم الذي شاهد القصة
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * تحويل الحقل لتاريخ تلقائياً
     */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}