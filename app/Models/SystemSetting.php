<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'is_website_active',
        'website_maintenance_message',
        'is_app_active',
        'app_maintenance_message',
    ];

    protected $casts = [
        'is_website_active' => 'boolean',
        'is_app_active'     => 'boolean',
    ];

    public const CACHE_KEY = 'system_settings_array_cache';

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('system_settings_cache');
        });

        static::deleted(function () {
            Cache::forget(self::CACHE_KEY);
            Cache::forget('system_settings_cache');
        });
    }

    /**
     * جلب إعدادات النظام الحالية بصيغة مصفوفة آمنة تماماً للتخزين المؤقت وتجنب أخطاء Incomplete Class
     */
    public static function getSettings()
    {
        try {
            $data = Cache::remember(self::CACHE_KEY, 3600, function () {
                $setting = static::first();
                if (!$setting) {
                    $setting = static::create([
                        'is_website_active'           => true,
                        'website_maintenance_message' => 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
                        'is_app_active'               => true,
                        'app_maintenance_message'     => 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
                    ]);
                }
                return [
                    'id'                          => $setting->id,
                    'is_website_active'           => (bool)$setting->is_website_active,
                    'website_maintenance_message' => $setting->website_maintenance_message ?: 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
                    'is_app_active'               => (bool)$setting->is_app_active,
                    'app_maintenance_message'     => $setting->app_maintenance_message ?: 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
                ];
            });

            if (is_array($data)) {
                return (object)$data;
            }

            if (is_object($data) && !($data instanceof \__PHP_Incomplete_Class)) {
                return $data;
            }

            // إذا كان هناك أي كائن غير مكتمل في الكاش، نفرغ الكاش ونعيد القيم الافتراضية
            Cache::forget(self::CACHE_KEY);
            Cache::forget('system_settings_cache');

            return (object)[
                'id'                          => 1,
                'is_website_active'           => true,
                'website_maintenance_message' => 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
                'is_app_active'               => true,
                'app_maintenance_message'     => 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
            ];
        } catch (\Throwable $e) {
            return (object)[
                'id'                          => 1,
                'is_website_active'           => true,
                'website_maintenance_message' => 'الموقع متوقف مؤقتاً لأعمال الصيانة والتطوير، وسنعود للعمل قريباً.',
                'is_app_active'               => true,
                'app_maintenance_message'     => 'التطبيق متوقف مؤقتاً لأعمال الصيانة والتطوير، يرجى المحاولة لاحقاً.',
            ];
        }
    }

    /**
     * التحقق من حالة تشغيل الموقع الإلكتروني
     */
    public static function isWebsiteActive(): bool
    {
        $settings = static::getSettings();
        return (bool)($settings->is_website_active ?? true);
    }

    /**
     * التحقق من حالة تشغيل التطبيق
     */
    public static function isAppActive(): bool
    {
        $settings = static::getSettings();
        return (bool)($settings->is_app_active ?? true);
    }

    /**
     * مسح الكاش
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('system_settings_cache');
    }
}
