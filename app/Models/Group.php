<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\GroupMember;
use App\Models\Message;

#[Fillable([
    "name",
    "image",
    "descriptions",
    "created_by_user_id",
    "date_created",
    "member_count",
    "pinned_message_id",
    "pinned_by_user_id",
    "pinned_at",
    "pinned_until"
])]
class Group extends Model
{
    use HasFactory;

    const CREATED_AT = "date_created";
    const UPDATED_AT = "updated_at";

    protected $table = "groups";

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, "created_by_user_id");
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, "group_id");
    }

    public function pinnedMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, "pinned_message_id");
    }
}
