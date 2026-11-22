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
        'is_pinned',
        'pin_order',
        'pinned_at',
        'invite_code',
        'admin_user_id'
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'pin_order' => 'integer',
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * ترتيب المجموعات بحيث تظهر المثبتة أولاً حسب الترتيب ثم الأحدث
     */
    public function scopeOrderedWithPinned($query)
    {
        return $query->orderByDesc('is_pinned')
            ->orderBy('pin_order', 'asc')
            ->orderByDesc('pinned_at')
            ->latest();
    }

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
