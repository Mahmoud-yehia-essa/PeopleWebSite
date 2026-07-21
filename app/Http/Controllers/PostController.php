<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\PrivacyLevel;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Reaction;
use App\Models\Comment;
use App\Models\PollResponse;
use App\Models\Hashtag;
use App\Models\HashtagLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    /**
     * عرض كل المواضيع
     */
    public function allPosts(Request $request)
    {
        $query = Post::with(['user', 'poll.options', 'pin'])->withCount('reactions')->latest();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('media_filter') && !empty($request->media_filter)) {
            $mediaFilter = $request->media_filter;
            if ($mediaFilter === 'has_media') {
                $query->where(function($q) {
                    $q->where(function($sq) {
                        $sq->whereNotNull('image')->where('image', '!=', '');
                    })->orWhere(function($sq) {
                        $sq->whereNotNull('video')->where('video', '!=', '');
                    });
                });
            } elseif ($mediaFilter === 'has_image') {
                $query->whereNotNull('image')->where('image', '!=', '');
            } elseif ($mediaFilter === 'has_video') {
                $query->whereNotNull('video')->where('video', '!=', '');
            } elseif ($mediaFilter === 'no_media') {
                $query->where(function($q) {
                    $q->where(function($sq) {
                        $sq->whereNull('image')->orWhere('image', '');
                    })->where(function($sq) {
                        $sq->whereNull('video')->orWhere('video', '');
                    });
                });
            }
        }

        $posts = $query->paginate(25);
        return view('admin.posts.all_posts', compact('posts'));
    }

    /**
     * شاشة إضافة موضوع جديد
     */
    public function addPost()
    {
        $users = User::where('is_active', 1)->latest()->get();
        $privacyLevels = PrivacyLevel::all();
        return view('admin.posts.add_post', compact('users', 'privacyLevels'));
    }

    /**
     * حفظ موضوع جديد في قاعدة البيانات
     */
    public function storePost(Request $request)
    {
        if ($request->has('options') && is_array($request->options)) {
            $cleanedOptions = array_values(array_filter($request->options, function($value) {
                return !is_null($value) && trim($value) !== '';
            }));
            $request->merge(['options' => $cleanedOptions]);
        }

        $rules = [
            'user_id' => 'required|exists:users,id',
            'post_type_id' => 'required|in:1,2',
            'privacy_level_id' => 'required|exists:privacy_level,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
        ];

        if ($request->post_type_id == 1) {
            $rules['content'] = 'required_if:post_type_id,1|nullable|string';
        } else {
            $rules['question'] = 'required_if:post_type_id,2|nullable|string';
            $rules['options'] = 'required_if:post_type_id,2|nullable|array|min:2';
            $rules['options.*'] = 'required_if:post_type_id,2|nullable|string|max:255';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $post = new Post();
            $post->user_id = $request->user_id;
            $post->post_type_id = $request->post_type_id;
            $post->privacy_level_id = $request->privacy_level_id;
            $post->is_active = 1;
            $post->parent_id = 0;

            // إذا كان منشور عادي
            if ($request->post_type_id == 1) {
                $post->content = $request->content;

                // معالجة الصورة
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = date('YmdHis') . '_img.' . $image->getClientOriginalExtension();
                    $image->move(public_path('new_wiselook/uploads'), $imageName);
                    $post->image = $imageName;
                }

                // معالجة الفيديو
                if ($request->hasFile('video')) {
                    $video = $request->file('video');
                    $videoName = date('YmdHis') . '_vid.' . $video->getClientOriginalExtension();
                    $video->move(public_path('new_wiselook/uploads'), $videoName);
                    $post->video = $videoName;
                }
            } else {
                // إذا كان استطلاع رأي
                $post->content = $request->question;
            }

            $post->save();

            // إذا كان استطلاع رأي
            if ($request->post_type_id == 2) {
                $poll = new Poll();
                $poll->post_id = $post->id;
                $poll->question = $request->question;
                $poll->total_votes = 0;
                $poll->is_multiple_choice = 0;
                $poll->save();

                foreach ($request->options as $optionText) {
                    if (!is_null($optionText) && trim($optionText) !== '') {
                        $option = new PollOption();
                        $option->poll_id = $poll->id;
                        $option->content = trim($optionText);
                        $option->vote_count = 0;
                        $option->save();
                    }
                }
            }

            DB::commit();

            $notification = [
                'message' => 'تم إضافة الموضوع بنجاح',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.posts')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء حفظ البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حفظ موضوع جديد من الصفحة الرئيسية
     */
    public function storeFrontendPost(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('user.login')->with([
                'message' => 'يجب تسجيل الدخول أولاً.',
                'alert-type' => 'error'
            ]);
        }

        if ($request->has('options') && is_array($request->options)) {
            $cleanedOptions = array_values(array_filter($request->options, function($value) {
                return !is_null($value) && trim($value) !== '';
            }));
            $request->merge(['options' => $cleanedOptions]);
        }

        $rules = [
            'post_type_id' => 'required|in:1,2',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
        ];

        if ($request->post_type_id == 1) {
            $rules['content'] = 'required_if:post_type_id,1|nullable|string';
        } else {
            $rules['question'] = 'required_if:post_type_id,2|nullable|string';
            $rules['options'] = 'required_if:post_type_id,2|nullable|array|min:2';
            $rules['options.*'] = 'required_if:post_type_id,2|nullable|string|max:255';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $post = new Post();
            $post->user_id = auth()->id();
            $post->post_type_id = $request->post_type_id;
            $post->privacy_level_id = 1; // Default to Public (1) for frontend posts
            $post->is_active = 1;
            $post->parent_id = 0;

            // إذا كان منشور عادي
            if ($request->post_type_id == 1) {
                $post->content = $request->content;

                // معالجة الصورة
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $imageName = date('YmdHis') . '_img.' . $image->getClientOriginalExtension();
                    $image->move(public_path('new_wiselook/uploads'), $imageName);
                    $post->image = $imageName;
                }

                // معالجة الفيديو
                if ($request->hasFile('video')) {
                    $video = $request->file('video');
                    $videoName = date('YmdHis') . '_vid.' . $video->getClientOriginalExtension();
                    $video->move(public_path('new_wiselook/uploads'), $videoName);
                    $post->video = $videoName;
                }
            } else {
                // إذا كان استطلاع رأي
                $post->content = $request->question;
            }

            $post->save();
            $post->syncHashtags();

            // إذا كان استطلاع رأي
            if ($request->post_type_id == 2) {
                $poll = new Poll();
                $poll->post_id = $post->id;
                $poll->question = $request->question;
                $poll->total_votes = 0;
                $poll->is_multiple_choice = 0;
                $poll->save();

                foreach ($request->options as $optionText) {
                    if (!is_null($optionText) && trim($optionText) !== '') {
                        $option = new PollOption();
                        $option->poll_id = $poll->id;
                        $option->content = trim($optionText);
                        $option->vote_count = 0;
                        $option->save();
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                $post->load(['user', 'poll.options']);
                $html = view('frontend.wiselook.pages.posts_feed', ['posts' => collect([$post])])->render();
                return response()->json([
                    'success' => true,
                    'message' => 'تم نشر موضوعك بنجاح!',
                    'html' => $html
                ]);
            }

            $notification = [
                'message' => 'تم نشر موضوعك بنجاح!',
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء النشر: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء النشر: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * التصويت في استطلاع الرأي من الواجهة الأمامية
     */
    public function votePoll(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'يجب تسجيل الدخول أولاً للتصويت.'], 401);
        }

        $request->validate([
            'option_id' => 'required|integer|exists:poll_options,id'
        ]);

        $currentUser = auth()->user();
        $optionId = $request->option_id;

        DB::beginTransaction();
        try {
            $option = PollOption::with('poll')->findOrFail($optionId);
            $poll = $option->poll;

            if ($poll->expires_at && $poll->expires_at->isPast()) {
                return response()->json(['success' => false, 'message' => 'انتهت صلاحية هذا الاستطلاع.'], 400);
            }

            $pollOptionIds = PollOption::where('poll_id', $poll->id)->pluck('id')->toArray();
            $existingVote = PollResponse::where('user_id', $currentUser->id)
                ->whereIn('poll_option_id', $pollOptionIds)
                ->first();

            if ($existingVote) {
                if ($existingVote->poll_option_id == $optionId) {
                    // إلغاء التصويت
                    $existingVote->delete();
                    $option->decrement('vote_count');
                    $poll->decrement('total_votes');
                    $action = 'removed';
                } else {
                    // تغيير التصويت إلى خيار آخر
                    PollOption::where('id', $existingVote->poll_option_id)->decrement('vote_count');
                    $existingVote->update(['poll_option_id' => $optionId]);
                    $option->increment('vote_count');
                    $action = 'updated';
                }
            } else {
                // إضافة تصويت جديد
                PollResponse::create([
                    'user_id' => $currentUser->id,
                    'poll_option_id' => $optionId
                ]);
                $option->increment('vote_count');
                $poll->increment('total_votes');
                $action = 'added';
            }

            DB::commit();

            // تحديث بيانات الخيارات وعمل خريطة بالنسب المئوية الجديدة
            $poll->refresh();
            $updatedOptions = PollOption::where('poll_id', $poll->id)->get()->map(function($opt) use ($poll, $currentUser) {
                $votes = $opt->vote_count ?: 0;
                $total = $poll->total_votes ?: 0;
                $percent = $total > 0 ? round(($votes / $total) * 100, 1) : 0;
                
                $isUserVoted = PollResponse::where('user_id', $currentUser->id)->where('poll_option_id', $opt->id)->exists();

                return [
                    'id' => $opt->id,
                    'content' => $opt->content,
                    'votes' => $votes,
                    'percent' => $percent,
                    'is_selected' => $isUserVoted
                ];
            });

            return response()->json([
                'success' => true,
                'action' => $action,
                'total_votes' => $poll->total_votes,
                'options' => $updatedOptions
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * جلب البيانات المشتركة للقوائم الجانبية لتجنب تكرار الكود
     */
    private function getSidebarData()
    {
        $friendRequests = collect();
        $suggestedFriends = collect();
        
        if (auth()->check()) {
            $currentUserId = auth()->id();
            
            $friendRequests = \App\Models\Friendship::with('sender')
                ->where('receiver_id', $currentUserId)
                ->where('is_active', 0)
                ->latest()
                ->get();

            // Get active friend IDs of current user
            $myFriendships = \App\Models\Friendship::where('is_active', 1)
                ->where(function($q) use ($currentUserId) {
                    $q->where('sender_id', $currentUserId)
                      ->orWhere('receiver_id', $currentUserId);
                })
                ->get();
                
            $myFriendIds = [];
            foreach ($myFriendships as $fs) {
                $myFriendIds[] = ($fs->sender_id == $currentUserId) ? $fs->receiver_id : $fs->sender_id;
            }
            
            // Get all user IDs who have any relationship (pending or active) with the current user
            $existingRelationsUserIds = \App\Models\Friendship::where('sender_id', $currentUserId)
                ->orWhere('receiver_id', $currentUserId)
                ->pluck('sender_id')
                ->merge(
                    \App\Models\Friendship::where('sender_id', $currentUserId)
                        ->orWhere('receiver_id', $currentUserId)
                        ->pluck('receiver_id')
                )
                ->unique()
                ->toArray();
            
            // Suggest users excluding current user and existing relations
            $suggestedFriends = \App\Models\User::where('id', '!=', $currentUserId)
                ->whereNotIn('id', $existingRelationsUserIds)
                ->where('is_active', 1)
                ->get()
                ->map(function($potentialUser) use ($myFriendIds) {
                    // Count mutual friends
                    $theirFriendships = \App\Models\Friendship::where('is_active', 1)
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
                    return $potentialUser;
                })
                // Sort by mutual friends count descending, then randomize
                ->sort(function($a, $b) {
                    if ($a->mutual_count === $b->mutual_count) {
                        return rand(-1, 1);
                    }
                    return $b->mutual_count <=> $a->mutual_count;
                })
                ->take(5);
        } else {
            $suggestedFriends = \App\Models\User::where('is_active', 1)->inRandomOrder()->take(5)->get()->map(function($u) {
                $u->mutual_count = 0;
                return $u;
            });
        }

        $stories = collect();
        if (auth()->check()) {
            $currentUserId = auth()->id();
            
            // Get active friend IDs of current user
            $myFriendships = \App\Models\Friendship::where('is_active', 1)
                ->where(function($q) use ($currentUserId) {
                    $q->where('sender_id', $currentUserId)
                      ->orWhere('receiver_id', $currentUserId);
                })
                ->get();
                
            $myFriendIds = [];
            foreach ($myFriendships as $fs) {
                $myFriendIds[] = ($fs->sender_id == $currentUserId) ? $fs->receiver_id : $fs->sender_id;
            }
            
            $allowedUserIds = array_merge([$currentUserId], $myFriendIds);
            
            // Fetch stories that are active and not expired
            $activeStories = \App\Models\Story::with(['user', 'views'])
                ->whereIn('user_id', $allowedUserIds)
                ->where('is_active', 1)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->latest()
                ->get()
                ->groupBy('user_id');

            // Sort so that the current user's stories are shown first, followed by others in order of their latest story
            $activeStories = $activeStories->sortByDesc(function($userStories) {
                return $userStories->first()->created_at;
            });
            
            if ($activeStories->has($currentUserId)) {
                $currentUserStories = $activeStories->pull($currentUserId);
                $activeStories = collect([$currentUserId => $currentUserStories])->merge($activeStories);
            }
            
            $stories = $activeStories;
        }

        // المواضيع الرائجة: من آخر 30 يوم، مرتبة بالأكثر تعليقاً
        $trendingPosts = Post::where('is_active', 1)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('comment_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // جلب أكثر 4 مجموعات مفتوحة انضماماً للأعضاء
        $trendingGroups = \App\Models\GroupSite::where('status', 'open')
            ->withCount('members')
            ->orderBy('members_count', 'desc')
            ->take(4)
            ->get();

        // جلب أكثر 8 هاشتاجات تداولاً (بناءً على عدد الروابط في hashtag_links)
        $trendingHashtags = Hashtag::withCount('links')
            ->having('links_count', '>', 0)
            ->orderBy('links_count', 'desc')
            ->take(8)
            ->get();

        // جلب أعلى 8 مواضيع مقيمة من لجنة الحكماء
        $wiseRatedPosts = Post::with('user')
            ->where('is_active', 1)
            ->whereNotNull('wise_rating')
            ->where('wise_rating', '>', 0)
            ->orderBy('wise_rating', 'desc')
            ->take(8)
            ->get();

        // جلب أعلى 10 أعضاء تقييماً (نقاطاً) (باستثناء من نقاطهم صفر)
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

        return compact('friendRequests', 'suggestedFriends', 'stories', 'trendingPosts', 'trendingGroups', 'trendingHashtags', 'wiseRatedPosts', 'topRatedUsers');
    }

    /**
     * عرض الصفحة الرئيسية والمواضيع ديناميكياً مع دعم Lazyloading
     */
    public function indexFrontend(Request $request)
    {
        $posts = Post::with(['user', 'poll.options', 'pin'])
            ->where('is_active', 1)
            ->orderByDesc(
                \App\Models\PinnedPost::selectRaw('1')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('pin_scope', 'home')
                    ->limit(1)
            )
            ->orderBy(
                \App\Models\PinnedPost::select('pin_order')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('pin_scope', 'home')
                    ->limit(1)
            )
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {
            return view('frontend.wiselook.pages.posts_feed', compact('posts'))->render();
        }

        $sidebarData = $this->getSidebarData();

        return view('frontend.wiselook.pages.index', array_merge(compact('posts'), $sidebarData));
    }

    /**
     * عرض كل القضايا الأكثر مشاركة ونقاشاً مع دعم Lazyloading
     */
    public function trendingIssues(Request $request)
    {
        // المواضيع الرائجة: من آخر 30 يوم، مرتبة بالأكثر تعليقاً
        $posts = Post::with(['user', 'poll.options'])
            ->where('is_active', 1)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('comment_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->ajax()) {
            return view('frontend.wiselook.pages.posts_feed', compact('posts'))->render();
        }

        $sidebarData = $this->getSidebarData();

        return view('frontend.wiselook.pages.trending_issues', array_merge(compact('posts'), $sidebarData));
    }

    /**
     * عرض جميع المواضيع المقيمة من لجنة الحكماء مع دعم Lazyloading
     */
    public function wiseRatedIndex(Request $request)
    {
        $posts = Post::with(['user', 'poll.options'])
            ->where('is_active', 1)
            ->whereNotNull('wise_rating')
            ->where('wise_rating', '>', 0)
            ->orderBy('wise_rating', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        if ($request->ajax()) {
            return view('frontend.wiselook.pages.posts_feed', compact('posts'))->render();
        }

        $sidebarData = $this->getSidebarData();

        return view('frontend.wiselook.pages.wise_rated_posts', array_merge(compact('posts'), $sidebarData));
    }

    /**
     * شاشة تعديل موضوع
     */
    public function editPost($id)
    {
        $post = Post::with(['poll.options'])->findOrFail($id);
        $users = User::where('is_active', 1)->latest()->get();
        $privacyLevels = PrivacyLevel::all();
        return view('admin.posts.edit_post', compact('post', 'users', 'privacyLevels'));
    }

    /**
     * تحديث بيانات موضوع
     */
    public function updatePost(Request $request)
    {
        $id = $request->id;
        $post = Post::findOrFail($id);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'privacy_level_id' => 'required|exists:privacy_level,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:20480',
        ];

        if ($post->post_type_id == 1) {
            $rules['content'] = 'required_if:post_type_id,1|nullable|string';
        } else {
            $rules['question'] = 'required_if:post_type_id,2|nullable|string';
            $rules['option_ids'] = 'required_if:post_type_id,2|nullable|array';
            $rules['options'] = 'required_if:post_type_id,2|nullable|array';
            $rules['options.*'] = 'required_if:post_type_id,2|nullable|string|max:255';
        }

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $post->user_id = $request->user_id;
            $post->privacy_level_id = $request->privacy_level_id;

            if ($post->post_type_id == 1) {
                $post->content = $request->content;

                // تعديل الصورة
                if ($request->hasFile('image')) {
                    if ($post->image && File::exists(public_path('new_wiselook/uploads/' . $post->image))) {
                        File::delete(public_path('new_wiselook/uploads/' . $post->image));
                    }
                    $image = $request->file('image');
                    $imageName = date('YmdHis') . '_img.' . $image->getClientOriginalExtension();
                    $image->move(public_path('new_wiselook/uploads'), $imageName);
                    $post->image = $imageName;
                }

                // تعديل الفيديو
                if ($request->hasFile('video')) {
                    if ($post->video && File::exists(public_path('new_wiselook/uploads/' . $post->video))) {
                        File::delete(public_path('new_wiselook/uploads/' . $post->video));
                    }
                    $video = $request->file('video');
                    $videoName = date('YmdHis') . '_vid.' . $video->getClientOriginalExtension();
                    $video->move(public_path('new_wiselook/uploads'), $videoName);
                    $post->video = $videoName;
                }
            } else {
                $post->content = $request->question;
            }

            $post->save();

            // تحديث استطلاع الرأي
            if ($post->post_type_id == 2) {
                $poll = Poll::where('post_id', $post->id)->first();
                if ($poll) {
                    $poll->question = $request->question;
                    $poll->save();

                    if ($request->has('option_ids')) {
                        foreach ($request->option_ids as $index => $optionId) {
                            $opt = PollOption::find($optionId);
                            if ($opt && isset($request->options[$index])) {
                                $opt->content = trim($request->options[$index]);
                                $opt->save();
                            }
                        }
                    }
                }
            }

            DB::commit();

            $notification = [
                'message' => 'تم تحديث الموضوع بنجاح',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.posts')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء تعديل البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف موضوع
     */
    public function deletePost($id)
    {
        $post = Post::findOrFail($id);

        DB::beginTransaction();
        try {
            // حذف الملفات المرتبطة
            if ($post->image && File::exists(public_path('new_wiselook/uploads/' . $post->image))) {
                File::delete(public_path('new_wiselook/uploads/' . $post->image));
            }

            if ($post->video && File::exists(public_path('new_wiselook/uploads/' . $post->video))) {
                File::delete(public_path('new_wiselook/uploads/' . $post->video));
            }

            // حذف الاستطلاع والخيارات إذا وجدا
            if ($post->post_type_id == 2) {
                $poll = Poll::where('post_id', $post->id)->first();
                if ($poll) {
                    PollOption::where('poll_id', $poll->id)->delete();
                    $poll->delete();
                }
            }

            $post->delete();

            DB::commit();

            $notification = [
                'message' => 'تم حذف الموضوع بنجاح',
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف البيانات: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف مجموعة من المواضيع (Bulk Delete)
     */
    public function bulkDeletePosts(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $ids = $request->ids;

        DB::beginTransaction();
        try {
            $posts = Post::whereIn('id', $ids)->get();

            foreach ($posts as $post) {
                // حذف الملفات المرتبطة
                if ($post->image && File::exists(public_path('new_wiselook/uploads/' . $post->image))) {
                    File::delete(public_path('new_wiselook/uploads/' . $post->image));
                }

                if ($post->video && File::exists(public_path('new_wiselook/uploads/' . $post->video))) {
                    File::delete(public_path('new_wiselook/uploads/' . $post->video));
                }

                // حذف الاستطلاع والخيارات إن وجدا
                if ($post->post_type_id == 2) {
                    $poll = Poll::where('post_id', $post->id)->first();
                    if ($poll) {
                        PollOption::where('poll_id', $poll->id)->delete();
                        $poll->delete();
                    }
                }

                // حذف التثبيت المرتبط إن وجد
                \App\Models\PinnedPost::where('post_id', $post->id)->delete();

                $post->delete();
            }

            DB::commit();

            $notification = [
                'message' => 'تم حذف المواضيع المحددة بنجاح (' . count($posts) . ' موضوع)',
                'alert-type' => 'success'
            ];

            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'message' => 'حدث خطأ أثناء حذف المواضيع: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * حذف الموضوع من الفرونت إند عبر AJAX
     */
    public function deleteFrontendPost($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== \Illuminate\Support\Facades\Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا الموضوع.'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // حذف الملفات المرتبطة
            if ($post->image && \Illuminate\Support\Facades\File::exists(public_path('new_wiselook/uploads/' . $post->image))) {
                \Illuminate\Support\Facades\File::delete(public_path('new_wiselook/uploads/' . $post->image));
            }

            if ($post->video && \Illuminate\Support\Facades\File::exists(public_path('new_wiselook/uploads/' . $post->video))) {
                \Illuminate\Support\Facades\File::delete(public_path('new_wiselook/uploads/' . $post->video));
            }

            // حذف الاستطلاع والخيارات إذا وجدا
            if ($post->post_type_id == 2) {
                $poll = Poll::where('post_id', $post->id)->first();
                if ($poll) {
                    PollOption::where('poll_id', $poll->id)->delete();
                    $poll->delete();
                }
            }

            // حذف التثبيت المرتبط إن وجد
            \App\Models\PinnedPost::where('post_id', $post->id)->delete();

            $post->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الموضوع بنجاح.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الموضوع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إيقاف تفعيل الموضوع
     */
    public function inactivePost($id)
    {
        $post = Post::findOrFail($id);
        $post->is_active = 0;
        $post->save();

        $notification = [
            'message' => 'تم إيقاف تفعيل الموضوع بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * تفعيل الموضوع
     */
    public function activePost($id)
    {
        $post = Post::findOrFail($id);
        $post->is_active = 1;
        $post->save();

        $notification = [
            'message' => 'تم تفعيل الموضوع بنجاح',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }

    /**
     * جلب قائمة المتفاعلين مع المنشور
     */
    public function getPostReactions($id)
    {
        $reactions = Reaction::with(['user', 'type'])
            ->where('content_id', $id)
            ->where('content_type_id', 1)
            ->where('is_active', 1)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'reactions' => $reactions->map(function ($reaction) {
                return [
                    'user_name' => $reaction->user ? trim($reaction->user->first_name . ' ' . $reaction->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($reaction->user && $reaction->user->profile_picture && $reaction->user->profile_picture != 'non') 
                        ? (filter_var($reaction->user->profile_picture, FILTER_VALIDATE_URL) ? $reaction->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reaction->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'type' => $reaction->type ? $reaction->type->type : 'like'
                ];
            })
        ]);
    }

    /**
     * جلب قائمة التعليقات والردود عليها للمنشور
     */
    public function getPostComments($id)
    {
        $comments = Comment::with(['user', 'replies.user'])
            ->where('post_id', $id)
            ->where('parent_id', 0)
            ->where('is_active', 1)
            ->latest()
            ->get();

        $userId = auth()->check() ? auth()->id() : null;
        $likedCommentIds = [];

        if ($userId) {
            $allCommentIds = [];
            foreach ($comments as $comment) {
                $allCommentIds[] = $comment->id;
                if ($comment->replies) {
                    foreach ($comment->replies as $reply) {
                        $allCommentIds[] = $reply->id;
                    }
                }
            }
            
            $likedCommentIds = Reaction::where('user_id', $userId)
                ->where('content_type_id', 2) // 2 للتعليق
                ->whereIn('content_id', $allCommentIds)
                ->where('is_active', 1)
                ->pluck('content_id')
                ->toArray();
        }

        return response()->json([
            'success' => true,
            'comments' => $comments->map(function ($comment) use ($likedCommentIds) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_id' => $comment->user ? $comment->user->id : null,
                    'user_name' => $comment->user ? trim($comment->user->first_name . ' ' . $comment->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($comment->user && $comment->user->profile_picture && $comment->user->profile_picture != 'non') 
                        ? (filter_var($comment->user->profile_picture, FILTER_VALIDATE_URL) ? $comment->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $comment->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
                    'reaction_count' => (int)($comment->reaction_count ?? 0),
                    'user_liked' => in_array($comment->id, $likedCommentIds),
                    'replies' => $comment->replies ? $comment->replies->map(function ($reply) use ($likedCommentIds) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user_id' => $reply->user ? $reply->user->id : null,
                            'user_name' => $reply->user ? trim($reply->user->first_name . ' ' . $reply->user->last_name) : 'مستخدم غير معروف',
                            'profile_picture' => ($reply->user && $reply->user->profile_picture && $reply->user->profile_picture != 'non') 
                                ? (filter_var($reply->user->profile_picture, FILTER_VALIDATE_URL) ? $reply->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reply->user->profile_picture) 
                                : url('upload/no_image.jpg'),
                            'created_at' => $reply->created_at ? $reply->created_at->diffForHumans() : '',
                            'reaction_count' => (int)($reply->reaction_count ?? 0),
                            'user_liked' => in_array($reply->id, $likedCommentIds),
                        ];
                    }) : []
                ];
            })
        ]);
    }

    /**
     * شاشة خيارات تثبيت موضوع
     */
    public function pinPostForm($id)
    {
        $post = Post::findOrFail($id);
        return view('admin.posts.pin_post', compact('post'));
    }

    /**
     * حفظ خيارات التثبيت
     */
    public function pinPostStore(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'pin_scope' => 'required|in:profile,home',
            'pin_order' => 'required|integer|min:0',
        ], [
            'pin_scope.required' => 'يرجى تحديد نطاق التثبيت.',
            'pin_order.required' => 'يرجى تحديد ترتيب التثبيت.',
        ]);

        DB::beginTransaction();
        try {
            // إلغاء تثبيت هذا الموضوع في هذا النطاق أولاً لتفادي تكراره وتحديث الترتيب
            \App\Models\PinnedPost::where('post_id', $request->post_id)
                ->where('pin_scope', $request->pin_scope)
                ->delete();

            \App\Models\PinnedPost::create([
                'post_id' => $request->post_id,
                'pin_scope' => $request->pin_scope,
                'pin_order' => $request->pin_order,
                'pinned_at' => now(),
            ]);

            DB::commit();

            $notification = [
                'message' => 'تم تثبيت الموضوع بنجاح.',
                'alert-type' => 'success'
            ];

            return redirect()->route('all.posts')->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with([
                'message' => 'حدث خطأ أثناء تثبيت الموضوع: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * إلغاء تثبيت موضوع
     */
    public function unpinPost($id)
    {
        $deleted = \App\Models\PinnedPost::where('post_id', $id)->delete();
        if ($deleted) {
            
            $notification = [
                'message' => 'تم إلغاء تثبيت الموضوع بنجاح.',
                'alert-type' => 'success'
            ];
            return redirect()->back()->with($notification);
        }

        return redirect()->back()->with([
            'message' => 'الموضوع غير مثبت بالفعل.',
            'alert-type' => 'info'
        ]);
    }

    /**
     * جلب كافة تفاصيل المنشور مع التفاعلات والتعليقات والردود عليها
     */
    public function getPostDetailsJson($id)
    {
        $post = Post::with([
            'user',
            'poll.options',
            'pin',
            'reactions.user',
            'comments' => function($q) {
                $q->latest();
            },
            'comments.user',
            'comments.replies.user'
        ])
        ->withCount('reactions')
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'image' => $post->image ? 'http://localhost:8888/new_wiselook/uploads/' . $post->image : null,
                'video' => $post->video ? 'http://localhost:8888/new_wiselook/uploads/' . $post->video : null,
                'privacy_level_id' => $post->privacy_level_id,
                'like_count' => $post->like_count,
                'comment_count' => $post->comment_count,
                'share_count' => $post->share_count,
                'is_active' => $post->is_active,
                'post_type_id' => $post->post_type_id,
                'created_at' => $post->created_at ? $post->created_at->diffForHumans() : 'غير محدد',
                'created_at_formatted' => $post->created_at ? $post->created_at->format('Y-m-d H:i:s') : 'غير محدد',
                'user' => $post->user ? [
                    'name' => trim($post->user->first_name . ' ' . $post->user->last_name),
                    'profile_picture' => ($post->user->profile_picture && $post->user->profile_picture != 'non') 
                        ? (filter_var($post->user->profile_picture, FILTER_VALIDATE_URL) ? $post->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $post->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                ] : null,
                'poll' => ($post->post_type_id == 2 && $post->poll) ? [
                    'question' => $post->poll->question,
                    'total_votes' => $post->poll->total_votes,
                    'options' => $post->poll->options ? $post->poll->options->map(function ($opt) {
                        return [
                            'content' => $opt->content,
                            'vote_count' => $opt->vote_count
                        ];
                    }) : []
                ] : null,
                'pin' => $post->pin ? [
                    'pin_scope' => $post->pin->pin_scope == 'home' ? 'الرئيسية' : 'الملف الشخصي',
                    'pin_order' => $post->pin->pin_order,
                ] : null,
                'reactions' => $post->reactions ? $post->reactions->map(function ($react) {
                    return [
                        'user_name' => $react->user ? trim($react->user->first_name . ' ' . $react->user->last_name) : 'مستخدم غير معروف',
                        'profile_picture' => ($react->user && $react->user->profile_picture && $react->user->profile_picture != 'non') 
                            ? (filter_var($react->user->profile_picture, FILTER_VALIDATE_URL) ? $react->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $react->user->profile_picture) 
                            : url('upload/no_image.jpg'),
                    ];
                }) : [],
                'comments' => $post->comments ? $post->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'user_name' => $comment->user ? trim($comment->user->first_name . ' ' . $comment->user->last_name) : 'مستخدم غير معروف',
                        'profile_picture' => ($comment->user && $comment->user->profile_picture && $comment->user->profile_picture != 'non') 
                            ? (filter_var($comment->user->profile_picture, FILTER_VALIDATE_URL) ? $comment->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $comment->user->profile_picture) 
                            : url('upload/no_image.jpg'),
                        'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : '',
                        'replies' => $comment->replies ? $comment->replies->map(function ($reply) {
                            return [
                                'id' => $reply->id,
                                'content' => $reply->content,
                                'user_name' => $reply->user ? trim($reply->user->first_name . ' ' . $reply->user->last_name) : 'مستخدم غير معروف',
                                'profile_picture' => ($reply->user && $reply->user->profile_picture && $reply->user->profile_picture != 'non') 
                                    ? (filter_var($reply->user->profile_picture, FILTER_VALIDATE_URL) ? $reply->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reply->user->profile_picture) 
                                    : url('upload/no_image.jpg'),
                                'created_at' => $reply->created_at ? $reply->created_at->diffForHumans() : ''
                            ];
                        }) : []
                    ];
                }) : []
            ]
        ]);
    }

    /**
     * حفظ تعليق جديد أو رد على تعليق من الواجهة الأمامية
     */
    public function storeCommentFrontend(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|integer'
        ]);

        $parentId = (int)$request->input('parent_id', 0);

        // إنشاء التعليق
        $comment = Comment::create([
            'post_id' => (int)$postId,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'parent_id' => $parentId,
            'is_active' => 1,
            'reaction_count' => 0,
            'reply_count' => 0
        ]);

        // تحديث العدادات
        if ($parentId > 0) {
            Comment::where('id', $parentId)->increment('reply_count');
        } else {
            Post::where('id', $postId)->increment('comment_count');
        }

        // إرسال إشعار لصاحب التعليق/الرد الأصلي
        if ($parentId > 0) {
            $parentComment = Comment::with('post')->find($parentId);
            if ($parentComment && $parentComment->user_id !== auth()->id()) {
                $postTitle = $parentComment->post ? \Illuminate\Support\Str::limit($parentComment->post->content, 35) : 'موضوع';
                
                // تحديد نوع الإشعار بناءً على ما إذا كان الأب تعليقاً رئيسياً أم رداً
                $isParentReply = $parentComment->parent_id > 0;
                $notifType = $isParentReply ? 'reply_to_reply' : 'comment_reply';
                $message = $isParentReply 
                    ? 'قام ' . auth()->user()->first_name . ' ' . auth()->user()->last_name . ' بالرد على ردك في موضوع: "' . $postTitle . '"'
                    : 'قام ' . auth()->user()->first_name . ' ' . auth()->user()->last_name . ' بالرد على تعليقك في موضوع: "' . $postTitle . '"';

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'type' => 'App\Notifications\GeneralNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $parentComment->user_id,
                    'data' => json_encode([
                        'type' => $notifType,
                        'sender_id' => auth()->id(),
                        'sender_name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
                        'avatar' => auth()->user()->profile_picture,
                        'message' => $message,
                        'post_id' => (int)$postId
                    ]),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        // إرجاع استجابة JSON مع تفاصيل التعليق المضاف
        return response()->json([
            'success' => true,
            'message' => 'تم إضافة التعليق بنجاح',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'user_id' => auth()->id(),
                'user_name' => auth()->user() ? trim(auth()->user()->first_name . ' ' . auth()->user()->last_name) : 'مستخدم',
                'profile_picture' => (auth()->user() && auth()->user()->profile_picture && auth()->user()->profile_picture != 'non') 
                    ? (filter_var(auth()->user()->profile_picture, FILTER_VALIDATE_URL) ? auth()->user()->profile_picture : asset('new_wiselook/uploads/' . auth()->user()->profile_picture)) 
                    : asset('upload/no_image.jpg'),
                'created_at' => $comment->created_at ? $comment->created_at->diffForHumans() : 'الآن',
                'reaction_count' => 0,
                'user_liked' => false,
                'replies' => []
            ]
        ]);
    }

    /**
     * حذف تعليق أو رد من الواجهة الأمامية للمستخدم صاحب التعليق أو كاتب الموضوع
     */
    public function deleteCommentFrontend($id)
    {
        $comment = Comment::with('post')->find($id);

        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'التعليق غير موجود.'
            ], 404);
        }

        $post = $comment->post;

        // السماح فقط لكاتب التعليق أو صاحب الموضوع بحذفه
        if ($comment->user_id !== auth()->id() && ($post && $post->user_id !== auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا التعليق.'
            ], 403);
        }

        try {
            DB::beginTransaction();

            if ($comment->parent_id > 0) {
                // تقليل عدد الردود من التعليق الأصلي
                Comment::where('id', $comment->parent_id)->where('reply_count', '>', 0)->decrement('reply_count');
            } else {
                // تقليل عدد التعليقات من الموضوع
                if ($post && $post->comment_count > 0) {
                    $post->decrement('comment_count');
                }
                
                // حذف الردود التابعة لهذا التعليق الأصلي
                $replyIds = Comment::where('parent_id', $id)->pluck('id')->toArray();
                if (!empty($replyIds)) {
                    Reaction::whereIn('content_id', $replyIds)->where('content_type_id', 2)->delete();
                    Comment::where('parent_id', $id)->delete();
                }
            }

            // حذف الإعجابات بالتعليق
            Reaction::where('content_id', $id)->where('content_type_id', 2)->delete();

            // حذف التعليق نفسه
            $comment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف التعليق بنجاح.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error deleting comment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف التعليق.'
            ], 500);
        }
    }

    /**
     * التفاعل بالإعجاب مع تعليق أو رد
     */
    public function reactCommentFrontend(Request $request, $id)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,remove'
        ]);

        $comment = Comment::findOrFail($id);
        $userId = auth()->id();

        if ($request->reaction_type === 'like') {
            Reaction::updateOrCreate(
                [
                    'user_id' => $userId,
                    'content_id' => $id,
                    'content_type_id' => 2, // 2 للتعليق
                    'reaction_type_id' => 1 // 1 للإعجاب
                ],
                [
                    'is_active' => 1
                ]
            );

            $comment->increment('reaction_count');
        } else {
            Reaction::where('user_id', $userId)
                ->where('content_id', $id)
                ->where('content_type_id', 2)
                ->update(['is_active' => 0]);

            if ($comment->reaction_count > 0) {
                $comment->decrement('reaction_count');
            }
        }

        $comment->refresh();

        return response()->json([
            'success' => true,
            'reaction_count' => (int)$comment->reaction_count
        ]);
    }

    /**
     * جلب قائمة المستخدمين الذين أعجبوا بالتعليق أو الرد
     */
    public function getCommentReactions($id)
    {
        $reactions = Reaction::with(['user'])
            ->where('content_id', $id)
            ->where('content_type_id', 2) // 2 للتعليق
            ->where('is_active', 1)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'reactions' => $reactions->map(function ($reaction) {
                return [
                    'user_name' => $reaction->user ? trim($reaction->user->first_name . ' ' . $reaction->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($reaction->user && $reaction->user->profile_picture && $reaction->user->profile_picture != 'non') 
                        ? (filter_var($reaction->user->profile_picture, FILTER_VALIDATE_URL) ? $reaction->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reaction->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'rank' => __t('member_word'),
                    'created_at' => $reaction->created_at ? $reaction->created_at->diffForHumans() : ''
                ];
            })
        ]);
    }

    /**
     * التفاعل بتأييد (لايك) منشور من الواجهة الأمامية
     */
    public function reactPostFrontend(Request $request, $id)
    {
        $request->validate([
            'reaction_type' => 'required|string|in:like,remove'
        ]);

        $post = Post::findOrFail($id);
        $userId = auth()->id();

        if ($request->reaction_type === 'like') {
            Reaction::updateOrCreate(
                [
                    'user_id' => $userId,
                    'content_id' => $id,
                    'content_type_id' => 1, // 1 للمنشور
                    'reaction_type_id' => 1 // 1 للإعجاب/التأييد
                ],
                [
                    'is_active' => 1
                ]
            );

            $post->increment('like_count');
        } else {
            Reaction::where('user_id', $userId)
                ->where('content_id', $id)
                ->where('content_type_id', 1)
                ->update(['is_active' => 0]);

            if ($post->like_count > 0) {
                $post->decrement('like_count');
            }
        }

        $post->refresh();

        return response()->json([
            'success' => true,
            'like_count' => (int)$post->like_count
        ]);
    }

    /**
     * جلب قائمة المؤيدين لمنشور من الواجهة الأمامية
     */
    public function getPostReactionsPublic($id)
    {
        $reactions = Reaction::with(['user'])
            ->where('content_id', $id)
            ->where('content_type_id', 1) // 1 للمنشور
            ->where('is_active', 1)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'reactions' => $reactions->map(function ($reaction) {
                return [
                    'user_name' => $reaction->user ? trim($reaction->user->first_name . ' ' . $reaction->user->last_name) : 'مستخدم غير معروف',
                    'profile_picture' => ($reaction->user && $reaction->user->profile_picture && $reaction->user->profile_picture != 'non') 
                        ? (filter_var($reaction->user->profile_picture, FILTER_VALIDATE_URL) ? $reaction->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/' . $reaction->user->profile_picture) 
                        : url('upload/no_image.jpg'),
                    'rank' => __t('member_word'),
                    'created_at' => $reaction->created_at ? $reaction->created_at->diffForHumans() : ''
                ];
            })
        ]);
    }

    /**
     * عرض موضوع واحد (تفاصيل المنشور) مع التعليقات للجمهور والزوار
     */
    public function showPostPublic($id)
    {
        $post = Post::with(['user', 'poll.options', 'wiseRatings.user'])->findOrFail($id);
        
        // Return a premium public view for the single post
        return view('frontend.wiselook.pages.post_details', compact('post'));
    }
}
