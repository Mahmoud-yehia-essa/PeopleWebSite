<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupSiteSubjectReaction extends Model
{
    protected $table = 'group_site_subject_reactions';

    protected $fillable = [
        'group_subject_id',
        'user_id',
        'type'
    ];

    /**
     * صاحب التفاعل
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
