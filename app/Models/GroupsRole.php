<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\GroupMember;

#[Fillable(['name'])]
class GroupsRole extends Model
{
    use HasFactory;

    protected $table = 'groups_role';

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'role_id');
    }
}