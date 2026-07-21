<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSite extends Model
{
    protected $table = 'group_sites';

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'logo_path',
        'status',
        'invite_code',
        'admin_user_id'
    ];

    /**
     * مشرف المجموعة
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * أعضاء المجموعة
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'group_site_users', 'group_site_id', 'user_id')->withTimestamps();
    }

    /**
     * مواضيع المجموعة
     */
    public function subjects()
    {
        return $this->hasMany(GroupSubject::class, 'group_site_id');
    }
}
