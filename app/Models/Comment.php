<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;
use App\Models\User;

#[Fillable([
    'post_id',
    'user_id',
    'content',
    'is_active',
    'reaction_count', // تم إضافته للـ Fillable
    'reply_count',    // تم إضافته للـ Fillable
    'parent_id'       // تم إضافته للـ Fillable
])]
class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comments';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الردود الفرعية التابعة للتعليق
     */
    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->where('is_active', 1);
    }
}