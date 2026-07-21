<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Hashtag;

#[Fillable([
    'hashtag_id',
    'content_id',
    'content_type_id'
])]
class HashtagLink extends Model
{
    use HasFactory;

    protected $table = 'hashtag_links';

    public function hashtag(): BelongsTo
    {
        return $this->belongsTo(Hashtag::class, 'hashtag_id');
    }
}