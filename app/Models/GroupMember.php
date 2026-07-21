<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Group;
use App\Models\User;
use App\Models\GroupsRole;

#[Fillable([
    'group_id',
    'user_id',
    'role_id',
    'joined_at',
    'left_at',
    'added_by_user_id',
    'is_active'
])]
class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'group_member';

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(GroupsRole::class, 'role_id');
    }
}