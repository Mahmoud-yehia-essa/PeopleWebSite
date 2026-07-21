<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Display the user's profile page.
     */
    public function showProfile(Request $request, $id = null): View
    {
        if ($id) {
            $user = \App\Models\User::findOrFail($id);
        } else {
            $user = Auth::user();
        }
        $posts = $user->posts()->with(['poll.options'])->where('is_active', 1)->latest()->take(10)->get();
        
        $wiseRatedPosts = $user->posts()
            ->where('is_active', 1)
            ->whereNotNull('wise_rating')
            ->where('wise_rating', '>', 0)
            ->orderBy('wise_rating', 'desc')
            ->get();
            
        $wisePointLogs = \App\Models\WisePointLog::where('recipient_user_id', $user->id)
            ->whereNotNull('post_id')
            ->with('post')
            ->latest()
            ->get();

        $joinedGroups = \App\Models\GroupSite::whereIn('id', function($q) use ($user) {
            $q->select('group_site_id')
              ->from('group_site_users')
              ->where('user_id', $user->id);
        })->get();

        return view('frontend.wiselook.pages.profile', compact('user', 'posts', 'wiseRatedPosts', 'wisePointLogs', 'joinedGroups'));
    }

    /**
     * Display the user's friends list page.
     */
    public function showFriends(Request $request, $id): View
    {
        $user = \App\Models\User::findOrFail($id);
        
        // 1. Get active friendships of this user
        $userId = $user->id;
        $activeFriendships = \App\Models\Friendship::where('is_active', 1)
            ->where(function($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('receiver_id', $userId);
            })
            ->get();

        $friendIds = [];
        foreach ($activeFriendships as $f) {
            if ($f->sender_id == $userId) {
                $friendIds[] = $f->receiver_id;
            } else {
                $friendIds[] = $f->sender_id;
            }
        }
        
        // 2. Fetch friend user models with pagination
        $friends = \App\Models\User::whereIn('id', $friendIds)
            ->latest()
            ->paginate(18);

        // 3. Get viewer's friend IDs for mutual friends calculation
        $myFriendIds = [];
        if (Auth::check()) {
            $myId = Auth::id();
            $myFriendships = \App\Models\Friendship::where('is_active', 1)
                ->where(function($q) use ($myId) {
                    $q->where('sender_id', $myId)
                      ->orWhere('receiver_id', $myId);
                })
                ->get();
            foreach ($myFriendships as $f) {
                if ($f->sender_id == $myId) {
                    $myFriendIds[] = $f->receiver_id;
                } else {
                    $myFriendIds[] = $f->sender_id;
                }
            }
        }

        // 4. Get active friendships of these friends to build friends-of-friends map
        $friendUserIds = $friends->pluck('id')->toArray();
        $friendsFriendsMap = [];
        if (!empty($friendUserIds)) {
            $friendsFriendships = \App\Models\Friendship::where('is_active', 1)
                ->where(function($q) use ($friendUserIds) {
                    $q->whereIn('sender_id', $friendUserIds)
                      ->orWhereIn('receiver_id', $friendUserIds);
                })
                ->get();

            foreach ($friendsFriendships as $f) {
                if (in_array($f->sender_id, $friendUserIds)) {
                    $friendsFriendsMap[$f->sender_id][] = $f->receiver_id;
                }
                if (in_array($f->receiver_id, $friendUserIds)) {
                    $friendsFriendsMap[$f->receiver_id][] = $f->sender_id;
                }
            }
        }

        return view('frontend.wiselook.pages.profile_friends', compact('user', 'friends', 'myFriendIds', 'friendsFriendsMap'));
    }

    /**
     * Display frontend edit profile form.
     */
    public function editProfile(Request $request): \Illuminate\View\View
    {
        $user = Auth::user();
        $countryList = [
            ['code' => 'KWT', 'dial' => '+965', 'name' => 'الكويت', 'flag' => '🇰🇼'],
            ['code' => 'SAU', 'dial' => '+966', 'name' => 'السعودية', 'flag' => '🇸🇦'],
            ['code' => 'UAE', 'dial' => '+971', 'name' => 'الإمارات', 'flag' => '🇦🇪'],
            ['code' => 'QAT', 'dial' => '+974', 'name' => 'قطر', 'flag' => '🇶🇦'],
            ['code' => 'EGY', 'dial' => '+20', 'name' => 'مصر', 'flag' => '🇪🇬']
        ];
        return view('frontend.wiselook.pages.profile_edit', compact('user', 'countryList'));
    }

    /**
     * Update frontend profile information.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // التحقق من أخطاء الرفع الناتجة عن قيود السيرفر (مثل حجم الملف في php.ini)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_INI_SIZE) {
            return redirect()->back()->withInput()->withErrors(['photo' => 'حجم الصورة الشخصية كبير جداً ويتجاوز الحد الأقصى المسموح به على السيرفر.']);
        }
        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_INI_SIZE) {
            return redirect()->back()->withInput()->withErrors(['cover_photo' => 'حجم صورة الغلاف كبير جداً ويتجاوز الحد الأقصى المسموح به على السيرفر.']);
        }

        $request->validate([
            'fname' => 'required|string|max:50',
            'lname' => 'required|string|max:50',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|confirmed|min:8',
            'phone' => 'nullable|string|max:20',
            'country_data' => 'required|string',
            'address' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $countryData = json_decode($request->country_data, true);
        $dial = $countryData['dial'] ?? '';
        $flag = $countryData['flag'] ?? '';

        $phoneNumber = $request->phone ? ($dial . $request->phone) : null;

        // Profile Photo
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if ($user->profile_picture && \Illuminate\Support\Facades\File::exists(public_path('new_wiselook/uploads/' . $user->profile_picture))) {
                \Illuminate\Support\Facades\File::delete(public_path('new_wiselook/uploads/' . $user->profile_picture));
            }
            $photoName = date('YmdHis') . '_profile.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $photoName);
            $user->profile_picture = $photoName;
        }

        // Cover Photo
        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            if ($user->cover_picture && \Illuminate\Support\Facades\File::exists(public_path('new_wiselook/uploads/' . $user->cover_picture))) {
                \Illuminate\Support\Facades\File::delete(public_path('new_wiselook/uploads/' . $user->cover_picture));
            }
            $coverName = date('YmdHis') . '_cover.' . $file->getClientOriginalExtension();
            $file->move(public_path('new_wiselook/uploads'), $coverName);
            $user->cover_picture = $coverName;
        }

        $user->first_name = $request->fname;
        $user->last_name = $request->lname;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->password_hash = md5($request->password);
        }
        $user->phone_number = $phoneNumber;
        $user->country_flag = $flag;
        $user->address = $request->address;
        $user->bio = $request->bio;

        $user->save();

        $notification = [
            'message' => 'تم تحديث ملفك الشخصي بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->route('profile.edit')->with($notification);
    }

    /**
     * Helper to get notifications list for the current user.
     */
    private function getNotificationsList($currentUser): array
    {
        $notificationsCollection = collect();

        // 1. Friend requests
        $friendRequests = \App\Models\Friendship::with('sender')
            ->where('receiver_id', $currentUser->id)
            ->where('is_active', 0)
            ->get();

        foreach ($friendRequests as $req) {
            if ($req->sender) {
                $notificationsCollection->push([
                    'id'         => $req->id,
                    'type'       => 'friend_request',
                    'title'      => 'طلب صداقة جديد',
                    'message'    => 'قام ' . $req->sender->first_name . ' ' . $req->sender->last_name . ' بإرسال طلب صداقة إليك.',
                    'sender_name'=> $req->sender->first_name . ' ' . $req->sender->last_name,
                    'avatar'     => $req->sender->profile_picture,
                    'url'        => route('profile.edit', $req->sender->id),
                    'created_at' => $req->created_at,
                    'diff'       => $req->created_at->diffForHumans()
                ]);
            }
        }

        // 2. Post reactions
        $postLikes = \App\Models\Reaction::with(['user', 'post'])
            ->where('content_type_id', 1)
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postLikes as $like) {
            if ($like->user && $like->post) {
                $notificationsCollection->push([
                    'id'         => $like->id,
                    'type'       => 'like',
                    'title'      => 'تفاعل جديد',
                    'message'    => 'قام ' . $like->user->first_name . ' بالإعجاب بموضوعك: "' . \Illuminate\Support\Str::limit($like->post->content, 35) . '"',
                    'sender_name'=> $like->user->first_name . ' ' . $like->user->last_name,
                    'avatar'     => $like->user->profile_picture,
                    'url'        => route('frontend.posts.show', $like->post->id),
                    'created_at' => $like->created_at,
                    'diff'       => $like->created_at->diffForHumans()
                ]);
            }
        }

        // 3. Post comments
        $postComments = \App\Models\Comment::with(['user', 'post'])
            ->where('is_active', 1)
            ->where('user_id', '!=', $currentUser->id)
            ->whereHas('post', function ($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })->get();

        foreach ($postComments as $comment) {
            if ($comment->user && $comment->post) {
                $notificationsCollection->push([
                    'id'         => $comment->id,
                    'type'       => 'comment',
                    'title'      => 'تعليق جديد',
                    'message'    => 'قام ' . $comment->user->first_name . ' بالتعليق على موضوعك: "' . \Illuminate\Support\Str::limit($comment->post->content, 35) . '"',
                    'sender_name'=> $comment->user->first_name . ' ' . $comment->user->last_name,
                    'avatar'     => $comment->user->profile_picture,
                    'url'        => route('frontend.posts.show', $comment->post->id),
                    'created_at' => $comment->created_at,
                    'diff'       => $comment->created_at->diffForHumans()
                ]);
            }
        }

    // 4. Custom Database Notifications (friend_accept, comment_reply, reply_to_reply)
    $dbNotifications = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('notifiable_type', 'App\Models\User')
        ->where('notifiable_id', $currentUser->id)
        ->get();

    foreach ($dbNotifications as $notif) {
        $data = json_decode($notif->data, true);
        if ($data) {
            $url = '#';
            $type = $data['type'] ?? 'general';
            if ($type === 'friend_accept' && isset($data['sender_id'])) {
                $url = route('profile.edit', $data['sender_id']);
            } elseif (($type === 'comment_reply' || $type === 'reply_to_reply' || $type === 'mention') && isset($data['post_id'])) {
                $url = route('frontend.posts.show', $data['post_id']);
            }

            $notificationsCollection->push([
                'id'         => $notif->id,
                'type'       => $type,
                'title'      => $type === 'friend_accept' ? 'قبول طلب صداقة' : ($type === 'mention' ? 'إشارة إليك' : 'رد جديد'),
                'message'    => $data['message'] ?? '',
                'sender_name'=> $data['sender_name'] ?? '',
                'avatar'     => $data['avatar'] ?? null,
                'url'        => $url,
                'created_at' => \Carbon\Carbon::parse($notif->created_at),
                'diff'       => \Carbon\Carbon::parse($notif->created_at)->diffForHumans(),
                'is_seen'    => !is_null($notif->read_at)
            ]);
        }
    }


    $seenItems = \App\Models\Seen::where('user_id', $currentUser->id)->get();

    $notifications = $notificationsCollection->map(function ($item) use ($seenItems) {
        if (isset($item['is_seen'])) {
            return $item;
        }
        $enumType = $item['type'] === 'like' ? 'post_like' : ($item['type'] === 'comment' ? 'post_comment' : 'friend_request');
        $isSeen = $seenItems->where('notification_id', $item['id'])
                            ->where('notification_type', $enumType)
                            ->isNotEmpty();
        $item['is_seen'] = $isSeen;
        return $item;
    });

    return $notifications->filter(function($item) {
        return !$item['is_seen'];
    })->sortByDesc('created_at')->values()->all();
    }

    /**
     * Endpoint returning notifications JSON for dropdown.
     */
    public function getNotificationsApi(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['notifications' => [], 'unread_count' => 0, 'has_more' => false]);
        }

        $notifications = $this->getNotificationsList($currentUser);
        
        // Format dates as strings for JSON response
        $formattedNotifications = collect($notifications)->map(function ($item) {
            $item['created_at'] = $item['created_at']->toISOString();
            return $item;
        })->all();

        $unreadCount = collect($formattedNotifications)->where('is_seen', false)->count();

        // Paginate the in-memory array for lazyloading
        $page = intval($request->query('page', 1));
        $perPage = intval($request->query('per_page', 5));
        $offset = ($page - 1) * $perPage;
        
        $pagedNotifications = array_slice($formattedNotifications, $offset, $perPage);
        $hasMore = count($formattedNotifications) > ($offset + $perPage);

        return response()->json([
            'notifications' => $pagedNotifications,
            'unread_count'  => $unreadCount,
            'has_more'      => $hasMore
        ]);
    }

    /**
     * Endpoint to mark a single notification as read.
     */
    public function markSingleRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['success' => false]);
        }

        $id = $request->id;
        $type = $request->type;

        // إذا كان معرّف الإشعار UUID (من جدول notifications)، نحدث حقل read_at
        if (is_string($id) && \Illuminate\Support\Str::isUuid($id)) {
            \Illuminate\Support\Facades\DB::table('notifications')
                ->where('id', $id)
                ->update(['read_at' => now()]);
        } else {
            $enumType = $type === 'like' ? 'post_like' : ($type === 'comment' ? 'post_comment' : 'friend_request');
            \App\Models\Seen::firstOrCreate([
                'user_id' => $currentUser->id,
                'notification_id' => $id,
                'notification_type' => $enumType
            ], [
                'seen_at' => now()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Endpoint to mark all notifications as read.
     */
    public function markAllRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['success' => false]);
        }

        $notifications = $this->getNotificationsList($currentUser);
        foreach ($notifications as $item) {
            if (!$item['is_seen']) {
                // إذا كان معرّف الإشعار UUID (من جدول notifications)، نحدث حقل read_at
                if (is_string($item['id']) && \Illuminate\Support\Str::isUuid($item['id'])) {
                    \Illuminate\Support\Facades\DB::table('notifications')
                        ->where('id', $item['id'])
                        ->update(['read_at' => now()]);
                } else {
                    $enumType = $item['type'] === 'like' ? 'post_like' : ($item['type'] === 'comment' ? 'post_comment' : 'friend_request');
                    \App\Models\Seen::firstOrCreate([
                        'user_id' => $currentUser->id,
                        'notification_id' => $item['id'],
                        'notification_type' => $enumType
                    ], [
                        'seen_at' => now()
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Render the View All notifications page.
     */
    public function showNotifications(Request $request): \Illuminate\View\View
    {
        return view('frontend.wiselook.pages.notifications');
    }

    /**
     * جلب سجل نقاط التقييم التفصيلي للمستخدم لتعريضه في الـ Popup
     */
    public function getPointsDetails($id): \Illuminate\Http\JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);
        
        $logs = \App\Models\WisePointLog::where('recipient_user_id', $id)
            ->with(['wiseUser', 'post'])
            ->latest()
            ->get();
            
        $formattedLogs = $logs->map(function($log) {
            return [
                'id' => $log->id,
                'points_given' => $log->points_given,
                'wise_name' => $log->wiseUser ? ($log->wiseUser->first_name . ' ' . $log->wiseUser->last_name) : 'حكيم منصة',
                'note' => $log->note ?? 'لا توجد ملاحظات إضافية.',
                'post_id' => $log->post_id,
                'post_snippet' => $log->post ? \Illuminate\Support\Str::limit(strip_tags($log->post->content), 60) : null,
                'post_url' => $log->post_id ? route('frontend.posts.show', $log->post_id) : '#',
                'created_at' => $log->created_at->format('Y-m-d H:i'),
                'diff' => $log->created_at->diffForHumans()
            ];
        });
        
        return response()->json([
            'success' => true,
            'user_name' => $user->first_name . ' ' . $user->last_name,
            'total_points' => $user->points ?? 0,
            'logs' => $formattedLogs
        ]);
    }

    /**
     * Get paginated profile posts for lazyloading.
     */
    public function getProfilePostsApi(Request $request, $id = null): \Illuminate\Http\JsonResponse
    {
        if ($id) {
            $user = \App\Models\User::findOrFail($id);
        } else {
            $user = Auth::user();
        }

        if (!$user) {
            return response()->json(['html' => '', 'has_more' => false]);
        }

        $perPage = intval($request->query('per_page', 10));
        
        $posts = $user->posts()
            ->with(['poll.options'])
            ->where('is_active', 1)
            ->latest()
            ->paginate($perPage);

        $html = '';
        foreach ($posts as $post) {
            $html .= view('frontend.wiselook.pages.profile_post_card', compact('post'))->render();
        }

        return response()->json([
            'html' => $html,
            'has_more' => $posts->hasMorePages(),
            'current_page' => $posts->currentPage(),
        ]);
    }
}
