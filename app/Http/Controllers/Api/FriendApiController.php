<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Friendship;
use App\Models\User;
use App\Models\Block;

class FriendApiController extends Controller
{
    /**
     * 5.1 جلب قائمة الأصدقاء أو طلبات الصداقة المعلقة
     */
    public function listFriends(Request $request)
    {
        $currentUser = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser && $request->input('sender_id')) {
            $currentUser = User::find($request->input('sender_id'));
        }
        
        // دعم استقبال المدخلات الهجينة تماشياً مع كود الـ Native القديم
        $targetUserId = $request->input('person_id', $request->input('id', $request->input('user_id', $currentUser ? $currentUser->id : null)));
        $isActive = $request->input('is_active', 1); // 1 للأصدقاء المقبولين، 0 للطلبات المعلقة
        $filterType = $request->input('filter_type'); // 'sent' لمعرفة الطلبات المعلقة التي أرسلتها أنا
        
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $userIds = [];

        if ($isActive == 1) {
            // أصدقاء حقيقيين ونشطين (تجميع الطرف الآخر في العلاقة)
            $friendships = Friendship::where('is_active', 1)
                ->where(function($q) use ($targetUserId) {
                    $q->where('sender_id', $targetUserId)
                      ->orWhere('receiver_id', $targetUserId);
                })
                ->skip($offset)
                ->take($limit)
                ->get();

            foreach ($friendships as $f) {
                $userIds[] = ($f->sender_id == $targetUserId) ? $f->receiver_id : $f->sender_id;
            }
        } else {
            // طلبات الصداقة المعلقة والمرفوضة قبل القبول
            if ($filterType === 'sent') {
                // الطلبات الصادرة مني للشخص الآخر ولم يُجب عليها بعد
                $userIds = Friendship::where('sender_id', $targetUserId)
                    ->where('is_active', 0)
                    ->skip($offset)
                    ->take($limit)
                    ->pluck('receiver_id')
                    ->toArray();
            } else {
                // الطلبات الواردة إلي من أشخاص آخرين وتنتظر موافقتي
                $userIds = Friendship::where('receiver_id', $targetUserId)
                    ->where('is_active', 0)
                    ->skip($offset)
                    ->take($limit)
                    ->pluck('sender_id')
                    ->toArray();
            }
        }

        // جلب بيانات الحسابات المستهدفة بالـ IDs المستخرجة
        $users = User::whereIn('id', $userIds)->get();

        // صياغة المخرجات بدقة لمطابقة واجهة تطبيق الموبايل
        $formattedData = $users->map(function($user) use ($currentUser) {
            // فحص هل هذا الحساب يعتبر صديقاً فعلياً للمستخدم الحالي (Bearer Token)
            $isFriendCheck = Friendship::where('is_active', 1)
                ->where(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $user->id);
                })->orWhere(function($q) use ($currentUser, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $currentUser->id);
                })->exists();

            return [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'profile_picture' => $user->profile_picture,
                'isFriend'        => $isFriendCheck,
                'type'            => 'friend'
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $formattedData
        ]);
    }

    /**
     * 5.2 محرك إجراءات الصداقة الموحد
     */
    public function friendAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'        => 'required|string|in:add,accept,confirm,reject,remove,cancel,block,unfriend',
            'receiver_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser && $request->input('sender_id')) {
            $currentUser = User::find($request->input('sender_id'));
        }
        if (!$currentUser) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $receiverId = (int)$request->receiver_id;

        // جودة برمجية: منع المستخدم من التفاعل الاجتماعي مع نفسه
        if ($currentUser->id == $receiverId) {
            return response()->json(['success' => false, 'message' => 'Cannot perform social actions on yourself'], 400);
        }

        switch ($request->type) {
            case 'add':
                // إرسال طلب صداقة جديد (التأكد من عدم وجود علاقة قائمة مسبقاً)
                $exists = Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->first();

                if ($exists) {
                    return response()->json(['success' => true, 'message' => 'Friendship relation or request already exists']);
                }

                Friendship::create([
                    'sender_id'   => $currentUser->id,
                    'receiver_id' => $receiverId,
                    'is_active'   => 0 // 0 تعني طلب معلق قيد الانتظار
                ]);

                $notifId = \Illuminate\Support\Str::uuid()->toString();
                $msg = 'قام ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' بإرسال طلب صداقة إليك.';
                $avatarFormatted = $currentUser->profile_picture ? (filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL) ? $currentUser->profile_picture : asset('new_wiselook/uploads/' . $currentUser->profile_picture)) : null;

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => $notifId,
                    'type' => 'App\\Notifications\\GeneralNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $receiverId,
                    'data' => json_encode([
                        'type' => 'friend_request',
                        'sender_id' => $currentUser->id,
                        'sender_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
                        'avatar' => $currentUser->profile_picture,
                        'message' => $msg,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                try {
                    broadcast(new \App\Events\NotificationSent($receiverId, [
                        'id'          => $notifId,
                        'type'        => 'friend_request',
                        'title'       => 'طلب صداقة جديد',
                        'message'     => $msg,
                        'sender_id'   => (int)$currentUser->id,
                        'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        'avatar'      => $avatarFormatted,
                        'post_id'     => null,
                        'created_at'  => now()->toDateTimeString(),
                        'time_ago'    => 'الآن',
                        'is_seen'     => false,
                    ]));
                } catch (\Exception $e) {}

                try {
                    app(\App\Services\FcmNotificationService::class)->sendFriendRequestNotification(
                        $receiverId,
                        trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        (int)$currentUser->id
                    );
                } catch (\Throwable $e) {}
                break;

            case 'accept':
            case 'confirm':
                // قبول طلب صداقة وارد إليك (أو تفعيل العلاقة القائمة)
                $friendship = Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->first();

                if (!$friendship) {
                    Friendship::create([
                        'sender_id'   => $receiverId,
                        'receiver_id' => $currentUser->id,
                        'is_active'   => 1
                    ]);
                    User::where('id', $currentUser->id)->increment('friend_count');
                    User::where('id', $receiverId)->increment('friend_count');
                } else if ($friendship->is_active == 0) {
                    $friendship->update(['is_active' => 1]);
                    User::where('id', $currentUser->id)->increment('friend_count');
                    User::where('id', $receiverId)->increment('friend_count');
                }

                // تنظيف إشعار طلب الصداقة من جدول الإشعارات
                \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('notifiable_id', $currentUser->id)
                    ->where('data->type', 'friend_request')
                    ->where('data->sender_id', $receiverId)
                    ->delete();

                $acceptNotifId = \Illuminate\Support\Str::uuid()->toString();
                $acceptMsg = 'وافق ' . $currentUser->first_name . ' ' . $currentUser->last_name . ' على طلب الصداقة الخاص بك.';
                $avatarFormatted = $currentUser->profile_picture ? (filter_var($currentUser->profile_picture, FILTER_VALIDATE_URL) ? $currentUser->profile_picture : asset('new_wiselook/uploads/' . $currentUser->profile_picture)) : null;

                // إضافة إشعار قبول طلب الصداقة للمرسل
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => $acceptNotifId,
                    'type' => 'App\\Notifications\\GeneralNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $receiverId,
                    'data' => json_encode([
                        'type' => 'friend_accept',
                        'sender_id' => $currentUser->id,
                        'sender_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
                        'avatar' => $currentUser->profile_picture,
                        'message' => $acceptMsg,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                try {
                    broadcast(new \App\Events\NotificationSent($receiverId, [
                        'id'          => $acceptNotifId,
                        'type'        => 'friend_accept',
                        'title'       => 'قبول طلب صداقة',
                        'message'     => $acceptMsg,
                        'sender_id'   => (int)$currentUser->id,
                        'sender_name' => trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        'avatar'      => $avatarFormatted,
                        'post_id'     => null,
                        'created_at'  => now()->toDateTimeString(),
                        'time_ago'    => 'الآن',
                        'is_seen'     => false,
                    ]));
                } catch (\Exception $e) {}

                try {
                    app(\App\Services\FcmNotificationService::class)->sendFriendAcceptNotification(
                        $receiverId,
                        trim($currentUser->first_name . ' ' . $currentUser->last_name),
                        (int)$currentUser->id
                    );
                } catch (\Throwable $e) {}
                break;

            case 'reject':
            case 'remove':
                // رفض أو إلغاء أو حذف طلب صداقة
                Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->delete();

                // حذف إشعار طلب الصداقة من جدول الإشعارات
                \Illuminate\Support\Facades\DB::table('notifications')
                    ->where('notifiable_id', $currentUser->id)
                    ->where('data->type', 'friend_request')
                    ->where('data->sender_id', $receiverId)
                    ->delete();
                break;

            case 'cancel':
                // إلغاء طلب صداقة مرسل من المستخدم الحالي للطرف الآخر
                Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->where('is_active', 0)->delete();
                break;
            case 'unfriend':
                // إنهاء وإلغاء صداقة نشطة قائمة بين طرفين
                $friendship = Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->where('is_active', 1)->first();

                if (!$friendship) {
                    return response()->json(['success' => false, 'message' => 'No active friendship found to terminate'], 404);
                }

                $friendship->delete();

                // إنقاص العدادات الرقمية للأصدقاء
                User::where('id', $currentUser->id)->where('friend_count', '>', 0)->decrement('friend_count');
                User::where('id', $receiverId)->where('friend_count', '>', 0)->decrement('friend_count');
                break;

            case 'block':
                // حظر مستخدم (مسح وحذف الصداقة والطلبات القائمة فوراً إن وجدت وتثبيت الحظر بجدول block بالمفرد)
                Friendship::where(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $currentUser->id)->where('receiver_id', $receiverId);
                })->orWhere(function($q) use ($currentUser, $receiverId) {
                    $q->where('sender_id', $receiverId)->where('receiver_id', $currentUser->id);
                })->delete();

                // تسجيل قيد الحظر الصارم بجدول block المعتمد بملف قاعدة بياناتك
                Block::updateOrCreate([
                    'blocker_id' => $currentUser->id,
                    'blocked_id' => $receiverId
                ]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Friend social action processed successfully'
        ]);
    }

    /**
     * 5.3 جلب بيانات شبكتي المتكاملة (أصدقاء مقترحون، كل الأصدقاء، طلبات واردة، طلبات مرسلة، أضيفوا حديثاً)
     */
    public function myNetwork(Request $request)
    {
        $currentUser = auth('sanctum')->user() ?? $request->user('sanctum') ?? $request->user();
        if (!$currentUser && $request->input('user_id')) {
            $currentUser = User::find($request->input('user_id'));
        }
        if (!$currentUser && $request->input('sender_id')) {
            $currentUser = User::find($request->input('sender_id'));
        }

        if (!$currentUser) {
            $suggestedUsers = User::where('is_active', 1)
                ->orderBy('id', 'desc')
                ->take(15)
                ->get()
                ->map(function($u) {
                    return [
                        'id'              => (int)$u->id,
                        'name'            => trim($u->first_name . ' ' . $u->last_name),
                        'first_name'      => $u->first_name,
                        'last_name'       => $u->last_name,
                        'email'           => $u->email,
                        'profile_picture' => $u->profile_picture,
                        'points'          => (int)($u->points ?? 0),
                        'role'            => $u->role ?? 'حكيم',
                        'mutual_count'    => 0,
                        'friendship_type' => 'none',
                        'friendship_id'   => null,
                    ];
                });

            return response()->json([
                'success' => true,
                'stats'   => [
                    'total_friends'    => 0,
                    'pending_requests' => 0,
                    'sent_requests'    => 0,
                    'mutual_contacts'  => User::where('is_active', 1)->count(),
                ],
                'data'     => $suggestedUsers,
                'has_more' => false,
                'page'     => 1,
            ]);
        }

        $userId = $currentUser->id;

        // 1. الإحصائيات (Stats)
        $totalFriendsCount = Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })->count();

        $pendingRequestsCount = Friendship::where('is_active', 0)
            ->where('receiver_id', $userId)
            ->count();

        $sentRequestsCount = Friendship::where('is_active', 0)
            ->where('sender_id', $userId)
            ->count();

        $mutualContactsCount = User::where('is_active', 1)->where('id', '!=', $userId)->count();

        // 2. الفلترة والتصفح
        $filter = $request->input('filter', 'suggested'); // suggested, all, pending, sent_requests, recent
        $search = $request->input('search');
        $limit = (int)$request->input('limit', 15);
        $page = (int)$request->input('page', 1);
        $offset = ($page - 1) * $limit;
        $hasMore = false;
        $usersList = collect();

        // جلب قائمة أصدقائي الحاليين لحساب الأصدقاء المشتركين
        $myFriendships = Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })->get();

        $myFriendIds = [];
        foreach ($myFriendships as $fs) {
            $myFriendIds[] = ($fs->sender_id == $userId) ? $fs->receiver_id : $fs->sender_id;
        }

        if ($filter === 'suggested') {
            // الأصدقاء المقترحون
            $existingRelationsUserIds = Friendship::where('sender_id', $userId)
                ->orWhere('receiver_id', $userId)
                ->pluck('sender_id')
                ->merge(
                    Friendship::where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId)
                        ->pluck('receiver_id')
                )
                ->unique()
                ->toArray();

            $usersQuery = User::where('id', '!=', $userId)
                ->whereNotIn('id', $existingRelationsUserIds)
                ->where('is_active', 1);

            if (!empty($search)) {
                $usersQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $allSuggested = $usersQuery->get()->map(function($potentialUser) use ($myFriendIds) {
                $theirFriendships = Friendship::where('is_active', 1)
                    ->where(function($q) use ($potentialUser) {
                        $q->where('sender_id', $potentialUser->id)
                          ->orWhere('receiver_id', $potentialUser->id);
                    })->get();

                $theirFriendIds = [];
                foreach ($theirFriendships as $fs) {
                    $theirFriendIds[] = ($fs->sender_id == $potentialUser->id) ? $fs->receiver_id : $fs->sender_id;
                }

                $mutualFriends = array_intersect($myFriendIds, $theirFriendIds);
                $potentialUser->mutual_count = count($mutualFriends);
                $potentialUser->friendship_type = 'suggested';
                return $potentialUser;
            })->sort(function($a, $b) {
                return $b->mutual_count <=> $a->mutual_count;
            })->values();

            $totalCount = $allSuggested->count();
            $usersList = $allSuggested->slice($offset, $limit)->values();
            $hasMore = $totalCount > ($offset + $limit);

        } elseif ($filter === 'pending') {
            // طلبات الصداقة الواردة
            $friendshipsQuery = Friendship::where('is_active', 0)
                ->where('receiver_id', $userId)
                ->with('sender')
                ->latest();

            if (!empty($search)) {
                $friendshipsQuery->whereHas('sender', function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $totalCount = $friendshipsQuery->count();
            $friendships = $friendshipsQuery->offset($offset)->limit($limit)->get();

            $usersList = $friendships->map(function($f) {
                if ($f->sender) {
                    $sender = $f->sender;
                    $sender->friendship_id = $f->id;
                    $sender->friendship_type = 'pending_received';
                    return $sender;
                }
                return null;
            })->filter()->values();

            $hasMore = $totalCount > ($offset + $limit);

        } elseif ($filter === 'sent_requests') {
            // الطلبات المرسلة المعلقة
            $friendshipsQuery = Friendship::where('is_active', 0)
                ->where('sender_id', $userId)
                ->with('receiver')
                ->latest();

            if (!empty($search)) {
                $friendshipsQuery->whereHas('receiver', function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $totalCount = $friendshipsQuery->count();
            $friendships = $friendshipsQuery->offset($offset)->limit($limit)->get();

            $usersList = $friendships->map(function($f) {
                if ($f->receiver) {
                    $receiver = $f->receiver;
                    $receiver->friendship_id = $f->id;
                    $receiver->friendship_type = 'pending_sent';
                    return $receiver;
                }
                return null;
            })->filter()->values();

            $hasMore = $totalCount > ($offset + $limit);

        } else {
            // كل الأصدقاء المقبولين (all أو recent)
            $friendships = Friendship::where('is_active', 1)
                ->where(function($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })->get();

            $friendIds = [];
            $friendshipMap = [];
            foreach ($friendships as $fs) {
                $friendId = ($fs->sender_id == $userId) ? $fs->receiver_id : $fs->sender_id;
                $friendIds[] = $friendId;
                $friendshipMap[$friendId] = $fs->id;
            }

            $usersQuery = User::whereIn('id', $friendIds)->where('is_active', 1);

            if ($filter === 'recent') {
                $usersQuery->latest();
            }

            if (!empty($search)) {
                $usersQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $totalCount = $usersQuery->count();
            $usersList = $usersQuery->offset($offset)->limit($limit)->get()->map(function($u) use ($friendshipMap) {
                $u->friendship_id = $friendshipMap[$u->id] ?? null;
                $u->friendship_type = 'accepted';
                return $u;
            });

            $hasMore = $totalCount > ($offset + $limit);
        }

        // تنسيق مخرجات المستخدمين للتطبيق
        $formattedUsers = $usersList->map(function($user) {
            $photo = (!empty($user->profile_picture) && $user->profile_picture != 'non')
                ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : asset('new_wiselook/uploads/' . $user->profile_picture))
                : asset('upload/no_image.jpg');

            return [
                'id'              => (int)$user->id,
                'name'            => trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'first_name'      => $user->first_name ?? '',
                'last_name'       => $user->last_name ?? '',
                'email'           => $user->email ?? '',
                'profile_picture' => $photo,
                'points'          => (int)($user->points ?? 0),
                'role'            => $user->role ?? 'user',
                'mutual_count'    => (int)($user->mutual_count ?? 0),
                'friendship_type' => $user->friendship_type ?? 'suggested',
                'friendship_id'   => $user->friendship_id ? (int)$user->friendship_id : null,
            ];
        });

        return response()->json([
            'success'  => true,
            'stats'    => [
                'total_friends_count'    => $totalFriendsCount,
                'pending_requests_count' => $pendingRequestsCount,
                'sent_requests_count'    => $sentRequestsCount,
                'mutual_contacts_count'  => $mutualContactsCount,
            ],
            'data'     => $formattedUsers,
            'has_more' => $hasMore,
            'page'     => $page,
        ]);
    }
}
