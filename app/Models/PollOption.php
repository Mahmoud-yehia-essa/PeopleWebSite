<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Poll;
use App\Models\PollResponse;

#[Fillable([
    'poll_id',
    'content',
    'vote_count'
])]
class PollOption extends Model
{
    use HasFactory;

    protected $table = 'poll_options';

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class, 'poll_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class, 'poll_option_id');
    }
}