<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ChatPoll;
use App\Models\ChatPollVote;

class ChatPollOption extends Model
{
    use HasFactory;

    protected $table = 'chat_poll_options';

    protected $fillable = [
        'chat_poll_id',
        'option_uid',
        'text',
        'vote_count'
    ];

    protected $casts = [
        'vote_count' => 'integer',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(ChatPoll::class, 'chat_poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChatPollVote::class, 'chat_poll_option_id');
    }
}
