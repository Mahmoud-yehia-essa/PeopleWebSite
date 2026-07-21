<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ranking extends Model
{
    protected $table = 'rankings';

    protected $fillable = [
        'rank_name',
        'rank_description',
        'rank_order',
        'rank_start_point',
        'rank_end_point',
        'level_reward_amount',
        'is_last',
        'photo'
    ];
}
