<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HashtagLink;

#[Fillable([
    'name'
])]
class Hashtag extends Model
{
    use HasFactory;

    protected $table = 'hashtags';

    public function links(): HasMany
    {
        return $this->hasMany(HashtagLink::class, 'hashtag_id');
    }
}