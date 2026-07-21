<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateLink extends Model
{
    protected $table = 'affiliate_links';

    protected $fillable = [
        'user_id',
        'code',
        'clicks',
        'is_active'
    ];

    /**
     * المستخدم (المسوق) صاحب الرابط
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * عمليات التسجيل التابعة للرابط
     */
    public function trackings()
    {
        return $this->hasMany(AffiliateTracking::class, 'affiliate_link_id');
    }

    /**
     * توليد كود فريد تلقائياً بناء على اسم المستخدم ورقم عشوائي
     */
    public static function generateUniqueCode($user)
    {
        $base = strtolower(trim($user->first_name . $user->last_name));
        // إزالة أي رموز غير أحرف وأرقام إنجليزية
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        if (empty($base)) {
            $base = 'ref';
        }
        
        $code = $base . rand(10, 99);
        while (self::where('code', $code)->exists()) {
            $code = $base . rand(100, 999);
        }
        return $code;
    }
}
