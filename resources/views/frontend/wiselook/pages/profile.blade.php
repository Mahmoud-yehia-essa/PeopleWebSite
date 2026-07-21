@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $avatarUrl = url('upload/no_image.jpg');
    if ($user->profile_picture && $user->profile_picture !== 'non') {
        $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
            ? $user->profile_picture
            : asset('new_wiselook/uploads/' . $user->profile_picture);
    }
    
    $coverUrl = (!empty($user->cover_picture) && $user->cover_picture != 'non') 
        ? asset('new_wiselook/uploads/' . $user->cover_picture) 
        : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1964&auto=format&fit=crop';

    $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
    if (empty($fullName)) {
        $fullName = __t('default_wisdom_user');
    }

    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
    $flexAlignSelf = $dir === 'rtl' ? 'lg:justify-end' : 'lg:justify-start';

    // جلب قائمة أصدقاء المستخدم النشطين
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
    
    $friends = \App\Models\User::whereIn('id', $friendIds)->take(9)->get();
    $totalFriendsCount = count($friendIds);

    // حساب الأصدقاء المشتركين للزائر المسجل الدخول
    $myFriendIds = [];
    if (auth()->check()) {
        $myId = auth()->id();
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

    // جلب علاقات أصدقاء هذا المستخدم بكفاءة لحساب الأصدقاء المشتركين
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
@endphp
<!-- Cover & Header Section -->
<div class="w-full relative bg-white pb-6 border-b border-primary/5">
    <!-- Cover Image -->
    <div class="w-full h-64 md:h-80 lg:h-96 relative overflow-hidden">
        <img alt="Cover Photo" class="w-full h-full object-cover" src="{{ $coverUrl }}">
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
    </div>
    
    <!-- Profile Info Container -->
    <div class="max-w-[1200px] mx-auto px-4 md:px-8 relative -mt-20 sm:-mt-24 pb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-end gap-6 justify-between">
            <!-- Avatar -->
            <div class="w-32 h-32 sm:w-40 sm:h-40 rounded-full border-4 border-white bg-surface-container-high overflow-hidden shadow-lg flex-shrink-0 z-10 relative cursor-pointer group/avatar-view">
                <img id="avatar-view-trigger" alt="{{ $fullName }}" class="w-full h-full object-cover transition-transform duration-300 group-hover/avatar-view:scale-105" src="{{ $avatarUrl }}">
                <div class="absolute inset-0 bg-black/35 flex items-center justify-center opacity-0 group-hover/avatar-view:opacity-100 transition-opacity duration-300 z-20">
                    <span class="material-symbols-outlined text-white text-[28px] scale-90 group-hover/avatar-view:scale-100 transition-transform duration-300">visibility</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-3 w-full sm:w-auto mt-4 sm:mt-0 mb-2">
                @if(Auth::check() && Auth::id() == $user->id)
                    <a href="{{ route('profile.edit_form') }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-primary-container text-white rounded-lg font-label-md text-label-md hover:bg-primary transition-colors flex items-center justify-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                        {{ __t('edit_profile') }}
                    </a>
                @elseif(Auth::check())
                    @php
                        $friendship = \App\Models\Friendship::where(function($q) use ($user) {
                            $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
                        })->orWhere(function($q) use ($user) {
                            $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
                        })->first();
                    @endphp

                    @if($friendship)
                        @if($friendship->is_active == 1)
                            <!-- Friends -->
                            <a href="{{ route('frontend.messages', $user->id) }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:bg-primary-dark transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                <span class="material-symbols-outlined text-[20px]">chat</span>
                                إرسال رسالة
                            </a>
                        @else
                            @if($friendship->sender_id == Auth::id())
                                <!-- Request Sent: Cancel Button -->
                                <button id="profile-cancel-request-btn" data-friendship-id="{{ $friendship->id }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-error text-white rounded-lg font-label-md text-label-md hover:bg-error-dark transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="material-symbols-outlined text-[20px]">cancel</span>
                                    {{ __t('cancel_request') }}
                                </button>
                                <a href="{{ route('frontend.messages', $user->id) }}" class="flex-1 sm:flex-none px-4 py-2.5 border border-primary text-primary hover:bg-primary/5 rounded-lg font-label-md text-label-md transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    إرسال رسالة
                                </a>
                            @else
                                <!-- Request Received -->
                                <form action="{{ route('frontend.friendships.accept', $friendship->id) }}" method="POST" class="flex-1 sm:flex-none">
                                    @csrf
                                    <button type="submit" class="w-full px-6 py-2.5 bg-secondary text-white rounded-lg font-label-md text-label-md hover:bg-secondary-dark transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                                        {{ __t('accept_friendship') }}
                                    </button>
                                </form>
                                <a href="{{ route('frontend.messages', $user->id) }}" class="flex-1 sm:flex-none px-4 py-2.5 border border-primary text-primary hover:bg-primary/5 rounded-lg font-label-md text-label-md transition-colors flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    إرسال رسالة
                                </a>
                            @endif
                        @endif
                    @else
                        <!-- No friendship, Add Friend button & Message button -->
                        <button id="profile-add-friend-btn" data-receiver-id="{{ $user->id }}" class="flex-1 sm:flex-none px-6 py-2.5 bg-primary text-white rounded-lg font-label-md text-label-md hover:bg-primary-dark transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">person_add</span>
                            {{ __t('add_friend') }}
                        </button>
                        <a href="{{ route('frontend.messages', $user->id) }}" class="flex-1 sm:flex-none px-4 py-2.5 border border-primary text-primary hover:bg-primary/5 rounded-lg font-label-md text-label-md transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            <span class="material-symbols-outlined text-[20px]">chat</span>
                            إرسال رسالة
                        </a>
                    @endif
                @endif
                <button class="flex-1 sm:flex-none px-6 py-2.5 border-2 border-primary text-primary rounded-lg font-label-md text-label-md hover:bg-surface-container-low transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">share</span>
                    {{ __t('share') }}
                </button>
            </div>
        </div>
        
        <!-- Bio & Stats -->
        <div class="mb-6 mt-4 text-center {{ $textAlign === 'text-right' ? 'sm:text-right' : 'sm:text-left' }}">
            <h1 class="font-headline-lg text-headline-lg text-on-surface font-bold">{{ $fullName }}</h1>
            <p class="font-body-lg text-body-lg text-on-surface-variant mt-1 flex items-center justify-center sm:justify-start gap-1.5">
                @if($user->role === 'admin')
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-secondary">workspace_premium</span>
                        <span>{{ __t('platform_admin') }}</span>
                    </span>
                @elseif($user->role === 'owner')
                    <span class="inline-flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-secondary">workspace_premium</span>
                        <span>{{ __t('platform_owner') }}</span>
                    </span>
                @elseif($user->rank)
                    @php
                        $rankPhoto = $user->rank->photo;
                        $rankPhotoPath = url('upload/no_image.jpg');
                        if (!empty($rankPhoto) && file_exists(public_path('upload/rankings/' . $rankPhoto))) {
                            $rankPhotoPath = asset('upload/rankings/' . $rankPhoto);
                        }
                    @endphp
                    <span class="inline-flex items-center gap-1.5" style="display: inline-flex; align-items: center; gap: 6px; vertical-align: middle;">
                        <img src="{{ $rankPhotoPath }}" alt="{{ __t($user->rank->rank_name) }}" style="width: 22px; height: 22px; object-fit: contain; margin-left: 2px;">
                        <span class="font-bold text-sm" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ __t($user->rank->rank_name) }}</span>
                    </span>
                @else
                    <span>{{ __t('technical_advisor') }}</span>
                @endif
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-2">
            <div class="lg:col-span-2">
                <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed max-w-3xl {{ $textAlign }}">
                    {{ $user->bio ?: __t('no_bio_written') }}
                </p>
            </div>
            <div class="flex gap-8 {{ $flexAlignSelf }} items-center justify-center">
                <div class="text-center">
                    <div class="font-headline-md text-2xl font-bold text-primary">{{ $posts->count() }}</div>
                    <div class="font-label-sm text-xs text-on-surface-variant">{{ __t('topics_count_label') }}</div>
                </div>
                <div class="w-px h-12 bg-outline-variant"></div>
                <div class="text-center">
                    <div class="font-headline-md text-2xl font-bold text-secondary">{{ number_format($user->points ?? 0) }}</div>
                    <div class="font-label-sm text-xs text-on-surface-variant">{{ __t('wisdom_points_label') }}</div>
                </div>
                <div class="w-px h-12 bg-outline-variant"></div>
                <div class="text-center">
                    <div class="font-headline-md text-2xl font-bold text-primary">{{ number_format($user->friend_count ?? 0) }}</div>
                    <div class="font-label-sm text-xs text-on-surface-variant">{{ __t('friend_count_label') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Layout Grid -->
<div class="max-w-[1200px] mx-auto px-4 md:px-8 mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 pb-16">
    <!-- Main Feed (Center/Right) -->
    <div class="lg:col-span-8 space-y-6">
        <div id="profile-posts-container" class="space-y-6">
            @forelse($posts as $post)
                @include('frontend.wiselook.pages.profile_post_card', ['post' => $post])
            @empty
                <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-8 text-center text-on-surface-variant shadow-sm w-full">
                    <span class="material-symbols-outlined text-4xl text-primary mb-2">post_add</span>
                    <p class="font-body-md">{{ __t('no_contributions_yet') }}</p>
                </div>
            @endforelse
        </div>
        
        <!-- Loading Spinner -->
        <div id="profile-posts-loader" class="text-center py-4 hidden">
            <div class="animate-spin inline-block rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>
    </div>
    
    <!-- Sidebar (Left) -->
    <aside class="lg:col-span-4 space-y-6">
        <!-- Achievements & Ratings / الإنجازات والتقييمات -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <h3 class="font-title-lg text-sm font-bold text-primary mb-4 flex items-center gap-2 border-b border-primary/5 pb-2">
                <span class="material-symbols-outlined text-secondary">workspace_premium</span>
                {{ __t('achievements_ratings') }}
            </h3>
            
            <div class="space-y-6">

                <!-- Wise Rated Posts / المواضيع المقيمة من الحكماء -->
                <div class="space-y-3 pb-4 border-b border-outline-variant/40">
                    <h4 class="text-xs font-bold text-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-secondary">gavel</span>
                        {{ __t('wise_rated_posts') }}
                    </h4>
                    @if($wiseRatedPosts->isEmpty())
                        <p class="text-[11px] text-on-surface-variant italic {{ $textAlign }}">{{ __t('no_rated_posts_yet') }}</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach($wiseRatedPosts as $wrPost)
                                @php
                                    $ratingVal = floatval($wrPost->wise_rating);
                                    $ratingBg = $ratingVal >= 8 ? 'bg-green-100 text-green-800' : ($ratingVal >= 6 ? 'bg-amber-100 text-amber-800' : 'bg-orange-100 text-orange-800');
                                @endphp
                                <li class="flex items-start justify-between gap-3 {{ $textAlign }}">
                                    <a href="{{ route('frontend.posts.show', $wrPost->id) }}" class="text-xs font-bold text-on-surface hover:text-primary hover:underline line-clamp-2 leading-relaxed flex-grow">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($wrPost->content), 55) }}
                                    </a>
                                    <span class="{{ $ratingBg }} text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0">
                                        {{ number_format($ratingVal, 1) }}/10
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Points Posts / مواضيع نالت نقاط تقييم -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-secondary">military_tech</span>
                        {{ __t('points_awarded_posts') }}
                    </h4>
                    @if($wisePointLogs->isEmpty())
                        <p class="text-[11px] text-on-surface-variant italic {{ $textAlign }}">{{ __t('no_points_posts_yet') }}</p>
                    @else
                        <ul class="space-y-2.5">
                            @foreach($wisePointLogs as $log)
                                @if($log->post)
                                    <li class="flex items-start justify-between gap-3 {{ $textAlign }}">
                                        <a href="{{ route('frontend.posts.show', $log->post->id) }}" class="text-xs font-bold text-on-surface hover:text-primary hover:underline line-clamp-2 leading-relaxed flex-grow">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($log->post->content), 55) }}
                                        </a>
                                        <span class="bg-secondary/10 text-secondary text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0">
                                            +{{ $log->points_given }} {{ __t('points_label') }}
                                        </span>
                                    </li>
                                  @endif
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <!-- Friends Section -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex justify-between items-center mb-4 border-b border-primary/5 pb-2">
                <div>
                    <h3 class="font-title-lg text-sm font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary">group</span>
                        {{ __t('friends_title') }}
                    </h3>
                    <span class="text-[11px] text-on-surface-variant/80 font-label-sm block mt-0.5">{{ $totalFriendsCount }} {{ __t('friend_count_label') }}</span>
                </div>
                <a href="{{ route('profile.friends', $user->id) }}" class="text-xs text-primary font-bold hover:underline transition-all">{{ __t('view_all') }}</a>
            </div>
            
            @if($friends->isEmpty())
                <p class="font-body-md text-xs text-on-surface-variant text-center py-4">{{ __t('no_friends_added_yet') }}</p>
            @else
                <div class="grid grid-cols-3 gap-3">
                    @foreach($friends as $friend)
                        @php
                            $friendName = trim($friend->first_name . ' ' . $friend->last_name);
                            $friendAvatar = url('upload/no_image.jpg');
                            if ($friend->profile_picture && $friend->profile_picture !== 'non') {
                                $friendAvatar = filter_var($friend->profile_picture, FILTER_VALIDATE_URL)
                                    ? $friend->profile_picture
                                    : asset('new_wiselook/uploads/' . $friend->profile_picture);
                            }
                            $theirFriendIds = $friendsFriendsMap[$friend->id] ?? [];
                            $mutualCount = count(array_intersect($myFriendIds, $theirFriendIds));
                        @endphp
                        <div class="text-center group">
                            <a href="{{ route('profile.edit', $friend->id) }}" class="block">
                                <div class="aspect-square w-full rounded-xl overflow-hidden border border-outline-variant bg-surface-container-high transition-transform duration-300 group-hover:scale-[1.03] group-hover:shadow-md">
                                    <img src="{{ $friendAvatar }}" alt="{{ $friendName }}" class="w-full h-full object-cover">
                                </div>
                                <h4 class="font-label-md text-[10px] font-bold text-on-surface mt-1.5 line-clamp-1 group-hover:text-primary transition-colors text-center" title="{{ $friendName }}">{{ $friendName }}</h4>
                                @if(auth()->check() && auth()->id() !== $friend->id && $mutualCount > 0)
                                    <button type="button" class="open-mutual-btn text-[9px] text-primary hover:underline font-bold block mt-0.5 text-center w-full bg-transparent border-0 cursor-pointer" data-user-id="{{ $friend->id }}" data-user-name="{{ $friendName }}">
                                        {{ $mutualCount }} {{ __t('mutual_friend_count') }}
                                    </button>
                                @endif
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        
        <!-- Joined Groups / المجموعات المشترك بها -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <h3 class="font-title-lg text-sm font-bold text-primary mb-4 border-b border-primary/5 pb-2">{{ __t('joined_groups') }}</h3>
            @if($joinedGroups->isEmpty())
                <p class="font-body-md text-xs text-on-surface-variant text-center py-4">{{ __t('no_joined_groups_yet') }}</p>
            @else
                <div class="flex flex-wrap gap-2 {{ $textAlign }}">
                    @foreach($joinedGroups as $group)
                        <a href="{{ route('frontend.groups.details', $group->id) }}" class="bg-surface px-4 py-2 rounded-xl font-label-md text-xs text-on-surface flex items-center gap-2 border border-primary/5 hover:bg-primary/5 transition-all hover:text-primary hover:scale-[1.02]">
                            <span class="material-symbols-outlined text-[18px] text-primary">groups</span>
                            <span>{{ $group->title }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </aside>
</div>

<!-- Profile Image Viewer Modal -->
<div id="avatar-viewer-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/80 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-2xl w-full max-h-[85vh] flex flex-col items-center justify-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300">
        <!-- Close Button -->
        <button id="close-viewer-btn" class="absolute -top-12 right-2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">close</span>
        </button>
        
        <!-- Image Card -->
        <div class="bg-surface-container-lowest/10 p-2 rounded-2xl border border-white/10 shadow-2xl overflow-hidden backdrop-blur-sm">
            <img id="viewer-img" alt="Profile Image" class="max-w-full max-h-[70vh] rounded-xl object-contain shadow-inner transition-transform duration-300 hover:scale-102" src="">
        </div>

        <!-- Details / Download Action -->
        <div class="mt-4 flex gap-4">
            <a id="download-avatar-btn" href="" download="profile_picture.jpg" class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg font-label-md text-xs backdrop-blur-sm transition-all duration-200 flex items-center gap-2 border border-white/10">
                <span class="material-symbols-outlined text-[18px]">download</span>
                {{ __t('download_image') }}
            </a>
        </div>
    </div>
</div>

<!-- Mutual Friends Modal -->
<div id="mutual-friends-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-md w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[70vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low {{ $textAlign }}" style="direction: {{ $dir }};">
            <h3 class="font-headline-md text-base font-bold text-primary" id="mutual-friends-title">{{ __t('mutual_friends_title') }}</h3>
            <button id="close-mutual-friends-btn" class="text-on-surface-variant hover:text-on-surface p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <!-- Modal Body (List) -->
        <div class="p-6 overflow-y-auto flex-grow {{ $textAlign }} space-y-4" id="mutual-friends-list" style="direction: {{ $dir }};">
            <!-- Dynamic list will be rendered here -->
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .ambient-shadow-low { box-shadow: 0px 4px 20px rgba(26, 82, 55, 0.05); }
    .ambient-shadow-mid { box-shadow: 0px 8px 30px rgba(26, 82, 55, 0.1); }
    
    /* Image Viewer Modal Transitions */
    #avatar-viewer-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #avatar-viewer-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Mutual Friends Modal Transitions */
    #mutual-friends-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #mutual-friends-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const _tp = {
        totalVotes:                '{{ __t("total_votes_label") }}',
        mustLoginVote:             '{{ __t("must_login_vote") }}',
        voteError:                 '{{ __t("vote_error") }}',
        cancelRequest:             '{{ __t("cancel_request") }}',
        addFriend:                 '{{ __t("add_friend") }}',
        friendRequestError:        '{{ __t("friend_request_error") }}',
        cancelFriendRequestError:  '{{ __t("cancel_friend_request_error") }}',
        mutualFriendsWith:         '{{ __t("mutual_friends_with") }}',
        noMutualFriends:           '{{ __t("no_mutual_friends") }}',
        mutualFriendLabel:         '{{ __t("mutual_friend_label") }}',
        viewProfile:               '{{ __t("view_profile") }}',
        errorLoadingData:          '{{ __t("error_loading_data") }}',
    };

    // --- Infinite Scroll / Lazy Loading for Profile Posts ---
    let postPage = 2; // Start from page 2 since page 1 is rendered on server
    let postsLoading = false;
    let hasMorePosts = true;

    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
            if (!postsLoading && hasMorePosts) {
                loadMoreProfilePosts();
            }
        }
    });

    function loadMoreProfilePosts() {
        postsLoading = true;
        $('#profile-posts-loader').removeClass('hidden');

        $.ajax({
            url: "{{ route('profile.posts.api', $user->id) }}?page=" + postPage + "&per_page=10",
            type: "GET",
            success: function(response) {
                $('#profile-posts-loader').addClass('hidden');
                postsLoading = false;
                hasMorePosts = response.has_more;
                
                if (response.html.trim() !== '') {
                    $('#profile-posts-container').append(response.html);
                    postPage++;
                } else {
                    hasMorePosts = false;
                }
            },
            error: function() {
                $('#profile-posts-loader').addClass('hidden');
                postsLoading = false;
            }
        });
    }

    // --- Image Viewer Lightbox ---
    const modal = $('#avatar-viewer-modal');
    const modalImg = $('#viewer-img');
    const downloadBtn = $('#download-avatar-btn');

    $('.group\\/avatar-view').on('click', function(e) {
        e.preventDefault();
        const src = $('#avatar-view-trigger').attr('src');
        modalImg.attr('src', src);
        downloadBtn.attr('href', src);
        
        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        
        // Trigger reflow for transition
        setTimeout(() => {
            modal.addClass('modal-show');
        }, 20);
    });

    function closeViewer() {
        modal.removeClass('modal-show');
        $('body').removeClass('modal-active');
        setTimeout(() => {
            modal.addClass('hidden').removeClass('flex');
        }, 300);
    }

    $('#close-viewer-btn, #avatar-viewer-modal .modal-backdrop').on('click', function() {
        closeViewer();
    });



    // --- Poll Voting Handler ---
    $(document).on('click', '.poll-option-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const optionId = btn.attr('data-option-id');
        const container = btn.closest('.poll-container');
        
        // Prevent multiple clicks during request
        if (btn.hasClass('pointer-events-none')) return;
        container.find('.poll-option-btn').addClass('pointer-events-none');

        $.ajax({
            url: "{{ route('frontend.polls.vote') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                option_id: optionId
            },
            success: function(response) {
                if (response.success) {
                    // Update all options inside this poll container
                    container.find('.total-votes-count').text(_tp.totalVotes + ': ' + response.total_votes);
                    
                    // Clear list and reconstruct options dynamically to update UI beautifully
                    const optionsList = container.find('.poll-options-list');
                    optionsList.empty();
                    
                    response.options.forEach(function(opt) {
                        const isVoted = opt.is_selected;
                        const borderBgClass = isVoted ? 'border-primary bg-primary/5' : 'border-primary/5 bg-surface';
                        const progressClass = isVoted ? 'bg-primary/10' : 'bg-primary/5';
                        
                        const optionHtml = `
                            <div class="poll-option-btn relative w-full p-3 rounded-lg border transition-all duration-200 hover:border-primary/20 cursor-pointer overflow-hidden flex items-center justify-between group ${borderBgClass}" data-option-id="${opt.id}">
                                <div class="progress-bar absolute inset-y-0 right-0 transition-all duration-500 ${progressClass}" style="width: ${opt.percent}%"></div>
                                <span class="relative z-10 font-body-md text-xs font-semibold text-on-surface select-none pr-1 option-text">
                                    ${opt.content}
                                </span>
                                <span class="relative z-10 font-label-sm text-xs font-bold text-primary select-none pl-1 option-percent">
                                    ${opt.percent}% (${opt.votes})
                                </span>
                            </div>
                        `;
                        optionsList.append(optionHtml);
                    });
                }
            },
            error: function(xhr) {
                const defaultMsg = _tp.voteError;
                let msg = defaultMsg;
                if (xhr.status === 401) {
                    msg = _tp.mustLoginVote;
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                
                if (typeof toastr !== "undefined") {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
            },
            complete: function() {
                container.find('.poll-option-btn').removeClass('pointer-events-none');
            }
        });
    });

    // Close on ESC key for both viewers
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!modal.hasClass('hidden')) {
                closeViewer();
            }
            if (!videoModal.hasClass('hidden')) {
                closeVideoViewer();
            }
        }
    });
    // Add Friend from Profile (Delegated)
    $(document).on('click', '#profile-add-friend-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const receiverId = btn.attr('data-receiver-id');
        
        btn.prop('disabled', true).addClass('opacity-50');
        
        $.ajax({
            url: "{{ route('frontend.friendships.request') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                receiver_id: receiverId
            },
            success: function(response) {
                if (response.success) {
                    // Turn it into a Cancel button dynamically
                    btn.prop('disabled', false).removeClass('opacity-50 bg-primary hover:bg-primary-dark')
                       .addClass('bg-error text-white hover:bg-error-dark')
                       .attr('id', 'profile-cancel-request-btn')
                       .attr('data-friendship-id', response.friendship_id)
                       .html(`<span class="material-symbols-outlined text-[20px]">cancel</span> ${_tp.cancelRequest}`);
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || _tp.friendRequestError);
                btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    });

    // Cancel Friend Request from Profile (Delegated)
    $(document).on('click', '#profile-cancel-request-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const friendshipId = btn.attr('data-friendship-id');
        
        btn.prop('disabled', true).addClass('opacity-50');
        
        $.ajax({
            url: `/friendships/delete/${friendshipId}`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Turn it back to an Add Friend button dynamically
                const receiverId = "{{ $user->id }}";
                btn.prop('disabled', false).removeClass('opacity-50 bg-error hover:bg-error-dark')
                   .addClass('bg-primary text-white hover:bg-primary-dark')
                   .attr('id', 'profile-add-friend-btn')
                   .removeAttr('data-friendship-id')
                   .attr('data-receiver-id', receiverId)
                   .html(`<span class="material-symbols-outlined text-[20px]">person_add</span> ${_tp.addFriend}`);
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || _tp.cancelFriendRequestError);
                btn.prop('disabled', false).removeClass('opacity-50');
            }
        });
    });

    // --- Mutual Friends Modal Handlers ---
    const mutualModal = $('#mutual-friends-modal');
    const mutualList = $('#mutual-friends-list');
    const mutualTitle = $('#mutual-friends-title');

    function openMutualModal(userName, userId) {
        mutualTitle.text(_tp.mutualFriendsWith + ' ' + userName);
        mutualList.html(`
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        `);

        // Show layout first
        mutualModal.removeClass('hidden').addClass('flex');
        setTimeout(() => {
            mutualModal.addClass('modal-show');
        }, 10);

        // Fetch mutual friends via AJAX
        $.ajax({
            url: "/friends/mutual/" + userId,
            type: "GET",
            success: function(data) {
                mutualList.empty();
                if (data.length === 0) {
                    mutualList.append(`
                        <p class="text-xs text-on-surface-variant text-center py-4">${_tp.noMutualFriends}</p>
                    `);
                } else {
                    data.forEach(friend => {
                        mutualList.append(`
                            <div class="flex items-center justify-between py-2 border-b border-primary/5 last:border-0" style="direction: ${_tp.mutualFriendsWith.includes('مع') ? 'rtl' : 'ltr'};">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <a href="${friend.profile_url}">
                                        <img src="${friend.avatar}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" alt="${friend.name}">
                                    </a>
                                    <div class="text-right pr-2">
                                        <a href="${friend.profile_url}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">${friend.name}</a>
                                        <p class="text-[10px] text-on-surface-variant">${_tp.mutualFriendLabel}</p>
                                    </div>
                                </div>
                                <a href="${friend.profile_url}" class="text-xs font-bold text-primary hover:underline">${_tp.viewProfile}</a>
                            </div>
                        `);
                    });
                }
            },
            error: function() {
                mutualList.html(`
                    <p class="text-xs text-error text-center py-4">${_tp.errorLoadingData}</p>
                `);
            }
        });
    }

    function closeMutualModal() {
        mutualModal.removeClass('modal-show');
        setTimeout(() => {
            mutualModal.removeClass('flex').addClass('hidden');
        }, 300);
    }

    $(document).on('click', '.open-mutual-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const userId = btn.attr('data-user-id');
        const userName = btn.attr('data-user-name');
        openMutualModal(userName, userId);
    });

    $(document).on('click', '#close-mutual-friends-btn, #mutual-friends-modal .modal-backdrop', function() {
        closeMutualModal();
    });
});
</script>
@endpush
