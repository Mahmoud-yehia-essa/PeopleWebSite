<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    protected $table = 'post_views';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'viewer_key',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
