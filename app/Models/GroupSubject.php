<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Hashtag;
use App\Models\HashtagLink;

class GroupSubject extends Model
{
    protected $table = 'group_subjects';

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($subject) {
            HashtagLink::where('content_id', $subject->id)
                ->where('content_type_id', 5) // 5 = GroupSubject
                ->delete();
        });
    }

    /**
     * الهاشتاجات المرتبطة بالموضوع
     */
    public function hashtagLinks()
    {
        return $this->hasMany(HashtagLink::class, 'content_id')->where('content_type_id', 5);
    }

    /**
     * استخراج ومزامنة الهاشتاجات الخاصة بالموضوع
     */
    public function syncHashtags()
    {
        // 1. استخراج الهاشتاجات من الوصف (يدعم الحروف العربية والإنجليزية والأرقام والشرطة السفلية)
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $this->description, $matches);
        $hashtagNames = array_unique($matches[1] ?? []);

        // 2. حذف الارتباطات القديمة لهذا الموضوع
        HashtagLink::where('content_id', $this->id)
            ->where('content_type_id', 5)
            ->delete();

        // 3. حفظ الهاشتاجات الجديدة والربط
        foreach ($hashtagNames as $name) {
            $name = trim($name);
            if (empty($name)) {
                continue;
            }
            
            $hashtag = Hashtag::firstOrCreate(['name' => $name]);

            HashtagLink::create([
                'hashtag_id' => $hashtag->id,
                'content_id' => $this->id,
                'content_type_id' => 5
            ]);
        }
    }

    /**
     * ترميز النص بأمان وتحويل الهاشتاجات إلى روابط نشطة
     */
    public static function formatHashtags($text)
    {
        if (empty($text)) {
            return $text;
        }

        $safeText = e($text);
        
        return preg_replace_callback(
            '/#([\p{L}\p{N}_]+)/u',
            function ($matches) {
                $hashtagName = $matches[1];
                $url = route('frontend.hashtags.show', $hashtagName);
                return '<a href="' . $url . '" class="text-primary hover:underline font-bold" data-hashtag="' . e($hashtagName) . '">#' . e($hashtagName) . '</a>';
            },
            $safeText
        );
    }

    protected $fillable = [
        'user_id',
        'group_site_id',
        'title',
        'description',
        'likes',
        'dislikes',
        'attachment_type',
        'attachment_path',
        'is_pinned',
        'pin_order',
        'pinned_at'
    ];

    protected function casts(): array
    {
        return [
            'is_pinned' => 'boolean',
            'pin_order' => 'integer',
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * ترتيب المواضيع بحيث تظهر المثبتة أولاً حسب الترتيب ثم الأحدث
     */
    public function scopeOrderedWithPinned($query)
    {
        return $query->orderByDesc('is_pinned')
            ->orderBy('pin_order', 'asc')
            ->orderByDesc('pinned_at')
            ->latest();
    }

    /**
     * كاتب الموضوع
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * المجموعة التابع لها الموضوع
     */
    public function groupSite()
    {
        return $this->belongsTo(GroupSite::class, 'group_site_id');
    }

    /**
     * تعليقات الموضوع
     */
    public function comments()
    {
        return $this->hasMany(GroupSiteComment::class, 'group_subject_id');
    }

    /**
     * تفاعلات الموضوع (likes/dislikes)
     */
    public function reactions()
    {
        return $this->hasMany(GroupSiteSubjectReaction::class, 'group_subject_id');
    }
}
