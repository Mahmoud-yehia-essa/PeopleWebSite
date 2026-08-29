<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ChatPoll;
use App\Models\ChatPollOption;
use App\Models\User;

class ChatPollVote extends Model
{
    use HasFactory;

    protected $table = 'chat_poll_votes';

    protected $fillable = [
        'chat_poll_id',
        'chat_poll_option_id',
        'user_id'
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(ChatPoll::class, 'chat_poll_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ChatPollOption::class, 'chat_poll_option_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
