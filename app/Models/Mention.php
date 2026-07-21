<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

#[Fillable(['content_type_id', 'content_id', 'user_id'])]
class Mention extends Model
{
    use HasFactory;

    protected $table = 'mentions';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}