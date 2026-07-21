<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSiteUser extends Model
{
    protected $table = 'group_site_users';

    protected $fillable = [
        'user_id',
        'group_site_id'
    ];

    /**
     * المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * المجموعة الخاصة/العامة
     */
    public function groupSite()
    {
        return $this->belongsTo(GroupSite::class, 'group_site_id');
    }
}
