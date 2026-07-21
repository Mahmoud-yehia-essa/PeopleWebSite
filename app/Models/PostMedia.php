<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // تم الاستدعاء لدعم الحذف المؤقت
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;

#[Fillable([
    'post_id',
    'image',      // الحقل الأصلي
    'video',      // الحقل الأصلي
    'caption',    // الحقل الأصلي
    'is_active',  // الحقل الأصلي
    'position'
])]
class PostMedia extends Model
{
    use HasFactory, SoftDeletes; // تفعيل السوفت ديليت للوسائط

    protected $table = 'post_media';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}