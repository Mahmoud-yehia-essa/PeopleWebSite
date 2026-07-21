<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = 'translations';

    protected $fillable = [
        'language_id',
        'key',
        'value'
    ];

    /**
     * اللغة التي تنتمي إليها الترجمة
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
