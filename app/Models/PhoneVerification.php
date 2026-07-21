<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'verification_id',
    'phone_number',
    'otp_code',
    'expires_at',
    'used',
    'verified',
    'verified_at'
])]
class PhoneVerification extends Model
{
    use HasFactory;

    protected $table = 'phone_verifications';

    /**
     * كاستنج التواريخ لسهولة التعامل البرمجي داخل الـ Controller
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}