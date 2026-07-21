<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WiseCommittee extends Model
{
    protected $table = 'wise_committees';

    protected $fillable = [
        'user_id',
        'specialty',
        'bio',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * الحكيم كعضو مستخدم في الأصل
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
