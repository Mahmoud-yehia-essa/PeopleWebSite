<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WisePointLog extends Model
{
    protected $table = 'wise_point_logs';

    protected $fillable = [
        'wise_user_id',
        'recipient_user_id',
        'post_id',
        'points_given',
        'note'
    ];

    /**
     * الحكيم الذي قام بمنح النقاط
     */
    public function wiseUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wise_user_id');
    }

    /**
     * العضو (المستخدم) الذي استلم النقاط
     */
    public function recipientUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * المنشور المرتبط بعملية التقييم
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}

