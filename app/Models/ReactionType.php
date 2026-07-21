<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type',
    'is_active'
])]
class ReactionType extends Model
{
    use HasFactory;

    // تحديد اسم الجدول في قاعدة البيانات لأن لارافيل يبحث افتراضياً عن الجمع (reaction_types)
    protected $table = 'reaction_type';

    /**
     * النوع الواحد ينتمي إليه العديد من تفاعلات المستخدمين
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'reaction_type_id');
    }
}