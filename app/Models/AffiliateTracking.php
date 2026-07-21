<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTracking extends Model
{
    protected $table = 'affiliate_trackings';

    protected $fillable = [
        'affiliate_link_id',
        'registered_user_id',
        'ip_address'
    ];

    /**
     * رابط التسويق بالعمولة التابع له
     */
    public function link()
    {
        return $this->belongsTo(AffiliateLink::class, 'affiliate_link_id');
    }

    /**
     * المستخدم الجديد الذي سجل من خلال هذا الرابط
     */
    public function registeredUser()
    {
        return $this->belongsTo(User::class, 'registered_user_id');
    }
}
