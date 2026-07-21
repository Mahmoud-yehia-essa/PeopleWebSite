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

class MiscApiController extends Controller
{
    /**
     * 6.1 جلب الإشعارات العامة ديناميكياً
     */
    public function listNotifications(Request $request)
    {
        $currentUser = $request->user();
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $notificationsCollection = collect();

        // أ. جلب إشعارات طلبات الصداقة المعلقة الواردة للمستخدم
        $friendRequests = Friendship::with('sender')
            ->where('receiver_id', $currentUser->id)
            ->where('is_active', 0)
            ->get();

        foreach ($friendRequests as $req) {
            $notificationsCollection->push([
                'id'         => $req->id,
                'type'       => 'friend_request',
                'title'      => 'طلب صداقة جديد',
                'message'    => 'قام ' . $req->sender->first_name . ' ' . $req->sender->last_name . ' بإرسال طلب صداقة إليك.',
                'created_at' => $req->created_at
            ]);
        }

        // ب. جلب إشعارات الإعجابات على منشورات المستخدم الحالي
        $postLikes = Reaction::with(['user', 'post'])
            ->where('content_type_id', 1) // 1 تعني منشور
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postLikes as $like) {
            $notificationsCollection->push([
                'id'         => $like->id,
                'type'       => 'like',
                'title'      => 'تفاعل جديد',
                'message'    => 'قام ' . $like->user->first_name . ' بالإعجاب بمنشورك.',
                'created_at' => $like->created_at
            ]);
        }

        // جـ. جلب إشعارات التعليقات على منشورات المستخدم الحالي
        $postComments = Comment::with(['user', 'post'])
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postComments as $comment) {
            $notificationsCollection->push([
                'id'         => $comment->id,
                'type'       => 'comment',
                'title'      => 'تعليق جديد',
                'message'    => 'قام ' . $comment->user->first_name . ' بالتعليق على منشورك.',
                'created_at' => $comment->created_at
            ]);
        }

        // دـ. جلب الإشعارات المخصصة بجدول notifications
        $dbNotifications = \Illuminate\Support\Facades\DB::table('notifications')
            ->where('notifiable_type', 'App\Models\User')
            ->where('notifiable_id', $currentUser->id)
            ->get();

        foreach ($dbNotifications as $notif) {
            $data = json_decode($notif->data, true);
            if ($data) {
                $notificationsCollection->push([
                    'id'         => $notif->id,
                    'type'       => $data['type'] ?? 'general',
                    'title'      => $data['type'] === 'friend_accept' ? 'قبول طلب صداقة' : ($data['type'] === 'mention' ? 'إشارة إليك' : 'رد جديد'),
                    'message'    => $data['message'] ?? '',
                    'created_at' => \Carbon\Carbon::parse($notif->created_at),
                    'read_at'    => $notif->read_at
                ]);
            }
        }

        // رصد الفلترة والمطابقة مع جدول الـ seen لمعرفة حالة القراءة
        $seenItems = Seen::where('user_id', $currentUser->id)->get();

        $finalData = $notificationsCollection->map(function ($item) use ($seenItems) {
            if (isset($item['read_at'])) {
                return [
                    'id'         => $item['id'], // keep string/uuid as is
                    'type'       => $item['type'],
                    'title'      => $item['title'],
                    'message'    => $item['message'],
                    'created_at' => $item['created_at'] ? $item['created_at']->toDateTimeString() : now()->toDateTimeString(),
                    'is_seen'    => !is_null($item['read_at'])
                ];
            }

            // تحويل النوع البرمجي للنوع المخزن في جدول الـ Seen Enum
            $enumType = $item['type'] === 'like' ? 'post_like' : ($item['type'] === 'comment' ? 'post_comment' : 'friend_request');
            
            $isSeen = $seenItems->where('notification_id', $item['id'])
                                ->where('notification_type', $enumType)
                                ->isNotEmpty();

            return [
                'id'         => (int)$item['id'],
                'type'       => $item['type'],
                'title'      => $item['title'],
                'message'    => $item['message'],
                'created_at' => $item['created_at'] ? $item['created_at']->toDateTimeString() : now()->toDateTimeString(),
                'is_seen'    => $isSeen
            ];
        })->sortByDesc('created_at')->values();

        // حساب عدد الإشعارات غير المقروءة بدقة
        $unseenCount = $finalData->where('is_seen', false)->count();

        // تطبيق الـ Pagination (Limit & Offset)
        $paginatedData = $finalData->slice($offset, $limit)->values();

        return response()->json([
            'success'      => true,
            'unseen_count' => $unseenCount,
            'data'         => $paginatedData
        ]);
    }

    /**
     * 6.2 تعيين الإشعار كمقروء
     */
    public function markSeen(Request $request)
    {
        $currentUser = $request->user();

        if ($request->input('mark_all') == true) {
            // تحديث الإشعارات في جدول notifications
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('notifiable_type', 'App\Models\User')
                ->where('notifiable_id', $currentUser->id)
                ->update(['read_at' => now()]);

            return response()->json(['success' => true, 'message' => 'Notifications updated']);
        }

        $validator = Validator::make($request->all(), [
            'notification_id'   => 'required|string',
            'notification_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if (\Illuminate\Support\Str::isUuid($request->notification_id)) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('id', $request->notification_id)
                ->update(['read_at' => now()]);
        } else {
            // تحويل مسمى الـ Request الـ Native إلى الـ Enum المعتمد بقاعدة بياناتك الصارمة
            $enumType = 'friend_request';
            if ($request->notification_type === 'like') $enumType = 'post_like';
            if ($request->notification_type === 'comment') $enumType = 'post_comment';

            // الحفظ الفوري بجدول seen الصارم لمنع تكرار ظهور الإشعار كـ غير مقروء
            Seen::updateOrCreate([
                'user_id'           => $currentUser->id,
                'notification_id'   => (string)$request->notification_id,
                'notification_type' => $enumType
            ], [
                'seen_at'           => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications updated'
        ]);
    }

    /**
     * 6.3 محرك البحث الشامل عن المستخدمين والمنشورات
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search_term' => 'required|string|min:1',
            'search_type' => 'required|string|in:all,users,posts'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $term = $request->search_term;
        $type = $request->search_type;

        $usersResult = [];
        $postsResult = [];

        // أ. البحث في المستخدمين (الاسم الأول أو العائلة)
        if ($type === 'all' || $type === 'users') {
            $usersResult = User::where('first_name', 'LIKE', "%{$term}%")
                ->orWhere('last_name', 'LIKE', "%{$term}%")
                ->get()
                ->map(function ($user) {
                    return [
                        'id'              => (int)$user->id,
                        'first_name'      => $user->first_name,
                        'last_name'       => $user->last_name,
                        'profile_picture' => $user->profile_picture
                    ];
                });
        }

        // ب. البحث في نصوص ومحتويات المنشورات النشطة
        if ($type === 'all' || $type === 'posts') {
            $postsResult = Post::where('content', 'LIKE', "%{$term}%")
                ->where('is_active', 1)
                ->get()
                ->map(function ($post) {
                    return [
                        'id'      => (int)$post->id,
                        'content' => $post->content ?? ''
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'users'   => $usersResult,
            'posts'   => $postsResult
        ]);
    }

    /**
     * 6.4 جلب القاموس والترجمات
     */
    public function dictionary(Request $request)
    {
        $lang = $request->input('lang', 'ar');
        if (!in_array($lang, ['ar', 'en'])) {
            $lang = 'ar';
        }

        $dictionaryItems = Dictionary::select('key', $lang)->get();

        $dictionary = [];
        foreach ($dictionaryItems as $item) {
            $dictionary[$item->key] = $item->$lang ?? $item->key;
        }

        return response()->json([
            'success' => true,
            'dictionary' => (object)$dictionary
        ]);
    }
}