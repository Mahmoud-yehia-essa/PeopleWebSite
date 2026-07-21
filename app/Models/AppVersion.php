<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $table = 'app_versions';

    protected $fillable = [
        'version',
        'des',
        'android',
        'ios',
        'update_required',
        'contact'
    ];

    protected $casts = [
        'update_required' => 'boolean',
    ];
}
