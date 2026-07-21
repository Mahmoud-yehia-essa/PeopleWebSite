<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSiteComment extends Model
{
    protected $table = 'group_site_comments';

    protected $fillable = [
        'group_subject_id',
        'user_id',
        'parent_id',
        'content',
        'attachment_type',
        'attachment_path',
        'reaction_count',
        'reply_count'
    ];

    /**
     * الردود المتداخلة
     */
    public function replies()
    {
        return $this->hasMany(GroupSiteComment::class, 'parent_id')->latest();
    }

    /**
     * التعليق الأب
     */
    public function parent()
    {
        return $this->belongsTo(GroupSiteComment::class, 'parent_id');
    }

    /**
     * كاتب التعليق
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * الموضوع الرئيسي
     */
    public function subject()
    {
        return $this->belongsTo(GroupSubject::class, 'group_subject_id');
    }
}
