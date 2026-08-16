<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\SavedPost;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Mention;
use App\Models\PinnedPost;
use App\Models\PollResponse;

class PostApiController extends Controller
{
    /**
     * 2.1 جلب قائمة المنشورات (الرئيسية، ملف شخصي، أو المحفوظات)
     */
    public function list(Request $request)
    {
        $currentUser = $request->user() ?? auth('sanctum')->user();
        $currentUserId = $currentUser ? $currentUser->id : 0;
        
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        
        // بناء الاستعلام الأساسي مع جلب علاقة الناشر والوسائط والاستطلاع والخيارات والتثبيت
        $query = Post::with(['user', 'media', 'poll.options', 'pin'])->where('is_active', 1);

        // 1. الفلترة حسب مستخدم معين (عرض بروفايل شخص آخر أو بروفايلي)
        if ($request->has('profile_id')) {
            $query->where('user_id', $request->profile_id);
        }

        // 2. جلب منشور واحد محدد عبر الـ id
        if ($request->has('post_id')) {
            $query->where('id', $request->post_id);
        }

        // 3. الترتيب والفلترة حسب الخيار المحدد (recent, most_interactive, most_liked, random, saved)
        $filter = $request->input('filter', 'recent');

        if ($filter === 'saved') {
            if (!$currentUserId) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $savedPostIds = SavedPost::where('user_id', $currentUserId)->pluck('post_id');
            $query->whereIn('id', $savedPostIds)->orderBy('created_at', 'desc');
        } elseif ($filter === 'most_interactive') {
            $query->orderByRaw('(like_count + comment_count + share_count) DESC')
                  ->orderBy('created_at', 'desc');
        } elseif ($filter === 'most_liked') {
            $query->orderBy('like_count', 'desc')
                  ->orderBy('created_at', 'desc');
        } elseif ($filter === 'random') {
            $query->inRandomOrder();
        } else {
            // الترتيب الأحدث مع تقديم المنشورات المثبتة حسب النطاق (home للرئيسية، profile للملف الشخصي)
            $pinScope = $request->has('profile_id') ? 'profile' : 'home';
            $query->orderByDesc(
                PinnedPost::selectRaw('1')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('pin_scope', $pinScope)
                    ->limit(1)
            )
            ->orderBy(
                PinnedPost::select('pin_order')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('pin_scope', $pinScope)
                    ->limit(1)
            )
            ->orderBy('created_at', 'desc');
        }

        // الأوفست والليميت
        $posts = $query->skip($offset)
                       ->take($limit)
                       ->get();

        // دالة مساعدة لتشكيل المنشور لتطابق تماما ما يتوقعه Flutter
        $mapPost = function ($post) use ($currentUser, $currentUserId, $request) {
            $isLiked = $currentUserId > 0 ? Reaction::where('user_id', $currentUserId)
                               ->where('content_id', $post->id)
                               ->where('content_type_id', 1)
                               ->where('is_active', 1)
                               ->exists() : false;

            $isSaved = $currentUserId > 0 ? SavedPost::where('user_id', $currentUserId)
                                ->where('post_id', $post->id)
                                ->exists() : false;

            $pinScope = $request->has('profile_id') ? 'profile' : 'home';
            if ($request->has('post_id')) {
                $isPinned = PinnedPost::where('post_id', $post->id)->exists();
            } else {
                $isPinned = PinnedPost::where('post_id', $post->id)
                                      ->where('pin_scope', $pinScope)
                                      ->exists();
            }

            // تنسيق الوسائط لتطابق MediaItemModel في Flutter
            $mediaArray = $post->media->map(function ($mediaItem) {
                $path = $mediaItem->image ? asset('storage/' . $mediaItem->image) : asset('storage/' . $mediaItem->video);
                $type = $mediaItem->image ? 'image' : 'video';
                return [
                    'path' => $path,
                    'type' => $type
                ];
            })->toArray();

            // إذا كان جدول post_media فارغاً، نتحقق من وجود صورة أو فيديو مباشرة على جدول posts (للمنشورات المرفوعة من الموقع)
            if (empty($mediaArray)) {
                if (!empty($post->image)) {
                    $mediaArray[] = [
                        'path' => asset('new_wiselook/uploads/' . $post->image),
                        'type' => 'image'
                    ];
                } elseif (!empty($post->video)) {
                    $mediaArray[] = [
                        'path' => asset('new_wiselook/uploads/' . $post->video),
                        'type' => 'video'
                    ];
                }
            }

            // معالجة الاستبيان
            $question = null;
            $expiresAt = null;
            $totalVotes = null;
            $options = [];
            $selectedOptionId = null;

            if ($post->poll) {
                $question = $post->poll->question;
                $expiresAt = $post->poll->expires_at ? $post->poll->expires_at->toDateTimeString() : null;
                $totalVotes = (int)$post->poll->total_votes;

                // جلب خيارات الاستبيان
                $options = $post->poll->options->map(function ($opt) use ($currentUser, $currentUserId, &$selectedOptionId) {
                    $isSelected = $currentUserId > 0 ? PollResponse::where('poll_option_id', $opt->id)
                                              ->where('user_id', $currentUserId)
                                              ->exists() : false;
                    if ($isSelected) {
                        $selectedOptionId = (int)$opt->id;
                    }
                    return [
                        'id' => (int)$opt->id,
                        'content' => $opt->content,
                        'vote_count' => (int)$opt->vote_count,
                        'is_selected' => $isSelected ? 1 : 0,
                        'recent_voters' => []
                    ];
                })->toArray();
            }

            // معالجة بيانات رتبة المستخدم ونقاطه وتقييم لجنة الحكماء
            $userRankName = null;
            $userRankIcon = null;
            $userPoints = 0;

            if ($post->user) {
                $userPoints = (int)($post->user->points ?? 0);
                $rank = $post->user->rank;
                if ($rank) {
                    $userRankName = $rank->rank_name;
                    if (!empty($rank->photo)) {
                        $userRankIcon = asset('upload/rankings/' . $rank->photo);
                    }
                } else {
                    $userRankName = ($post->user->role == 'admin') ? 'مدير المنصة' : 'مستشار تقني';
                }
            }

            $wiseRating = null;
            if ($post->wise_rating !== null && $post->wise_rating !== '') {
                $wiseRating = (float)$post->wise_rating;
            }

            return [
                'post_id'            => (int)$post->id,
                'user_id'            => (int)$post->user_id,
                'content'            => $post->content ?? '',
                'selected_option_id' => $selectedOptionId,
                'like_count'         => (int)$post->like_count,
                'comment_count'      => Comment::where('post_id', $post->id)->where('is_active', 1)->count(),
                'share_count'        => (int)$post->share_count,
                'first_name'         => $post->user->first_name ?? '',
                'last_name'          => $post->user->last_name ?? '',
                'time_ago'           => $post->created_at ? $post->created_at->diffForHumans() : '',
                'profile_picture'    => $post->user->profile_picture ?: asset('images/default_profile.png'),
                'post_type_id'       => $post->poll ? 2 : 1,
                'media'              => $mediaArray,
                'question'           => $question,
                'expires_at'         => $expiresAt,
                'options'            => $options,
                'is_reacted'         => $isLiked ? 1 : 0,
                'is_liked'           => $isLiked, 
                'is_pinned'          => $isPinned ? 1 : 0,
                'is_saved'           => $isSaved ? 1 : 0,
                'current_image_index'=> 0,
                'total_votes'        => $totalVotes,
                'parent_id'          => (int)$post->parent_id,
                'mentions'           => [],
                'user_rank_name'     => $userRankName,
                'user_rank_icon'     => $userRankIcon,
                'user_points'        => $userPoints,
                'wise_rating'        => $wiseRating
            ];
        };

        // إعادة تشكيل البيانات
        $formattedData = $posts->map(function ($post) use ($mapPost) {
            $mapped = $mapPost($post);
            
            // معالجة المنشور الأصلي إذا كان منشور مشارك
            if ($post->parent_id > 0) {
                $originalPost = Post::with(['user', 'media', 'poll.options'])->find($post->parent_id);
                if ($originalPost) {
                    $mapped['original_post'] = $mapPost($originalPost);
                }
            }
            return $mapped;
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }

    /**
     * 2.1.2 جلب المنشورات المحفوظة
     */
    public function listSaved(Request $request)
    {
        $request->merge(['filter' => 'saved']);
        return $this->list($request);
    }

    /**
     * 2.7 حفظ المنشور أو إلغاء حفظه
     */
    public function toggleSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        $postId = $request->post_id;

        $existing = SavedPost::where('user_id', $currentUser->id)
                             ->where('post_id', $postId)
                             ->first();

        if ($existing) {
            $existing->delete();
            $message = 'Post unsaved successfully';
        } else {
            SavedPost::create([
                'user_id' => $currentUser->id,
                'post_id' => $postId
            ]);
            $message = 'Post saved successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * 2.8 تثبيت المنشور أو إلغاء تثبيته
     */
    public function togglePin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id',
            'pin_scope' => 'nullable|string|in:profile,home'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $postId = $request->post_id;
        $scope = $request->input('pin_scope', 'profile');

        $existing = PinnedPost::where('post_id', $postId)
                              ->where('pin_scope', $scope)
                              ->first();

        if ($existing) {
            $existing->delete();
            $isPinned = false;
            $message = 'Post unpinned successfully';
        } else {
            PinnedPost::create([
                'post_id' => $postId,
                'pin_scope' => $scope,
                'pinned_at' => now()
            ]);
            $isPinned = true;
            $message = 'Post pinned successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'isPinned' => $isPinned,
                'pin_scope' => $scope
            ]
        ]);
    }

    /**
     * 2.9 التصويت في الاستبيان
     */
    public function vote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'option_id' => 'required|integer|exists:poll_options,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        $optionId = $request->option_id;

        $option = PollOption::with('poll')->find($optionId);
        $poll = $option->poll;

        if ($poll->expires_at && $poll->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'Poll has expired'], 400);
        }

        $pollOptionIds = PollOption::where('poll_id', $poll->id)->pluck('id')->toArray();
        $existingVote = PollResponse::where('user_id', $currentUser->id)
            ->whereIn('poll_option_id', $pollOptionIds)
            ->first();

        if ($existingVote) {
            if ($existingVote->poll_option_id == $optionId) {
                $existingVote->delete();
                $option->decrement('vote_count');
                $poll->decrement('total_votes');
                return response()->json(['success' => true, 'message' => 'Vote removed']);
            } else {
                PollOption::where('id', $existingVote->poll_option_id)->decrement('vote_count');
                $existingVote->update(['poll_option_id' => $optionId]);
                $option->increment('vote_count');
                return response()->json(['success' => true, 'message' => 'Vote updated']);
            }
        } else {
            PollResponse::create([
                'user_id' => $currentUser->id,
                'poll_option_id' => $optionId
            ]);
            $option->increment('vote_count');
            $poll->increment('total_votes');
            return response()->json(['success' => true, 'message' => 'Vote added']);
        }
    }

    /**
     * 2.10 جلب قائمة المصوتين لخيار استطلاع محدد (Poll Voters)
     */
    public function getVoters(Request $request)
    {
        $pollOptionId = (int)($request->input('poll_option_id') ?? $request->input('option_id'));
        if (!$pollOptionId) {
            return response()->json(['success' => false, 'message' => 'poll_option_id is required'], 422);
        }

        $option = PollOption::find($pollOptionId);
        if (!$option) {
            return response()->json(['success' => false, 'message' => 'Option not found'], 404);
        }

        $responses = PollResponse::with('user')
            ->where('poll_option_id', $pollOptionId)
            ->latest('id')
            ->get();

        $voters = $responses->map(function ($resp) {
            $user = $resp->user;
            if (!$user) return null;

            $profilePic = null;
            if ($user->profile_picture && $user->profile_picture !== 'non' && $user->profile_picture !== 'null') {
                $profilePic = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . basename($user->profile_picture));
            }

            return [
                'id'              => (int)$user->id,
                'user_id'         => (int)$user->id,
                'first_name'      => $user->first_name ?? '',
                'last_name'       => $user->last_name ?? '',
                'full_name'       => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'profile_picture' => $profilePic,
                'avatar'          => $profilePic,
                'time_ago'        => $resp->created_at ? $resp->created_at->diffForHumans() : '',
                'created_at'      => $resp->created_at ? $resp->created_at->toDateTimeString() : '',
            ];
        })->filter()->values();

        return response()->json([
            'success' => true,
            'data'    => $voters,
            'total'   => $voters->count()
        ]);
    }

    /**
     * 2.2 إضافة منشور جديد (نص، وسائط، أو استبيان)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'           => 'nullable|string',
            'privacy_level_id'  => 'nullable|integer',
            'post_type_id'      => 'nullable|integer',
            'shared_id'         => 'nullable|integer',
            'options'           => 'nullable',
            'expires_at'        => 'nullable|date',
            'media'             => 'nullable|array',
            'media.*'           => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // استخدام Transaction لضمان سلامة العمليات المتداخلة بقاعدة البيانات
        DB::beginTransaction();
        try {
            $sharedId = $request->input('shared_id') ?? $request->input('parent_id');

            // 1. إنشاء المنشور الأساسي
            $post = Post::create([
                'user_id'          => $currentUser->id,
                'content'          => $request->content,
                'privacy_level_id' => $request->input('privacy_level_id', 1),
                'post_type_id'     => $request->input('post_type_id', 1),
                'parent_id'        => $sharedId ? (int)$sharedId : 0,
                'is_active'        => 1,
                'like_count'       => 0,
                'comment_count'    => 0,
                'share_count'      => 0
            ]);

            $post->syncHashtags();

            if ($sharedId) {
                Post::where('id', $sharedId)->increment('share_count');
            }

            // 2. معالجة رفع الملفات والوسائط المتعددة إن وجدت
            if ($request->hasFile('media')) {
                $firstImage = null;
                $firstVideo = null;

                foreach ($request->file('media') as $index => $file) {
                    $mimeType = $file->getMimeType();
                    $isImage = str_contains($mimeType, 'image');
                    
                    $path = $file->store('posts_media', 'public');
                    $fileName = basename($path);

                    // Copy to new_wiselook/uploads as well for website compatibility
                    try {
                        $uploadsDir = public_path('new_wiselook/uploads');
                        if (!file_exists($uploadsDir)) {
                            mkdir($uploadsDir, 0755, true);
                        }
                        copy(storage_path('app/public/' . $path), $uploadsDir . '/' . $fileName);
                    } catch (\Exception $e) {}

                    if ($index === 0) {
                        if ($isImage) $firstImage = $fileName;
                        else $firstVideo = $fileName;
                    }

                    PostMedia::create([
                        'post_id'   => $post->id,
                        'image'     => $isImage ? $path : null,
                        'video'     => !$isImage ? $path : null,
                        'position'  => $index,
                        'is_active' => 1
                    ]);
                }

                if ($firstImage || $firstVideo) {
                    $post->update([
                        'image' => $firstImage,
                        'video' => $firstVideo,
                    ]);
                }
            }

            // 3. معالجة إنشاء استبيان الرأي (Polls) إذا كان نوع المنشور مخصصاً لذلك
            $isPoll = ($request->input('post_type_id') == 2 || $request->input('post_type_id') == 3 || $request->has('options'));
            if ($isPoll && $request->has('options')) {
                $rawOptions = $request->options;
                $optionsArray = [];
                if (is_array($rawOptions)) {
                    $optionsArray = $rawOptions;
                } elseif (is_string($rawOptions)) {
                    $decoded = json_decode($rawOptions, true);
                    $optionsArray = is_array($decoded) ? $decoded : [$rawOptions];
                }

                if (count($optionsArray) > 0) {
                    // تحديث نوع المنشور ليكون 2 (استطلاع رأي)
                    $post->update(['post_type_id' => 2]);

                    $pollQuestion = $request->question ?: ($request->content ?: 'استطلاع رأي');
                    $poll = Poll::create([
                        'post_id'            => $post->id,
                        'question'           => $pollQuestion,
                        'total_votes'        => '0',
                        'is_multiple_choice' => $request->input('is_multiple_choice', 0),
                        'expires_at'         => $request->expires_at
                    ]);

                    foreach ($optionsArray as $optionContent) {
                        if (!empty(trim((string)$optionContent))) {
                            PollOption::create([
                                'poll_id'    => $poll->id,
                                'content'    => trim((string)$optionContent),
                                'vote_count' => '0'
                            ]);
                        }
                    }
                }
            }

            
            // معالجة الإشارات (Mentions) في المنشور
            try {
                preg_match_all('/@([a-zA-Z0-9_\x{0621}-\x{064A}]+)/u', $request->content ?? '', $matches);
                if (!empty($matches[1])) {
                    $usernames = array_unique($matches[1]);
                    $mentionedUsers = \App\Models\User::whereIn('user_name', $usernames)
                        ->orWhereIn('id', array_filter($usernames, 'is_numeric'))
                        ->where('id', '!=', $currentUser->id)
                        ->get();
                    foreach ($mentionedUsers as $mUser) {
                        \App\Models\Mention::firstOrCreate([
                            'content_type_id' => 1,
                            'content_id'      => $post->id,
                            'user_id'         => $mUser->id,
                        ]);
                        app(\App\Services\FcmNotificationService::class)->sendMentionNotification(
                            $mUser->id,
                            trim($currentUser->first_name . ' ' . $currentUser->last_name),
                            \Illuminate\Support\Str::limit(strip_tags($post->content), 35) ?: 'منشور',
                            (int)$post->id,
                            (int)$currentUser->id
                        );
                    }
                }
            } catch (\Throwable $e) {}

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post added successfully',
                'data' => [
                    'id'         => (int)$post->id,
                    'content'    => $post->content ?? '',
                    'created_at' => $post->created_at->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2.11 تعديل منشور أو استبيان (Edit Post / Poll)
     */
    public function update(Request $request)
    {
        $postId = (int)($request->input('post_id') ?? $request->input('id'));
        if (!$postId) {
            return response()->json(['success' => false, 'message' => 'post_id is required'], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $post = Post::with(['poll.options', 'media'])->find($postId);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        // التأكد من أن المستخدم هو صاحب المنشور أو مدير النظام
        if ($post->user_id != $currentUser->id && $currentUser->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Only the author can edit this post'], 403);
        }

        // المنشورات المشاركة لا يمكن تعديلها
        if ($post->parent_id > 0) {
            return response()->json(['success' => false, 'message' => 'Shared posts cannot be edited'], 422);
        }

        DB::beginTransaction();
        try {
            // تحديث مستوى الخصوصية إن تم تمريره
            if ($request->has('privacy_level_id')) {
                $post->privacy_level_id = (int)$request->input('privacy_level_id');
            }

            // تعديل منشور عادي (post_type_id == 1)
            if ($post->post_type_id == 1) {
                if ($request->has('content')) {
                    $post->content = $request->content;
                }

                // رفع وسائط جديدة إن وجدت
                if ($request->hasFile('media')) {
                    foreach ($request->file('media') as $index => $file) {
                        $mimeType = $file->getMimeType();
                        $isImage = str_contains($mimeType, 'image');
                        $path = $file->store('posts_media', 'public');
                        $fileName = basename($path);

                        try {
                            $uploadsDir = public_path('new_wiselook/uploads');
                            if (!file_exists($uploadsDir)) {
                                mkdir($uploadsDir, 0755, true);
                            }
                            copy(storage_path('app/public/' . $path), $uploadsDir . '/' . $fileName);
                        } catch (\Exception $e) {}

                        PostMedia::create([
                            'post_id'   => $post->id,
                            'image'     => $isImage ? $path : null,
                            'video'     => !$isImage ? $path : null,
                            'position'  => $post->media()->count() + $index,
                            'is_active' => 1
                        ]);
                    }
                }
            } 
            // تعديل استطلاع رأي (post_type_id == 2)
            else if ($post->post_type_id == 2) {
                $question = $request->input('question') ?? $request->input('content');
                if ($question) {
                    $post->content = $question;
                }

                $poll = Poll::where('post_id', $post->id)->first();
                if ($poll) {
                    if ($question) {
                        $poll->question = $question;
                    }
                    if ($request->has('expires_at')) {
                        $poll->expires_at = $request->expires_at;
                    }
                    $poll->save();

                    // تعديل نصوص الخيارات
                    $rawOptions = $request->input('options');
                    $rawOptionIds = $request->input('option_ids');

                    $optionsList = [];
                    if (is_array($rawOptions)) {
                        $optionsList = $rawOptions;
                    } elseif (is_string($rawOptions)) {
                        $decoded = json_decode($rawOptions, true);
                        $optionsList = is_array($decoded) ? $decoded : [];
                    }

                    $optionIdsList = [];
                    if (is_array($rawOptionIds)) {
                        $optionIdsList = $rawOptionIds;
                    } elseif (is_string($rawOptionIds)) {
                        $decoded = json_decode($rawOptionIds, true);
                        $optionIdsList = is_array($decoded) ? $decoded : [];
                    }

                    if (!empty($optionsList)) {
                        // إذا تم تمرير option_ids بشكل متطابق مع options
                        if (!empty($optionIdsList) && count($optionIdsList) === count($optionsList)) {
                            foreach ($optionIdsList as $idx => $optId) {
                                $opt = PollOption::find($optId);
                                if ($opt && isset($optionsList[$idx]) && !empty(trim((string)$optionsList[$idx]))) {
                                    $opt->content = trim((string)$optionsList[$idx]);
                                    $opt->save();
                                }
                            }
                        } else {
                            // مطابقة الخيارات الحالية بالترتيب
                            $existingOptions = PollOption::where('poll_id', $poll->id)->orderBy('id')->get();
                            foreach ($optionsList as $idx => $optContent) {
                                if (isset($existingOptions[$idx])) {
                                    $existingOptions[$idx]->update(['content' => trim((string)$optContent)]);
                                } else if (!empty(trim((string)$optContent))) {
                                    PollOption::create([
                                        'poll_id'    => $poll->id,
                                        'content'    => trim((string)$optContent),
                                        'vote_count' => '0'
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            $post->save();
            $post->syncHashtags();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'data'    => [
                    'id'         => (int)$post->id,
                    'content'    => $post->content,
                    'updated_at' => $post->updated_at ? $post->updated_at->toDateTimeString() : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error updating post: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 2.3 حذف منشور أو تعليق بشكل موحد
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_id'   => 'required|integer',
            'content_type' => 'required|string|in:post,comment'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        if ($request->content_type === 'post') {
            $post = Post::where('id', $request->content_id)->where('user_id', $currentUser->id)->first();
            if (!$post) {
                return response()->json(['success' => false, 'message' => 'Post not found or unauthorized'], 404);
            }
            $post->delete(); // يعتمد التراجع والمسح على ضبط الـ SoftDeletes بملفك المحدث
        } else {
            $comment = Comment::where('id', $request->content_id)->where('user_id', $currentUser->id)->first();
            if (!$comment) {
                return response()->json(['success' => false, 'message' => 'Comment not found or unauthorized'], 404);
            }
            $postId = $comment->post_id;
            $parentId = $comment->parent_id;
            $comment->delete();

            if ($postId) {
                Post::where('id', $postId)->where('comment_count', '>', 0)->decrement('comment_count');
            }
            if ($parentId > 0) {
                Comment::where('id', $parentId)->where('reply_count', '>', 0)->decrement('reply_count');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully'
        ]);
    }

    /**
     * 2.4 التفاعل مع المنشورات والتعليقات (Like / Remove)
     */
    public function react(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content_id'    => 'required|integer',
            'content_type'  => 'required|string|in:post,comment',
            'reaction_type' => 'required|string|in:like,remove'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $contentTypeId = ($request->content_type === 'post') ? 1 : 2; // 1 للمنشور، 2 للتعليق
        $contentId = (int)$request->content_id;

        $reaction = Reaction::where('user_id', $currentUser->id)
            ->where('content_id', $contentId)
            ->where('content_type_id', $contentTypeId)
            ->first();

        $isLiked = false;

        if ($request->reaction_type === 'like') {
            $isNewLike = false;
            if (!$reaction) {
                Reaction::create([
                    'user_id'          => $currentUser->id,
                    'content_id'       => $contentId,
                    'content_type_id'  => $contentTypeId,
                    'reaction_type_id' => 1,
                    'is_active'        => 1
                ]);
                if ($request->content_type === 'post') {
                    Post::where('id', $contentId)->increment('like_count');
                } else {
                    Comment::where('id', $contentId)->increment('reaction_count');
                }
                $isNewLike = true;
            } else if ((int)$reaction->is_active === 0) {
                $reaction->update(['is_active' => 1]);
                if ($request->content_type === 'post') {
                    Post::where('id', $contentId)->increment('like_count');
                } else {
                    Comment::where('id', $contentId)->increment('reaction_count');
                }
                $isNewLike = true;
            }
            $isLiked = true;

            // إرسال إشعار لحظي وسحابي لصاحب المنشور عند الإعجاب
            if ($isNewLike && $request->content_type === 'post') {
                try {
                    $post = Post::find($contentId);
                    if ($post && $post->user_id !== $currentUser->id) {
                        $snippet = \Illuminate\Support\Str::limit(strip_tags($post->content), 35) ?: 'موضوعك';
                        $message = 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالإعجاب بموضوعك: "' . $snippet . '"';
                        $notifId = \Illuminate\Support\Str::uuid()->toString();
                        $avatarFormatted = $currentUser->profile_picture ? (filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL) ? $currentUser->profile_picture : asset('new_wiselook/uploads/' . $currentUser->profile_picture)) : null;

                        \Illuminate\Support\Facades\DB::table('notifications')->insert([
                            'id' => $notifId,
                            'type' => 'App\Notifications\GeneralNotification',
                            'notifiable_type' => 'App\Models\User',
                            'notifiable_id' => $post->user_id,
                            'data' => json_encode([
                                'type' => 'like',
                                'sender_id' => $currentUser->id,
                                'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                                'avatar' => $currentUser->profile_picture,
                                'message' => $message,
                                'post_id' => (int)$post->id
                            ]),
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        broadcast(new \App\Events\NotificationSent($post->user_id, [
                            'id'          => $notifId,
                            'type'        => 'like',
                            'title'       => 'تفاعل جديد',
                            'message'     => $message,
                            'sender_id'   => (int)$currentUser->id,
                            'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                            'avatar'      => $avatarFormatted,
                            'post_id'     => (int)$post->id,
                            'created_at'  => now()->toDateTimeString(),
                            'time_ago'    => 'الآن',
                            'is_seen'     => false,
                        ]));

                        app(\App\Services\FcmNotificationService::class)->sendLikeNotification(
                            $post->user_id,
                            trim($currentUser->first_name . ' ' . $currentUser->last_name),
                            $snippet,
                            (int)$post->id,
                            (int)$currentUser->id
                        );
                    }
                } catch (\Throwable $e) {}
            }
        } else {
            if ($reaction && (int)$reaction->is_active === 1) {
                $reaction->update(['is_active' => 0]);
                if ($request->content_type === 'post') {
                    Post::where('id', $contentId)->where('like_count', '>', 0)->decrement('like_count');
                } else {
                    Comment::where('id', $contentId)->where('reaction_count', '>', 0)->decrement('reaction_count');
                }
            }
            $isLiked = false;
        }

        $currentLikeCount = Reaction::where('content_id', $contentId)
            ->where('content_type_id', $contentTypeId)
            ->where('is_active', 1)
            ->count();

        if ($request->content_type === 'post') {
            Post::where('id', $contentId)->update(['like_count' => $currentLikeCount]);
        } else {
            Comment::where('id', $contentId)->update(['reaction_count' => $currentLikeCount]);
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Reaction updated successfully',
            'is_liked'   => $isLiked,
            'like_count' => $currentLikeCount
        ]);
    }

    /**
     * 2.5 جلب تعليقات منشور محدد مع الردود التابعة لها
     */
    public function listComments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required|integer|exists:posts,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }
        $currentUserId = $currentUser ? (int)$currentUser->id : 0;

        // جلب التعليقات الرئيسية فقط (التي يكون الـ parent_id فيها مساوياً لـ 0)
        $comments = Comment::with(['user'])
                           ->where('post_id', $request->post_id)
                           ->where('parent_id', 0)
                           ->where('is_active', 1)
                           ->orderBy('created_at', 'asc')
                           ->get();

        $formattedComments = $comments->map(function ($comment) use ($currentUserId) {
            // جلب الردود الفرعية التابعة لهذا التعليق (Threads)
            $replies = Comment::with(['user'])
                              ->where('parent_id', $comment->id)
                              ->where('is_active', 1)
                              ->orderBy('created_at', 'asc')
                              ->get()
                              ->map(function ($reply) use ($currentUserId) {
                                  $replyAvatarUrl = null;
                                  if ($reply->user && $reply->user->profile_picture && $reply->user->profile_picture !== 'non') {
                                      $replyAvatarUrl = filter_var($reply->user->profile_picture, FILTER_VALIDATE_URL)
                                          ? $reply->user->profile_picture
                                          : asset('new_wiselook/uploads/' . $reply->user->profile_picture);
                                  }

                                  $isReplyLiked = $currentUserId > 0 ? Reaction::where('user_id', $currentUserId)
                                      ->where('content_id', $reply->id)
                                      ->where('content_type_id', 2)
                                      ->where('is_active', 1)
                                      ->exists() : false;

                                  $replyReactionCount = Reaction::where('content_id', $reply->id)
                                      ->where('content_type_id', 2)
                                      ->where('is_active', 1)
                                      ->count();

                                  return [
                                      'id'             => (int)$reply->id,
                                      'post_id'        => (int)$reply->post_id,
                                      'content'        => $reply->content,
                                      'created_at'     => $reply->created_at->toDateTimeString(),
                                      'reaction_count' => $replyReactionCount,
                                      'is_reacted'     => $isReplyLiked ? 1 : 0,
                                      'is_liked'       => $isReplyLiked,
                                      'user' => [
                                          'id'              => (int)$reply->user->id,
                                          'first_name'      => $reply->user->first_name ?? '',
                                          'last_name'       => $reply->user->last_name ?? '',
                                          'profile_picture' => $replyAvatarUrl
                                      ]
                                  ];
                              })->toArray();

            $avatarUrl = null;
            if ($comment->user && $comment->user->profile_picture && $comment->user->profile_picture !== 'non') {
                $avatarUrl = filter_var($comment->user->profile_picture, FILTER_VALIDATE_URL)
                    ? $comment->user->profile_picture
                    : asset('new_wiselook/uploads/' . $comment->user->profile_picture);
            }

            $isCommentLiked = $currentUserId > 0 ? Reaction::where('user_id', $currentUserId)
                ->where('content_id', $comment->id)
                ->where('content_type_id', 2)
                ->where('is_active', 1)
                ->exists() : false;

            $reactionCount = Reaction::where('content_id', $comment->id)
                ->where('content_type_id', 2)
                ->where('is_active', 1)
                ->count();

            return [
                'id'             => (int)$comment->id,
                'post_id'        => (int)$comment->post_id,
                'content'        => $comment->content,
                'created_at'     => $comment->created_at->toDateTimeString(),
                'reaction_count' => $reactionCount,
                'is_reacted'     => $isCommentLiked ? 1 : 0,
                'is_liked'       => $isCommentLiked,
                'user' => [
                    'id'              => (int)$comment->user->id,
                    'first_name'      => $comment->user->first_name ?? '',
                    'last_name'       => $comment->user->last_name ?? '',
                    'profile_picture' => $avatarUrl
                ],
                'replies'        => $replies
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedComments
        ]);
    }

    /**
     * 2.6 إضافة تعليق جديد أو الرد على تعليق قائم
     */
    public function addComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'    => 'required|string',
            'post_id'    => 'required|integer|exists:posts,id',
            'comment_id' => 'nullable|integer|exists:comments,id' // يرسل في حال الرد كـ Parent ID
        ]);

        if ($validator->fails()) {
            
        // معالجة الإشارات (Mentions) في التعليق
        try {
            preg_match_all('/@([a-zA-Z0-9_\x{0621}-\x{064A}]+)/u', $request->content ?? '', $matches);
            if (!empty($matches[1])) {
                $usernames = array_unique($matches[1]);
                $mentionedUsers = \App\Models\User::whereIn('user_name', $usernames)
                    ->orWhereIn('id', array_filter($usernames, 'is_numeric'))
                    ->where('id', '!=', $currentUser->id)
                    ->get();
                foreach ($mentionedUsers as $mUser) {
                    \App\Models\Mention::firstOrCreate([
                        'content_type_id' => 2,
                        'content_id'      => $comment->id,
                        'user_id'         => $mUser->id,
                    ]);
                    app(\App\Services\FcmNotificationService::class)->sendMentionNotification(
                        $mUser->id,
                        trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        \Illuminate\Support\Str::limit(strip_tags($request->content), 35) ?: 'تعليق',
                        (int)$request->post_id,
                        (int)$currentUser->id
                    );
                }
            }
        } catch (\Throwable $e) {}

        return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        $parentId = $request->input('comment_id', 0); // الافتراضي 0 تعني تعليق رئيسي

        // إنشاء سطر التعليق
        $comment = Comment::create([
            'post_id'        => $request->post_id,
            'user_id'        => $currentUser->id,
            'content'        => $request->content,
            'parent_id'      => $parentId,
            'is_active'      => 1,
            'reaction_count' => 0,
            'reply_count'    => 0
        ]);

        // تحديث عدادات المنشور والتعليق الأب
        Post::where('id', $request->post_id)->increment('comment_count');
        if ($parentId > 0) {
            Comment::where('id', $parentId)->increment('reply_count');
        }

        // إرسال إشعار لصاحب المنشور إذا كان تعليقاً رئيسياً
        if ($parentId == 0) {
            $post = Post::find($request->post_id);
            if ($post && $post->user_id !== $currentUser->id) {
                $postTitle = \Illuminate\Support\Str::limit(strip_tags($post->content), 35) ?: 'موضوعك';
                $message = 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالتعليق على موضوعك: "' . $postTitle . '"';
                $notifId = \Illuminate\Support\Str::uuid()->toString();
                $avatarFormatted = $currentUser->profile_picture ? (filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL) ? $currentUser->profile_picture : asset('new_wiselook/uploads/' . $currentUser->profile_picture)) : null;

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => $notifId,
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $post->user_id,
                    'data' => json_encode([
                        'type' => 'comment',
                        'sender_id' => $currentUser->id,
                        'sender_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
                        'avatar' => $currentUser->profile_picture,
                        'message' => $message,
                        'post_id' => (int)$request->post_id
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                try {
                    broadcast(new \App\Events\NotificationSent($post->user_id, [
                        'id'          => $notifId,
                        'type'        => 'comment',
                        'title'       => 'تعليق جديد',
                        'message'     => $message,
                        'sender_id'   => (int)$currentUser->id,
                        'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        'avatar'      => $avatarFormatted,
                        'post_id'     => (int)$request->post_id,
                        'created_at'  => now()->toDateTimeString(),
                        'time_ago'    => 'الآن',
                        'is_seen'     => false,
                    ]));
                } catch (\Exception $e) {}
                // إرسال إشعار FCM سحابي لصاحب المنشور
                try {
                    app(\App\Services\FcmNotificationService::class)->sendCommentNotification(
                        $post->user_id,
                        trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        $postTitle,
                        (int)$request->post_id,
                        (int)$currentUser->id
                    );
                } catch (\Throwable $e) {}

            }
        }

        // إرسال إشعار لصاحب التعليق/الرد الأصلي إذا كان رداً
        if ($parentId > 0) {
            $parentComment = Comment::with('post')->find($parentId);
            if ($parentComment && $parentComment->user_id !== $currentUser->id) {
                $postTitle = $parentComment->post ? \Illuminate\Support\Str::limit(strip_tags($parentComment->post->content), 35) : 'موضوع';
                
                // تحديد نوع الإشعار بناءً على ما إذا كان الأب تعليقاً رئيسياً أم رداً
                $isParentReply = $parentComment->parent_id > 0;
                $notifType = $isParentReply ? 'reply_to_reply' : 'comment_reply';
                $message = $isParentReply 
                    ? 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالرد على ردك في موضوع: "' . $postTitle . '"'
                    : 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالرد على تعليقك في موضوع: "' . $postTitle . '"';
                $notifId = \Illuminate\Support\Str::uuid()->toString();
                $avatarFormatted = $currentUser->profile_picture ? (filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL) ? $currentUser->profile_picture : asset('new_wiselook/uploads/' . $currentUser->profile_picture)) : null;

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => $notifId,
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $parentComment->user_id,
                    'data' => json_encode([
                        'type' => $notifType,
                        'sender_id' => $currentUser->id,
                        'sender_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
                        'avatar' => $currentUser->profile_picture,
                        'message' => $message,
                        'post_id' => (int)$request->post_id
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                try {
                    broadcast(new \App\Events\NotificationSent($parentComment->user_id, [
                        'id'          => $notifId,
                        'type'        => $notifType,
                        'title'       => 'رد جديد على تعليقك',
                        'message'     => $message,
                        'sender_id'   => (int)$currentUser->id,
                        'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        'avatar'      => $avatarFormatted,
                        'post_id'     => (int)$request->post_id,
                        'created_at'  => now()->toDateTimeString(),
                        'time_ago'    => 'الآن',
                        'is_seen'     => false,
                    ]));
                } catch (\Exception $e) {}
                // إرسال إشعار FCM سحابي لصاحب التعليق
                try {
                    app(\App\Services\FcmNotificationService::class)->sendReplyNotification(
                        $parentComment->user_id,
                        trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        $postTitle,
                        (int)$request->post_id,
                        (int)$currentUser->id
                    );
                } catch (\Throwable $e) {}

            }
        }

        $avatarUrl = null;
        if ($currentUser->profile_picture && $currentUser->profile_picture !== 'non') {
            $avatarUrl = filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL)
                ? $currentUser->profile_picture
                : asset('new_wiselook/uploads/' . $currentUser->profile_picture);
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => [
                'id'              => (int)$comment->id,
                'post_id'         => (int)$comment->post_id,
                'content'         => $comment->content,
                'created_at'      => $comment->created_at->toDateTimeString(),
                'user_id'         => (int)$currentUser->id,
                'first_name'      => $currentUser->first_name ?? '',
                'last_name'       => $currentUser->last_name ?? '',
                'profile_picture' => $avatarUrl,
                'user' => [
                    'id'              => (int)$currentUser->id,
                    'first_name'      => $currentUser->first_name ?? '',
                    'last_name'       => $currentUser->last_name ?? '',
                    'profile_picture' => $avatarUrl
                ],
                'replies'         => []
            ]
        ]);
    }

    /**
     * جلب تفاصيل تقييمات لجنة الحكماء لمنشور محدد
     */
    public function wiseRatings(Request $request)
    {
        $postId = $request->input('post_id') ?? $request->input('id');
        if (!$postId) {
            return response()->json(['success' => false, 'message' => 'Post ID is required'], 400);
        }

        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['success' => false, 'message' => 'Post not found'], 404);
        }

        $ratings = \App\Models\WiseSubjectRating::where('post_id', $postId)
            ->with('user')
            ->latest()
            ->get();

        $ratingsCount = $ratings->count();
        $avgRating = $ratingsCount > 0 ? (float)round($ratings->avg('rating'), 1) : 0;

        $excellent = $ratings->filter(fn($r) => $r->rating >= 8)->count();
        $good = $ratings->filter(fn($r) => $r->rating >= 6 && $r->rating < 8)->count();
        $acceptable = $ratings->filter(fn($r) => $r->rating >= 4 && $r->rating < 6)->count();
        $weak = $ratings->filter(fn($r) => $r->rating < 4)->count();

        $formattedRatings = $ratings->map(function($r) {
            $user = $r->user;
            $avatarUrl = url('upload/no_image.jpg');
            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }

            return [
                'id' => (int)$r->id,
                'rating' => (float)$r->rating,
                'reason' => $r->reason ?? '',
                'wise_id' => $user ? (int)$user->id : 0,
                'wise_name' => $user ? trim($user->first_name . ' ' . $user->last_name) : 'حكيم منصة',
                'wise_avatar' => $avatarUrl,
                'created_at' => $r->created_at ? $r->created_at->format('Y-m-d H:i') : '',
                'diff' => $r->created_at ? $r->created_at->diffForHumans() : ''
            ];
        });

        return response()->json([
            'success' => true,
            'post_id' => (int)$post->id,
            'avg_rating' => $avgRating,
            'ratings_count' => $ratingsCount,
            'counts' => [
                'excellent' => $excellent,
                'good' => $good,
                'acceptable' => $acceptable,
                'weak' => $weak,
            ],
            'ratings' => $formattedRatings
        ]);
    }

    /**
     * جلب قائمة المعجبين والتفاعلات لمنشور محدد
     */
    public function listPostReactions(Request $request)
    {
        $postId = $request->input('post_id') ?? $request->input('id');
        if (!$postId) {
            return response()->json(['success' => false, 'message' => 'Post ID is required'], 400);
        }

        $offset = (int)($request->input('offset', 0));
        $limit = (int)($request->input('limit', 20));

        $reactions = Reaction::where('content_id', $postId)
            ->where('content_type_id', 1) // 1 للمنشور
            ->where('is_active', 1)
            ->with('user')
            ->orderBy('id', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();

        $formattedList = $reactions->map(function($r) {
            $user = $r->user;
            $avatarUrl = null;
            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }

            return [
                'reaction_id'      => (int)$r->id,
                'reaction_type_id' => (int)($r->reaction_type_id ?? 1),
                'reaction_name'    => 'like',
                'user_id'          => $user ? (int)$user->id : 0,
                'first_name'       => $user ? ($user->first_name ?? '') : '',
                'last_name'        => $user ? ($user->last_name ?? '') : '',
                'profile_picture'   => $avatarUrl,
                'created_at'       => $r->created_at ? $r->created_at->toDateTimeString() : '',
                'time_ago'         => $r->created_at ? $r->created_at->diffForHumans() : ''
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedList
        ]);
    }

    /**
     * جلب قائمة الهاشتاجات الأكثر استخداماً والبحث فيها
     */
    public function listHashtags(Request $request)
    {
        $q = $request->input('q', $request->input('search', ''));
        
        $query = \App\Models\Hashtag::withCount(['links as posts_count' => function ($qBuilder) {
            $qBuilder->where('content_type_id', 1);
        }]);

        if (!empty($q)) {
            $cleanQ = ltrim($q, '#');
            $query->where('name', 'LIKE', "%{$cleanQ}%");
        }

        $hashtags = $query->orderByDesc('posts_count')
            ->orderBy('name')
            ->limit(40)
            ->get()
            ->map(function ($tag) {
                return [
                    'id'    => (int)$tag->id,
                    'name'  => $tag->name,
                    'count' => (int)$tag->posts_count
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $hashtags
        ]);
    }

    /**
     * 2.12 تعديل تعليق أو رد (Edit Comment / Reply)
     */
    public function updateComment(Request $request)
    {
        $commentId = (int)($request->input('comment_id') ?? $request->input('id') ?? $request->input('content_id'));
        if (!$commentId) {
            return response()->json(['success' => false, 'message' => 'comment_id is required'], 422);
        }

        $content = trim((string)$request->input('content'));
        if (empty($content)) {
            return response()->json(['success' => false, 'message' => 'content cannot be empty'], 422);
        }

        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = \App\Models\User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $comment = Comment::find($commentId);
        if (!$comment) {
            return response()->json(['success' => false, 'message' => 'Comment not found'], 404);
        }

        // التأكد من أن المستخدم هو صاحب التعليق/الرد أو مدير النظام
        if ($comment->user_id != $currentUser->id && $currentUser->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Only the author can edit this comment'], 403);
        }

        $comment->content = $content;
        $comment->save();

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data'    => [
                'id'         => (int)$comment->id,
                'post_id'    => (int)$comment->post_id,
                'content'    => $comment->content,
                'parent_id'  => (int)$comment->parent_id,
                'updated_at' => $comment->updated_at ? $comment->updated_at->toDateTimeString() : null
            ]
        ]);
    }

    /**
     * 2.13 جلب أعضاء لجنة الحكماء (Sage Committee)
     */
    public function getSageCommittee(Request $request)
    {
        $committeeMembers = \App\Models\WiseCommittee::with('user')
            ->where('is_active', 1)
            ->latest()
            ->get()
            ->map(function ($member, $index) {
                $user = $member->user;
                if (!$user) return null;

                $avatarUrl = null;
                if (!empty($user->profile_picture) && $user->profile_picture !== 'non') {
                    $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                        ? $user->profile_picture
                        : asset('new_wiselook/uploads/' . $user->profile_picture);
                }

                $rank = $user->rank;
                $rankIconUrl = null;
                if ($rank && !empty($rank->photo)) {
                    $rankIconUrl = filter_var($rank->photo, FILTER_VALIDATE_URL)
                        ? $rank->photo
                        : asset('new_wiselook/uploads/' . $rank->photo);
                }

                return [
                    'id'             => (int)$member->id,
                    'user_id'        => (int)$user->id,
                    'order'          => $index + 1,
                    'first_name'     => $user->first_name ?? '',
                    'last_name'      => $user->last_name ?? '',
                    'full_name'      => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                    'profile_picture'=> $avatarUrl,
                    'role'           => $user->role ?? 'user',
                    'is_admin'       => ($user->role === 'admin'),
                    'specialty'      => $member->specialty ?? 'حكيم عام',
                    'bio'            => $member->bio ?? 'لا توجد نبذة مضافة لهذا الحكيم حتى الآن.',
                    'points'         => (int)($user->points ?? 0),
                    'rank_name'      => $rank?->rank_name ?? 'سفير الحكمة',
                    'rank_icon'      => $rankIconUrl,
                    'appointed_at'   => $member->created_at ? $member->created_at->format('d M Y') : '—',
                ];
            })
            ->filter()
            ->values();

        $specialtiesCount = $committeeMembers->pluck('specialty')->unique()->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'total_sages'       => $committeeMembers->count(),
                'specialties_count' => $specialtiesCount,
                'title'             => 'مقر انعقاد لجنة الحكماء',
                'description'       => 'نخبة الحكماء المعتمدين لتقييم المحتوى ومتابعة جودة المنشورات وفق معايير النزاهة والحكمة.',
                'members'           => $committeeMembers,
            ]
        ]);
    }
}