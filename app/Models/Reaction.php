<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\ReactionType;
use App\Models\Post;

#[Fillable([
    'user_id',
    'content_id',
    'content_type_id',
    'reaction_type_id',
    'is_active'
])]
class Reaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * التفاعل ينتمي إلى مستخدم محدد
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * التفاعل ينتمي إلى نوع تفاعل محدد (مثل Like)
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ReactionType::class, 'reaction_type_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'content_id');
    }


}