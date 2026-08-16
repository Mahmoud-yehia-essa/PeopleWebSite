<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Friendship;
use App\Models\Seen;
use App\Models\Dictionary;
use App\Models\Language;
use App\Models\Translation;
use App\Models\NotificationForApp;

class MiscApiController extends Controller
{
    /**
     * 6.1 جلب الإشعارات العامة وإشعارات الإدارة ديناميكياً
     */
    public function listNotifications(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json([
                'success'       => false,
                'message'       => 'Unauthenticated',
                'unseen_count'  => 0,
                'unread_count'  => 0,
                'notifications' => [],
                'data'          => []
            ], 401);
        }

        $limit = (int)$request->input('limit', 30);
        $offset = (int)$request->input('offset', 0);

        $notificationsCollection = collect();

        $formatAvatar = function ($raw) {
            if (empty($raw) || $raw === 'non') {
                return null;
            }
            return filter_var($raw, FILTER_VALIDATE_URL) ? $raw : asset('new_wiselook/uploads/' . $raw);
        };

        // أ. جلب إشعارات الإدارة من جدول notification_for_apps
        try {
            $adminNotifications = NotificationForApp::where(function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id)
                      ->orWhereNull('user_id')
                      ->orWhere('user_id', 0);
            })->get();

            foreach ($adminNotifications as $adminNotif) {
                $rawView = strtolower(trim((string)$adminNotif->user_view));
                $isAdminSeen = ($rawView === 'yes' || $rawView === '1' || $rawView === 'true');

                $createdAt = $adminNotif->created_at ?: ($adminNotif->date ? \Carbon\Carbon::parse($adminNotif->date) : now());

                $notificationsCollection->push([
                    'id'          => 'admin_' . $adminNotif->id,
                    'raw_id'      => (int)$adminNotif->id,
                    'type'        => 'admin',
                    'title'       => $adminNotif->title ?: 'إشعار من الإدارة',
                    'message'     => $adminNotif->des ?: '',
                    'description' => $adminNotif->des ?: '',
                    'link'        => $adminNotif->link ?? null,
                    'url'         => $adminNotif->link ?? null,
                    'sender_id'   => 0,
                    'sender_name' => 'إدارة وايز لوك',
                    'avatar'      => null,
                    'post_id'     => null,
                    'created_at'  => $createdAt,
                    'read_at'     => $isAdminSeen ? ($adminNotif->updated_at ?: now()) : null,
                    'is_seen'     => $isAdminSeen,
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching notification_for_apps: ' . $e->getMessage());
        }

        // ب. جلب الإشعارات الأساسية المسجلة في جدول notifications
        $dbNotifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $currentUser->id)
            ->get();

        $existingNotificationKeys = [];

        foreach ($dbNotifications as $notif) {
            $data = json_decode($notif->data, true);
            if ($data) {
                $type = $data['type'] ?? 'general';
                $title = 'إشعار جديد';
                if ($type === 'friend_accept') $title = 'قبول طلب صداقة';
                elseif ($type === 'friend_request') $title = 'طلب صداقة جديد';
                elseif ($type === 'mention') $title = 'إشارة إليك';
                elseif ($type === 'comment_reply' || $type === 'reply_to_reply') $title = 'رد جديد على تعليقك';
                elseif ($type === 'comment') $title = 'تعليق جديد';
                elseif ($type === 'like' || $type === 'post_like') $title = 'تفاعل جديد';

                $postId = isset($data['post_id']) ? (int)$data['post_id'] : null;
                $senderId = isset($data['sender_id']) ? (int)$data['sender_id'] : null;

                if ($type === 'friend_request' && $senderId) {
                    $isActiveFriend = \App\Models\Friendship::where(function($q) use ($currentUser, $senderId) {
                        $q->where('sender_id', $senderId)->where('receiver_id', $currentUser->id);
                    })->orWhere(function($q) use ($currentUser, $senderId) {
                        $q->where('sender_id', $currentUser->id)->where('receiver_id', $senderId);
                    })->where('is_active', 1)->exists();

                    if ($isActiveFriend) {
                        $type = 'friend_accept';
                        $title = 'قبول طلب صداقة';
                    }
                }

                $dedupKey = "{$type}_{$senderId}_{$postId}";
                $existingNotificationKeys[$dedupKey] = true;

                $notificationsCollection->push([
                    'id'          => (string)$notif->id,
                    'type'        => $type,
                    'title'       => $title,
                    'message'     => $data['message'] ?? '',
                    'description' => $data['message'] ?? '',
                    'sender_id'   => $senderId,
                    'sender_name' => $data['sender_name'] ?? '',
                    'avatar'      => $formatAvatar($data['avatar'] ?? null),
                    'post_id'     => $postId,
                    'created_at'  => \Carbon\Carbon::parse($notif->created_at),
                    'read_at'     => $notif->read_at
                ]);
            }
        }

        // جـ. جلب إشعارات طلبات الصداقة (فقط في حال لم تكن مسجلة بجدول notifications)
        $friendRequests = Friendship::with('sender')
            ->where('receiver_id', $currentUser->id)
            ->where('is_active', 0)
            ->get();

        foreach ($friendRequests as $req) {
            if ($req->sender) {
                $dedupKey = "friend_request_{$req->sender->id}_";
                if (isset($existingNotificationKeys[$dedupKey])) {
                    continue; // منع التكرار
                }
                $existingNotificationKeys[$dedupKey] = true;

                $notificationsCollection->push([
                    'id'          => (string)$req->id,
                    'type'        => 'friend_request',
                    'title'       => 'طلب صداقة جديد',
                    'message'     => 'قام ' . $req->sender->first_name . ' ' . $req->sender->last_name . ' بإرسال طلب صداقة إليك.',
                    'description' => 'قام ' . $req->sender->first_name . ' ' . $req->sender->last_name . ' بإرسال طلب صداقة إليك.',
                    'sender_id'   => (int)$req->sender->id,
                    'sender_name' => trim($req->sender->first_name . ' ' . $req->sender->last_name),
                    'avatar'      => $formatAvatar($req->sender->profile_picture),
                    'post_id'     => null,
                    'created_at'  => $req->created_at
                ]);
            }
        }

        // دـ. جلب إشعارات الإعجابات (فقط في حال لم تكن مسجلة مسبقاً)
        $postLikes = Reaction::with(['user', 'post'])
            ->where('content_type_id', 1)
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postLikes as $like) {
            if ($like->user && $like->post) {
                $dedupKey = "like_{$like->user->id}_{$like->post->id}";
                if (isset($existingNotificationKeys[$dedupKey])) {
                    continue; // منع التكرار
                }
                $existingNotificationKeys[$dedupKey] = true;

                $snippet = \Illuminate\Support\Str::limit(strip_tags($like->post->content), 35) ?: 'منشورك';
                $notificationsCollection->push([
                    'id'          => (string)$like->id,
                    'type'        => 'like',
                    'title'       => 'تفاعل جديد',
                    'message'     => 'قام ' . $like->user->first_name . ' بالإعجاب بموضوعك: "' . $snippet . '"',
                    'description' => 'قام ' . $like->user->first_name . ' بالإعجاب بموضوعك: "' . $snippet . '"',
                    'sender_id'   => (int)$like->user->id,
                    'sender_name' => trim($like->user->first_name . ' ' . $like->user->last_name),
                    'avatar'      => $formatAvatar($like->user->profile_picture),
                    'post_id'     => (int)$like->post->id,
                    'created_at'  => $like->created_at
                ]);
            }
        }

        // هـ. جلب إشعارات التعليقات القديمة (فقط في حال لم تكن مسجلة مسبقاً)
        $postComments = Comment::with(['user', 'post'])
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postComments as $comment) {
            if ($comment->user && $comment->post) {
                $dedupKey = "comment_{$comment->user->id}_{$comment->post->id}";
                if (isset($existingNotificationKeys[$dedupKey])) {
                    continue; // منع التكرار الجذري
                }
                $existingNotificationKeys[$dedupKey] = true;

                $snippet = \Illuminate\Support\Str::limit(strip_tags($comment->post->content), 35) ?: 'منشورك';
                $notificationsCollection->push([
                    'id'          => (string)$comment->id,
                    'type'        => 'comment',
                    'title'       => 'تعليق جديد',
                    'message'     => 'قام ' . $comment->user->first_name . ' بالتعليق على موضوعك: "' . $snippet . '"',
                    'description' => 'قام ' . $comment->user->first_name . ' بالتعليق على موضوعك: "' . $snippet . '"',
                    'sender_id'   => (int)$comment->user->id,
                    'sender_name' => trim($comment->user->first_name . ' ' . $comment->user->last_name),
                    'avatar'      => $formatAvatar($comment->user->profile_picture),
                    'post_id'     => (int)$comment->post->id,
                    'created_at'  => $comment->created_at
                ]);
            }
        }

        // و. رصد الفلترة والمطابقة مع جدول الـ seen لمعرفة حالة القراءة
        $seenItems = Seen::where('user_id', $currentUser->id)->get();

        $finalData = $notificationsCollection->map(function ($item) use ($seenItems) {
            $isSeen = false;
            if (isset($item['is_seen'])) {
                $isSeen = (bool)$item['is_seen'];
            } elseif (isset($item['read_at'])) {
                $isSeen = !is_null($item['read_at']);
            } else {
                $enumType = $item['type'] === 'like' ? 'post_like' : ($item['type'] === 'comment' ? 'post_comment' : 'friend_request');
                $isSeen = $seenItems->where('notification_id', (string)$item['id'])
                                    ->where('notification_type', $enumType)
                                    ->isNotEmpty();
            }

            $carbonDate = $item['created_at'] instanceof \Carbon\Carbon
                ? $item['created_at']
                : \Carbon\Carbon::parse($item['created_at']);

            return [
                'id'              => (string)$item['id'],
                'type'            => $item['type'],
                'title'           => $item['title'],
                'message'         => $item['message'],
                'description'     => $item['description'] ?? $item['message'],
                'link'            => $item['link'] ?? $item['url'] ?? null,
                'url'             => $item['link'] ?? $item['url'] ?? null,
                'sender_id'       => $item['sender_id'] ?? null,
                'user_id'         => $item['sender_id'] ?? null,
                'sender_name'     => $item['sender_name'] ?? '',
                'avatar'          => $item['avatar'] ?? null,
                'profile_picture' => $item['avatar'] ?? null,
                'post_id'         => $item['post_id'] ?? null,
                'created_at'      => $carbonDate->toDateTimeString(),
                'time_ago'        => $carbonDate->diffForHumans(),
                'is_seen'         => $isSeen,
                'is_read'         => $isSeen
            ];
        })->sortByDesc('created_at')->values();

        // حساب عدد الإشعارات غير المقروءة بدقة
        $unseenCount = $finalData->where('is_seen', false)->count();

        // تطبيق الـ Pagination (Limit & Offset)
        $paginatedData = $finalData->slice($offset, $limit)->values();

        return response()->json([
            'success'       => true,
            'unseen_count'  => $unseenCount,
            'unread_count'  => $unseenCount,
            'notifications' => $paginatedData,
            'data'          => $paginatedData
        ]);
    }

    /**
     * 6.2 تعيين الإشعار كمقروء
     */
    public function markSeen(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        if ($request->input('mark_all') == true) {
            // تحديث إشعارات الإدارة في جدول notification_for_apps
            try {
                NotificationForApp::where(function ($q) use ($currentUser) {
                    $q->where('user_id', $currentUser->id)
                      ->orWhereNull('user_id')
                      ->orWhere('user_id', 0);
                })->update(['user_view' => 'yes']);
            } catch (\Throwable $e) {}

            // تحديث الإشعارات في جدول notifications
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $currentUser->id)
                ->update(['read_at' => now()]);

            // تحديث إشعارات الإعجابات
            $postLikes = Reaction::where('content_type_id', 1)
                ->where('is_active', 1)
                ->where('user_id', '!=', $currentUser->id)
                ->whereHas('post', fn($q) => $q->where('user_id', $currentUser->id))
                ->pluck('id');
            foreach ($postLikes as $lId) {
                Seen::firstOrCreate([
                    'user_id'           => $currentUser->id,
                    'notification_id'   => (string)$lId,
                    'notification_type' => 'post_like'
                ], ['seen_at' => now()]);
            }

            // تحديث إشعارات التعليقات
            $postComments = Comment::where('is_active', 1)
                ->where('user_id', '!=', $currentUser->id)
                ->whereHas('post', fn($q) => $q->where('user_id', $currentUser->id))
                ->pluck('id');
            foreach ($postComments as $cId) {
                Seen::firstOrCreate([
                    'user_id'           => $currentUser->id,
                    'notification_id'   => (string)$cId,
                    'notification_type' => 'post_comment'
                ], ['seen_at' => now()]);
            }

            // تحديث طلبات الصداقة
            $friendRequests = Friendship::where('receiver_id', $currentUser->id)
                ->where('is_active', 0)
                ->pluck('id');
            foreach ($friendRequests as $fId) {
                Seen::firstOrCreate([
                    'user_id'           => $currentUser->id,
                    'notification_id'   => (string)$fId,
                    'notification_type' => 'friend_request'
                ], ['seen_at' => now()]);
            }

            return response()->json(['success' => true, 'message' => 'All notifications marked as seen']);
        }

        $validator = Validator::make($request->all(), [
            'notification_id'   => 'required|string',
            'notification_type' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $notifId = (string)$request->notification_id;

        if (str_starts_with($notifId, 'admin_') || $request->notification_type === 'admin') {
            $rawId = str_replace('admin_', '', $notifId);
            try {
                NotificationForApp::where('id', $rawId)
                    ->where(function ($q) use ($currentUser) {
                        $q->where('user_id', $currentUser->id)
                          ->orWhereNull('user_id')
                          ->orWhere('user_id', 0);
                    })->update(['user_view' => 'yes']);
            } catch (\Throwable $e) {}

            return response()->json(['success' => true, 'message' => 'Admin notification marked as seen']);
        }

        if (\Illuminate\Support\Str::isUuid($notifId)) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('id', $notifId)
                ->update(['read_at' => now()]);
        } else {
            $enumType = 'friend_request';
            if ($request->notification_type === 'like') $enumType = 'post_like';
            if ($request->notification_type === 'comment') $enumType = 'post_comment';

            Seen::updateOrCreate([
                'user_id'           => $currentUser->id,
                'notification_id'   => (string)$notifId,
                'notification_type' => $enumType
            ], [
                'seen_at'           => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as seen'
        ]);
    }

    
    
    /**
     * توليد تنويعات الكلمات العربية (ى/ي، أ/إ/آ/ا، ة/ه) لتحسين دقة البحث
     */
    private function getArabicVariations($query)
    {
        $variations = [$query];
        
        if (str_contains($query, "ى") || str_contains($query, "ي")) {
            $variations[] = str_replace("ى", "ي", $query);
            $variations[] = str_replace("ي", "ى", $query);
        }
        
        if (str_contains($query, "أ") || str_contains($query, "إ") || str_contains($query, "آ") || str_contains($query, "ا")) {
            $normalized = str_replace(["أ", "إ", "آ"], "ا", $query);
            $variations[] = $normalized;
            $variations[] = str_replace("ا", "أ", $normalized);
            $variations[] = str_replace("ا", "إ", $normalized);
        }
        
        if (str_contains($query, "ة") || str_contains($query, "ه")) {
            $variations[] = str_replace("ة", "ه", $query);
            $variations[] = str_replace("ه", "ة", $query);
        }

        return array_unique($variations);
    }

    /**
     * 6.3 محرك البحث الشامل عن المستخدمين والمنشورات الاحترافي
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "search_term" => "required|string|min:1",
            "search_type" => "required|string|in:all,users,posts"
        ]);

        if ($validator->fails()) {
            return response()->json(["success" => false, "message" => $validator->errors()->first()], 422);
        }

        $term = trim($request->search_term);
        $type = $request->search_type;

        $currentUser = $request->user();
        if (!$currentUser && $request->input("user_id")) {
            $currentUser = User::find($request->input("user_id"));
        }
        $currentUserId = $currentUser ? (int)$currentUser->id : 0;

        $formatAvatar = function ($raw) {
            if (empty($raw) || $raw === "non") {
                return asset("images/default_profile.png");
            }
            if (filter_var($raw, FILTER_VALIDATE_URL)) {
                return $raw;
            }
            return asset("new_wiselook/uploads/" . $raw);
        };

        $variations = $this->getArabicVariations($term);

        $usersResult = [];
        $postsResult = [];

        // أ. البحث الشامل في المستخدمين
        if ($type === "all" || $type === "users") {
            $users = User::where(function ($q) use ($variations) {
                    foreach ($variations as $var) {
                        $searchVar = "%" . $var . "%";
                        $q->orWhere("first_name", "LIKE", $searchVar)
                          ->orWhere("last_name", "LIKE", $searchVar)
                          ->orWhere("email", "LIKE", $searchVar)
                          ->orWhere("phone_number", "LIKE", $searchVar)
                          ->orWhere("bio", "LIKE", $searchVar)
                          ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), "LIKE", $searchVar);
                    }
                })
                ->orderBy("first_name", "asc")
                ->limit(40)
                ->get();

            $usersResult = $users->map(function ($user) use ($formatAvatar) {
                $rankName = "مستشار تقني";
                if ($user->rank) {
                    $rankName = $user->rank->rank_name;
                } elseif ($user->role === "admin") {
                    $rankName = "مدير المنصة";
                }

                return [
                    "user_id"         => (int)$user->id,
                    "id"              => (int)$user->id,
                    "first_name"      => $user->first_name ?? "",
                    "last_name"       => $user->last_name ?? "",
                    "full_name"       => trim(($user->first_name ?? "") . " " . ($user->last_name ?? "")),
                    "username"        => $user->email ?? "",
                    "email"           => $user->email ?? "",
                    "profile_picture" => $formatAvatar($user->profile_picture),
                    "user_points"     => (int)($user->points ?? 0),
                    "user_rank_name" => $rankName,
                ];
            })->values()->toArray();
        }

        // ب. البحث الشامل في المنشورات
        if ($type === "all" || $type === "posts") {
            $posts = Post::with(["user", "media", "poll.options", "reactions"])
                ->where("is_active", 1)
                ->where(function ($q) use ($variations) {
                    foreach ($variations as $var) {
                        $searchVar = "%" . $var . "%";
                        $q->orWhere("content", "LIKE", $searchVar)
                          ->orWhereHas("poll", function ($pq) use ($searchVar) {
                              $pq->where("question", "LIKE", $searchVar);
                          })
                          ->orWhereHas("user", function ($uq) use ($searchVar) {
                              $uq->where("first_name", "LIKE", $searchVar)
                                 ->orWhere("last_name", "LIKE", $searchVar)
                                 ->orWhere(\Illuminate\Support\Facades\DB::raw("CONCAT(first_name, ' ', last_name)"), "LIKE", $searchVar);
                          });
                    }
                })
                ->orderBy("created_at", "desc")
                ->limit(40)
                ->get();

            $mapPost = function ($post) use ($currentUser, $currentUserId, $formatAvatar) {
                $isLiked = $currentUserId > 0 ? Reaction::where("user_id", $currentUserId)
                                   ->where("content_id", $post->id)
                                   ->where("content_type_id", 1)
                                   ->where("is_active", 1)
                                   ->exists() : false;

                $isSaved = $currentUserId > 0 ? \App\Models\SavedPost::where("user_id", $currentUserId)
                                    ->where("post_id", $post->id)
                                    ->exists() : false;

                $isPinned = \App\Models\PinnedPost::where("post_id", $post->id)->exists();

                $mediaArray = $post->media->map(function ($mediaItem) {
                    $path = $mediaItem->image ? asset("storage/" . $mediaItem->image) : asset("storage/" . $mediaItem->video);
                    $type = $mediaItem->image ? "image" : "video";
                    return [
                        "path" => $path,
                        "type" => $type
                    ];
                })->toArray();

                if (empty($mediaArray)) {
                    if (!empty($post->image)) {
                        $mediaArray[] = [
                            "path" => asset("new_wiselook/uploads/" . $post->image),
                            "type" => "image"
                        ];
                    } elseif (!empty($post->video)) {
                        $mediaArray[] = [
                            "path" => asset("new_wiselook/uploads/" . $post->video),
                            "type" => "video"
                        ];
                    }
                }

                $question = null;
                $expiresAt = null;
                $totalVotes = null;
                $options = [];
                $selectedOptionId = null;

                if ($post->poll) {
                    $question = $post->poll->question;
                    $expiresAt = $post->poll->expires_at ? $post->poll->expires_at->toDateTimeString() : null;
                    $totalVotes = (int)$post->poll->total_votes;

                    $options = $post->poll->options->map(function ($opt) use ($currentUserId, &$selectedOptionId) {
                        $isSelected = $currentUserId > 0 ? \App\Models\PollResponse::where("poll_option_id", $opt->id)
                                                  ->where("user_id", $currentUserId)
                                                  ->exists() : false;
                        if ($isSelected) {
                            $selectedOptionId = (int)$opt->id;
                        }
                        return [
                            "id"            => (int)$opt->id,
                            "content"       => $opt->content,
                            "vote_count"    => (int)$opt->vote_count,
                            "is_selected"   => $isSelected ? 1 : 0,
                            "recent_voters" => []
                        ];
                    })->toArray();
                }

                $userRankName = null;
                $userRankIcon = null;
                $userPoints = 0;

                if ($post->user) {
                    $userPoints = (int)($post->user->points ?? 0);
                    $rank = $post->user->rank;
                    if ($rank) {
                        $userRankName = $rank->rank_name;
                        if (!empty($rank->photo)) {
                            $userRankIcon = asset("upload/rankings/" . $rank->photo);
                        }
                    } else {
                        $userRankName = ($post->user->role == "admin") ? "مدير المنصة" : "مستشار تقني";
                    }
                }

                $wiseRating = null;
                if ($post->wise_rating !== null && $post->wise_rating !== "") {
                    $wiseRating = (float)$post->wise_rating;
                }

                return [
                    "post_id"            => (int)$post->id,
                    "user_id"            => (int)$post->user_id,
                    "content"            => $post->content ?? "",
                    "selected_option_id" => $selectedOptionId,
                    "like_count"         => (int)$post->like_count,
                    "comment_count"      => Comment::where("post_id", $post->id)->where("is_active", 1)->count(),
                    "share_count"        => (int)$post->share_count,
                    "first_name"         => $post->user->first_name ?? "",
                    "last_name"          => $post->user->last_name ?? "",
                    "time_ago"           => $post->created_at ? $post->created_at->diffForHumans() : "",
                    "profile_picture"    => $formatAvatar($post->user ? $post->user->profile_picture : null),
                    "post_type_id"       => $post->poll ? 2 : 1,
                    "media"              => $mediaArray,
                    "question"           => $question,
                    "expires_at"         => $expiresAt,
                    "options"            => $options,
                    "is_reacted"         => $isLiked ? 1 : 0,
                    "is_liked"           => (bool)$isLiked,
                    "is_pinned"          => $isPinned ? 1 : 0,
                    "is_saved"           => $isSaved ? 1 : 0,
                    "current_image_index"=> 0,
                    "total_votes"        => $totalVotes,
                    "parent_id"          => (int)$post->parent_id,
                    "mentions"           => [],
                    "user_rank_name"     => $userRankName,
                    "user_rank_icon"     => $userRankIcon,
                    "user_points"        => $userPoints,
                    "wise_rating"        => $wiseRating
                ];
            };

            $postsResult = $posts->map(function ($post) use ($mapPost) {
                $mapped = $mapPost($post);
                if ($post->parent_id > 0) {
                    $originalPost = Post::with(["user", "media", "poll.options"])->find($post->parent_id);
                    if ($originalPost) {
                        $mapped["original_post"] = $mapPost($originalPost);
                    }
                }
                return $mapped;
            })->values()->toArray();
        }

        return response()->json([
            "success" => true,
            "users"   => $usersResult,
            "posts"   => $postsResult
        ]);
    }



    /**
     * 6.4 جلب اللغات المفعلة للنظام مع الأعلام والاتجاه
     */
    public function languages()
    {
        $languages = Language::where('is_active', 1)
            ->orderBy('is_default', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($lang) {
                $flagUrl = null;
                $flagEmoji = $lang->flag_path;

                if ($lang->flag_path && !preg_match('/[\x{1F1E6}-\x{1F1FF}]/u', $lang->flag_path)) {
                    // إذا كان مسار صورة
                    if (filter_var($lang->flag_path, FILTER_VALIDATE_URL)) {
                        $flagUrl = $lang->flag_path;
                    } else {
                        $flagUrl = asset('upload/flags/' . $lang->flag_path);
                    }
                }

                // أعلام افتراضية إن لم تكن متوفرة
                if (empty($flagEmoji)) {
                    $flagEmoji = ($lang->code === 'ar') ? '🇸🇦' : '🇬🇧';
                }

                return [
                    'id'         => (int)$lang->id,
                    'name'       => $lang->name,
                    'code'       => strtolower($lang->code),
                    'flag_path'  => $flagEmoji,
                    'flag_url'   => $flagUrl,
                    'direction'  => $lang->direction ?: ($lang->code === 'ar' ? 'rtl' : 'ltr'),
                    'is_default' => (bool)$lang->is_default,
                    'is_active'  => (bool)$lang->is_active,
                ];
            });

        return response()->json([
            'success'   => true,
            'languages' => $languages
        ]);
    }

    /**
     * 6.5 جلب القاموس والترجمات للغة المحددة
     */
    public function dictionary(Request $request)
    {
        $langCode = strtolower($request->input('lang', 'ar'));
        $langId = $request->input('language_id');

        $language = null;
        if ($langId) {
            $language = Language::find($langId);
        }
        if (!$language && $langCode) {
            $language = Language::where('code', $langCode)->first();
        }
        if (!$language) {
            $language = Language::where('is_default', 1)->first() ?? Language::first();
        }

        $dictionary = [];

        // 1. جلب كافة الترجمات من جدول translations التابع للغة
        if ($language) {
            $translations = Translation::where('language_id', $language->id)->get();
            foreach ($translations as $tr) {
                if (!empty($tr->key)) {
                    $dictionary[$tr->key] = $tr->value;
                }
            }
        }

        // 2. دمج مع جدول Dictionary القديم كـ Fallback لتغطية أي مفاتيح غير مترجمة
        if (in_array($langCode, ['ar', 'en'])) {
            try {
                $legacyItems = Dictionary::select('key', $langCode)->get();
                foreach ($legacyItems as $item) {
                    if (!isset($dictionary[$item->key]) && !empty($item->$langCode)) {
                        $dictionary[$item->key] = $item->$langCode;
                    }
                }
            } catch (\Exception $e) {}
        }

        return response()->json([
            'success'    => true,
            'language'   => $language ? [
                'id'        => (int)$language->id,
                'name'      => $language->name,
                'code'      => $language->code,
                'flag_path' => $language->flag_path,
                'direction' => $language->direction,
            ] : null,
            'dictionary' => (object)$dictionary
        ]);
    }

    /**
     * 6.3 حذف إشعار محدد أو حذف كافة الإشعارات
     */
    public function deleteNotification(Request $request)
    {
        $currentUser = $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }

        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $notificationId = $request->input('notification_id') ?? $request->input('id');
        $deleteAll = $request->boolean('delete_all') || $notificationId === 'all';

        if ($deleteAll) {
            try {
                NotificationForApp::where('user_id', $currentUser->id)->delete();
            } catch (\Throwable $e) {}

            // حذف كل إشعارات المستخدم في جدول notifications
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', 'App\\Models\\User')
                ->where('notifiable_id', $currentUser->id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف جميع الإشعارات بنجاح'
            ]);
        }

        if (!$notificationId) {
            return response()->json(['success' => false, 'message' => 'Notification ID is required'], 422);
        }

        if (str_starts_with((string)$notificationId, 'admin_') || $request->input('type') === 'admin') {
            $rawId = str_replace('admin_', '', (string)$notificationId);
            try {
                NotificationForApp::where('id', $rawId)
                    ->where('user_id', $currentUser->id)
                    ->delete();
            } catch (\Throwable $e) {}

            return response()->json(['success' => true, 'message' => 'تم حذف الإشعار بنجاح']);
        }

        // إذا كان UUID في جدول notifications
        $deleted = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('id', $notificationId)
            ->where('notifiable_id', $currentUser->id)
            ->delete();

        // في حال كان إشعاراً ناتجاً عن تفاعل (Seen/Reaction/Friendship/Comment)
        if (!$deleted) {
            $type = $request->input('notification_type') ?? $request->input('type');
            if ($type) {
                $enumType = $type === 'like' ? 'post_like' : ($type === 'comment' ? 'post_comment' : 'friend_request');
                Seen::updateOrCreate([
                    'user_id'           => $currentUser->id,
                    'notification_id'   => (string)$notificationId,
                    'notification_type' => $enumType,
                ], [
                    'seen_at' => now(),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار بنجاح'
        ]);
    }

    /**
     * 6.4 جلب صفحات الشروط والأحكام والسياسات
     */
    public function pages(Request $request)
    {
        $id = $request->input('id', 1);
        $lang = $request->input('lang', 'ar');

        $page = \App\Models\Page::find($id);

        if (!$page) {
            $isAr = ($lang === 'ar');
            $title = $isAr ? 'شروط وأحكام الاستخدام' : 'Terms & Conditions';
            $content = $isAr
                ? '<h3>أهلاً بك في منصة مجلس الحكماء (Wiselook)</h3>'
                . '<p>باستخدامك لتطبيق ومنصة مجلس الحكماء، فإنك توافق على الالتزام بالشروط والأحكام التالية:</p>'
                . '<h4>1. شروط الحساب والتسجيل</h4>'
                . '<p>يجب تقديم معلومات صحيحة ودقيقة عند إنشاء الحساب، وتتحمل المسؤولية الكاملة عن سرية بيانات حسابك ونشاطك داخل المنصة.</p>'
                . '<h4>2. قواعد النشر والسلوك المجتمعي</h4>'
                . '<p>يمنع نشر أو تداول أي محتوى مسيء، غير لائق، ينتهك حقوق الملكية الفكرية، أو يخالف القوانين والآداب العامة.</p>'
                . '<h4>3. الخصوصية وحماية البيانات</h4>'
                . '<p>نلتزم بحماية خصوصيتك وبياناتك الشخصية وتشفير كلمات المرور وفق أعلى معايير الأمان المتبعة.</p>'
                . '<h4>4. حقوق الملكية الفكرية</h4>'
                . '<p>جميع الحقوق الفكرية والتصميمات والشعارات الخاصة بمنصة Wiselook محفوظة بالكامل.</p>'
                . '<p style="margin-top:15px; color:#0F7A4D; font-weight:bold;">للمزيد من الاستفسارات يرجى التواصل مع الدعم الفني: support@worldwisepeople.net</p>'
                : '<h3>Welcome to Wiselook Platform</h3>'
                . '<p>By accessing or using Wiselook, you agree to be bound by these Terms and Conditions.</p>'
                . '<h4>1. Account Terms</h4>'
                . '<p>You must provide accurate and complete information during registration and maintain account confidentiality.</p>'
                . '<h4>2. Community Guidelines</h4>'
                . '<p>You must not post offensive, abusive, or infringing content.</p>'
                . '<h4>3. Privacy</h4>'
                . '<p>Your privacy is protected in accordance with our Privacy Policy.</p>';

            return response()->json([
                'success' => true,
                'data' => [
                    'id'      => $id,
                    'title'   => $title,
                    'content' => $content,
                ]
            ]);
        }

        $title = ($lang === 'ar' && !empty($page->title_ar)) ? $page->title_ar : $page->title;
        $content = ($lang === 'ar' && !empty($page->content_ar)) ? $page->content_ar : $page->content;

        return response()->json([
            'success' => true,
            'data' => [
                'id'      => $page->id,
                'title'   => $title,
                'content' => $content,
            ]
        ]);
    }
}
