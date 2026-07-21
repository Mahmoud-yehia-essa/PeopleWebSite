<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'phone_number',
    'code',
    'expires_at',
    'used'
])]
class OtpCode extends Model
{
    use HasFactory;

    // تحديد اسم الجدول الصريح في قاعدة البيانات
    protected $table = 'otp_codes';

    /**
     * تحويل الحقل إلى تاريخ تلقائياً لسهولة المقارنة والتحقق من الصلاحية
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}