<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppSetting extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_settings';

    protected $guarded = [];

    protected $casts = [
        'is_connected'      => 'boolean',
        'last_connected_at' => 'datetime',
    ];

    /**
     * Get or create singleton WhatsApp settings record
     */
    public static function getSettings(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'engine'             => 'local',
                'api_key'            => 'wl_wa_' . Str::random(36),
                'local_server_url'   => 'http://127.0.0.1:3001',
                'is_connected'       => false,
            ]);
        }

        return $settings;
    }
}
