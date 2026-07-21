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
        $currentUser = $request->user();
        
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        
        // بناء الاستعلام الأساسي مع جلب علاقة الناشر والوسائط والاستطلاع والخيارات
        $query = Post::with(['user', 'media', 'poll.options'])->where('is_active', 1);

        // 1. الفلترة حسب مستخدم معين (عرض بروفايل شخص آخر أو بروفايلي)
        if ($request->has('profile_id')) {
            $query->where('user_id', $request->profile_id);
        }

        // 2. جلب منشور واحد محدد عبر الـ id
        if ($request->has('post_id')) {
            $query->where('id', $request->post_id);
        }

        // 3. الفلترة حسب المنشورات المحفوظة فقط
        if ($request->input('filter') === 'saved') {
            $savedPostIds = SavedPost::where('user_id', $currentUser->id)->pluck('post_id');
            $query->whereIn('id', $savedPostIds);
        }

        // ترتيب الاستعلام والأوفست والليميت
        $posts = $query->orderBy('created_at', 'desc')
                       ->skip($offset)
                       ->take($limit)
                       ->get();

        // دالة مساعدة لتشكيل المنشور لتطابق تماما ما يتوقعه Flutter
        $mapPost = function ($post) use ($currentUser) {
            $isLiked = Reaction::where('user_id', $currentUser->id)
                               ->where('content_id', $post->id)
                               ->where('content_type_id', 1)
                               ->where('is_active', 1)
                               ->exists();

            $isSaved = SavedPost::where('user_id', $currentUser->id)
                                ->where('post_id', $post->id)
                                ->exists();

            $isPinned = PinnedPost::where('post_id', $post->id)
                                 ->exists();

            // تنسيق الوسائط لتطابق MediaItemModel في Flutter
            $mediaArray = $post->media->map(function ($mediaItem) {
                $path = $mediaItem->image ? asset('storage/' . $mediaItem->image) : asset('storage/' . $mediaItem->video);
                $type = $mediaItem->image ? 'image' : 'video';
                return [
                    'path' => $path,
                    'type' => $type
                ];
            })->toArray();

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
                $options = $post->poll->options->map(function ($opt) use ($currentUser, &$selectedOptionId) {
                    $isSelected = PollResponse::where('poll_option_id', $opt->id)
                                              ->where('user_id', $currentUser->id)
                                              ->exists();
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

            return [
                'post_id'            => (int)$post->id,
                'user_id'            => (int)$post->user_id,
                'content'            => $post->content ?? '',
                'selected_option_id' => $selectedOptionId,
                'like_count'         => (int)$post->like_count,
                'comment_count'      => (int)$post->comment_count,
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
                'mentions'           => []
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
            $message = 'Post unpinned successfully';
        } else {
            PinnedPost::create([
                'post_id' => $postId,
                'pin_scope' => $scope,
                'pinned_at' => now()
            ]);
            $message = 'Post pinned successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message
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
     * 2.2 إضافة منشور جديد (نص، وسائط، أو استبيان)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'           => 'nullable|string',
            'privacy_level_id'  => 'required|integer',
            'post_type_id'      => 'required|integer',
            'options'           => 'nullable|json', // خيارات الاستبيان ["نعم", "لا"]
            'expires_at'        => 'nullable|date',
            'media'             => 'nullable|array',
            'media.*'           => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480' // حد أقصى 20 ميجا للملف
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();

        // استخدام Transaction لضمان سلامة العمليات المتداخلة بقاعدة البيانات
        DB::beginTransaction();
        try {
            // 1. إنشاء المنشور الأساسي
            $post = Post::create([
                'user_id'          => $currentUser->id,
                'content'          => $request->content,
                'privacy_level_id' => $request->privacy_level_id,
                'post_type_id'     => $request->post_type_id,
                'is_active'        => 1,
                'like_count'       => 0,
                'comment_count'    => 0,
                'share_count'      => 0
            ]);

            // 2. معالجة رفع الملفات والوسائط المتعددة إن وجدت
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $index => $file) {
                    $mimeType = $file->getMimeType();
                    $isImage = str_contains($mimeType, 'image');
                    
                    $path = $file->store('posts_media', 'public');

                    PostMedia::create([
                        'post_id'   => $post->id,
                        'image'     => $isImage ? $path : null,
                        'video'     => !$isImage ? $path : null,
                        'position'  => $index,
                        'is_active' => 1
                    ]);
                }
            }

            // 3. معالجة إنشاء استبيان الرأي (Polls) إذا كان نوع المنشور مخصصاً لذلك
            if ($request->post_type_id == 3 && $request->has('options')) {
                $optionsArray = json_decode($request->options, true);
                if (is_array($optionsArray) && count($optionsArray) > 0) {
                    $poll = Poll::create([
                        'post_id'            => $post->id,
                        'question'           => $request->content ?? 'استطلاع رأي',
                        'total_votes'        => '0',
                        'is_multiple_choice' => $request->input('is_multiple_choice', 0),
                        'expires_at'         => $request->expires_at
                    ]);

                    foreach ($optionsArray as $optionContent) {
                        PollOption::create([
                            'poll_id'    => $poll->id,
                            'content'    => $optionContent,
                            'vote_count' => '0'
                        ]);
                    }
                }
            }

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
            $comment->delete();
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
        $contentTypeId = ($request->content_type === 'post') ? 1 : 2; // 1 للمنشور، 2 للتعليق

        if ($request->reaction_type === 'like') {
            // إنشاء التفاعل أو تحديثه إذا كان موجوداً مسبقاً وغير نشط
            Reaction::updateOrCreate(
                [
                    'user_id'         => $currentUser->id,
                    'content_id'      => $request->content_id,
                    'content_type_id' => $contentTypeId,
                    'reaction_type_id'=> 1 // تفاعل الإعجاب الأساسي تماشياً مع قاعدة بياناتك
                ],
                [
                    'is_active'       => 1
                ]
            );

            // تحديث عداد الإعجابات التابع للمنشور لسرعة القراءة الفورية بالـ Feed
            if ($request->content_type === 'post') {
                Post::where('id', $request->content_id)->increment('like_count');
            } else {
                Comment::where('id', $request->content_id)->increment('reaction_count');
            }
        } else {
            // إزالة التفاعل (تحديث حقل النشاط تماشياً مع الـ Logic القديم)
            Reaction::where('user_id', $currentUser->id)
                    ->where('content_id', $request->content_id)
                    ->where('content_type_id', $contentTypeId)
                    ->update(['is_active' => 0]);

            if ($request->content_type === 'post') {
                Post::where('id', $request->content_id)->where('like_count', '>', 0)->decrement('like_count');
            } else {
                Comment::where('id', $request->content_id)->where('reaction_count', '>', 0)->decrement('reaction_count');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Reaction updated successfully'
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

        // جلب التعليقات الرئيسية فقط (التي يكون الـ parent_id فيها مساوياً لـ 0)
        $comments = Comment::with(['user'])
                           ->where('post_id', $request->post_id)
                           ->where('parent_id', 0)
                           ->where('is_active', 1)
                           ->orderBy('created_at', 'asc')
                           ->get();

        $formattedComments = $comments->map(function ($comment) {
            // جلب الردود الفرعية التابعة لهذا التعليق (Threads)
            $replies = Comment::with(['user'])
                              ->where('parent_id', $comment->id)
                              ->where('is_active', 1)
                              ->orderBy('created_at', 'asc')
                              ->get()
                              ->map(function ($reply) {
                                  return [
                                      'id'         => (int)$reply->id,
                                      'post_id'    => (int)$reply->post_id,
                                      'content'    => $reply->content,
                                      'created_at' => $reply->created_at->toDateTimeString(),
                                      'user' => [
                                          'id'         => (int)$reply->user->id,
                                          'first_name' => $reply->user->first_name,
                                          'last_name'  => $reply->user->last_name,
                                          'profile_picture' => $reply->user->profile_picture
                                      ]
                                  ];
                              })->toArray();

            return [
                'id'         => (int)$comment->id,
                'post_id'    => (int)$comment->post_id,
                'content'    => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString(),
                'user' => [
                    'id'         => (int)$comment->user->id,
                    'first_name' => $comment->user->first_name,
                    'last_name'  => $comment->user->last_name,
                    'profile_picture' => $comment->user->profile_picture
                ],
                'replies'    => $replies
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

        // تحديث عدادات المنشور أو التعليق الأب
        if ($parentId > 0) {
            Comment::where('id', $parentId)->increment('reply_count');
        } else {
            Post::where('id', $request->post_id)->increment('comment_count');
        }

        // إرسال إشعار لصاحب التعليق/الرد الأصلي
        if ($parentId > 0) {
            $parentComment = Comment::with('post')->find($parentId);
            if ($parentComment && $parentComment->user_id !== $currentUser->id) {
                $postTitle = $parentComment->post ? \Illuminate\Support\Str::limit($parentComment->post->content, 35) : 'موضوع';
                
                // تحديد نوع الإشعار بناءً على ما إذا كان الأب تعليقاً رئيسياً أم رداً
                $isParentReply = $parentComment->parent_id > 0;
                $notifType = $isParentReply ? 'reply_to_reply' : 'comment_reply';
                $message = $isParentReply 
                    ? 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالرد على ردك في موضوع: "' . $postTitle . '"'
                    : 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بالرد على تعليقك في موضوع: "' . $postTitle . '"';

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
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
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data' => [
                'id'         => (int)$comment->id,
                'content'    => $comment->content,
                'created_at' => $comment->created_at->toDateTimeString()
            ]
        ]);
    }
}