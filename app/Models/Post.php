<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PostMedia;
use App\Models\Hashtag;
use App\Models\HashtagLink;

#[Fillable([
    'user_id',
    'content',
    'image',
    'video',
    'privacy_level_id',
    'like_count',
    'comment_count',
    'share_count',
    'is_active',
    'parent_id',
    'post_type_id',
    'wise_rating'
])]
class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($post) {
            HashtagLink::where('content_id', $post->id)
                ->where('content_type_id', 1) // 1 = Post
                ->delete();
        });

        static::created(function ($post) {
            if ($post->content) {
                preg_match_all('/@\[([^\]]+)\]/u', $post->content, $matches);
                if (!empty($matches[1])) {
                    $mentionedNames = array_unique($matches[1]);
                    $sender = $post->user;
                    if ($sender) {
                        foreach ($mentionedNames as $name) {
                            $user = \App\Models\User::where(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), $name)->first();
                            if ($user && $user->id !== $sender->id) {
                                $snippet = \Illuminate\Support\Str::limit(strip_tags($post->content), 35);
                                $message = 'قام ' . $sender->first_name . ' ' . $sender->last_name . ' بالإشارة إليك في موضوع: "' . $snippet . '"';

                                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                                    'type' => 'App\Notifications\GeneralNotification',
                                    'notifiable_type' => 'App\Models\User',
                                    'notifiable_id' => $user->id,
                                    'data' => json_encode([
                                        'type' => 'mention',
                                        'sender_id' => $sender->id,
                                        'sender_name' => $sender->first_name . ' ' . $sender->last_name,
                                        'avatar' => $sender->profile_picture,
                                        'message' => $message,
                                        'post_id' => (int)$post->id
                                    ]),
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }
        });
    }

    /**
     * الهاشتاجات المرتبطة بالمنشور
     */
    public function hashtagLinks()
    {
        return $this->hasMany(HashtagLink::class, 'content_id')->where('content_type_id', 1);
    }

    /**
     * استخراج ومزامنة الهاشتاجات الخاصة بالمنشور
     */
    public function syncHashtags()
    {
        if (empty($this->content)) {
            return;
        }

        // 1. استخراج الهاشتاجات من المحتوى (يدعم الحروف العربية والإنجليزية والأرقام والشرطة السفلية)
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $this->content, $matches);
        $hashtagNames = array_unique($matches[1] ?? []);

        // 2. حذف الارتباطات القديمة لهذا المنشور
        HashtagLink::where('content_id', $this->id)
            ->where('content_type_id', 1)
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
                'content_type_id' => 1
            ]);
        }
    }

    /**
     * تنسيق محتوى المنشور بترميز آمن مع تحويل المنشورات المشيرة بالـ @ والهاشتاجات إلى روابط نشطة
     */
    public static function formatContent($text)
    {
        if (empty($text)) {
            return $text;
        }

        // 1. ترميز النص لحمايته من ثغرات XSS
        $safeText = e($text);

        // 2. تحويل الإشارات للمستخدمين @[اسم المستخدم] إلى روابط نشطة
        $safeText = preg_replace_callback('/@\[([^\]]+)\]/u', function($matches) {
            $name = $matches[1];
            $user = \App\Models\User::where(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), $name)->first();
            if ($user) {
                return '<a href="' . url('/profile/' . $user->id) . '" style="color: rgb(37, 99, 235); font-weight: 700; text-decoration: underline; direction: ltr; display: inline-block;">@' . htmlspecialchars($name) . '</a>';
            }
            return '<span style="color: rgb(37, 99, 235); font-weight: 700; text-decoration: underline; direction: ltr; display: inline-block;">@' . htmlspecialchars($name) . '</span>';
        }, $safeText);

        // 3. تحويل الهاشتاجات #الاسم إلى روابط نشطة
        $safeText = preg_replace_callback(
            '/#([\p{L}\p{N}_]+)/u',
            function ($matches) {
                $hashtagName = $matches[1];
                $url = route('frontend.hashtags.show', $hashtagName);
                return '<a href="' . $url . '" class="text-primary hover:underline font-bold" data-hashtag="' . e($hashtagName) . '">#' . e($hashtagName) . '</a>';
            },
            $safeText
        );

        return $safeText;
    }

    /**
     * علاقة المنشور بالمستخدم: كل منشور ينتمي إلى مستخدم واحد
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class, 'post_id');
    }

    /**
     * علاقة المنشور بالاستطلاع (Poll)
     */
    public function poll(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Poll::class, 'post_id');
    }

    /**
     * تفاعلات المنشور
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'content_id')->where('content_type_id', 1);
    }

    /**
     * تثبيت المنشور
     */
    public function pin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PinnedPost::class, 'post_id');
    }

    /**
     * تقييمات الحكماء لهذا المنشور
     */
    public function wiseRatings(): HasMany
    {
        return $this->hasMany(WiseSubjectRating::class, 'post_id');
    }

    /**
     * تعليقات المنشور الأساسية (التي ليست ردوداً)
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id')->where('parent_id', 0)->where('is_active', 1);
    }
}