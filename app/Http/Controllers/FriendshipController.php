<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendshipController extends Controller
{
    /**
     * عرض جميع علاقات وطلبات الصداقة
     */
    public function allFriendships()
    {
        $friendships = Friendship::with(['sender', 'receiver'])->latest()->get();
        return view('admin.friendships.all_friendships', compact('friendships'));
    }

    /**
     * شاشة إضافة علاقة صداقة جديدة
     */
    public function addFriendship()
    {
        $users = User::where('is_active', 1)->orderBy('first_name', 'asc')->get();
        return view('admin.friendships.add_friendship', compact('users'));
    }

    /**
     * حفظ علاقة الصداقة الجديدة
     */
    public function storeFriendship(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id|different:sender_id',
            'is_active' => 'required|in:0,1',
        ], [
            'sender_id.required' => 'يرجى اختيار مرسل الطلب.',
            'receiver_id.required' => 'يرجى اختيار مستقبل الطلب.',
            'receiver_id.different' => 'لا يمكن أن يكون مرسل الطلب ومستقبله نفس الشخص.',
            'is_active.required' => 'يرجى تحديد حالة العلاقة.',
        ]);

        // التحقق من عدم وجود علاقة مسبقة بين الطرفين في كلا الاتجاهين
        $exists = Friendship::where(function ($q) use ($request) {
            $q->where('sender_id', $request->sender_id)
              ->where('receiver_id', $request->receiver_id);
        })->orWhere(function ($q) use ($request) {
            $q->where('sender_id', $request->receiver_id)
              ->where('receiver_id', $request->sender_id);
        })->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with([
                'message' => 'علاقة الصداقة أو الطلب موجود بالفعل بين هذين المستخدمين.',
                'alert-type' => 'error'
            ]);
        }

        DB::beginTransaction();
        try {
            $friendship = Friendship::create([
                'sender_id' => $request->sender_id,
                'receiver_id' => $request->receiver_id,
                'is_active' => $request->is_active,
            ]);

            // إذا كانت العلاقة مفعلة مباشرة، نقوم بزيادة العداد للطرفين
            if ($request->is_active == 1) {
                User::where('id', $request->sender_id)->increment('friend_count');
                User::where('id', $request->receiver_id)->increment('friend_count');
            }

            DB::commit();

            $notification = [
                'message' => 'تم إضافة علاقة الصداقة بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.friendships')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * قبول وتفعيل طلب الصداقة
     */
    public function activeFriendship($id)
    {
        $friendship = Friendship::findOrFail($id);

        if ($friendship->is_active == 0) {
            DB::beginTransaction();
            try {
                $friendship->is_active = 1;
                $friendship->save();

                // زيادة العداد للطرفين
                User::where('id', $friendship->sender_id)->increment('friend_count');
                User::where('id', $friendship->receiver_id)->increment('friend_count');

                // إضافة إشعار قبول طلب الصداقة للمرسل
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $friendship->sender_id,
                    'data' => json_encode([
                        'type' => 'friend_accept',
                        'sender_id' => auth()->id(),
                        'sender_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                        'avatar' => auth()->user()->profile_picture,
                        'message' => 'وافق ' . auth()->user()->first_name . ' ' . auth()->user()->last_name . ' على طلب الصداقة الخاص بك.',
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();

                $notification = [
                    'message' => 'تم تفعيل علاقة الصداقة وقبول الطلب بنجاح.',
                    'alert-type' => 'success'
                ];

                if (request()->ajax()) {
                    $userId = auth()->id();
                    $totalFriendsCount = Friendship::where('is_active', 1)
                        ->where(function($q) use ($userId) {
                            $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                        })->count();
                    $pendingRequestsCount = Friendship::where('is_active', 0)->where('receiver_id', $userId)->count();
                    $sentRequestsCount = Friendship::where('is_active', 0)->where('sender_id', $userId)->count();

                    return response()->json([
                        'success' => true,
                        'message' => __t('friend_request_accepted'),
                        'totalFriendsCount' => $totalFriendsCount,
                        'pendingRequestsCount' => $pendingRequestsCount,
                        'sentRequestsCount' => $sentRequestsCount,
                    ]);
                }

                return redirect()->back()->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()
                    ], 500);
                }

                return redirect()->back()->with([
                    'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage(),
                    'alert-type' => 'error'
                ]);
            }
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'علاقة الصداقة مفعلة بالفعل.'
            ], 422);
        }

        return redirect()->back()->with([
            'message' => 'علاقة الصداقة مفعلة بالفعل.',
            'alert-type' => 'info'
        ]);
    }

    /**
     * إلغاء تفعيل علاقة الصداقة (إعادتها لحالة طلب معلق)
     */
    public function inactiveFriendship($id)
    {
        $friendship = Friendship::findOrFail($id);

        if ($friendship->is_active == 1) {
            DB::beginTransaction();
            try {
                $friendship->is_active = 0;
                $friendship->save();

                // تخفيض العداد للطرفين بشرط ألا يقل عن 0
                User::where('id', $friendship->sender_id)->where('friend_count', '>', 0)->decrement('friend_count');
                User::where('id', $friendship->receiver_id)->where('friend_count', '>', 0)->decrement('friend_count');

                DB::commit();

                $notification = [
                    'message' => 'تم إلغاء تفعيل علاقة الصداقة (إعادتها لمعلقة) بنجاح.',
                    'alert-type' => 'success'
                ];
                return redirect()->back()->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with([
                    'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage(),
                    'alert-type' => 'error'
                ]);
            }
        }

        return redirect()->back()->with([
            'message' => 'العلاقة معلقة بالفعل.',
            'alert-type' => 'info'
        ]);
    }

    /**
     * حذف علاقة الصداقة بالكامل
     */
    public function deleteFriendship($id)
    {
        $friendship = Friendship::findOrFail($id);

        DB::beginTransaction();
        try {
            // إذا كانت العلاقة نشطة قبل الحذف، نقوم بتخفيض العداد لكلا الطرفين
            if ($friendship->is_active == 1) {
                User::where('id', $friendship->sender_id)->where('friend_count', '>', 0)->decrement('friend_count');
                User::where('id', $friendship->receiver_id)->where('friend_count', '>', 0)->decrement('friend_count');
            }

            $friendship->delete();

            DB::commit();

            $notification = [
                'message' => 'تم حذف علاقة الصداقة بنجاح.',
                'alert-type' => 'success'
            ];

            if (request()->ajax()) {
                $userId = auth()->id();
                $totalFriendsCount = Friendship::where('is_active', 1)
                    ->where(function($q) use ($userId) {
                        $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
                    })->count();
                $pendingRequestsCount = Friendship::where('is_active', 0)->where('receiver_id', $userId)->count();
                $sentRequestsCount = Friendship::where('is_active', 0)->where('sender_id', $userId)->count();

                return response()->json([
                    'success' => true,
                    'message' => __t('friend_request_cancelled'),
                    'totalFriendsCount' => $totalFriendsCount,
                    'pendingRequestsCount' => $pendingRequestsCount,
                    'sentRequestsCount' => $sentRequestsCount,
                ]);
            }

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء حذف السجل: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف السجل: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * الحصول على قائمة الأصدقاء للبحث وعمل منشن
     */
    public function getFriendsSearch(Request $request)
    {
        $userId = auth()->id();
        
        // جلب الأصدقاء المقبولين (is_active = 1)
        // صديق هو إما sender_id أو receiver_id والمستخدم الحالي هو الطرف الآخر
        $friendships = Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->get();

        $friendIds = [];
        foreach ($friendships as $fs) {
            if ($fs->sender_id == $userId) {
                $friendIds[] = $fs->receiver_id;
            } else {
                $friendIds[] = $fs->sender_id;
            }
        }

        // جلب تفاصيل الأصدقاء
        $friends = User::whereIn('id', $friendIds)
            ->where('is_active', 1)
            ->when($request->q, function($q) use ($request) {
                $search = $request->q;
                $q->where(function($sub) use ($search) {
                    $sub->where('first_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            })
            ->select(['id', 'first_name', 'last_name', 'profile_picture'])
            ->get();

        // تنسيق البيانات لتضمين المسار الصحيح للصورة الشخصية
        $data = $friends->map(function($user) {
            $avatar = url('upload/no_image.jpg');
            if ($user->profile_picture && $user->profile_picture !== 'non') {
                $avatar = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                    ? $user->profile_picture
                    : asset('new_wiselook/uploads/' . $user->profile_picture);
            }
            return [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'avatar' => $avatar
            ];
        });

        return response()->json($data);
    }

    /**
     * إرسال طلب صداقة من الواجهة الأمامية عبر AJAX
     */
    public function sendFriendRequest(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id'
        ]);

        $senderId = auth()->id();
        $receiverId = $request->receiver_id;

        if ($senderId == $receiverId) {
            return response()->json([
                'success' => false,
                'message' => __t('cannot_send_friend_request_self')
            ], 422);
        }

        // تحقق من العلاقة الحالية
        $exists = Friendship::where(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $senderId)
              ->where('receiver_id', $receiverId);
        })->orWhere(function ($q) use ($senderId, $receiverId) {
            $q->where('sender_id', $receiverId)
              ->where('receiver_id', $senderId);
        })->first();

        if ($exists) {
            if ($exists->is_active == 1) {
                return response()->json([
                    'success' => false,
                    'message' => __t('already_friends')
                ], 422);
            }
            return response()->json([
                'success' => false,
                'message' => __t('friend_request_pending')
            ], 422);
        }

        $friendship = Friendship::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'is_active' => 0
        ]);

        $totalFriendsCount = Friendship::where('is_active', 1)
            ->where(function($q) use ($senderId) {
                $q->where('sender_id', $senderId)->orWhere('receiver_id', $senderId);
            })->count();
        $pendingRequestsCount = Friendship::where('is_active', 0)->where('receiver_id', $senderId)->count();
        $sentRequestsCount = Friendship::where('is_active', 0)->where('sender_id', $senderId)->count();

        return response()->json([
            'success' => true,
            'message' => __t('friend_request_sent'),
            'friendship_id' => $friendship->id,
            'totalFriendsCount' => $totalFriendsCount,
            'pendingRequestsCount' => $pendingRequestsCount,
            'sentRequestsCount' => $sentRequestsCount,
        ]);
    }

    /**
     * الحصول على الأاصدقاء المشتركين بين المستخدم الحالي ومستشار آخر
     */
    public function getMutualFriends($otherUserId)
    {
        $currentUserId = auth()->id();

        // 1. Get current user's active friend IDs
        $myFriendships = Friendship::where('is_active', 1)
            ->where(function($q) use ($currentUserId) {
                $q->where('sender_id', $currentUserId)
                  ->orWhere('receiver_id', $currentUserId);
            })
            ->get();
            
        $myFriendIds = [];
        foreach ($myFriendships as $fs) {
            $myFriendIds[] = ($fs->sender_id == $currentUserId) ? $fs->receiver_id : $fs->sender_id;
        }

        // 2. Get other user's active friend IDs
        $theirFriendships = Friendship::where('is_active', 1)
            ->where(function($q) use ($otherUserId) {
                $q->where('sender_id', $otherUserId)
                  ->orWhere('receiver_id', $otherUserId);
            })
            ->get();
            
        $theirFriendIds = [];
        foreach ($theirFriendships as $fs) {
            $theirFriendIds[] = ($fs->sender_id == $otherUserId) ? $fs->receiver_id : $fs->sender_id;
        }

        // 3. Find intersection (mutual friend IDs)
        $mutualFriendIds = array_intersect($myFriendIds, $theirFriendIds);

        // 4. Fetch mutual users
        $mutualUsers = User::whereIn('id', $mutualFriendIds)
            ->where('is_active', 1)
            ->select(['id', 'first_name', 'last_name', 'profile_picture'])
            ->get()
            ->map(function($user) {
                $avatar = url('upload/no_image.jpg');
                if ($user->profile_picture && $user->profile_picture !== 'non') {
                    $avatar = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                        ? $user->profile_picture
                        : asset('new_wiselook/uploads/' . $user->profile_picture);
                }
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'avatar' => $avatar,
                    'profile_url' => route('profile.edit', $user->id)
                ];
            });

        return response()->json($mutualUsers);
    }

    /**
     * عرض شبكتي للمستخدم الحالي في الواجهة الأمامية
     */
    public function myNetwork(Request $request)
    {
        $userId = auth()->id();
        
        // 1. إحصائيات الشبكة
        // إجمالي الأصدقاء (المقبولين)
        $friendsQuery = Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            });
            
        $totalFriendsCount = $friendsQuery->count();
        
        // طلبات الصداقة المعلقة الواردة للمستخدم الحالي
        $pendingRequestsQuery = Friendship::where('is_active', 0)
            ->where('receiver_id', $userId);
            
        $pendingRequestsCount = $pendingRequestsQuery->count();

        // طلبات الصداقة المعلقة المرسلة من المستخدم الحالي
        $sentRequestsCount = Friendship::where('is_active', 0)
            ->where('sender_id', $userId)
            ->count();

        // جهات الاتصال المشتركة (المستشارين في نفس مجاله أو الأصدقاء المشتركين)
        // لنحسب إجمالي جهات الاتصال في المنصة بشكل مبسط أو عدد المستخدمين ذوي النقاط
        $mutualContactsCount = User::where('is_active', 1)->where('id', '!=', $userId)->count();

        // 2. جلب أعلى 10 أعضاء تقييماً (نقاطاً) (باستثناء من نقاطهم صفر)
        $topRatedUsers = \App\Models\User::where('is_active', 1)
            ->where('role', 'user')
            ->where('points', '>', 0)
            ->orderBy('points', 'desc')
            ->take(10)
            ->get();

        if (auth()->check()) {
            $currentUserId = auth()->id();
            $topRatedUserIds = $topRatedUsers->pluck('id')->toArray();
            
            $friendships = \App\Models\Friendship::where(function($q) use ($currentUserId) {
                    $q->where('sender_id', $currentUserId)
                      ->orWhere('receiver_id', $currentUserId);
                })
                ->where(function($q) use ($topRatedUserIds) {
                    $q->whereIn('sender_id', $topRatedUserIds)
                      ->orWhereIn('receiver_id', $topRatedUserIds);
                })
                ->get();
                
            foreach ($topRatedUsers as $user) {
                if ($user->id == $currentUserId) {
                    $user->friendship_status = 'self';
                    continue;
                }
                
                $friendship = $friendships->first(function($fs) use ($currentUserId, $user) {
                    return ($fs->sender_id == $currentUserId && $fs->receiver_id == $user->id)
                        || ($fs->sender_id == $user->id && $fs->receiver_id == $currentUserId);
                });
                
                if ($friendship) {
                    if ($friendship->is_active == 1) {
                        $user->friendship_status = 'friends';
                    } elseif ($friendship->sender_id == $currentUserId) {
                        $user->friendship_status = 'pending_sent';
                    } else {
                        $user->friendship_status = 'pending_received';
                    }
                } else {
                    $user->friendship_status = 'none';
                }
            }
        } else {
            foreach ($topRatedUsers as $user) {
                $user->friendship_status = 'none';
            }
        }

        // 3. جلب قائمة الأصدقاء أو الطلبات بناء على الفلتر المختار
        $filter = $request->get('filter', 'suggested'); // suggested, all, pending, sent_requests, sages, recent
        $search = $request->get('search');

        $users = collect();
        $perPage = 10;
        $page = intval($request->get('page', 1));
        $offset = ($page - 1) * $perPage;
        $hasMore = false;

        if ($filter === 'suggested') {
            // جلب الأصدقاء المقترحين (باستثناء الأصدقاء الفعليين وأي علاقة معلقة والمستخدم نفسه)
            $myFriendships = Friendship::where('is_active', 1)
                ->where(function($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->get();
                
            $myFriendIds = [];
            foreach ($myFriendships as $fs) {
                $myFriendIds[] = ($fs->sender_id == $userId) ? $fs->receiver_id : $fs->sender_id;
            }
            
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

            if ($search) {
                $usersQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $allSuggested = $usersQuery->get()
                ->map(function($potentialUser) use ($myFriendIds) {
                    $theirFriendships = Friendship::where('is_active', 1)
                        ->where(function($q) use ($potentialUser) {
                            $q->where('sender_id', $potentialUser->id)
                              ->orWhere('receiver_id', $potentialUser->id);
                        })
                        ->get();
                    
                    $theirFriendIds = [];
                    foreach ($theirFriendships as $fs) {
                        $theirFriendIds[] = ($fs->sender_id == $potentialUser->id) ? $fs->receiver_id : $fs->sender_id;
                    }
                    
                    $mutualFriends = array_intersect($myFriendIds, $theirFriendIds);
                    $potentialUser->mutual_count = count($mutualFriends);
                    $potentialUser->friendship_type = 'suggested';
                    return $potentialUser;
                })
                ->sort(function($a, $b) {
                    if ($a->mutual_count === $b->mutual_count) {
                        return rand(-1, 1);
                    }
                    return $b->mutual_count <=> $a->mutual_count;
                })
                ->values();

            $totalCount = $allSuggested->count();
            $users = $allSuggested->slice($offset, $perPage)->values();
            $hasMore = $totalCount > ($offset + $perPage);
        } elseif ($filter === 'pending') {
            // جلب طلبات الصداقة الواردة المعلقة
            $friendshipsQuery = Friendship::where('is_active', 0)
                ->where('receiver_id', $userId)
                ->with('sender')
                ->latest();
                
            $totalCount = $friendshipsQuery->count();
            $friendships = $friendshipsQuery->offset($offset)->limit($perPage)->get();
                
            $users = $friendships->map(function($f) {
                if ($f->sender) {
                    $f->sender->friendship_id = $f->id;
                    $f->sender->friendship_type = 'pending_received';
                    return $f->sender;
                }
                return null;
            })->filter()->values();
            
            $hasMore = $totalCount > ($offset + $perPage);
        } elseif ($filter === 'sent_requests') {
            // جلب طلبات الصداقة المرسلة المعلقة
            $friendshipsQuery = Friendship::where('is_active', 0)
                ->where('sender_id', $userId)
                ->with('receiver')
                ->latest();
                
            $totalCount = $friendshipsQuery->count();
            $friendships = $friendshipsQuery->offset($offset)->limit($perPage)->get();
                
            $users = $friendships->map(function($f) {
                if ($f->receiver) {
                    $f->receiver->friendship_id = $f->id;
                    $f->receiver->friendship_type = 'pending_sent';
                    return $f->receiver;
                }
                return null;
            })->filter()->values();
            
            $hasMore = $totalCount > ($offset + $perPage);
        } else {
            // جلب الأصدقاء المقبولين
            $friendships = Friendship::where('is_active', 1)
                ->where(function($q) use ($userId) {
                    $q->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
                })
                ->with(['sender', 'receiver'])
                ->get();

            $friendIds = [];
            $friendshipMap = [];
            foreach ($friendships as $fs) {
                $friendId = ($fs->sender_id == $userId) ? $fs->receiver_id : $fs->sender_id;
                $friendIds[] = $friendId;
                $friendshipMap[$friendId] = $fs->id;
            }

            $usersQuery = User::whereIn('id', $friendIds)->where('is_active', 1);

            if ($filter === 'sages') {
                $usersQuery->where(function($q) {
                    $q->where('role', 'admin')
                      ->orWhere('points', '>=', 100);
                });
            } elseif ($filter === 'recent') {
                $usersQuery->latest();
            }

            if ($search) {
                $usersQuery->where(function($q) use ($search) {
                    $q->where('first_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('last_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('email', 'LIKE', '%' . $search . '%');
                });
            }

            $totalCount = $usersQuery->count();
            $users = $usersQuery->offset($offset)->limit($perPage)->get()->map(function($u) use ($friendshipMap) {
                $u->friendship_id = $friendshipMap[$u->id] ?? null;
                $u->friendship_type = 'accepted';
                return $u;
            });
            
            $hasMore = $totalCount > ($offset + $perPage);
        }

        if ($request->ajax()) {
            return view('frontend.wiselook.pages.my_network_grid', compact('users', 'filter', 'search', 'hasMore', 'page'))->render();
        }

        return view('frontend.wiselook.pages.my_network', compact(
            'users', 
            'totalFriendsCount', 
            'pendingRequestsCount', 
            'sentRequestsCount',
            'mutualContactsCount', 
            'topRatedUsers',
            'filter',
            'search',
            'hasMore',
            'page'
        ));
    }
}
