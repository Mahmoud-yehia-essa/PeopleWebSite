<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\StorySeen;

#[Fillable([
    'user_id',
    'content',
    'image',
    'video',
    'view_count',
    'expires_at',
    'is_active'
])]
class Story extends Model
{
    use HasFactory;

    // الجدول يحتوي فقط على created_at ولا يحتوي على updated_at
    public const UPDATED_AT = null;

    /**
     * القصة تنتمي إلى المستخدم الذي قام بنشرها
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * القصة الواحدة تمتلك العديد من المشاهدات من قبل المستخدمين الآخرين
     */
    public function views(): HasMany
    {
        return $this->hasMany(StorySeen::class, 'story_id');
    }

    /**
     * تحويل الحقول إلى تواريخ تلقائياً
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}