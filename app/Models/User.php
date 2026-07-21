<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Message;


#[Fillable([
    'email',
    'password',
    'password_hash',
    'phone_number',
    'first_name',
    'last_name',
    'profile_picture',
    'cover_picture',
    'birth_date',
    'gender',
    'address',
    'bio',
    'post_count',
    'friend_count',
    'reset_code',
    'last_login',
    'is_active',
    'token',
    'status',
    'is_verified',
    'country_flag',
    'role',
    'provider',
    'points',
    'google_id',
    'facebook_id'
])]
#[Hidden([
    'password', 
    'password_hash', 
    'token', 
    'reset_code', 
    'remember_token'
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    
    protected $appends = ['avatar_url', 'name'];

    /**
     * تحديد الحقول التي سيتم التعامل معها كتواريخ تلقائياً
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor for photo attribute, mapping to profile_picture.
     */
    public function getPhotoAttribute()
    {
        return $this->profile_picture;
    }

    /**
     * Accessor for fname attribute, mapping to first_name.
     */
    public function getFnameAttribute()
    {
        return $this->first_name;
    }

    /**
     * Accessor for lname attribute, mapping to last_name.
     */
    public function getLnameAttribute()
    {
        return $this->last_name;
    }

    /**
     * Accessor for name attribute, combining first_name and last_name.
     */
    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Accessor for avatar_url attribute, retrieving complete avatar URL.
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->profile_picture && $this->profile_picture !== 'non') {
            return filter_var($this->profile_picture, FILTER_VALIDATE_URL)
                ? $this->profile_picture
                : asset('new_wiselook/uploads/' . basename($this->profile_picture));
        }
        return url('upload/no_image.jpg');
    }

    /**
     * Accessor for phone attribute, mapping to phone_number (without country prefix).
     */
    public function getPhoneAttribute()
    {
        $phone = $this->phone_number;
        foreach (['+965', '+966', '+971', '+974', '+20'] as $prefix) {
            if (str_starts_with($phone, $prefix)) {
                return substr($phone, strlen($prefix));
            }
        }
        return $phone;
    }

    /**
     * Accessor for country_code attribute, extracting country prefix from phone_number.
     */
    public function getCountryCodeAttribute()
    {
        $phone = $this->phone_number;
        if (!$phone) {
            return '';
        }
        foreach (['+965', '+966', '+971', '+974', '+20'] as $prefix) {
            if (str_starts_with($phone, $prefix)) {
                return $prefix;
            }
        }
        return '';
    }

    /**
     * Accessor for role. Returns the role column or defaults to 'user'.
     */
    public function getRoleAttribute($value)
    {
        return $value ?: 'user';
    }

    /**
     * Mutator for role. Writes to the role column and syncs is_verified for compatibility.
     */
    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;

        if ($value === 'admin') {
            $this->attributes['is_verified'] = 2;
        } elseif ($value === 'owner') {
            $this->attributes['is_verified'] = 3;
        } else {
            $this->attributes['is_verified'] = 1;
        }
    }

    /**
     * Mutator for is_verified. Writes to is_verified and syncs role for compatibility.
     */
    public function setIsVerifiedAttribute($value)
    {
        $this->attributes['is_verified'] = $value;

        if ($value == 2) {
            $this->attributes['role'] = 'admin';
        } elseif ($value == 3) {
            $this->attributes['role'] = 'owner';
        } else {
            $this->attributes['role'] = 'user';
        }
    }

    /**
     * Accessor for provider attribute.
     * Maps to normal, google, apple, or phone dynamically.
     */
    public function getProviderAttribute($value)
    {
        if (empty($value) || $value === 'normal') {
            if (empty($this->email) && !empty($this->phone_number)) {
                return 'phone';
            }
            return 'normal';
        }
        return $value;
    }

    /**
     * Accessor for status attribute.
     * Converts integer database status to string representation for views.
     */
    public function getStatusAttribute($value)
    {
        return $value == 1 ? 'active' : 'inactive';
    }

    /**
     * مواضيع المستخدم
     */
    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    /**
     * سجلات النقاط التي حصل عليها العضو من لجنة الحكماء
     */
    public function wisePointLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WisePointLog::class, 'recipient_user_id');
    }

    /**
     * سجلات النقاط التي منحها العضو (كحكيم) للآخرين
     */
    public function givenPointLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WisePointLog::class, 'wise_user_id');
    }

    /**
     * الحصول على الرتبة الحالية للمستخدم بناءً على نقاطه مع التخزين المؤقت في الذاكرة لتفادي استعلامات N+1
     */
    public function getRankAttribute()
    {
        static $allRankings = null;
        if ($allRankings === null) {
            $allRankings = \App\Models\Ranking::orderBy('rank_order', 'asc')->get();
        }

        if ($allRankings->isEmpty()) {
            return null;
        }

        $userPoints = $this->points ?? 0;

        $matchedRank = $allRankings->first(function ($rank) use ($userPoints) {
            if ($rank->is_last) {
                return $userPoints >= $rank->rank_start_point;
            }
            return $userPoints >= $rank->rank_start_point && $userPoints <= $rank->rank_end_point;
        });

        // إذا لم يعثر على رتبة مطابقة (مثلاً نقاط العضو 0 أو أقل من البداية)، نرجع الرتبة الأولى بالسيستم تلقائياً كبداية
        return $matchedRank ?: $allRankings->first();
    }

    /**
     * الحصول على الرتبة التالية للمستخدم
     */
    public function getNextRankAttribute()
    {
        $currentRank = $this->rank;
        if (!$currentRank) {
            return null;
        }

        static $allRankings = null;
        if ($allRankings === null) {
            $allRankings = \App\Models\Ranking::orderBy('rank_order', 'asc')->get();
        }

        return $allRankings->first(function ($rank) use ($currentRank) {
            return $rank->rank_order > $currentRank->rank_order;
        });
    }

    public function sentMessages()
{
    return $this->hasMany(Message::class, 'sender_id');
}

public function receivedMessages()
{
    return $this->hasMany(Message::class, 'receiver_id');
}
}