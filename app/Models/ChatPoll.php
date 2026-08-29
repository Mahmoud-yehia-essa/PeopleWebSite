<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Message;
use App\Models\Group;
use App\Models\User;
use App\Models\ChatPollOption;
use App\Models\ChatPollVote;

class ChatPoll extends Model
{
    use HasFactory;

    protected $table = 'chat_polls';

    protected $fillable = [
        'message_id',
        'group_id',
        'user_id',
        'question',
        'is_multiple_choice',
        'total_votes',
        'expires_at'
    ];

    protected $casts = [
        'is_multiple_choice' => 'boolean',
        'total_votes' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ChatPollOption::class, 'chat_poll_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ChatPollVote::class, 'chat_poll_id');
    }
}
