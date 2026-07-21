<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WiseSubjectRating extends Model
{
    protected $table = 'wise_subject_ratings';

    protected $fillable = [
        'user_id',
        'post_id',
        'rating',
        'reason'
    ];

    /**
     * الحكيم الذي قام بالتقييم
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الموضوع أو المنشور الذي تم تقييمه
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
