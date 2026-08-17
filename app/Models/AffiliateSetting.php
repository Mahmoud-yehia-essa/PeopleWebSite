<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateSetting extends Model
{
    protected $table = 'affiliate_settings';

    protected $fillable = [
        'reward_points_per_referral',
        'min_points_silver_rank',
        'min_points_gold_rank',
        'is_affiliate_enabled'
    ];

    /**
     * جلب إعدادات الأفيليت أو إنشاء السجل الافتراضي إن لم يكن موجوداً
     */
    public static function getSettings()
    {
        try {
            $setting = static::first();
            if (!$setting) {
                $setting = static::create([
                    'reward_points_per_referral' => 50,
                    'min_points_silver_rank' => 5,
                    'min_points_gold_rank' => 20,
                    'is_affiliate_enabled' => true,
                ]);
            }
            return $setting;
        } catch (\Exception $e) {
            return (object)[
                'reward_points_per_referral' => 50,
                'min_points_silver_rank' => 5,
                'min_points_gold_rank' => 20,
                'is_affiliate_enabled' => true,
            ];
        }
    }
}
