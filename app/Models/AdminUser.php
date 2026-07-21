<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'email',
    'username',
    'password',
    'reset_code'
])]
#[Hidden([
    'password',
    'reset_code'
])]
class AdminUser extends Model
{
    use HasFactory;

    protected $table = 'admin_users';

    /**
     * عمل تشفير تلقائي لكلمة المرور عند حفظها
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}