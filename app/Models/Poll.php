<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Post;
use App\Models\PollOption;

#[Fillable([
    'post_id',
    'question',
    'total_votes',
    'is_multiple_choice',
    'expires_at'
])]
class Poll extends Model
{
    use HasFactory;

    protected $table = 'polls';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class, 'poll_id');
    }

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}