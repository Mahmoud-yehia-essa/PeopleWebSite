<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'name',
        'code',
        'flag_path',
        'direction',
        'is_default',
        'is_active'
    ];

    /**
     * الترجمات الخاصة باللغة
     */
    public function translations()
    {
        return $this->hasMany(Translation::class, 'language_id');
    }
}
