<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_code',
        'phone_number',
        'verification_id',
        'otp_code',
        'flow_type',
        'status',
        'ip_address',
        'attempts',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Scope query to active pending verification requests for a phone number.
     */
    public function scopePending($query, string $countryCode, string $phoneNumber)
    {
        return $query->where('country_code', $countryCode)
            ->where('phone_number', $phoneNumber)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now());
    }
}
