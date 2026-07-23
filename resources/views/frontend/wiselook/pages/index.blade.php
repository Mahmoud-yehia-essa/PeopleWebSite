@extends('frontend.wiselook.master_dashboard')

@push('styles')
<style>
    @media (min-width: 1280px) {
        .sticky-sidebar {
            max-height: calc(100vh - 7rem);
            overflow-y: auto;
            -ms-overflow-style: none; /* IE and Edge */
            scrollbar-width: none; /* Firefox */
        }
        .sticky-sidebar::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }
    }
</style>
@endpush

@section('main')
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto grid grid-cols-1 xl:grid-cols-12 gap-gutter mt-stack-md">
    <!-- Right Sidebar Column (3 cols on XL) -->
    <aside class="order-2 xl:order-1 xl:col-span-3 xl:sticky xl:top-24 self-start space-y-stack-md sticky-sidebar">
        <!-- Friend Requests -->
        @auth
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary/5">
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('friend_requests') }}</h3>
                <span class="material-symbols-outlined text-secondary text-sm">person_add</span>
            </div>
            <div class="space-y-4">
                @forelse($friendRequests->take(10) as $req)
                    @if($req->sender)
                        @php
                            $senderName = $req->sender->first_name . ' ' . $req->sender->last_name;
                            $senderAvatar = url('upload/no_image.jpg');
                            if ($req->sender->profile_picture && $req->sender->profile_picture !== 'non') {
                                $senderAvatar = filter_var($req->sender->profile_picture, FILTER_VALIDATE_URL)
                                    ? $req->sender->profile_picture
                                    : asset('new_wiselook/uploads/' . $req->sender->profile_picture);
                            }
                        @endphp
                        <div class="flex items-center justify-between gap-3 group friend-request-sidebar-row">
                            <div class="flex items-center space-x-3 space-x-reverse min-w-0 flex-1">
                                <a href="{{ route('profile.edit', $req->sender->id) }}" class="shrink-0">
                                    <img alt="{{ $senderName }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="{{ $senderAvatar }}">
                                </a>
                                <div class="text-right min-w-0 flex-1">
                                    <a href="{{ route('profile.edit', $req->sender->id) }}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block truncate" title="{{ $senderName }}">{{ $senderName }}</a>
                                    <p class="text-[11px] text-on-surface-variant truncate" title="{{ $req->sender->email ?? '' }}">{{ $req->sender->email ?? __t('wisdom_member') }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-1 space-x-reverse shrink-0">
                                <a href="{{ route('frontend.friendships.accept', $req->id) }}" class="text-secondary hover:text-primary transition-colors flex items-center justify-center accept-friendship-sidebar-btn" title="{{ __t('accept') }}">
                                    <span class="material-symbols-outlined text-[22px]">check_circle</span>
                                </a>
                                <a href="{{ route('frontend.friendships.delete', $req->id) }}" class="text-error hover:text-red-700 transition-colors flex items-center justify-center reject-friendship-sidebar-btn" title="{{ __t('reject') }}">
                                    <span class="material-symbols-outlined text-[22px]">cancel</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_pending_friend_requests') }}</p>
                @endforelse

                @if($friendRequests->isNotEmpty())
                    <div id="explore-more-sidebar-container" class="pt-3 border-t border-primary/5 text-center">
                        <a href="{{ route('frontend.my_network', ['filter' => 'pending']) }}" class="text-xs font-bold text-primary hover:underline transition-all flex items-center justify-center gap-1">
                            <span>{{ __t('explore_more') }}</span>
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endauth

        <!-- Suggested Friends -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm" id="suggested-friends-block">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary/5">
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('suggested_friends') }}</h3>
                <span class="material-symbols-outlined text-secondary text-sm">group_add</span>
            </div>
            <div class="space-y-4">
                @forelse($suggestedFriends as $suggested)
                    @php
                        $suggestedName = $suggested->first_name . ' ' . $suggested->last_name;
                        $suggestedAvatar = url('upload/no_image.jpg');
                        if ($suggested->profile_picture && $suggested->profile_picture !== 'non') {
                            $suggestedAvatar = filter_var($suggested->profile_picture, FILTER_VALIDATE_URL)
                                ? $suggested->profile_picture
                                : asset('new_wiselook/uploads/' . $suggested->profile_picture);
                        }
                    @endphp
                    <div class="flex items-center justify-between group suggested-friend-row transition-all duration-300" data-user-id="{{ $suggested->id }}">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <a href="{{ route('profile.edit', $suggested->id) }}" class="shrink-0">
                                <img alt="{{ $suggestedName }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="{{ $suggestedAvatar }}">
                            </a>
                            <div class="text-right">
                                <a href="{{ route('profile.edit', $suggested->id) }}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">{{ $suggestedName }}</a>
                                <p class="text-[11px] text-on-surface-variant">{{ __t('wisdom_member') }}</p>
                                @if($suggested->mutual_count > 0)
                                    <p class="text-[11px] text-on-surface-variant mt-0.5">
                                        <span class="mutual-friends-trigger cursor-pointer text-primary hover:underline font-semibold" data-user-id="{{ $suggested->id }}" data-user-name="{{ $suggestedName }}">
                                            {{ $suggested->mutual_count }} {{ $suggested->mutual_count == 1 ? __t('mutual_friend') : __t('mutual_friends') }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                        @auth
                            <button class="send-friend-request-btn text-primary hover:bg-primary/10 p-1 rounded-full transition-colors shrink-0 cursor-pointer flex items-center justify-center" data-receiver-id="{{ $suggested->id }}" title="{{ __t('add_friend') }}">
                                <span class="material-symbols-outlined text-[20px]">person_add</span>
                            </button>
                        @else
                            <a href="{{ route('user.login') }}" class="text-primary hover:bg-primary/10 p-1 rounded-full transition-colors shrink-0 flex items-center justify-center" title="{{ __t('add_friend') }}">
                                <span class="material-symbols-outlined text-[20px]">person_add</span>
                            </a>
                        @endauth
                    </div>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_new_suggestions') }}</p>
                @endforelse
            </div>
            <a class="block text-center mt-4 pt-3 border-t border-primary/5 text-primary font-bold text-xs hover:underline" href="{{ route('frontend.my_network') }}">{{ __t('explore_more') }}</a>
        </div>

        <!-- Wise Rated Posts Sidebar Widget -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm mt-6" id="wise-rated-posts-sidebar-block" style="direction: rtl;">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary/5">
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-sm">gavel</span>
                    <span>{{ __t('wise_rated_posts') }}</span>
                </h3>
                <span class="inline-flex items-center justify-center bg-secondary/10 text-secondary w-5 h-5 rounded-full text-[10px] font-bold">
                    {{ $wiseRatedPosts->count() }}
                </span>
            </div>
            <div class="space-y-4">
                @forelse($wiseRatedPosts as $wPost)
                    @php
                        $wAuthorName = $wPost->user ? ($wPost->user->first_name . ' ' . $wPost->user->last_name) : __t('unknown_user');
                        $wAuthorAvatar = url('upload/no_image.jpg');
                        if ($wPost->user && $wPost->user->profile_picture && $wPost->user->profile_picture !== 'non') {
                            $wAuthorAvatar = filter_var($wPost->user->profile_picture, FILTER_VALIDATE_URL)
                                ? $wPost->user->profile_picture
                                : asset('new_wiselook/uploads/' . $wPost->user->profile_picture);
                        }
                        $wRatingVal = floatval($wPost->wise_rating);
                        $wRatingColor = $wRatingVal >= 8 ? '#16a34a' : ($wRatingVal >= 6 ? '#b8922a' : ($wRatingVal >= 4 ? '#ea580c' : '#dc2626'));
                        
                        // تنظيف النص وتحديده بـ 120 حرفًا كحد أقصى للحماية التامة من التمدد الطويل
                        $cleanText = strip_tags($wPost->content);
                        $shortText = Str::limit($cleanText, 120, '');
                    @endphp
                    <div class="p-3.5 rounded-xl border border-primary/5 bg-surface hover:border-primary/25 transition-all duration-300 shadow-[0_2px_8px_rgba(0,0,0,0.01)] hover:shadow-[0_4px_12px_rgba(0,0,0,0.03)]" style="direction: rtl;">
                        {{-- رأس البطاقة: الكاتب والتقييم --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <img src="{{ $wAuthorAvatar }}" alt="{{ $wAuthorName }}" class="w-7 h-7 rounded-full object-cover border border-outline-variant">
                                <span class="text-[11px] font-bold text-on-surface-variant truncate max-w-[100px]">{{ $wAuthorName }}</span>
                            </div>
                            <span class="inline-flex items-center gap-0.5 font-extrabold" style="
                                background: {{ $wRatingVal >= 8 ? 'rgba(34,197,94,0.08)' : ($wRatingVal >= 6 ? 'rgba(234,179,8,0.08)' : ($wRatingVal >= 4 ? 'rgba(249,115,22,0.08)' : 'rgba(239,68,68,0.08)')) }};
                                border: 1px solid {{ $wRatingColor }}33;
                                color: {{ $wRatingColor }} !important;
                                padding: 2px 7px; border-radius: 20px;
                                font-size: 11px;
                            ">
                                <span class="material-symbols-outlined" style="font-size:11px; font-variation-settings:'FILL' 1;">star</span>
                                {{ number_format($wRatingVal, 1) }}
                            </span>
                        </div>
                        {{-- محتوى الموضوع --}}
                        <div class="text-right">
                            <a href="{{ route('frontend.posts.show', $wPost->id) }}" class="font-body-md text-xs text-on-surface hover:text-primary transition-colors block leading-[1.6]" style="
                                display: -webkit-box;
                                -webkit-line-clamp: 3;
                                -webkit-box-orient: vertical;
                                overflow: hidden;
                                word-break: break-word;
                                text-decoration: none;
                            ">
                                {{ $shortText }}
                                <span class="text-primary hover:underline font-bold inline-block mr-1 text-[11px]" style="white-space: nowrap;">... {{ __t('more_link') }}</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-4">{{ __t('no_rated_posts_yet') }}</p>
                @endforelse
            </div>
            @if($wiseRatedPosts->isNotEmpty())
                <a class="block text-center mt-4 pt-3 border-t border-primary/5 text-primary font-bold text-xs hover:underline" href="{{ route('frontend.wise_rated.index') }}">{{ __t('view_all') }}</a>
            @endif
        </div>
    </aside>

    <!-- Center Feed Column (6 cols on XL) -->
    <div class="order-1 xl:order-2 xl:col-span-6 space-y-stack-md">
        <!-- Reels Component -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 shadow-[0_4px_20px_rgba(27,67,50,0.05)] p-4 relative z-20">
            <div class="relative w-full">
                <!-- Slide Prev Button (RTL back to start) -->
                <button id="slide-prev-btn" class="absolute -right-4 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white text-on-surface shadow-md hover:bg-surface-container-high transition-all flex items-center justify-center border border-outline-variant z-10 cursor-pointer hidden">
                    <span class="material-symbols-outlined text-[20px]">chevron_right</span>
                </button>
                
                <!-- Stories Scroll Container -->
                <div class="flex space-x-3 space-x-reverse overflow-x-auto pb-1 scrollbar-none scroll-smooth" id="stories-scroll-container" style="-ms-overflow-style: none; scrollbar-width: none;">
                    
                    <!-- Create Story Card -->
                    <div id="add-story-card" class="relative shrink-0 w-28 h-44 bg-white rounded-xl overflow-hidden shadow-sm border border-outline-variant flex flex-col group cursor-pointer">
                        @php
                            $userAvatar = url('upload/no_image.jpg');
                            if (Auth::check() && Auth::user()->profile_picture && Auth::user()->profile_picture !== 'non') {
                                $userAvatar = filter_var(Auth::user()->profile_picture, FILTER_VALIDATE_URL)
                                    ? Auth::user()->profile_picture
                                    : asset('new_wiselook/uploads/' . Auth::user()->profile_picture);
                            }
                        @endphp
                        <div class="h-[65%] w-full bg-cover bg-center transition-transform duration-500 group-hover:scale-105" style="background-image: url('{{ $userAvatar }}');"></div>
                        <div class="h-[35%] w-full bg-white flex flex-col items-center justify-end pb-2.5 relative">
                            <div class="absolute -top-4.5 left-1/2 -translate-x-1/2 w-9 h-9 rounded-full bg-primary border-[3px] border-white flex items-center justify-center text-white shadow-md transition-transform duration-300 group-hover:scale-110">
                                <span class="material-symbols-outlined text-[18px] font-bold">add</span>
                            </div>
                            <span class="text-[10px] font-bold text-on-surface">إضافة قصة</span>
                        </div>
                    </div>

                    @foreach($stories as $userId => $userStories)
                        @php
                            $latestStory = $userStories->first();
                            $user = $latestStory->user;
                            $userName = $user ? ($user->first_name . ' ' . $user->last_name) : 'مستخدم غير معروف';
                            $userAvatar = url('upload/no_image.jpg');
                            if ($user && $user->profile_picture && $user->profile_picture !== 'non') {
                                $userAvatar = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                                    ? $user->profile_picture
                                    : asset('new_wiselook/uploads/' . $user->profile_picture);
                            }
                            
                            // Check for unseen stories
                            $hasUnseen = false;
                            if (Auth::check()) {
                                foreach($userStories as $st) {
                                    $isSeen = $st->views->where('user_id', Auth::id())->where('is_active', 1)->isNotEmpty();
                                    if (!$isSeen) {
                                        $hasUnseen = true;
                                        break;
                                    }
                                }
                            }
                            $avatarBorderColor = $hasUnseen ? 'border-primary border-[3px]' : 'border-outline-variant border-2';
                        @endphp
                        
                        <div class="story-user-card relative shrink-0 w-28 h-44 rounded-xl overflow-hidden group cursor-pointer shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md" 
                             data-user-id="{{ $userId }}" 
                             data-stories="{{ json_encode($userStories->map(function($st) use ($user, $userAvatar, $userName) {
                                 $mediaPath = '';
                                 $mediaType = 'text';
                                 if ($st->image) {
                                     $mediaPath = asset('upload/stories/' . $st->image);
                                     $mediaType = 'image';
                                 } elseif ($st->video) {
                                     $mediaPath = asset('upload/stories/' . $st->video);
                                     $mediaType = 'video';
                                 }
                                 
                                 $viewCount = $st->views->where('is_active', 1)->count();
                                 $isOwner = Auth::check() && Auth::id() == $st->user_id;
                                 
                                 return [
                                     'id' => $st->id,
                                     'content' => $st->content,
                                     'media' => $mediaPath,
                                     'type' => $mediaType,
                                     'view_count' => $viewCount,
                                     'is_owner' => $isOwner,
                                     'is_seen' => Auth::check() && $st->views->where('user_id', Auth::id())->where('is_active', 1)->isNotEmpty() ? 1 : 0,
                                     'created_at' => $st->created_at ? $st->created_at->diffForHumans() : '',
                                     'user_name' => $userName,
                                     'user_avatar' => $userAvatar,
                                 ];
                             })->values()) }}">
                            
                            <!-- Background Preview -->
                            @if($latestStory->video)
                                <div class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden">
                                    <video class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" muted loop playsinline autoplay>
                                        <source src="{{ asset('upload/stories/' . $latestStory->video) }}" type="video/mp4">
                                    </video>
                                </div>
                            @elseif($latestStory->image)
                                <img alt="{{ $userName }} Story" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="{{ asset('upload/stories/' . $latestStory->image) }}">
                            @else
                                <div class="absolute inset-0 bg-primary-container flex items-center justify-center p-3 text-center transition-transform duration-500 group-hover:scale-105">
                                    <p class="text-[9px] font-bold text-on-primary-container leading-tight line-clamp-4">{{ $latestStory->content }}</p>
                                </div>
                            @endif
                            
                            <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/75"></div>
                            
                            <!-- User Avatar Ring -->
                            <div class="absolute top-2 left-2 w-8 h-8 rounded-full {{ $avatarBorderColor }} overflow-hidden bg-white shadow-md transition-transform duration-300 group-hover:scale-105 z-10 flex items-center justify-center">
                                <img alt="{{ $userName }}" class="w-full h-full object-cover rounded-full" src="{{ $userAvatar }}">
                            </div>
                            
                            <!-- Name -->
                            <div class="absolute bottom-2 right-2 left-2 text-right z-10">
                                <p class="text-[10px] font-bold text-white leading-tight drop-shadow-sm truncate">{{ $userName }}</p>
                            </div>
                        </div>
                    @endforeach

                </div>
                
                <!-- Slide Next Button (RTL scrolls left) -->
                <button id="slide-next-btn" class="absolute -left-4 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white text-on-surface shadow-md hover:bg-surface-container-high transition-all flex items-center justify-center border border-outline-variant z-10 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">chevron_left</span>
                </button>
            </div>
        </div>

        <!-- Create Post Component -->
        <div class="bg-white rounded-2xl border border-primary/10 shadow-[0_10px_30px_rgba(27,67,50,0.03)] p-5 relative z-30 transition-all duration-300 hover:shadow-[0_10px_35px_rgba(27,67,50,0.06)]">
            @auth
            <form action="{{ route('frontend.posts.store') }}" method="POST" enctype="multipart/form-data" id="create-post-form">
                @csrf
                <input type="hidden" name="post_type_id" id="frontend_post_type_id" value="1">
                
                <!-- Sleek Segmented Tabs -->
                <div class="flex bg-surface-container/60 p-1 rounded-xl mb-4 border border-primary/5 gap-1">
                    <button type="button" id="tab-regular-post" class="flex-1 py-2 text-xs font-bold text-center rounded-lg transition-all duration-300 cursor-pointer flex items-center justify-center gap-1.5 bg-white text-primary shadow-sm">
                        <span class="material-symbols-outlined text-[16px] font-variation-settings-fill">edit_note</span>
                        <span>{{ __t('regular_post') }}</span>
                    </button>
                    <button type="button" id="tab-poll-post" class="flex-1 py-2 text-xs font-bold text-center rounded-lg transition-all duration-300 cursor-pointer flex items-center justify-center gap-1.5 text-on-surface-variant hover:text-primary">
                        <span class="material-symbols-outlined text-[16px]">ballot</span>
                        <span>{{ __t('poll_post') }}</span>
                    </button>
                </div>

                <div class="flex items-start gap-4">
                    @php
                        $userAvatar = url('upload/no_image.jpg');
                        if (Auth::user()->profile_picture && Auth::user()->profile_picture !== 'non') {
                            $userAvatar = filter_var(Auth::user()->profile_picture, FILTER_VALIDATE_URL)
                                ? Auth::user()->profile_picture
                                : asset('new_wiselook/uploads/' . Auth::user()->profile_picture);
                        }
                    @endphp
                    <div class="relative shrink-0">
                        <img alt="User Avatar" class="w-11 h-11 rounded-full object-cover ring-2 ring-primary/10 shadow-sm" src="{{ $userAvatar }}"/>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    </div>

                    <div class="flex-grow relative min-w-0">
                        <!-- Regular Post Fields -->
                        <div id="frontend_post_fields">
                            <div class="border border-primary/10 bg-surface-container-lowest/30 rounded-xl p-3.5 focus-within:border-primary/40 focus-within:bg-white focus-within:ring-2 focus-within:ring-primary/5 transition-all duration-300">
                                <textarea id="post-content-textarea" name="content" dir="{{ current_language()->direction ?? 'rtl' }}" class="w-full bg-transparent border-none outline-none resize-none min-h-[90px] font-body-md text-sm text-on-surface placeholder:text-on-surface-variant/80 focus:ring-0 px-2 py-1 text-right" placeholder="{{ __t('post_placeholder') }}">{{ old('content') }}</textarea>
                            </div>
                            
                            <!-- Mention Dropdown -->
                            <div id="mention-dropdown" class="absolute hidden bottom-auto top-full right-0 mt-1.5 bg-white rounded-xl border border-primary/10 shadow-lg z-50 w-64 max-h-60 overflow-y-auto text-right"></div>
                            
                            <!-- Media Previews -->
                            <div class="hidden mt-3 relative rounded-xl overflow-hidden border border-primary/10 bg-surface-container-low max-h-60 flex items-center justify-center shadow-inner" id="image-preview-container">
                                <img id="frontend-image-preview" src="" class="max-h-full max-w-full object-contain">
                                <button type="button" id="remove-image-btn" class="absolute top-2 right-2 p-1.5 bg-black/60 hover:bg-black/85 text-white rounded-full transition-all cursor-pointer flex items-center justify-center shadow">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>

                            <div class="hidden mt-3 relative rounded-xl overflow-hidden border border-primary/10 bg-surface-container-low p-4 flex items-center justify-between shadow-sm" id="video-preview-container">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-primary text-[28px]">movie</span>
                                    <div class="text-right">
                                        <p id="video-file-name" class="text-xs font-bold text-on-surface truncate max-w-[200px]"></p>
                                        <p id="video-file-size" class="text-[10px] text-on-surface-variant/80"></p>
                                    </div>
                                </div>
                                <button type="button" id="remove-video-btn" class="p-1.5 bg-error/10 hover:bg-error/20 text-error rounded-full transition-all cursor-pointer flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>
                        </div>

                        <!-- Poll Fields -->
                        <div id="frontend_poll_fields" class="hidden space-y-3.5 mt-2">
                            <div class="relative">
                                <span class="material-symbols-outlined absolute right-3.5 top-1/2 -translate-y-1/2 text-primary/40 text-[20px]">help</span>
                                <input name="question" type="text" class="w-full bg-surface border border-primary/10 rounded-xl py-3 pr-11 pl-4 font-body-md text-sm text-on-surface placeholder:text-on-surface-variant focus:border-primary/40 focus:ring-2 focus:ring-primary/5 transition-all outline-none" placeholder="{{ __t('poll_question_placeholder') }}" value="{{ old('question') }}" id="poll_question_input" />
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary/30 text-[18px]">radio_button_unchecked</span>
                                    <input name="options[]" type="text" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 pr-9 pl-3 text-xs text-on-surface placeholder:text-on-surface-variant focus:border-primary/40 focus:ring-2 focus:ring-primary/5 transition-all outline-none poll-option-input" placeholder="{{ __t('poll_option_1') }}" value="{{ old('options.0') }}" />
                                </div>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary/30 text-[18px]">radio_button_unchecked</span>
                                    <input name="options[]" type="text" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 pr-9 pl-3 text-xs text-on-surface placeholder:text-on-surface-variant focus:border-primary/40 focus:ring-2 focus:ring-primary/5 transition-all outline-none poll-option-input" placeholder="{{ __t('poll_option_2') }}" value="{{ old('options.1') }}" />
                                </div>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary/30 text-[18px]">radio_button_unchecked</span>
                                    <input name="options[]" type="text" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 pr-9 pl-3 text-xs text-on-surface placeholder:text-on-surface-variant focus:border-primary/40 focus:ring-2 focus:ring-primary/5 transition-all outline-none" placeholder="{{ __t('poll_option_3') }}" value="{{ old('options.2') }}" />
                                </div>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-primary/30 text-[18px]">radio_button_unchecked</span>
                                    <input name="options[]" type="text" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 pr-9 pl-3 text-xs text-on-surface placeholder:text-on-surface-variant focus:border-primary/40 focus:ring-2 focus:ring-primary/5 transition-all outline-none" placeholder="{{ __t('poll_option_4') }}" value="{{ old('options.3') }}" />
                                </div>
                            </div>
                        </div>

                        <!-- Actions Area -->
                        <div class="flex justify-between items-center mt-5 pt-4 border-t border-primary/5">
                            <div class="flex items-center gap-1 relative" id="media-buttons-container">
                                <!-- Hidden File Pickers -->
                                <input type="file" name="image" id="frontend_image_input" accept="image/*" class="hidden">
                                <input type="file" name="video" id="frontend_video_input" accept="video/*" class="hidden">

                                <button type="button" id="trigger-image-select" class="text-on-surface-variant/80 hover:text-green-600 hover:bg-green-50 p-2.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center" title="إرفاق صورة">
                                    <span class="material-symbols-outlined text-[20px]">image</span>
                                </button>
                                <button type="button" id="trigger-video-select" class="text-on-surface-variant/80 hover:text-blue-600 hover:bg-blue-50 p-2.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center" title="إرفاق فيديو">
                                    <span class="material-symbols-outlined text-[20px]">movie</span>
                                </button>
                                <button type="button" id="trigger-emoji-picker" class="text-on-surface-variant/80 hover:text-amber-500 hover:bg-amber-50 p-2.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center" title="إدراج إيموجي">
                                    <span class="material-symbols-outlined text-[20px]">mood</span>
                                </button>

                                <!-- Emoji Picker Dropdown (Positioned ABOVE trigger button) -->
                                <div id="emoji-picker-dropdown" class="absolute hidden bottom-full mb-2.5 right-0 bg-white rounded-2xl border border-primary/10 shadow-[0_15px_35px_-5px_rgba(0,0,0,0.15)] z-50 w-72 sm:w-80 max-w-[calc(100vw-32px)] overflow-hidden text-right">
                                    <!-- Emoji Categories Tabs -->
                                    <div class="flex border-b border-primary/5 bg-surface-container-low p-2 gap-1 justify-between" style="direction: rtl;">
                                        <button type="button" class="emoji-cat-btn active text-primary bg-primary/5 p-1.5 rounded-lg transition-all flex-grow text-center text-[15px] cursor-pointer" data-category="smileys" title="وجوه وتعبيرات">😂</button>
                                        <button type="button" class="emoji-cat-btn text-on-surface-variant hover:bg-primary/5 p-1.5 rounded-lg transition-all flex-grow text-center text-[15px] cursor-pointer" data-category="animals" title="حيوانات وطبيعة">🐱</button>
                                        <button type="button" class="emoji-cat-btn text-on-surface-variant hover:bg-primary/5 p-1.5 rounded-lg transition-all flex-grow text-center text-[15px] cursor-pointer" data-category="food" title="طعام وشراب">🍕</button>
                                        <button type="button" class="emoji-cat-btn text-on-surface-variant hover:bg-primary/5 p-1.5 rounded-lg transition-all flex-grow text-center text-[15px] cursor-pointer" data-category="activities" title="أنشطة وسفر">⚽</button>
                                        <button type="button" class="emoji-cat-btn text-on-surface-variant hover:bg-primary/5 p-1.5 rounded-lg transition-all flex-grow text-center text-[15px] cursor-pointer" data-category="objects" title="رموز وأشياء">💡</button>
                                    </div>
                                    
                                    <!-- Emoji Grid Area -->
                                    <div class="p-3 max-h-48 overflow-y-auto grid grid-cols-6 gap-2 text-center" id="emoji-grid-container" style="direction: rtl;">
                                        <!-- Emojis will be dynamically rendered here -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Dummy space-holder for alignment when poll fields are active -->
                            <div id="media-poll-hint" class="hidden text-[10px] text-on-surface-variant/80 font-bold bg-primary/5 px-3 py-1.5 rounded-lg border border-primary/5">
                                {{ __t('poll_no_media_hint') }}
                            </div>

                            <button type="submit" class="flex items-center gap-2 bg-primary hover:bg-primary-container text-white font-bold text-xs py-2.5 px-6 rounded-full transition-all duration-300 shadow-md shadow-primary/10 hover:shadow-lg hover:shadow-primary/20 hover:scale-[1.02] cursor-pointer">
                                <span>{{ __t('publish_post') }}</span>
                                <span class="material-symbols-outlined text-[14px]">send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <div class="text-center py-4">
                <p class="font-body-md text-on-surface-variant mb-3">{{ __t('login_to_post') }}</p>
                <a href="{{ route('user.login') }}" class="inline-block bg-primary text-on-primary font-label-sm text-label-sm px-6 py-2.5 rounded-full hover:bg-primary-container transition-all">
                    {{ __t('login') }}
                </a>
            </div>
            @endauth
        </div>

        <!-- Posts Feed Container -->
        <div id="posts-feed-container" class="space-y-stack-md">
            @include('frontend.wiselook.pages.posts_feed', ['posts' => $posts])
        </div>

        <!-- Loading Indicator -->
        <div id="posts-loading-indicator" class="py-6 text-center hidden">
            <div class="inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Mobile Load More Button (Only visible on mobile screens < 1024px) -->
        <div id="mobile-load-more-container" class="py-4 text-center block lg:hidden">
            <button type="button" id="mobile-load-more-btn" onclick="loadMorePosts()" class="w-full bg-surface-container-lowest hover:bg-primary/5 text-primary font-bold py-3.5 px-6 rounded-xl border border-primary/20 shadow-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-xs sm:text-sm">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span id="mobile-load-more-text">تحميل المزيد من المواضيع</span>
            </button>
        </div>
    </div>

    <!-- Left Sidebar Column (3 cols on XL) -->
    <aside class="order-3 xl:order-3 xl:col-span-3 xl:sticky xl:top-24 self-start space-y-stack-md sticky-sidebar">
        <!-- Global Issues Bento Box / قضايا رائجة الآن -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center space-x-2 space-x-reverse mb-6">
                <span class="material-symbols-outlined text-primary">public</span>
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('trending_issues_now') }}</h3>
            </div>
            <div class="space-y-4">
                @forelse($trendingPosts as $tPost)
                    @php
                        // extract first hashtag or use default
                        preg_match('/#([^\s#]+)/u', $tPost->content, $matches);
                        $hashtagName = !empty($matches[0]) ? $matches[0] : '#نقاش';
                        
                        // strip hashtags and html to get clean content snippet
                        $cleanContent = preg_replace('/#[^\s#]+/u', '', $tPost->content);
                        $cleanContent = strip_tags($cleanContent);
                        $titleSnippet = Str::limit(trim($cleanContent), 60);
                        if (empty($titleSnippet)) {
                            $titleSnippet = __t('topic_discussion') . ' #' . $tPost->id;
                        }
                    @endphp
                    <a class="group block {{ !$loop->first ? 'border-t border-primary/5 pt-3' : '' }}" href="{{ route('frontend.posts.show', $tPost->id) }}">
                        <p class="text-xs text-secondary font-bold mb-0.5">{{ $hashtagName }}</p>
                        <p class="font-body-md text-sm text-on-surface group-hover:text-primary transition-colors">{{ $titleSnippet }}</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">{{ $tPost->comment_count }} {{ $tPost->comment_count == 1 ? __t('discussion') : __t('discussions') }}</p>
                    </a>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_trending_issues_yet') }}</p>
                @endforelse
            </div>
            <a href="{{ route('frontend.trending') }}" class="block w-full mt-4 text-primary font-bold text-xs hover:underline text-center">{{ __t('view_all_trending_issues') }}</a>
        </div>

        <!-- Trending Hashtags / أكثر الهاشتاجات تداولاً -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center space-x-2 space-x-reverse mb-5">
                <span class="material-symbols-outlined text-secondary">tag</span>
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('most_trending_hashtags') }}</h3>
            </div>
            <div class="flex flex-wrap gap-2" dir="rtl">
                @forelse($trendingHashtags as $tag)
                    <a href="{{ route('frontend.hashtags.show', $tag->name) }}"
                       class="inline-flex items-center gap-1 bg-primary/5 hover:bg-secondary/10 transition-colors rounded-full px-3 py-1.5 text-xs font-bold text-primary no-underline">
                        <span class="text-secondary">#</span>{{ $tag->name }}
                        <span class="bg-secondary/15 text-secondary rounded-full px-1.5 py-0.5 text-[10px] font-bold ml-1">{{ $tag->links_count }}</span>
                    </a>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2 w-full">{{ __t('no_hashtags_available_yet') }}</p>
                @endforelse
            </div>
        </div>

        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center space-x-2 space-x-reverse mb-4 pb-3 border-b border-primary/5">
                <span class="material-symbols-outlined text-primary">groups</span>
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('trending_groups') }}</h3>
            </div>
            <div class="space-y-4">
                @forelse($trendingGroups as $tGroup)
                    <a class="group block {{ !$loop->first ? 'border-t border-primary/5 pt-3' : '' }}" href="{{ route('frontend.groups.details', $tGroup->id) }}">
                        <p class="text-xs text-secondary font-bold mb-0.5">#{{ str_replace(' ', '_', $tGroup->title) }}</p>
                        <p class="font-body-md text-sm text-on-surface group-hover:text-primary transition-colors">{{ Str::limit($tGroup->description, 60) }}</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">{{ $tGroup->members_count }} {{ __t('member') }}</p>
                    </a>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_trending_groups_yet') }}</p>
                @endforelse
            </div>
            <a href="{{ route('frontend.groups') }}" class="block w-full mt-4 text-primary font-bold text-xs hover:underline text-center">{{ __t('view_all_groups') }}</a>
        </div>

        <!-- Top Scholars List / حكماء الأسبوع -> أعلى الأعضاء تقييماً -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-primary/5">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="material-symbols-outlined text-secondary">military_tech</span>
                    <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('top_rated_scholars') }}</h3>
                </div>
            </div>
            <ul class="space-y-4">
                @forelse($topRatedUsers as $index => $topUser)
                    @php
                        $topUserName = $topUser->first_name . ' ' . $topUser->last_name;
                        $topUserAvatar = url('upload/no_image.jpg');
                        if ($topUser->profile_picture && $topUser->profile_picture !== 'non') {
                            $topUserAvatar = filter_var($topUser->profile_picture, FILTER_VALIDATE_URL)
                                ? $topUser->profile_picture
                                : asset('new_wiselook/uploads/' . $topUser->profile_picture);
                        }
                    @endphp
                    <li class="flex items-center justify-between group transition-all duration-300">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="relative shrink-0">
                                <a href="{{ route('profile.edit', $topUser->id) }}">
                                    <img alt="{{ $topUserName }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="{{ $topUserAvatar }}">
                                </a>
                                <span class="absolute -top-1 -right-1 {{ $index == 0 ? 'bg-secondary text-on-secondary' : ($index < 3 ? 'bg-primary/80 text-white' : 'bg-surface-dim text-on-surface') }} text-[9px] w-4 h-4 flex items-center justify-center rounded-full font-extrabold shadow-sm border border-white">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="text-right">
                                <a href="{{ route('profile.edit', $topUser->id) }}" class="font-body-md text-xs font-bold text-on-surface hover:text-primary transition-colors block">{{ $topUserName }}</a>
                                
                                <div class="flex items-center gap-1.5 mt-0.5 text-[10px] text-on-surface-variant">
                                    @if($topUser->rank)
                                        @php
                                            $rPhoto = $topUser->rank->photo;
                                            $rPhotoPath = null;
                                            if (!empty($rPhoto) && file_exists(public_path('upload/rankings/' . $rPhoto))) {
                                                $rPhotoPath = asset('upload/rankings/' . $rPhoto);
                                            }
                                        @endphp
                                        @if($rPhotoPath)
                                            <img src="{{ $rPhotoPath }}" alt="" style="width: 14px; height: 14px; object-fit: contain;">
                                        @endif
                                        <span class="font-bold text-[10px]" style="color: #cda225;">{{ __t($topUser->rank->rank_name) }}</span>
                                    @else
                                        <span>{{ __t('wisdom_member') }}</span>
                                    @endif
                                    
                                    <span class="opacity-50">•</span>
                                    
                                    <button type="button" class="user-points-trigger inline-flex items-center gap-0.5 font-extrabold text-secondary hover:text-primary hover:underline cursor-pointer bg-secondary/5 px-1.5 py-0.5 rounded text-[10px]" data-user-id="{{ $topUser->id }}">
                                        {{ __t('points') }}: {{ $topUser->points ?? 0 }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="shrink-0 flex items-center justify-end">
                            @if($topUser->friendship_status === 'none')
                                @auth
                                    <button class="send-friend-request-btn text-primary hover:bg-primary/10 p-1.5 rounded-full transition-colors cursor-pointer flex items-center justify-center" data-receiver-id="{{ $topUser->id }}" title="{{ __t('add_friend') }}">
                                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                                    </button>
                                @else
                                    <a href="{{ route('user.login') }}" class="text-primary hover:bg-primary/10 p-1.5 rounded-full transition-colors flex items-center justify-center" title="{{ __t('add_friend') }}">
                                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                                    </a>
                                @endauth
                            @elseif($topUser->friendship_status === 'pending_sent')
                                <span class="text-on-surface-variant/40 p-1.5 rounded-full flex items-center justify-center" title="{{ __t('request_sent') }}">
                                    <span class="material-symbols-outlined text-[18px]">pending</span>
                                </span>
                            @elseif($topUser->friendship_status === 'pending_received')
                                <a href="{{ route('profile.friends', auth()->id()) }}" class="text-secondary hover:bg-secondary/10 p-1.5 rounded-full transition-colors flex items-center justify-center animate-pulse" title="{{ __t('pending_request_from_member') }}">
                                    <span class="material-symbols-outlined text-[18px]">group</span>
                                </a>
                            @elseif($topUser->friendship_status === 'friends')
                                <span class="text-green-600 p-1.5 rounded-full flex items-center justify-center" title="{{ __t('friend') }}">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                </span>
                            @endif
                        </div>
                    </li>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_records') }}</p>
                @endforelse
            </ul>
        </div>
    </aside>
</div>

<!-- First-Time Welcome Modal for Visitors & Users -->
<div id="welcome-modal" class="fixed inset-0 z-[120] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto">
    <!-- Backdrop with rich glassmorphism -->
    <div class="modal-backdrop fixed inset-0 bg-slate-950/75 backdrop-blur-md opacity-0 transition-opacity duration-500" id="welcome-modal-backdrop"></div>

    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-lg w-full bg-gradient-to-b from-white via-slate-50/95 to-emerald-50/50 backdrop-blur-2xl rounded-3xl border border-primary/20 shadow-[0_35px_60px_-15px_rgba(0,58,35,0.35)] p-6 sm:p-8 z-10 translate-y-12 scale-95 opacity-0 transition-all duration-500 text-center overflow-hidden my-auto" style="direction: rtl;">
        
        <!-- Glowing Ambient Orbs -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Close Button -->
        <button type="button" id="close-welcome-modal-btn" class="absolute top-4 left-4 w-9 h-9 rounded-full bg-slate-100/80 text-on-surface-variant hover:bg-error/10 hover:text-error transition-all flex items-center justify-center cursor-pointer border border-outline-variant/30 shadow-xs z-20" title="{{ __t('close') ?? 'إغلاق' }}">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>

        <!-- Main Header Icon Badge -->
        <div class="relative mb-5 inline-block">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-primary via-emerald-800 to-emerald-950 text-amber-400 flex items-center justify-center mx-auto shadow-xl shadow-primary/30 ring-4 ring-amber-400/20 transform hover:rotate-3 transition-transform">
                <span class="material-symbols-outlined text-[44px]">workspace_premium</span>
            </div>
            <div class="absolute -bottom-1 -right-1 bg-amber-500 text-slate-950 px-2 py-0.5 rounded-full text-[10px] font-extrabold shadow-md flex items-center gap-0.5">
                <span class="material-symbols-outlined text-[13px]">auto_awesome</span>
                <span>مرحباً بك</span>
            </div>
        </div>

        <!-- Title & Subtitle -->
        <h2 class="font-headline-lg text-xl sm:text-2xl font-bold text-primary mb-2 tracking-tight leading-snug">
            أهلاً بك في منصة حكماء العالم
        </h2>
        <div class="inline-block bg-amber-500/10 border border-amber-500/25 rounded-full py-1 px-4 mb-4">
            <span class="text-xs font-extrabold text-amber-700">الملتقى الرائد لأصحاب الرأي والمفكرين في كل المجالات</span>
        </div>

        <!-- Descriptive Formulation Text -->
        <p class="text-xs sm:text-sm text-on-surface-variant leading-relaxed mb-6 px-1">
            مساحتك الفكرية لمشاركة رأيك وتقديم حلولك المبتكرة. قد تكون أنت من يحل قضية معقدة، وتتدرج في مراتب الحكمة من خلال تقييمات نخبة الحكماء والمتخصصين في كافة المجالات... ومن يدري، قد تصبح يوماً أحدهم!
        </p>

        <!-- 3 Interactive Action Feature Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 text-right">
            <!-- Feature 1 -->
            <div class="bg-white/80 backdrop-blur-xs p-3.5 rounded-2xl border border-primary/10 shadow-xs hover:border-primary/30 transition-all group">
                <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[18px]">forum</span>
                </div>
                <h3 class="text-xs font-bold text-primary mb-1">اعرض قضيتك</h3>
                <p class="text-[11px] text-on-surface-variant leading-snug">شارك الآخرين في حل القضايا ونشر الرؤى القيمة.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white/80 backdrop-blur-xs p-3.5 rounded-2xl border border-primary/10 shadow-xs hover:border-primary/30 transition-all group">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[18px]">military_tech</span>
                </div>
                <h3 class="text-xs font-bold text-primary mb-1">ارتقِ برتبتك</h3>
                <p class="text-[11px] text-on-surface-variant leading-snug">احصل على التقييمات واكسب النقاط للتدرج في رتب الحكمة.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white/80 backdrop-blur-xs p-3.5 rounded-2xl border border-primary/10 shadow-xs hover:border-primary/30 transition-all group">
                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-700 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <span class="material-symbols-outlined text-[18px]">group_add</span>
                </div>
                <h3 class="text-xs font-bold text-primary mb-1">أنشئ مجتمعك</h3>
                <p class="text-[11px] text-on-surface-variant leading-snug">أنشئ مجموعتك الخاصة وادعُ أصدقاءك للانضمام.</p>
            </div>
        </div>

        <!-- Action CTA Button -->
        <div class="w-full">
            <button type="button" id="start-welcome-journey-btn" class="w-full bg-gradient-to-r from-primary via-emerald-800 to-emerald-900 text-white py-3.5 px-6 rounded-full text-xs sm:text-sm font-bold hover:brightness-110 active:scale-[0.99] transition-all shadow-lg shadow-primary/20 flex items-center justify-center gap-2 group cursor-pointer">
                <span>ابدأ رحلتك في منصة الحكمة</span>
                <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // First-Time Welcome Modal Logic
    const SEEN_WELCOME_KEY = 'wiselook_welcome_modal_seen_v1';

    if (!localStorage.getItem(SEEN_WELCOME_KEY)) {
        setTimeout(function() {
            openWelcomeModal();
        }, 1200);
    }

    function openWelcomeModal() {
        const modal = $('#welcome-modal');
        if (!modal.length) return;

        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        setTimeout(() => {
            modal.addClass('modal-show');
        }, 20);
    }

    function closeWelcomeModal() {
        const modal = $('#welcome-modal');
        modal.removeClass('modal-show');
        setTimeout(() => {
            modal.removeClass('flex').addClass('hidden');
            $('body').removeClass('modal-active');
        }, 400);

        // Mark as seen so it NEVER pops up again
        localStorage.setItem(SEEN_WELCOME_KEY, 'true');
    }

    $(document).on('click', '#close-welcome-modal-btn, #start-welcome-journey-btn, #welcome-modal-backdrop', function() {
        closeWelcomeModal();
    });

    // Handle Sidebar Friend Request Actions (Accept / Reject) via AJAX
    $(document).on('click', '.accept-friendship-sidebar-btn, .reject-friendship-sidebar-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const url = btn.attr('href');
        const row = btn.closest('.friend-request-sidebar-row');
        const container = row.parent(); // the space-y-4 container

        if (btn.hasClass('pointer-events-none')) return;
        btn.addClass('pointer-events-none');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== "undefined") {
                        toastr.success(response.message);
                    }
                    
                    // Slide/fade out the row
                    row.addClass('scale-95 opacity-0 transition-all duration-300');
                    setTimeout(() => {
                        row.remove();
                        // Check if all rows are gone
                        if (container.find('.friend-request-sidebar-row').length === 0) {
                            $('#explore-more-sidebar-container').remove();
                            container.html('<p class="text-xs text-on-surface-variant text-center py-2">{{ __t("no_pending_friend_requests") }}</p>');
                        }
                    }, 300);
                } else {
                    if (typeof toastr !== "undefined") {
                        toastr.error(response.message || '{{ __t("action_failed") }}');
                    }
                    btn.removeClass('pointer-events-none');
                }
            },
            error: function(xhr) {
                let msg = '{{ __t("action_error") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                if (typeof toastr !== "undefined") {
                    toastr.error(msg);
                }
                btn.removeClass('pointer-events-none');
            }
        });
    });

    // --- Frontend Post Creation Tabs & Previews ---
    const tabRegular = $('#tab-regular-post');
    const tabPoll = $('#tab-poll-post');
    const postTypeId = $('#frontend_post_type_id');
    
    const postFields = $('#frontend_post_fields');
    const pollFields = $('#frontend_poll_fields');
    
    const mediaBtnContainer = $('#media-buttons-container');
    const mediaPollHint = $('#media-poll-hint');

    // Tab Switching Logic
    tabRegular.on('click', function() {
        tabRegular.addClass('bg-white text-primary shadow-sm').removeClass('text-on-surface-variant hover:text-on-surface');
        tabPoll.removeClass('bg-white text-primary shadow-sm').addClass('text-on-surface-variant hover:text-on-surface');
        postTypeId.val(1);
        
        postFields.show();
        pollFields.hide();
        mediaBtnContainer.show();
        mediaPollHint.hide();
        
        // Reset required state on poll fields
        $('.poll-option-input').removeAttr('required');
        $('#poll_question_input').removeAttr('required');
    });

    tabPoll.on('click', function() {
        tabPoll.addClass('bg-white text-primary shadow-sm').removeClass('text-on-surface-variant hover:text-on-surface');
        tabRegular.removeClass('bg-white text-primary shadow-sm').addClass('text-on-surface-variant hover:text-on-surface');
        postTypeId.val(2);
        
        postFields.hide();
        pollFields.show();
        mediaBtnContainer.hide();
        mediaPollHint.show();
        
        // Set required state on poll fields
        $('.poll-option-input').slice(0, 2).attr('required', 'required');
        $('#poll_question_input').attr('required', 'required');
    });

    // Image/Video selection triggers
    $('#trigger-image-select').on('click', function() {
        $('#frontend_image_input').click();
    });

    $('#trigger-video-select').on('click', function() {
        $('#frontend_video_input').click();
    });

    // Image file selection preview
    $('#frontend_image_input').on('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                $('#frontend-image-preview').attr('src', event.target.result);
                $('#image-preview-container').removeClass('hidden');
                
                // Clear video selection if any
                $('#frontend_video_input').val('');
                $('#video-preview-container').addClass('hidden');
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Video file selection preview
    $('#frontend_video_input').on('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
            $('#video-file-name').text(file.name);
            $('#video-file-size').text(sizeMB + ' MB');
            $('#video-preview-container').removeClass('hidden');
            
            // Clear image selection if any
            $('#frontend_image_input').val('');
            $('#image-preview-container').addClass('hidden');
            $('#frontend-image-preview').attr('src', '');
        }
    });

    // Remove Attachment Buttons
    $('#remove-image-btn').on('click', function() {
        $('#frontend_image_input').val('');
        $('#image-preview-container').addClass('hidden');
        $('#frontend-image-preview').attr('src', '');
    });

    $('#remove-video-btn').on('click', function() {
        $('#frontend_video_input').val('');
        $('#video-preview-container').addClass('hidden');
        $('#video-file-name').text('');
        $('#video-file-size').text('');
    });

    // --- Lazy Loading (Infinite Scroll on Desktop / Load More Button on Mobile) ---
    let page = 1;
    let isLoading = false;
    let hasMore = true;

    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 600) {
            if (!isLoading && hasMore) {
                // Skip auto-scroll loading on mobile (< 1024px) so users can reach footer and lower sections
                if (window.innerWidth < 1024) {
                    return;
                }
                loadMorePosts();
            }
        }
    });

    window.loadMorePosts = function() {
        if (isLoading || !hasMore) return;

        isLoading = true;
        $('#posts-loading-indicator').removeClass('hidden');
        $('#mobile-load-more-btn').prop('disabled', true).addClass('opacity-75');
        $('#mobile-load-more-text').text('جاري التحميل...');

        const $container = $('#posts-feed-container');
        const $lastExistingPost = $container.children('article').last();

        page++;

        $.ajax({
            url: "?page=" + page,
            type: "GET",
            success: function(html) {
                if (html.trim() === '') {
                    hasMore = false;
                    $('#posts-loading-indicator').addClass('hidden');
                    $('#mobile-load-more-container').html(`
                        <div class="py-3 px-4 text-center text-xs font-bold text-on-surface-variant/60 bg-surface-container-lowest/50 rounded-xl border border-primary/5">
                            لا توجد مواضيع أخرى
                        </div>
                    `);
                    return;
                }
                $container.append(html);

                // Identify first newly added post & smooth scroll to it
                let $firstNewPost;
                if ($lastExistingPost.length > 0) {
                    $firstNewPost = $lastExistingPost.next('article');
                } else {
                    $firstNewPost = $container.children('article').first();
                }

                if ($firstNewPost && $firstNewPost.length > 0) {
                    const targetScrollTop = $firstNewPost.offset().top - 90;
                    $('html, body').animate({
                        scrollTop: Math.max(0, targetScrollTop)
                    }, 400);
                }

                isLoading = false;
                $('#posts-loading-indicator').addClass('hidden');
                $('#mobile-load-more-btn').prop('disabled', false).removeClass('opacity-75');
                $('#mobile-load-more-text').text('تحميل المزيد من المواضيع');
            },
            error: function() {
                isLoading = false;
                $('#posts-loading-indicator').addClass('hidden');
                $('#mobile-load-more-btn').prop('disabled', false).removeClass('opacity-75');
                $('#mobile-load-more-text').text('تحميل المزيد من المواضيع');
            }
        });
    };



    // --- Poll Voting Handler ---
    $(document).on('click', '.poll-option-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const optionId = btn.attr('data-option-id');
        const container = btn.closest('.poll-container');
        
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
                    container.find('.total-votes-count').text('إجمالي الأصوات: ' + response.total_votes);
                    
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
                const defaultMsg = 'يجب تسجيل الدخول للتصويت في الاستطلاع.';
                let msg = defaultMsg;
                if (xhr.status === 401) {
                    msg = 'يجب تسجيل الدخول للتصويت في الاستطلاع.';
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

    // --- Friend Mention Autocomplete ---
    const textarea = $('#post-content-textarea');
    let friendsList = [];
    let isTracking = false;
    let mentionStartIdx = -1;
    let activeItemIdx = -1;

    function fetchFriends() {
        $.ajax({
            url: "{{ route('frontend.friends.search') }}",
            type: "GET",
            success: function(data) {
                friendsList = data;
            }
        });
    }

    @auth
        fetchFriends();
    @endauth

    textarea.on('input keyup click', function(e) {
        // Skip arrow keys up/down and enter if dropdown is open to allow keyboard selection
        if (!$('#mention-dropdown').hasClass('hidden') && (e.key === 'ArrowUp' || e.key === 'ArrowDown' || e.key === 'Enter')) {
            return;
        }

        const text = textarea.val();
        const caretPos = textarea[0].selectionStart;
        
        // Find the last index of '@' before the cursor
        const lastAtIdx = text.lastIndexOf('@', caretPos - 1);
        
        if (lastAtIdx !== -1) {
            // Ensure there is no space between '@' and the cursor
            const textAfterAt = text.substring(lastAtIdx + 1, caretPos);
            const hasSpace = /\s/.test(textAfterAt);
            
            // Also ensure '@' is preceded by a space or is at the start of textarea
            const charBeforeAt = lastAtIdx > 0 ? text.charAt(lastAtIdx - 1) : '';
            const isValidStart = lastAtIdx === 0 || /\s/.test(charBeforeAt);
            
            if (!hasSpace && isValidStart) {
                isTracking = true;
                mentionStartIdx = lastAtIdx;
                showMentionDropdown(textAfterAt);
                return;
            }
        }
        
        hideMentionDropdown();
    });

    textarea.on('keydown', function(e) {
        const dropdown = $('#mention-dropdown');
        if (dropdown.hasClass('hidden')) return;
        
        const items = dropdown.find('.mention-item');
        if (items.length === 0) return;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeItemIdx = (activeItemIdx + 1) % items.length;
            highlightMentionItem(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeItemIdx = (activeItemIdx - 1 + items.length) % items.length;
            highlightMentionItem(items);
        } else if (e.key === 'Enter') {
            if (activeItemIdx !== -1) {
                e.preventDefault();
                items.eq(activeItemIdx).click();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            hideMentionDropdown();
        }
    });

    // --- Helper function to get caret coordinates inside a textarea ---
    function getCaretCoordinates(element, position) {
        const properties = [
            'direction', 'boxSizing', 'width', 'height', 'overflowX', 'overflowY',
            'borderTopWidth', 'borderRightWidth', 'borderBottomWidth', 'borderLeftWidth',
            'borderStyle', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft',
            'fontStyle', 'fontVariant', 'fontWeight', 'fontStretch', 'fontSize', 'fontSizeAdjust',
            'lineHeight', 'fontFamily', 'textAlign', 'textTransform', 'textIndent', 'textDecoration',
            'letterSpacing', 'wordSpacing', 'tabSize', 'MozTabSize'
        ];

        const isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
        const div = document.createElement('div');
        div.id = 'textarea-caret-position-mirror-div';
        document.body.appendChild(div);

        const style = div.style;
        const computed = window.getComputedStyle(element);

        style.whiteSpace = 'pre-wrap';
        if (element.nodeName !== 'INPUT') style.wordWrap = 'break-word';

        style.position = 'absolute';
        style.visibility = 'hidden';

        properties.forEach(prop => {
            style[prop] = computed[prop];
        });

        if (isFirefox) {
            if (element.scrollHeight > element.clientHeight) style.overflowY = 'scroll';
        } else {
            style.overflowY = 'hidden';
        }

        div.textContent = element.value.substring(0, position);

        const span = document.createElement('span');
        span.textContent = element.value.substring(position) || '.';
        div.appendChild(span);

        const coordinates = {
            top: span.offsetTop + parseInt(computed['borderTopWidth']),
            left: span.offsetLeft + parseInt(computed['borderLeftWidth']),
            height: parseInt(computed['lineHeight'])
        };

        document.body.removeChild(div);
        return coordinates;
    }

    function showMentionDropdown(query) {
        const dropdown = $('#mention-dropdown');
        dropdown.empty();
        activeItemIdx = -1; // Reset active item index
        
        // Filter friends by query
        const filtered = friendsList.filter(friend => {
            return friend.name.toLowerCase().includes(query.toLowerCase());
        });
        
        if (filtered.length === 0) {
            dropdown.append(`
                <div class="p-4 text-xs text-on-surface-variant text-center">
                    لا يوجد أصدقاء
                </div>
            `);
        } else {
            filtered.forEach(friend => {
                dropdown.append(`
                    <button type="button" class="mention-item w-full flex items-center gap-3 p-2.5 hover:bg-primary/5 text-right transition-colors" data-name="${friend.name}" data-id="${friend.id}">
                        <img src="${friend.avatar}" class="w-8 h-8 rounded-full object-cover border border-outline-variant shrink-0" alt="${friend.name}">
                        <span class="text-xs font-bold text-on-surface">${friend.name}</span>
                    </button>
                `);
            });
        }
        
        // Calculate coordinates of the caret (cursor)
        const caretPos = textarea[0].selectionStart;
        const coords = getCaretCoordinates(textarea[0], caretPos);
        const textareaWidth = textarea.outerWidth();
        const textareaHeight = textarea.outerHeight();
        const dropdownWidth = 250; // w-64 is 16rem = 256px
        const dropdownHeight = 240; // max-h-60 is 15rem = 240px

        // Determine left position
        let leftPos = coords.left;
        if (leftPos + dropdownWidth > textareaWidth) {
            leftPos = textareaWidth - dropdownWidth - 10;
        }
        if (leftPos < 10) {
            leftPos = 10;
        }

        // Determine top position (positioned downward relative to current cursor line)
        let topPos = coords.top + coords.height + 5;

        dropdown.css({
            position: 'absolute',
            top: topPos + 'px',
            left: leftPos + 'px',
            right: 'auto'
        });

        dropdown.removeClass('hidden');
    }

    function hideMentionDropdown() {
        $('#mention-dropdown').addClass('hidden').empty();
        activeItemIdx = -1;
    }

    function highlightMentionItem(items) {
        items.removeClass('bg-primary/5');
        if (activeItemIdx !== -1) {
            const activeItem = items.eq(activeItemIdx);
            activeItem.addClass('bg-primary/5');
            
            // Scroll item into view inside dropdown if needed
            const dropdown = $('#mention-dropdown');
            const itemTop = activeItem.position().top;
            const dropdownHeight = dropdown.height();
            if (itemTop < 0 || itemTop >= dropdownHeight) {
                dropdown.scrollTop(activeItem.position().top + dropdown.scrollTop() - dropdownHeight / 2);
            }
        }
    }

    $(document).on('click', '.mention-item', function(e) {
        e.preventDefault();
        const name = $(this).attr('data-name');
        const text = textarea.val();
        const caretPos = textarea[0].selectionStart;
        
        // Replace the '@query' part with the friend's name wrapped in brackets
        const beforeMention = text.substring(0, mentionStartIdx);
        const afterMention = text.substring(caretPos);
        
        const newText = beforeMention + '@[' + name + '] ' + afterMention;
        textarea.val(newText);
        
        // Reposition cursor after the added mention
        const newCaretPos = mentionStartIdx + name.length + 4; // +2 for @[ and +2 for ] and space
        textarea[0].focus();
        textarea[0].setSelectionRange(newCaretPos, newCaretPos);
        
        hideMentionDropdown();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#mention-dropdown, #post-content-textarea').length) {
            hideMentionDropdown();
        }
    });

    // --- Send Friend Request AJAX Trigger ---
    $(document).on('click', '.send-friend-request-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const receiverId = btn.attr('data-receiver-id');
        const row = btn.closest('.suggested-friend-row');

        if (btn.hasClass('pointer-events-none')) return;
        btn.addClass('pointer-events-none');

        $.ajax({
            url: "{{ route('frontend.friendships.request') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                receiver_id: receiverId
            },
            success: function(response) {
                if (response.success) {
                    if (typeof toastr !== "undefined") {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    
                    if (row.length) {
                        // Premium fade out and remove the row
                        row.addClass('scale-95 opacity-0');
                        setTimeout(() => {
                            row.remove();
                            // If no suggestions left, show message
                            if ($('#suggested-friends-block .suggested-friend-row').length === 0) {
                                $('#suggested-friends-block .space-y-4').append(`
                                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t("no_new_suggestions") }}</p>
                                `);
                            }
                        }, 300);
                    } else {
                        // For other elements (like Top Rated Members), change the button state to pending icon
                        btn.html('<span class="material-symbols-outlined text-[18px]">pending</span>')
                           .attr('title', '{{ __t("request_sent") }}')
                           .removeClass('text-primary hover:bg-primary/10')
                           .addClass('text-on-surface-variant/40 pointer-events-none');
                    }
                }
            },
            error: function(xhr) {
                let msg = '{{ __t("friend_request_error") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                
                if (typeof toastr !== "undefined") {
                    toastr.error(msg);
                } else {
                    alert(msg);
                }
                btn.removeClass('pointer-events-none');
            }
        });
    });

    // --- Mutual Friends Modal Handlers ---
    const mutualModal = $('#mutual-friends-modal');
    const mutualList = $('#mutual-friends-list');
    const mutualTitle = $('#mutual-friends-title');
    const _tm = {
        mutualFriendsWith: '{{ __t("mutual_friends_with") }}',
        noMutualFriends:   '{{ __t("no_mutual_friends") }}',
        mutualFriendLabel: '{{ __t("mutual_friend_label") }}',
        viewProfile:       '{{ __t("view_profile") }}',
        errorLoadingData:  '{{ __t("error_loading_data") }}',
    };

    function openMutualModal(userName, userId) {
        mutualTitle.text(_tm.mutualFriendsWith + ' ' + userName);
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
            url: "{{ url('/friends/mutual') }}/" + userId,
            type: "GET",
            success: function(data) {
                mutualList.empty();
                if (data.length === 0) {
                    mutualList.append(`
                        <p class="text-xs text-on-surface-variant text-center py-4">${_tm.noMutualFriends}</p>
                    `);
                } else {
                    data.forEach(friend => {
                        mutualList.append(`
                            <div class="flex items-center justify-between py-2 border-b border-primary/5 last:border-0">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <a href="${friend.profile_url}">
                                        <img src="${friend.avatar}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" alt="${friend.name}">
                                    </a>
                                    <div class="text-right">
                                        <a href="${friend.profile_url}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">${friend.name}</a>
                                        <p class="text-[10px] text-on-surface-variant">${_tm.mutualFriendLabel}</p>
                                    </div>
                                </div>
                                <a href="${friend.profile_url}" class="text-xs font-bold text-primary hover:underline">${_tm.viewProfile}</a>
                            </div>
                        `);
                    });
                }
            },
            error: function() {
                mutualList.html(`
                    <p class="text-xs text-error text-center py-4">${_tm.errorLoadingData}</p>
                `);
            }
        });
    }

    function closeMutualModal() {
        mutualModal.removeClass('modal-show');
        setTimeout(() => {
            mutualModal.removeClass('flex').addClass('hidden');
            mutualList.empty();
        }, 300);
    }

    $(document).on('click', '.mutual-friends-trigger', function(e) {
        e.preventDefault();
        const userId = $(this).attr('data-user-id');
        const userName = $(this).attr('data-user-name');
        openMutualModal(userName, userId);
    });

    $('#close-mutual-friends-btn, .modal-backdrop', mutualModal).on('click', function(e) {
        closeMutualModal();
    });

    // --- Emoji Picker Handlers ---
    const emojis = {
        smileys: ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡','🤬','🤯','😳','🥵','🥶','😱','😨','😰','😥','😓','🤗','🤔','🫣','🤭','🤫','🤥','😶','😐','😑','😬','🫠','🙄','😯','😦','😧','😮','😲','🥱','😴','🤤','😪','😵','😵‍💫','🤐','🥴','🤢','🤮','🤧','😷','🤒','🤕','🤑','🤠','😈','👿','👹','👺','🤡','💩','👻','💀','☠️','👽','👾','🤖','🎃','😺','😸','😹','😻','😼','😽','🙀','😿','😾','👋','🤚','🖐️','✋','🖖','👌','🤌','🤏','✌️','🤞','🫰','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦿','🦵','🦶','👂','🦻','👃','🧠','🫀','🫁','🦷','🦴','👀','👁️','👅','👄','💋','🩸','❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❤️‍🔥','❤️‍🩹','❣️','💕','💞','💓','💗','💖','💘','💝','💟'],
        animals: ['🐱','🐈','🐈‍⬛','🐶','🐕','🦮','🐕‍🦺','🐩','🐺','🦊','🦝','🦁','🐯','🐅','🐆','🦄','🦓','🦌','🦬','🐮','🐂','🐃','🐄','🐷','🐖','🐗','🐽','🐏','🐑','🐐','🐪','🐫','🦙','🦒','🐘','🦣','🦏','🦛','🐭','🦫','🦔','🐰','🐇','🐿️','🐨','🐼','🦥','🦦','🦨','🦘','🦡','🐾','🦃','🐔','🐓','🐣','🐤','🐥','🐦','🐧','🕊️','🦅','🦆','🦢','🦉','🦤','🦩','🦚','Parrot','🐸','🐊','🐢','🦎','🐍','🐲','🐉','🦕','🦖','🐳','🐋','🐬','🦭','🐟','🐠','🐡','🦈','🐙','🐚','🐌','🦋','🐛','🐜','🐝','🪲','🐞','🦗','🕷️','🕸️','🦂','🦟','🪰','🪱','🦠','🌸','🏵️','🌹','🥀','🌺','🌻','🌼','🌷','🌱','🌲','🌳','🌴','🌵','🌾','🌿','🍀','🍁','🍂','🍃'],
        food: ['🍏','🍎','🍐','🍊','🍋','🍌','🍉','🍇','🍓','🍈','🍒','🍑','🥭','🍍','🥥','🥝','🍅','🍆','🥑','🥦','🥬','🥒','🌶️','🫑','🥔','🥕','🌽','🍠','🥐','🍞','🥖','🍕','🍔','🍟','🌭','🥪','🌮','🍳','🥘','🍲',' Salad','🍱','🍣','🍙','🍚','🍥','🥟','🍤','🍢','🍡','🍧','🍨','🍦','🥧','🧁','🍰','🎂','🍮','🍭','🍬','🍫','🍩','🍪','🥜','🌰',' honey','🥛','☕','🍵','🥤','🧋','🍺','🍻','🥂','🍷','🥃','🍸','🍹','🍾'],
        activities: ['⚽','🏀','🏈','⚾','🥎','🎾','🏐','🏉','🎱','🏓','🏸','🥅','🏒','⛳','🪁','🏹','🎣','🤿','🥊','🥋','skateboard','🏋️','🚴','🚵','🤸','🤼','🤽','🤾','🏎️','摩托','🛵','🚲','🚘','🚗','🚕','🚙','🚌','🚎','🚓','🚒','🚑','🚐','🛻','🚚','🚜','🚇','🚏','⛴️','🚢','✈️','🚀','🛰️','🪐','🌠','⛺','⛰️','🌋','🏟️','🏛️','🏠','🏡','🏢','🏥','🏦','🏨','🏫','🕌','🏰',' Ferris Wheel','🎢',' Carousel','⛱️','🏖️','🗺️'],
        objects: ['💻','🖥️','⌨️','🖱️','🖨️','📱','☎️','📞','🔋','🔌','💡','🔦','🕯️','💵','🪙','💳','💎','🔧','🔨','🛠️','🔩','⚙️','🧱','⛓️','🧲','⚖️','🔫','🛡️','🔑','🗝️','🔮','🧿','🔭','🔬','🩹','🩺','💉','🧪','🧬','🧹','🧺','🧻','🧼','🧽','🪣','🎁','🎈','🎉','🎊','🎀','📧','📨','📩','📦','🏷️','✉️','📮','📰','📄','📑','📂','📁','📔','📕','📖','📚','📓','📒','🔒','🔓','🖊️','🖋️','✒️','✏️','📝','💼','📁','📌','📍','📎','📏','📐']
    };

    const emojiPicker = $('#emoji-picker-dropdown');
    const emojiGrid = $('#emoji-grid-container');
    const emojiTrigger = $('#trigger-emoji-picker');

    function renderEmojiCategory(category) {
        emojiGrid.empty();
        const list = emojis[category] || [];
        list.forEach(emoji => {
            emojiGrid.append(`
                <span class="emoji-select-item text-[22px] cursor-pointer hover:bg-primary/10 p-1.5 rounded transition-all flex items-center justify-center select-none" data-emoji="${emoji}">
                    ${emoji}
                </span>
            `);
        });
    }

    // Toggle dropdown
    emojiTrigger.on('click', function(e) {
        e.stopPropagation();
        
        // Hide mention dropdown if open
        $('#mention-dropdown').addClass('hidden');
        
        if (emojiPicker.hasClass('hidden')) {
            // Render active category (default to smileys)
            const activeCat = $('.emoji-cat-btn.active').attr('data-category') || 'smileys';
            renderEmojiCategory(activeCat);
            
            emojiPicker.removeClass('hidden');
        } else {
            emojiPicker.addClass('hidden');
        }
    });

    // Switch categories
    $(document).on('click', '.emoji-cat-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('.emoji-cat-btn').removeClass('active text-primary bg-primary/5').addClass('text-on-surface-variant');
        $(this).addClass('active text-primary bg-primary/5').removeClass('text-on-surface-variant');
        
        const cat = $(this).attr('data-category');
        renderEmojiCategory(cat);
    });

    // Select emoji
    $(document).on('click', '.emoji-select-item', function(e) {
        e.stopPropagation();
        const emoji = $(this).attr('data-emoji');
        const textarea = $('#post-content-textarea');
        
        if (textarea.length) {
            const el = textarea[0];
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const text = textarea.val();
            
            const newText = text.substring(0, start) + emoji + text.substring(end);
            textarea.val(newText);
            
            // Move cursor to after emoji
            const newCursorPos = start + emoji.length;
            el.focus();
            el.setSelectionRange(newCursorPos, newCursorPos);
        }

        // Automatically close emoji picker on mobile screens
        if ($(window).width() < 1024) {
            emojiPicker.addClass('hidden');
        }
    });

    // Close on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#emoji-picker-dropdown, #trigger-emoji-picker').length) {
            emojiPicker.addClass('hidden');
        }
    });

    // --- Stories Scroll Component Slider Logic ---
    const scrollContainer = $('#stories-scroll-container');
    const nextBtn = $('#slide-next-btn');
    const prevBtn = $('#slide-prev-btn');

    nextBtn.on('click', function() {
        scrollContainer.animate({
            scrollLeft: scrollContainer.scrollLeft() - 220
        }, 300, updateButtons);
    });

    prevBtn.on('click', function() {
        scrollContainer.animate({
            scrollLeft: scrollContainer.scrollLeft() + 220
        }, 300, updateButtons);
    });

    scrollContainer.on('scroll', function() {
        updateButtons();
    });

    function updateButtons() {
        const scrollLeft = Math.abs(scrollContainer.scrollLeft());
        const maxScroll = scrollContainer[0].scrollWidth - scrollContainer[0].clientWidth;
        
        if (scrollLeft > 10) {
            prevBtn.removeClass('hidden');
        } else {
            prevBtn.addClass('hidden');
        }

        if (scrollLeft + 10 < maxScroll) {
            nextBtn.removeClass('hidden');
        } else {
            nextBtn.addClass('hidden');
        }
    }
    
    // Initial call
    updateButtons();

    // --- AJAX Post Submission ---
    $('#create-post-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();
        
        // Basic validation check
        const postType = $('#frontend_post_type_id').val();
        if (postType == '1') {
            const content = $('#post-content-textarea').val().trim();
            const hasImage = $('#frontend_image_input').val();
            const hasVideo = $('#frontend_video_input').val();
            if (!content && !hasImage && !hasVideo) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('يرجى كتابة نص أو إرفاق ملف لنشر الموضوع.');
                } else {
                    alert('يرجى كتابة نص أو إرفاق ملف لنشر الموضوع.');
                }
                return;
            }
        } else {
            const question = $('#poll_question_input').val().trim();
            let optionCount = 0;
            $('.poll-option-input').each(function() {
                if ($(this).val().trim()) {
                    optionCount++;
                }
            });
            if (!question || optionCount < 2) {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('يرجى كتابة السؤال وإضافة خيارين على الأقل لاستطلاع الرأي.');
                } else {
                    alert('يرجى كتابة السؤال وإضافة خيارين على الأقل لاستطلاع الرأي.');
                }
                return;
            }
        }
        
        // Prepare FormData
        const formData = new FormData(this);
        
        // Show loading state
        submitBtn.prop('disabled', true).html(`
            <span class="inline-block w-4.5 h-4.5 border-[2.5px] border-white border-t-transparent rounded-full animate-spin me-2 vertical-middle align-middle"></span>
            جاري النشر...
        `);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    // 1. Show Success Message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    
                    // 2. Prepend the new post HTML with slide down & fade in animation!
                    const newPostHtml = $(response.html).css({ opacity: 0, display: 'none' });
                    $('#posts-feed-container').prepend(newPostHtml);
                    newPostHtml.slideDown(500, function() {
                        $(this).animate({ opacity: 1 }, 300);
                    });
                    
                    // 3. Reset the form completely
                    form[0].reset();
                    
                    // Reset previews
                    $('#image-preview-container').addClass('hidden');
                    $('#frontend-image-preview').attr('src', '');
                    $('#video-preview-container').addClass('hidden');
                    $('#video-file-name').text('');
                    $('#video-file-size').text('');
                    
                    // Reset Tab active states (default to regular post)
                    $('#tab-regular-post').trigger('click');
                    
                    // Hide emoji picker if open
                    $('#emoji-picker-dropdown').addClass('hidden');
                }
            },
            error: function(xhr) {
                let errorMsg = 'حدث خطأ أثناء نشر الموضوع.';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    // Extract validation errors
                    const errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // ==========================================
    //       FRONTEND STORIES / REELS LOGIC
    // ==========================================
    const isAuth = @json(Auth::check());
    
    // --- Add Story Modal Logic ---
    const addStoryModal = $('#add-story-modal');
    const addStoryForm = $('#add-story-form');
    
    $('#add-story-card').on('click', function() {
        if (!isAuth) {
            window.location.href = "{{ route('user.login') }}";
            return;
        }
        addStoryModal.removeClass('hidden').addClass('flex');
        setTimeout(() => addStoryModal.addClass('modal-show'), 10);
    });
    
    function closeAddStoryModal() {
        addStoryModal.removeClass('modal-show');
        setTimeout(() => {
            addStoryModal.removeClass('flex').addClass('hidden');
            addStoryForm[0].reset();
            resetStoryUploadPreview();
        }, 300);
    }
    
    $('#close-add-story-btn, #cancel-add-story-btn, #add-story-backdrop').on('click', closeAddStoryModal);
    
    // File upload drag & drop and previews
    const dropzone = $('#story-dropzone');
    const fileInput = $('#story-file-input');
    const previewWrapper = $('#story-preview-wrapper');
    const imgPreview = $('#story-image-preview');
    const vidPreview = $('#story-video-preview');
    
    dropzone.on('click', () => fileInput[0].click());
    
    dropzone.on('dragover', function(e) {
        e.preventDefault();
        dropzone.addClass('border-primary bg-primary/5');
    });
    
    dropzone.on('dragleave drop', function(e) {
        e.preventDefault();
        dropzone.removeClass('border-primary bg-primary/5');
    });
    
    dropzone.on('drop', function(e) {
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            handleStoryFileSelect(files[0]);
        }
    });
    
    fileInput.on('change', function() {
        if (this.files.length > 0) {
            handleStoryFileSelect(this.files[0]);
        }
    });
    
    function handleStoryFileSelect(file) {
        const type = file.type;
        const size = file.size;
        
        if (type.startsWith('image/')) {
            if (size > 5 * 1024 * 1024) {
                toastr.error('حجم الصورة كبير جداً. الحد الأقصى 5 ميجابايت.');
                fileInput.val('');
                return;
            }
            imgPreview.attr('src', URL.createObjectURL(file)).removeClass('hidden');
            vidPreview.addClass('hidden').attr('src', '');
            previewWrapper.removeClass('hidden');
            dropzone.addClass('hidden');
        } else if (type.startsWith('video/')) {
            if (size > 25 * 1024 * 1024) {
                toastr.error('حجم الفيديو كبير جداً. الحد الأقصى 25 ميجابايت.');
                fileInput.val('');
                return;
            }
            vidPreview.attr('src', URL.createObjectURL(file)).removeClass('hidden');
            imgPreview.addClass('hidden').attr('src', '');
            previewWrapper.removeClass('hidden');
            dropzone.addClass('hidden');
        } else {
            toastr.error('نوع الملف غير مدعوم. يرجى اختيار صورة أو فيديو.');
            fileInput.val('');
        }
    }
    
    $('#remove-story-media-btn').on('click', resetStoryUploadPreview);
    
    function resetStoryUploadPreview() {
        fileInput.val('');
        imgPreview.addClass('hidden').attr('src', '');
        vidPreview.addClass('hidden').attr('src', '');
        previewWrapper.addClass('hidden');
        dropzone.removeClass('hidden');
        $('#story-upload-progress-wrapper').addClass('hidden');
        $('#story-upload-progress-bar').css('width', '0%');
    }
    
    function updateCardPreview(card, story) {
        card.find('video').closest('.pointer-events-none').remove();
        card.find('> img').remove();
        card.find('.bg-primary-container').remove();
        
        let previewHtml = '';
        if (story.type === 'video') {
            previewHtml = `
                <div class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden">
                    <video class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" muted loop playsinline autoplay>
                        <source src="${story.media}" type="video/mp4">
                    </video>
                </div>
            `;
        } else if (story.type === 'image') {
            previewHtml = `<img alt="${story.user_name} Story" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="${story.media}">`;
        } else {
            previewHtml = `
                <div class="absolute inset-0 bg-primary-container flex items-center justify-center p-3 text-center transition-transform duration-500 group-hover:scale-105">
                    <p class="text-[9px] font-bold text-on-primary-container leading-tight line-clamp-4">${story.content}</p>
                </div>
            `;
        }
        
        card.prepend(previewHtml);
        card.find('.absolute.top-2.left-2').removeClass('border-outline-variant border-2').addClass('border-primary border-[3px]');
    }

    addStoryForm.on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#submit-story-btn');
        const spinner = $('#story-spinner');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('hidden');
        
        const formData = new FormData(this);
        // Clean media input handling
        if (fileInput[0].files.length > 0) {
            const file = fileInput[0].files[0];
            if (file.type.startsWith('image/')) {
                formData.append('image', file);
            } else if (file.type.startsWith('video/')) {
                formData.append('video', file);
            }
        }
        
        $.ajax({
            url: "{{ route('frontend.stories.store') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                const myXhr = $.ajaxSettings.xhr();
                if (myXhr.upload) {
                    $('#story-upload-progress-wrapper').removeClass('hidden');
                    myXhr.upload.addEventListener('progress', function(event) {
                        if (event.lengthComputable) {
                            const percent = Math.round((event.loaded / event.total) * 100);
                            $('#story-upload-progress-bar').css('width', percent + '%');
                            $('#story-upload-percentage').text(percent + '%');
                        }
                    }, false);
                }
                return myXhr;
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeAddStoryModal();
                    
                    const newStory = res.story;
                    const userId = newStory.user_id;
                    let userCard = $(`.story-user-card[data-user-id="${userId}"]`);
                    
                    if (userCard.length > 0) {
                        let stories = JSON.parse(userCard.attr('data-stories') || '[]');
                        stories.unshift(newStory);
                        userCard.attr('data-stories', JSON.stringify(stories));
                        updateCardPreview(userCard, newStory);
                    } else {
                        const avatarBorderColor = 'border-primary border-[3px]';
                        let previewHtml = '';
                        if (newStory.type === 'video') {
                            previewHtml = `
                                <div class="absolute inset-0 w-full h-full pointer-events-none overflow-hidden">
                                    <video class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" muted loop playsinline autoplay>
                                        <source src="${newStory.media}" type="video/mp4">
                                    </video>
                                </div>
                            `;
                        } else if (newStory.type === 'image') {
                            previewHtml = `<img alt="${newStory.user_name} Story" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" src="${newStory.media}">`;
                        } else {
                            previewHtml = `
                                <div class="absolute inset-0 bg-primary-container flex items-center justify-center p-3 text-center transition-transform duration-500 group-hover:scale-105">
                                    <p class="text-[9px] font-bold text-on-primary-container leading-tight line-clamp-4">${newStory.content}</p>
                                </div>
                            `;
                        }
                        
                        const cardHtml = `
                            <div class="story-user-card relative shrink-0 w-28 h-44 rounded-xl overflow-hidden group cursor-pointer shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md" 
                                 data-user-id="${userId}" 
                                 data-stories='${JSON.stringify([newStory])}'
                                 style="display: none;">
                                
                                ${previewHtml}
                                
                                <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/75"></div>
                                
                                <div class="absolute top-2 left-2 w-8 h-8 rounded-full ${avatarBorderColor} overflow-hidden bg-white shadow-md transition-transform duration-300 group-hover:scale-105 z-10 flex items-center justify-center">
                                    <img alt="${newStory.user_name}" class="w-full h-full object-cover rounded-full" src="${newStory.user_avatar}">
                                </div>
                                
                                <div class="absolute bottom-2 right-2 left-2 text-right z-10">
                                    <p class="text-[10px] font-bold text-white leading-tight drop-shadow-sm truncate">${newStory.user_name}</p>
                                </div>
                            </div>
                        `;
                        
                        const $newCard = $(cardHtml);
                        $('#add-story-card').after($newCard);
                        $newCard.show(300);
                    }
                } else {
                    toastr.error(res.message || 'حدث خطأ ما.');
                }
            },
            error: function(xhr) {
                let msg = 'حدث خطأ أثناء رفع القصة.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                toastr.error(msg);
            },
            complete: function() {
                submitBtn.prop('disabled', false);
                spinner.addClass('hidden');
            }
        });
    });


    // --- Story Viewer Logic ---
    const viewerModal = $('#story-viewer-modal');
    const viewerImage = $('#viewer-image');
    const viewerVideo = $('#viewer-video');
    const viewerTextBg = $('#viewer-text-bg');
    const viewerTextContent = $('#viewer-text-content');
    const viewerUsername = $('#viewer-username');
    const viewerAvatar = $('#viewer-avatar');
    const viewerTime = $('#viewer-time');
    const viewerCaption = $('#viewer-caption');
    const viewerProgress = $('#viewer-progress-container');
    const viewerStatsBtn = $('#viewer-stats-btn');
    const viewerStatsCount = $('#viewer-stats-count');
    const viewerDeleteBtn = $('#viewer-delete-btn');
    
    let activeUserIndex = 0;
    let activeStoryIndex = 0;
    let allStoryUsers = [];
    let progressTimer = null;
    let currentStoryProgress = 0;
    let isStoryPaused = false;
    let currentStoryDuration = 5000;
    
    function initStoryUsers() {
        allStoryUsers = [];
        $('.story-user-card').each(function() {
            const uId = $(this).attr('data-user-id');
            const uStories = JSON.parse($(this).attr('data-stories') || '[]');
            if (uStories.length > 0) {
                allStoryUsers.push({
                    userId: uId,
                    stories: uStories
                });
            }
        });
    }
    
    $(document).on('click', '.story-user-card', function() {
        initStoryUsers();
        const clickedUserId = $(this).attr('data-user-id');
        activeUserIndex = allStoryUsers.findIndex(u => u.userId == clickedUserId);
        
        if (activeUserIndex === -1) return;
        
        // Start at first unseen story, or 0 if all are seen
        const user = allStoryUsers[activeUserIndex];
        const unseenIndex = user.stories.findIndex(s => s.is_seen === 0);
        activeStoryIndex = unseenIndex !== -1 ? unseenIndex : 0;
        
        viewerModal.removeClass('hidden').addClass('flex');
        setTimeout(() => viewerModal.addClass('modal-show'), 10);
        
        playStory();
    });
    
    function closeViewer() {
        clearStoryTimer();
        viewerVideo[0].pause();
        viewerVideo.attr('src', '');
        viewerModal.removeClass('modal-show');
        setTimeout(() => viewerModal.removeClass('flex').addClass('hidden'), 300);
    }
    
    $('#close-viewer-btn').on('click', closeViewer);
    
    function playStory() {
        clearStoryTimer();
        viewerVideo[0].pause();
        viewerVideo.addClass('hidden').attr('src', '');
        viewerImage.addClass('hidden').attr('src', '');
        viewerTextBg.addClass('hidden');
        
        const user = allStoryUsers[activeUserIndex];
        if (!user) {
            closeViewer();
            return;
        }
        
        const story = user.stories[activeStoryIndex];
        if (!story) {
            // End of stories for this user, move to next user
            activeUserIndex++;
            activeStoryIndex = 0;
            playStory();
            return;
        }
        
        // Set metadata
        viewerUsername.text(story.user_name);
        viewerAvatar.attr('src', story.user_avatar);
        viewerTime.text(story.created_at);
        
        if (story.content && story.type !== 'text') {
            viewerCaption.text(story.content).removeClass('hidden');
        } else {
            viewerCaption.addClass('hidden').text('');
        }
        
        // Show view stats & delete button for owner
        if (story.is_owner) {
            viewerStatsCount.text(story.view_count);
            viewerStatsBtn.removeClass('hidden').addClass('flex').attr('data-story-id', story.id);
            viewerDeleteBtn.removeClass('hidden').addClass('flex').attr('data-story-id', story.id);
        } else {
            viewerStatsBtn.addClass('hidden').removeClass('flex').removeAttr('data-story-id');
            viewerDeleteBtn.addClass('hidden').removeClass('flex').removeAttr('data-story-id');
        }
        
        // Build segments
        buildProgressSegments(user.stories.length);
        
        // Render media
        if (story.type === 'image') {
            viewerImage.attr('src', story.media).removeClass('hidden');
            currentStoryDuration = 5000;
            startProgressTimer();
        } else if (story.type === 'video') {
            viewerVideo.attr('src', story.media).removeClass('hidden');
            viewerVideo[0].load();
            viewerVideo[0].onloadedmetadata = function() {
                currentStoryDuration = viewerVideo[0].duration * 1000;
                startProgressTimer();
            };
            viewerVideo[0].play().catch(() => {
                currentStoryDuration = 5000;
                startProgressTimer();
            });
        } else {
            // Text only story
            viewerTextBg.removeClass('hidden');
            viewerTextContent.text(story.content);
            currentStoryDuration = 5000;
            startProgressTimer();
        }
        
        // Send seen request via AJAX
        markStorySeen(story);
    }
    
    function buildProgressSegments(count) {
        viewerProgress.empty();
        for (let i = 0; i < count; i++) {
            const segment = $('<div class="progress-bar-segment"></div>');
            const inner = $('<div class="progress-bar-segment-inner"></div>');
            
            if (i < activeStoryIndex) {
                inner.css('width', '100%');
            } else if (i > activeStoryIndex) {
                inner.css('width', '0%');
            } else {
                inner.attr('id', 'active-segment-inner');
            }
            segment.append(inner);
            viewerProgress.append(segment);
        }
    }
    
    function startProgressTimer() {
        clearStoryTimer();
        currentStoryProgress = 0;
        isStoryPaused = false;
        
        const step = 50; // ms
        progressTimer = setInterval(() => {
            if (!isStoryPaused) {
                currentStoryProgress += (step / currentStoryDuration) * 100;
                if (currentStoryProgress >= 100) {
                    currentStoryProgress = 100;
                    $('#active-segment-inner').css('width', '100%');
                    clearStoryTimer();
                    nextStory();
                } else {
                    $('#active-segment-inner').css('width', currentStoryProgress + '%');
                }
            }
        }, step);
    }
    
    function clearStoryTimer() {
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
    }
    
    function nextStory() {
        const user = allStoryUsers[activeUserIndex];
        if (!user) return;
        
        if (activeStoryIndex < user.stories.length - 1) {
            activeStoryIndex++;
            playStory();
        } else {
            if (activeUserIndex < allStoryUsers.length - 1) {
                activeUserIndex++;
                activeStoryIndex = 0;
                playStory();
            } else {
                closeViewer();
            }
        }
    }
    
    function prevStory() {
        if (activeStoryIndex > 0) {
            activeStoryIndex--;
            playStory();
        } else {
            if (activeUserIndex > 0) {
                activeUserIndex--;
                activeStoryIndex = allStoryUsers[activeUserIndex].stories.length - 1;
                playStory();
            } else {
                activeStoryIndex = 0;
                playStory();
            }
        }
    }
    
    // Tap controls (Right goes forward, Left goes backward)
    $('#viewer-tap-right, #viewer-next-arrow').on('click', function(e) {
        e.stopPropagation();
        nextStory();
    });
    
    $('#viewer-tap-left, #viewer-prev-arrow').on('click', function(e) {
        e.stopPropagation();
        prevStory();
    });
    
    // Hold to pause logic
    const tapArea = $('#viewer-content-area');
    
    tapArea.on('mousedown touchstart', function(e) {
        if ($(e.target).is('#viewer-tap-left, #viewer-tap-right')) return;
        isStoryPaused = true;
        if (allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
            viewerVideo[0].pause();
        }
    });
    
    tapArea.on('mouseup touchend mouseleave', function(e) {
        isStoryPaused = false;
        if (allStoryUsers[activeUserIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
            viewerVideo[0].play().catch(() => {});
        }
    });
    
    // Keyboard navigation
    $(document).on('keydown', function(e) {
        if (!viewerModal.hasClass('modal-show')) return;
        
        if (e.key === 'Escape') {
            closeViewer();
        } else if (e.key === 'ArrowRight') {
            nextStory();
        } else if (e.key === 'ArrowLeft') {
            prevStory();
        }
    });
    
    function markStorySeen(story) {
        if (!isAuth) return;
        if (story.is_seen === 1) return;
        
        $.ajax({
            url: "/stories/" + story.id + "/seen",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    story.is_seen = 1;
                    if (story.is_owner) {
                        story.view_count = res.view_count;
                        viewerStatsCount.text(res.view_count);
                    }
                    
                    // Update frontend state on cards
                    const card = $(`.story-user-card[data-user-id="${story.user_id}"]`);
                    const userStories = JSON.parse(card.attr('data-stories') || '[]');
                    const match = userStories.find(s => s.id == story.id);
                    if (match) match.is_seen = 1;
                    card.attr('data-stories', JSON.stringify(userStories));
                    
                    const anyUnseen = userStories.some(s => s.is_seen === 0);
                    const ringDiv = card.find('.rounded-full');
                    if (ringDiv.length > 0) {
                        if (anyUnseen) {
                            ringDiv.removeClass('border-outline-variant border-2').addClass('border-primary border-[3px]');
                        } else {
                            ringDiv.removeClass('border-primary border-[3px]').addClass('border-outline-variant border-2');
                        }
                    }
                }
            }
        });
    }


    // --- Story Viewers List Logic ---
    const viewersModal = $('#story-viewers-modal');
    const viewersList = $('#story-viewers-list');
    
    viewerStatsBtn.on('click', function() {
        const storyId = $(this).attr('data-story-id');
        if (!storyId) return;
        
        // Pause
        isStoryPaused = true;
        if (allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
            viewerVideo[0].pause();
        }
        
        viewersList.html(`
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
            </div>
        `);
        
        viewersModal.removeClass('hidden').addClass('flex');
        setTimeout(() => viewersModal.addClass('modal-show'), 10);
        
        $.ajax({
            url: "/stories/" + storyId + "/viewers",
            type: "GET",
            success: function(res) {
                viewersList.empty();
                if (res.success && res.viewers.length > 0) {
                    res.viewers.forEach(v => {
                        viewersList.append(`
                            <div class="flex items-center justify-between py-2 border-b border-primary/5 last:border-0 text-right" style="direction: rtl;">
                                <div class="flex items-center gap-2.5">
                                    <img src="${v.profile_picture}" class="w-9 h-9 rounded-full object-cover border border-outline-variant">
                                    <div>
                                        <p class="text-xs font-bold text-on-surface">${v.user_name}</p>
                                        <p class="text-[9px] text-on-surface-variant">${v.email}</p>
                                    </div>
                                </div>
                                <span class="text-[9px] text-on-surface-variant font-medium">${v.viewed_at}</span>
                            </div>
                        `);
                    });
                } else {
                    viewersList.append('<p class="text-xs text-on-surface-variant text-center py-4">لا توجد مشاهدات لهذه القصة بعد.</p>');
                }
            },
            error: function() {
                viewersList.html('<p class="text-xs text-error text-center py-4">حدث خطأ أثناء تحميل قائمة المشاهدات.</p>');
            }
        });
    });
    
    function closeViewersModal() {
        viewersModal.removeClass('modal-show');
        setTimeout(() => {
            viewersModal.removeClass('flex').addClass('hidden');
            viewersList.empty();
            
            // Resume
            isStoryPaused = false;
            if (allStoryUsers[activeUserIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
                viewerVideo[0].play().catch(() => {});
            }
        }, 300);
    }
    
    $('#close-story-viewers-btn, #viewers-backdrop').on('click', closeViewersModal);

    // --- Story Deletion Logic ---
    const deleteConfirmModal = $('#delete-confirm-modal');
    
    viewerDeleteBtn.on('click', function(e) {
        e.stopPropagation();
        const storyId = $(this).attr('data-story-id');
        if (!storyId) return;
        
        // Pause playback
        isStoryPaused = true;
        if (allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
            viewerVideo[0].pause();
        }
        
        // Set ID on confirm button
        $('#confirm-delete-story-btn').attr('data-story-id', storyId);
        
        // Open confirmation modal
        deleteConfirmModal.removeClass('hidden').addClass('flex');
        setTimeout(() => deleteConfirmModal.addClass('modal-show'), 10);
    });
    
    function closeDeleteConfirmModal() {
        deleteConfirmModal.removeClass('modal-show');
        setTimeout(() => {
            deleteConfirmModal.removeClass('flex').addClass('hidden');
            $('#confirm-delete-story-btn').removeAttr('data-story-id');
            
            // Hide spinner and enable button
            $('#confirm-delete-story-btn').prop('disabled', false);
            $('#delete-confirm-spinner').addClass('hidden');
        }, 300);
    }
    
    $('#cancel-delete-confirm-btn, #delete-confirm-backdrop').on('click', function() {
        closeDeleteConfirmModal();
        resumeViewerPlayback();
    });
    
    $('#confirm-delete-story-btn').on('click', function() {
        const storyId = $(this).attr('data-story-id');
        if (!storyId) return;
        
        const btn = $(this);
        const spinner = $('#delete-confirm-spinner');
        
        btn.prop('disabled', true);
        spinner.removeClass('hidden');
        
        $.ajax({
            url: "/stories/" + storyId + "/delete",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeDeleteConfirmModal();
                    closeViewer();
                    
                    const userId = allStoryUsers[activeUserIndex].userId;
                    let userCard = $(`.story-user-card[data-user-id="${userId}"]`);
                    
                    if (userCard.length > 0) {
                        let stories = JSON.parse(userCard.attr('data-stories') || '[]');
                        stories = stories.filter(s => s.id != storyId);
                        
                        if (stories.length > 0) {
                            userCard.attr('data-stories', JSON.stringify(stories));
                            updateCardPreview(userCard, stories[0]);
                        } else {
                            userCard.hide(300, function() {
                                $(this).remove();
                            });
                        }
                    }
                } else {
                    toastr.error(res.message || 'حدث خطأ أثناء حذف القصة.');
                    closeDeleteConfirmModal();
                    resumeViewerPlayback();
                }
            },
            error: function() {
                toastr.error('حدث خطأ في الاتصال بالخادم.');
                closeDeleteConfirmModal();
                resumeViewerPlayback();
            }
        });
    });
    
    function resumeViewerPlayback() {
        isStoryPaused = false;
        if (allStoryUsers[activeUserIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex] && allStoryUsers[activeUserIndex].stories[activeStoryIndex].type === 'video') {
            viewerVideo[0].play().catch(() => {});
        }
    }
});
</script>
@endpush



<!-- Mutual Friends Modal -->
<div id="mutual-friends-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-md w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[70vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low text-right" style="direction: rtl;">
            <h3 class="font-headline-md text-base font-bold text-primary" id="mutual-friends-title">{{ __t('mutual_friends_title') }}</h3>
            <button id="close-mutual-friends-btn" class="text-on-surface-variant hover:text-on-surface p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <!-- Modal Body (List) -->
        <div class="p-6 overflow-y-auto flex-grow text-right space-y-4" id="mutual-friends-list" style="direction: rtl;">
            <!-- Dynamic list will be rendered here -->
        </div>
    </div>
</div>


<!-- Add Story Modal -->
<div id="add-story-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="add-story-backdrop"></div>
    
    <!-- Modal Content Container -->
    @php $dir = current_language()->direction ?? 'rtl'; $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left'; @endphp
    <div class="modal-container relative max-w-lg w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[85vh] {{ $textAlign }}" style="direction: {{ $dir }};">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low">
            <h3 class="font-headline-md text-base font-bold text-primary">{{ __t('add_new_story') }}</h3>
            <button type="button" id="close-add-story-btn" class="text-on-surface-variant hover:text-on-surface p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="add-story-form" method="POST" enctype="multipart/form-data" class="flex-grow flex flex-col overflow-y-auto">
            @csrf
            <div class="p-6 space-y-4 flex-grow">
                <!-- Content text input -->
                <div>
                    <label for="story-caption" class="block text-xs font-bold text-on-surface-variant mb-2">{{ __t('story_caption_label') }}</label>
                    <textarea id="story-caption" name="content" dir="{{ $dir }}" rows="2" class="w-full bg-surface border border-outline-variant rounded-xl p-3 text-sm focus:ring-1 focus:ring-primary focus:border-primary resize-none placeholder:text-on-surface-variant/60" placeholder="{{ __t('story_caption_placeholder') }}"></textarea>
                </div>
                
                <!-- Drag and Drop Media File Picker -->
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-2">{{ __t('story_media_label') }}</label>
                    <div id="story-dropzone" class="border-2 border-dashed border-outline-variant hover:border-primary/50 rounded-xl p-6 flex flex-col items-center justify-center cursor-pointer transition-all duration-200 bg-surface-container-lowest">
                        <input type="file" id="story-file-input" name="media" accept="image/*,video/*" class="hidden">
                        <span class="material-symbols-outlined text-[36px] text-primary/60 mb-2">upload_file</span>
                        <p class="text-xs font-bold text-on-surface mb-1">{{ __t('story_dropzone_click') }}</p>
                        <p class="text-[10px] text-on-surface-variant">{{ __t('story_dropzone_drag') }}</p>
                        <p class="text-[9px] text-on-surface-variant/80 mt-2">{{ __t('story_file_limit') }}</p>
                    </div>
                </div>

                <!-- Live Previews -->
                <div id="story-preview-wrapper" class="hidden relative rounded-xl overflow-hidden border border-outline-variant bg-surface-container-low max-h-60 flex items-center justify-center">
                    <img id="story-image-preview" src="" class="hidden max-h-60 max-w-full object-contain">
                    <video id="story-video-preview" controls class="hidden max-h-60 max-w-full object-contain"></video>
                    <button type="button" id="remove-story-media-btn" class="absolute top-2 right-2 p-1.5 bg-black/60 hover:bg-black/80 text-white rounded-full transition-all cursor-pointer flex items-center justify-center">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </div>
                
                <!-- Progress Bar for Upload -->
                <div id="story-upload-progress-wrapper" class="hidden space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-primary" id="story-upload-percentage">0%</span>
                        <span class="text-on-surface-variant">{{ __t('story_uploading') }}</span>
                    </div>
                    <div class="w-full bg-surface-container-high rounded-full h-2 overflow-hidden">
                        <div id="story-upload-progress-bar" class="bg-primary h-full rounded-full transition-all duration-100" style="width: 0%;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="p-4 border-t border-primary/5 bg-surface-container-low flex justify-end gap-2">
                <button type="button" id="cancel-add-story-btn" class="px-5 py-2 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
                <button type="submit" id="submit-story-btn" class="bg-primary text-on-primary px-6 py-2 rounded-full text-xs font-bold hover:bg-primary-container hover:text-white transition-all shadow-sm flex items-center gap-2">
                    <span>{{ __t('publish_story') }}</span>
                    <div id="story-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Story Viewer Modal -->
<div id="story-viewer-modal" class="fixed inset-0 z-50 hidden flex-col items-center justify-center bg-black/95 backdrop-blur-md">
    <!-- Close Button -->
    <button id="close-viewer-btn" class="absolute top-4 left-4 z-50 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
        <span class="material-symbols-outlined text-[24px]">close</span>
    </button>
    
    <!-- Main Layout Wrapper -->
    <div class="relative w-full max-w-md h-full md:h-[90vh] md:max-h-[800px] bg-neutral-950 rounded-none md:rounded-2xl overflow-hidden flex flex-col justify-between shadow-2xl select-none">
        <!-- Top Overlay: Progress indicators & User info -->
        <div class="absolute top-0 inset-x-0 p-4 bg-gradient-to-b from-black/80 to-transparent z-30 pointer-events-none flex flex-col gap-3">
            <!-- Progress Indicators Segment bar -->
            <div class="flex gap-1.5 w-full" id="viewer-progress-container">
                <!-- Dynamic segments will be inserted here -->
            </div>
            
            <!-- User details & story timestamp -->
            <div class="flex items-center justify-between w-full pointer-events-auto">
                <div class="flex items-center gap-3">
                    <img id="viewer-avatar" src="" class="w-10 h-10 rounded-full object-cover border-2 border-primary bg-white shadow-md">
                    <div class="text-right">
                        <h4 id="viewer-username" class="text-sm font-bold text-white leading-tight"></h4>
                        <span id="viewer-time" class="text-[10px] text-white/70 font-medium"></span>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Delete Button for owner -->
                    <button id="viewer-delete-btn" class="hidden text-white/70 hover:text-red-500 bg-white/10 hover:bg-white/20 p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center" title="حذف القصة">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                    <span class="bg-white/10 text-white/90 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ __t('story_24h_badge') }}</span>
                </div>
            </div>
        </div>

        <!-- Content Container (Image, Video, Text) -->
        <div class="relative flex-grow flex items-center justify-center w-full h-full bg-neutral-900" id="viewer-content-area">
            <!-- Left/Right navigation tap zones (invisible on sides) -->
            <div class="absolute inset-y-0 right-0 w-1/3 z-20 cursor-pointer" id="viewer-tap-right"></div>
            <div class="absolute inset-y-0 left-0 w-1/3 z-20 cursor-pointer" id="viewer-tap-left"></div>
            
            <!-- Media elements -->
            <img id="viewer-image" src="" class="hidden w-full h-full object-contain max-h-full">
            <video id="viewer-video" class="hidden w-full h-full object-contain max-h-full" playsinline></video>
            
            <!-- Text Story Background -->
            <div id="viewer-text-bg" class="hidden absolute inset-0 flex items-center justify-center p-6 text-center bg-gradient-to-br from-primary-container to-secondary/20">
                <p id="viewer-text-content" class="text-lg font-bold text-white leading-relaxed max-w-sm"></p>
            </div>
        </div>
        
        <!-- Bottom Bar (Viewers button for owners, or caption) -->
        <div class="absolute bottom-0 inset-x-0 p-4 bg-gradient-to-t from-black/85 via-black/40 to-transparent z-30 flex flex-col items-center gap-3 text-center">
            <p id="viewer-caption" class="text-sm font-semibold text-white/95 leading-relaxed max-w-xs drop-shadow-md"></p>
            
            <!-- View Count Bar (Visible for owner) -->
            <button id="viewer-stats-btn" class="hidden items-center justify-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-bold py-1.5 px-4 rounded-full transition-all border border-white/10 cursor-pointer">
                <span class="material-symbols-outlined text-[16px] leading-none">visibility</span>
                <span id="viewer-stats-count" class="leading-none">0</span>
            </button>
        </div>
    </div>

    <!-- Desktop Navigation Chevrons (Outside container) -->
    <button id="viewer-prev-arrow" class="hidden md:flex absolute left-[calc(50%+240px)] top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 items-center justify-center shadow-lg transition-all cursor-pointer">
        <span class="material-symbols-outlined text-[28px]">chevron_right</span>
    </button>
    <button id="viewer-next-arrow" class="hidden md:flex absolute right-[calc(50%+240px)] top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 items-center justify-center shadow-lg transition-all cursor-pointer">
        <span class="material-symbols-outlined text-[28px]">chevron_left</span>
    </button>
</div>


<!-- Story Viewers Modal -->
<div id="story-viewers-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="viewers-backdrop"></div>
    
    <!-- Modal Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[50vh] text-right" style="direction: rtl;">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low">
            <h3 class="font-headline-md text-sm font-bold text-primary">{{ __t('story_views_title') }}</h3>
            <button type="button" id="close-story-viewers-btn" class="text-on-surface-variant hover:text-on-surface p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        
        <!-- Viewers list -->
        <div class="p-4 overflow-y-auto flex-grow space-y-3" id="story-viewers-list">
            <!-- Dynamic list will be rendered here -->
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="delete-confirm-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: rtl;">
        <!-- Red Trash Icon -->
        <div class="w-12 h-12 rounded-full bg-error-container/60 flex items-center justify-center text-error mb-4">
            <span class="material-symbols-outlined text-[26px]">delete_forever</span>
        </div>
        
        <!-- Title & Subtitle -->
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('delete_story_title') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-6">{{ __t('delete_story_confirm_msg') }}</p>
        
        <!-- Buttons -->
        <div class="flex gap-3 w-full">
            <button type="button" id="confirm-delete-story-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-red-700 transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer">
                <span>{{ __t('delete_permanent') }}</span>
                <div id="delete-confirm-spinner" class="hidden w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            </button>
            <button type="button" id="cancel-delete-confirm-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all cursor-pointer">{{ __t('cancel') }}</button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Mutual Friends Modal Transitions */
    #mutual-friends-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #mutual-friends-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Stories Modal Transitions */
    #add-story-modal, #story-viewers-modal, #delete-confirm-modal {
        transition: visibility 0.3s ease, opacity 0.3s ease;
    }
    #add-story-modal.modal-show, #story-viewers-modal.modal-show, #delete-confirm-modal.modal-show {
        display: flex !important;
    }
    #add-story-modal.modal-show .modal-backdrop, #story-viewers-modal.modal-show .modal-backdrop, #delete-confirm-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #add-story-modal.modal-show .modal-container, #story-viewers-modal.modal-show .modal-container, #delete-confirm-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    #story-viewer-modal.modal-show {
        display: flex !important;
    }
    
    #story-viewer-modal .progress-bar-segment {
        height: 3px;
        background-color: rgba(255, 255, 255, 0.35);
        border-radius: 2px;
        overflow: hidden;
        flex-grow: 1;
        position: relative;
    }
    
    #story-viewer-modal .progress-bar-segment-inner {
        height: 100%;
        width: 0%;
        background-color: #ffffff;
    }


    /* Premium Mention Dropdown Styling */
    #mention-dropdown {
        background-color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(27, 67, 50, 0.1);
        scrollbar-width: thin;
        scrollbar-color: rgba(37, 99, 235, 0.3) transparent;
        transition: opacity 0.15s ease, transform 0.15s ease;
        border-radius: 12px;
    }
    
    #mention-dropdown::-webkit-scrollbar {
        width: 6px;
    }
    
    #mention-dropdown::-webkit-scrollbar-track {
        background: transparent;
    }
    
    #mention-dropdown::-webkit-scrollbar-thumb {
        background-color: rgba(37, 99, 235, 0.25);
        border-radius: 10px;
    }
    
    #mention-dropdown::-webkit-scrollbar-thumb:hover {
        background-color: rgba(37, 99, 235, 0.45);
    }
    
    .mention-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.02);
        outline: none;
    }
    
    .mention-item:last-child {
        border-bottom: none;
    }
    
    .mention-item:hover, .mention-item.bg-primary\/5 {
        background-color: rgba(37, 99, 235, 0.08) !important;
        color: #2563eb !important;
    }
    
    .mention-item.bg-primary\/5 span {
        color: #2563eb !important;
    }

    /* Emoji Picker Styling */
    #emoji-picker-dropdown {
        background-color: #ffffff;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(27, 67, 50, 0.1);
        border-radius: 12px;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }

    #emoji-grid-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(37, 99, 235, 0.3) transparent;
    }

    #emoji-grid-container::-webkit-scrollbar {
        width: 6px;
    }

    #emoji-grid-container::-webkit-scrollbar-track {
        background: transparent;
    }

    #emoji-grid-container::-webkit-scrollbar-thumb {
        background-color: rgba(37, 99, 235, 0.25);
        border-radius: 10px;
    }

    #emoji-grid-container::-webkit-scrollbar-thumb:hover {
        background-color: rgba(37, 99, 235, 0.45);
    }

    .emoji-cat-btn.active {
        background-color: rgba(37, 99, 235, 0.08) !important;
        color: #2563eb !important;
        font-weight: bold;
    }
</style>
@endpush
