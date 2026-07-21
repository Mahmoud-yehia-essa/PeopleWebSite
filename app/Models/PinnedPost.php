<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Post;

#[Fillable(['post_id', 'pin_scope', 'pin_order', 'pinned_at'])]
class PinnedPost extends Model
{
    use HasFactory;

    protected $table = 'pinned_posts';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    protected function casts(): array
    {
        return [
            'pinned_at' => 'datetime',
        ];
    }
}