@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24">
    
    <!-- Top Header -->
    <div class="mb-8 bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm {{ $dir === 'rtl' ? 'text-right' : 'text-left' }} flex justify-between items-center gap-4">
        <div>
            <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-[28px] text-secondary">explore</span>
                <span>{{ __t('post_details_title') }}</span>
            </h1>
            <p class="font-body-md text-xs text-on-surface-variant mt-1">{{ __t('post_details_sub') }}</p>
        </div>
        <a href="{{ route('frontend.home') }}" class="flex items-center gap-1.5 px-4 py-2 border border-primary/10 hover:border-primary text-primary rounded-xl font-label-md text-xs transition-colors cursor-pointer bg-white">
            <span class="material-symbols-outlined text-[18px]">{{ $dir === 'rtl' ? 'arrow_forward' : 'arrow_back' }}</span>
            <span>{{ __t('back_to_home') }}</span>
        </a>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Right Column: Single Post & Comments (RTL: right side on Desktop) -->
        <section class="lg:col-span-9 order-2 lg:order-1 space-y-6 {{ $dir === 'rtl' ? 'text-right' : 'text-left' }}">
            @php
                $fullName = $post->user ? ($post->user->first_name . ' ' . $post->user->last_name) : __t('unknown_user');
                $avatarUrl = url('upload/no_image.jpg');
                if ($post->user && $post->user->profile_picture && $post->user->profile_picture !== 'non') {
                    $avatarUrl = filter_var($post->user->profile_picture, FILTER_VALIDATE_URL)
                        ? $post->user->profile_picture
                        : asset('new_wiselook/uploads/' . $post->user->profile_picture);
                }
            @endphp
            
            <!-- Post Card -->
            <article class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 shadow-[0_4px_20px_rgba(27,67,50,0.05)] overflow-hidden">
                <div class="p-6">
                    <!-- Post Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                @if($post->user)
                                    <a href="{{ route('profile.edit', $post->user->id) }}">
                                        <img alt="{{ $fullName }}" class="w-14 h-14 rounded-full object-cover border-2 border-outline-variant hover:opacity-90 transition-opacity" src="{{ $avatarUrl }}"/>
                                    </a>
                                @else
                                    <img alt="{{ $fullName }}" class="w-14 h-14 rounded-full object-cover border-2 border-outline-variant" src="{{ $avatarUrl }}"/>
                                @endif
                                @if($post->user && $post->user->role == 'admin')
                                    <span class="absolute -bottom-1 {{ $dir === 'rtl' ? '-right-1' : '-left-1' }} bg-secondary-container text-on-secondary-container rounded-full p-1 border-2 border-surface-container-lowest flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[14px]">workspace_premium</span>
                                    </span>
                                @endif
                            </div>
                            <div>
                                @if($post->user)
                                    <a href="{{ route('profile.edit', $post->user->id) }}" class="hover:underline block">
                                        <h3 class="font-headline-lg-mobile text-base md:text-base text-primary font-bold">{{ $fullName }}</h3>
                                    </a>
                                @else
                                    <h3 class="font-headline-lg-mobile text-base md:text-base text-primary font-bold">{{ $fullName }}</h3>
                                @endif
                                <div class="flex items-center text-on-surface-variant font-label-sm text-label-sm gap-2 mt-2">
                                    @if($post->user && $post->user->rank)
                                        @php
                                            $rankPhoto = $post->user->rank->photo;
                                            $rankPhotoPath = url('upload/no_image.jpg');
                                            if (!empty($rankPhoto) && file_exists(public_path('upload/rankings/' . $rankPhoto))) {
                                                $rankPhotoPath = asset('upload/rankings/' . $rankPhoto);
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5" style="display: inline-flex; align-items: center; gap: 6px; vertical-align: middle;">
                                            <img src="{{ $rankPhotoPath }}" alt="{{ __t($post->user->rank->rank_name) }}" style="width: 20px; height: 20px; object-fit: contain; margin-{{ $dir === 'rtl' ? 'left' : 'right' }}: 2px;">
                                            <span class="font-bold text-xs" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ __t($post->user->rank->rank_name) }}</span>
                                        </span>
                                    @else
                                        <span>{{ $post->user && $post->user->role == 'admin' ? __t('platform_admin') : __t('technical_advisor') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if(Auth::check() && $post->user_id === Auth::id())
                            <button type="button" class="delete-post-btn text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded-full transition-all shrink-0 cursor-pointer flex items-center justify-center" data-post-id="{{ $post->id }}" title="{{ __t('delete_post') }}">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        @endif
                    </div>
                    
                    <!-- Post Content -->
                    <div class="space-y-4">
                        <p class="font-body-lg text-body-lg text-on-surface leading-[1.8]">
                            {!! \App\Models\Post::formatContent($post->content) !!}
                        </p>
                        
                        @if($post->image)
                            <div class="post-image-trigger w-full h-64 rounded-xl overflow-hidden mb-4 relative bg-surface-container-high border border-outline-variant cursor-pointer hover:opacity-95 transition-opacity" style="background-image: url('{{ asset('new_wiselook/uploads/' . $post->image) }}'); background-size: cover; background-position: center;" data-image-src="{{ asset('new_wiselook/uploads/' . $post->image) }}">
                            </div>
                        @endif

                        @if($post->video)
                            <div class="video-preview-trigger relative w-full h-64 rounded-xl overflow-hidden mb-4 bg-black cursor-pointer group/video-card border border-outline-variant" data-video-src="{{ asset('new_wiselook/uploads/' . $post->video) }}">
                                <video class="w-full h-full object-cover opacity-75 transition-transform duration-500 group-hover/video-card:scale-103" preload="metadata">
                                    <source src="{{ asset('new_wiselook/uploads/' . $post->video) }}#t=0.5" type="video/mp4">
                                </video>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white transition-all duration-300 shadow-lg group-hover/video-card:scale-110 group-hover/video-card:bg-primary group-hover/video-card:border-primary/50 group-hover/video-card:rotate-6">
                                        <span class="material-symbols-outlined text-[36px]" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                                    </div>
                                </div>
                                <div class="absolute bottom-3 left-3 bg-black/60 px-2 py-1 rounded text-[10px] text-white/90 font-bold backdrop-blur-sm">
                                    {{ __t('video_label') }}
                                </div>
                            </div>
                        @endif

                        @if($post->post_type_id == 2 && $post->poll)
                            <div class="mt-4 mb-4 p-4 rounded-xl border border-primary/10 bg-surface-container-low/50 space-y-3 poll-container" data-poll-id="{{ $post->poll->id }}">
                                <h4 class="font-title-md text-sm font-bold text-primary flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px]">poll</span>
                                    {{ $post->poll->question }}
                                </h4>
                                
                                <div class="space-y-2 poll-options-list">
                                    @php
                                        $totalVotes = $post->poll->total_votes ?: 0;
                                    @endphp
                                    @foreach($post->poll->options as $option)
                                        @php
                                            $votes = $option->vote_count ?: 0;
                                            $percent = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0;
                                            $isUserVoted = false;
                                            if(Auth::check()) {
                                                $isUserVoted = \App\Models\PollResponse::where('user_id', Auth::id())->where('poll_option_id', $option->id)->exists();
                                            }
                                        @endphp
                                        <div class="poll-option-btn relative w-full p-3 rounded-lg border transition-all duration-200 hover:border-primary/20 cursor-pointer overflow-hidden flex items-center justify-between group {{ $isUserVoted ? 'border-primary bg-primary/5' : 'border-primary/5 bg-surface' }}" data-option-id="{{ $option->id }}">
                                            <div class="progress-bar absolute inset-y-0 {{ $dir === 'rtl' ? 'right-0' : 'left-0' }} transition-all duration-500 {{ $isUserVoted ? 'bg-primary/10' : 'bg-primary/5' }}" style="width: {{ $percent }}%"></div>
                                            <span class="relative z-10 font-body-md text-xs font-semibold text-on-surface select-none {{ $dir === 'rtl' ? 'pr-1' : 'pl-1' }} option-text">
                                                {{ $option->content }}
                                            </span>
                                            <span class="relative z-10 font-label-sm text-xs font-bold text-primary select-none {{ $dir === 'rtl' ? 'pl-1' : 'pr-1' }} option-percent">
                                                {{ $percent }}% ({{ $votes }})
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="text-[10px] text-on-surface-variant font-medium pt-1 {{ $dir === 'rtl' ? 'text-left' : 'text-right' }} flex justify-between">
                                    <span class="total-votes-count">{{ __t('total_votes') }}: {{ $totalVotes }}</span>
                                    <span>* {{ __t('poll_label') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Post Time at the end of the card -->
                    <div class="mt-4 {{ $dir === 'rtl' ? 'text-left' : 'text-right' }} px-6">
                        <span class="text-on-surface-variant/75 text-[11px] font-semibold bg-surface-variant/40 px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'wght' 300;">schedule</span>
                            {{ $post->created_at->diffForHumans() }}
                        </span>
                    </div>

                    <!-- Post Actions -->
                    <div class="mt-6 pt-4 border-t border-surface-variant flex items-center justify-between text-on-surface-variant">
                        <div class="flex items-center gap-6">
                            <div class="flex items-center gap-1">
                                @php
                                    $userLikedPost = false;
                                    if (Auth::check()) {
                                        $userLikedPost = \App\Models\Reaction::where('user_id', Auth::id())
                                            ->where('content_id', $post->id)
                                            ->where('content_type_id', 1)
                                            ->where('is_active', 1)
                                            ->exists();
                                    }
                                @endphp
                                <button class="post-support-action flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 hover:text-primary transition-all {{ $userLikedPost ? 'text-primary bg-primary/10' : '' }}" data-post-id="{{ $post->id }}" data-active="{{ $userLikedPost ? 'true' : 'false' }}" title="{{ __t('support') }}">
                                    <span class="material-symbols-outlined text-[20px] {{ $userLikedPost ? 'fill-1' : '' }}">lightbulb</span>
                                </button>
                                <button class="open-supporters-btn text-xs font-semibold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0" data-post-id="{{ $post->id }}" data-total-supports="{{ $post->like_count ?? 0 }}" title="{{ __t('view_supporters') }}">
                                    <span class="support-count">{{ $post->like_count ?? 0 }}</span> {{ __t('support') }}
                                </button>
                            </div>
                        </div>

                        @php
                            $isSaved = false;
                            if (Auth::check()) {
                                $isSaved = \App\Models\SavedPost::where('user_id', Auth::id())->where('post_id', $post->id)->exists();
                            }
                        @endphp
                        <div class="flex items-center gap-4">
                            <button class="toggle-save-post-btn flex items-center gap-2 hover:text-primary transition-colors {{ $isSaved ? 'text-primary' : '' }} cursor-pointer" data-post-id="{{ $post->id }}" data-saved="{{ $isSaved ? 'true' : 'false' }}">
                                <span class="material-symbols-outlined {{ $isSaved ? 'fill-1' : '' }}">{{ $isSaved ? 'bookmark' : 'bookmark_border' }}</span>
                                <span class="font-label-sm text-label-sm save-text">{{ $isSaved ? __t('saved') : __t('save') }}</span>
                            </button>
                            <button class="share-post-btn flex items-center gap-2 hover:text-primary transition-colors cursor-pointer" data-post-id="{{ $post->id }}" data-post-content="{{ urlencode($post->content) }}">
                                <span class="material-symbols-outlined">share</span>
                                <span class="font-label-sm text-label-sm">{{ __t('share_post') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Comments Section -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">

                <h3 class="font-headline-lg text-base font-bold text-primary flex items-center gap-2 mb-6 border-b border-primary/5 pb-4">
                    <span class="material-symbols-outlined text-secondary">forum</span>
                    <span>{{ __t('topic_discussion_board') }}</span>
                </h3>
                
                <!-- Page Comments List -->
                <div id="page-comments-list" class="space-y-6">
                    <!-- Loaded via AJAX -->
                </div>

                <!-- Add Comment form -->
                @auth
                    @php
                        if (Auth::user()->profile_picture && Auth::user()->profile_picture !== 'non') {
                            $currentUserAvatar = filter_var(Auth::user()->profile_picture, FILTER_VALIDATE_URL)
                                ? Auth::user()->profile_picture
                                : asset('new_wiselook/uploads/' . Auth::user()->profile_picture);
                        } else {
                            $currentUserAvatar = asset('upload/no_image.jpg');
                        }
                    @endphp
                    <div class="mt-6 pt-6 border-t border-primary/10">
                        <form id="page-new-comment-form" class="flex items-center gap-3">
                            <img alt="User" class="w-10 h-10 rounded-full object-cover shrink-0" src="{{ $currentUserAvatar }}">
                            <div class="flex-1 relative">
                                <input type="text" id="page-new-comment-input" class="w-full bg-surface border border-primary/10 rounded-full py-3 {{ $dir === 'rtl' ? 'pr-5 pl-14' : 'pl-5 pr-14' }} text-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('write_comment_placeholder') }}">
                                <button type="submit" class="absolute {{ $dir === 'rtl' ? 'left-2' : 'right-2' }} top-1/2 -translate-y-1/2 bg-primary text-white hover:bg-primary-dark p-2 rounded-full flex items-center justify-center transition-colors">
                                    <span class="material-symbols-outlined text-[20px]">{{ $dir === 'rtl' ? 'send' : 'send' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Guest placeholder -->
                    <div class="mt-6 pt-6 border-t border-primary/10">
                        <div class="guest-comment-trigger flex items-center gap-3 cursor-pointer">
                            <img alt="Guest" class="w-10 h-10 rounded-full object-cover shrink-0" src="{{ url('upload/no_image.jpg') }}">
                            <div class="flex-1 relative">
                                <input type="text" readonly class="w-full bg-surface-container-low border border-primary/5 rounded-full py-3 px-5 text-sm text-on-surface-variant cursor-pointer {{ $dir === 'rtl' ? 'text-right' : 'text-left' }}" placeholder="{{ __t('login_to_comment_placeholder') }}">
                                <div class="absolute {{ $dir === 'rtl' ? 'left-4' : 'right-4' }} top-1/2 -translate-y-1/2 text-xs font-bold text-primary hover:underline">{{ __t('login') }}</div>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>

            {{-- ============================================================
                 قسم تقييم لجنة الحكماء (يظهر فقط إذا كان هناك تقييمات)
                 ============================================================ --}}
            @include('frontend.wiselook.partials.wise_committee_rating')
        </section>
        
        <!-- Left Column: Sidebar details -->
        <aside class="lg:col-span-3 order-1 lg:order-2 space-y-6 lg:sticky lg:top-24 self-start {{ $dir === 'rtl' ? 'text-right' : 'text-left' }}">
            <!-- Author Card Widget -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
                <h4 class="font-title-lg text-sm font-bold text-primary mb-4 pb-2 border-b border-primary/5">{{ __t('topic_author') ?? 'كاتب الموضوع' }}</h4>
                <div class="flex flex-col items-center text-center space-y-3">
                    @if($post->user)
                        <a href="{{ route('profile.edit', $post->user->id) }}">
                            <img alt="{{ $fullName }}" class="w-20 h-20 rounded-full object-cover border-4 border-outline-variant hover:opacity-90 transition-opacity" src="{{ $avatarUrl }}">
                        </a>
                        <div>
                            <a href="{{ route('profile.edit', $post->user->id) }}" class="hover:underline">
                                <h5 class="font-headline-lg-mobile text-base font-bold text-primary">{{ $fullName }}</h5>
                            </a>
                            <div class="mt-1 flex justify-center items-center">
                                @if($post->user->rank)
                                    @php
                                        $authorRankPhoto = $post->user->rank->photo;
                                        $authorRankPhotoPath = null;
                                        if (!empty($authorRankPhoto) && file_exists(public_path('upload/rankings/' . $authorRankPhoto))) {
                                            $authorRankPhotoPath = asset('upload/rankings/' . $authorRankPhoto);
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5" style="display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;">
                                        @if($authorRankPhotoPath)
                                            <img src="{{ $authorRankPhotoPath }}" alt="{{ __t($post->user->rank->rank_name) }}" style="width: 16px; height: 16px; object-fit: contain;">
                                        @endif
                                        <span class="font-bold text-xs" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ __t($post->user->rank->rank_name) }}</span>
                                    </span>
                                @else
                                    <span class="font-label-sm text-xs text-on-surface-variant">{{ $post->user->role == 'admin' ? __t('platform_admin') : __t('honorary_member') }}</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <img alt="{{ $fullName }}" class="w-20 h-20 rounded-full object-cover border-4 border-outline-variant" src="{{ $avatarUrl }}">
                        <div>
                            <h5 class="font-headline-lg-mobile text-base font-bold text-primary">{{ $fullName }}</h5>
                            <p class="text-xs text-on-surface-variant mt-1">{{ __t('unknown_advisor') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($post->wise_rating || $post->wiseRatings->count() > 0)
            {{-- Widget: تقييم لجنة الحكماء (موجز للـ Sidebar) --}}
            @php
                $sidebarRatings = $post->wiseRatings;
                $sidebarAvg = $post->wise_rating ? floatval($post->wise_rating) : $sidebarRatings->avg('rating');
                $sidebarCount = $sidebarRatings->count();
                $sidebarColor = $sidebarAvg >= 8 ? '#16a34a' : ($sidebarAvg >= 6 ? '#b8922a' : ($sidebarAvg >= 4 ? '#ea580c' : '#dc2626'));
                $sidebarLabel = $sidebarAvg >= 8 ? __t('rating_excellent') : ($sidebarAvg >= 6 ? __t('rating_good') : ($sidebarAvg >= 4 ? __t('rating_acceptable') : __t('rating_weak')));
            @endphp
            <a href="#wise-rating-section" class="block no-underline" style="text-decoration:none;">
                <div style="
                    background: linear-gradient(135deg, #0a2218 0%, #132d1f 60%, #0d2218 100%);
                    border: 1px solid rgba(212,175,55,0.2);
                    border-radius: 16px;
                    padding: 18px;
                    box-shadow: 0 4px 20px rgba(10,34,24,0.2);
                    cursor: pointer;
                    transition: all 0.3s ease;
                " onmouseover="this.style.borderColor='rgba(212,175,55,0.4)'; this.style.transform='translateY(-2px)';"
                   onmouseout="this.style.borderColor='rgba(212,175,55,0.2)'; this.style.transform='translateY(0)';">

                    {{-- عنوان --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span class="material-symbols-outlined" style="font-size:18px; color:#d4af37; font-variation-settings:'FILL' 0,'wght' 300;">gavel</span>
                        <span style="color:rgba(255,255,255,0.85); font-size:13px; font-weight:700;">تقييم لجنة الحكماء</span>
                    </div>

                    {{-- متوسط الدرجة --}}
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div style="font-size:34px; font-weight:900; color:#f0d060; line-height:1; letter-spacing:-1.5px; text-shadow: 0 2px 10px rgba(212,175,55,0.3);">
                                {{ number_format($sidebarAvg, 1) }}
                            </div>
                            <div style="color:rgba(255,255,255,0.35); font-size:11px; font-weight:600;">{{ __t('out_of_10_stars') }}</div>
                        </div>
                        <div class="text-center">
                            <div style="
                                background: rgba(255,255,255,0.05);
                                border: 1px solid {{ $sidebarColor }}33;
                                color: {{ $sidebarColor }};
                                padding: 6px 12px; border-radius: 12px;
                                font-size: 12px; font-weight: 800;
                            ">{{ $sidebarLabel }}</div>
                            <div style="color:rgba(255,255,255,0.3); font-size:10px; margin-top:4px;">{{ $sidebarCount }} {{ $sidebarCount == 1 ? __t('wise_scholar') : __t('wise_committee') }}</div>
                        </div>
                    </div>

                    {{-- شريط التقدم --}}
                    <div style="height:6px; background:rgba(255,255,255,0.06); border-radius:10px; overflow:hidden;">
                        <div style="
                            height:100%; width:{{ ($sidebarAvg / 10) * 100 }}%; border-radius:10px;
                            background: linear-gradient(90deg, #d4af37, #f0d060);
                        "></div>
                    </div>

                    {{-- رابط عرض التفاصيل --}}
                    <div class="flex items-center gap-1 mt-3" style="color:rgba(212,175,55,0.6); font-size:11px; font-weight:600;">
                        <span>{{ __t('view_rating_details') }}</span>
                        <span class="material-symbols-outlined" style="font-size:13px; transform: {{ $dir === 'rtl' ? 'scaleX(-1)' : 'none' }};">arrow_back</span>
                    </div>
                </div>
            </a>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const postId = {{ $post->id }};
        const isRtl = {{ $dir === 'rtl' ? 'true' : 'false' }};
        let pageComments = [];

        // Load page comments
        function loadPageComments() {
            const container = $('#page-comments-list');
            container.html('<p class="text-center text-sm text-on-surface-variant py-8">{{ __t("loading_comments") }}</p>');
            
            $.ajax({
                url: `/posts/${postId}/comments`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        pageComments = response.comments;
                        renderPageCommentsList(pageComments);
                    } else {
                        container.html('<p class="text-center text-sm text-error py-8">{{ __t("error_loading_comments") }}</p>');
                    }
                },
                error: function() {
                    container.html('<p class="text-center text-sm text-error py-8">{{ __t("server_connection_failed") }}</p>');
                }
            });
        }

        // Helper to build HTML for a comment card
        function buildCommentHtml(comment) {
            const repliesHtml = renderPageReplies(comment.replies);
            const repliesCount = comment.replies ? comment.replies.length : 0;
            const userLiked = comment.user_liked;
            
            const profileLink = comment.user_id ? `/profile/${comment.user_id}` : '#';
            const avatarHtml = comment.user_id 
                ? `<a href="${profileLink}"><img alt="${comment.user_name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0 hover:opacity-90 transition-opacity" src="${comment.profile_picture}"></a>`
                : `<img alt="${comment.user_name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0" src="${comment.profile_picture}">`;
            
            const nameHtml = comment.user_id
                ? `<a href="${profileLink}" class="hover:underline"><h5 class="font-bold text-primary text-xs">${comment.user_name}</h5></a>`
                : `<h5 class="font-bold text-primary text-xs">${comment.user_name}</h5>`;

            return `
                <div class="comment-card space-y-3" data-comment-id="${comment.id}">
                    <div class="flex items-start gap-3">
                        ${avatarHtml}
                        <div class="flex-1 bg-surface-container-low/50 rounded-2xl p-4 border border-primary/5">
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    ${nameHtml}
                                    <p class="text-[10px] text-on-surface-variant">${comment.created_at}</p>
                                </div>
                            </div>
                            <p class="text-sm text-on-surface leading-relaxed">${comment.content}</p>
                            
                            <div class="flex items-center gap-4 mt-3 pt-2 border-t border-primary/5 text-xs text-on-surface-variant">
                                <div class="flex items-center gap-1">
                                    <button class="comment-like-action flex items-center justify-center w-7 h-7 rounded-full hover:bg-primary/10 hover:text-primary transition-all ${userLiked ? 'text-primary bg-primary/10' : ''}" data-active="${userLiked}">
                                        <span class="material-symbols-outlined text-[15px] ${userLiked ? 'fill-1' : ''}">thumb_up</span>
                                    </button>
                                    <button class="comment-likers-trigger text-[11px] font-bold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0">
                                        <span class="like-count">${comment.reaction_count || 0}</span>
                                    </button>
                                </div>
                                <button class="toggle-replies-btn flex items-center gap-1 hover:text-secondary transition-all">
                                    <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
                                    <span class="replies-count font-bold">{{ __t('replies') }} (${repliesCount})</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="replies-container ${isRtl ? 'mr-10 pr-4 border-r-2' : 'ml-10 pl-4 border-l-2'} border-primary/10 space-y-3 hidden">
                        <div class="replies-list space-y-3">
                            ${repliesHtml}
                        </div>
                        @auth
                        <form class="new-reply-form flex items-center gap-2 mt-3">
                            <input type="text" class="new-reply-input flex-1 bg-surface border border-primary/10 rounded-full py-1.5 px-3.5 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('write_reply_placeholder') }}">
                            <button type="submit" class="bg-primary text-white hover:bg-primary-dark px-4 py-1.5 rounded-full text-[10px] font-bold transition-all shrink-0">{{ __t('send') }}</button>
                        </form>
                        @else
                        <div class="guest-reply-trigger flex items-center gap-2 mt-3 cursor-pointer">
                            <input type="text" readonly class="flex-1 bg-surface-container-low border border-primary/5 rounded-full py-1.5 px-3.5 text-xs text-on-surface-variant cursor-pointer ${isRtl ? 'text-right' : 'text-left'}" placeholder="{{ __t('login_to_reply_placeholder') }}">
                        </div>
                        @endauth
                    </div>
                </div>
            `;
        }

        // Render comments list
        function renderPageCommentsList(comments) {
            const container = $('#page-comments-list');
            container.empty();

            if (!comments || comments.length === 0) {
                container.html('<p id="page-no-comments-placeholder" class="text-center text-sm text-on-surface-variant py-8">{{ __t("no_comments_yet") }}</p>');
                return;
            }

            comments.forEach(comment => {
                const commentHtml = buildCommentHtml(comment);
                container.append(commentHtml);
            });
        }

        // Helper to build HTML for a nested reply card
        function buildReplyHtml(reply) {
            const userLiked = reply.user_liked;
            
            const profileLink = reply.user_id ? `/profile/${reply.user_id}` : '#';
            const avatarHtml = reply.user_id 
                ? `<a href="${profileLink}"><img alt="${reply.user_name}" class="w-8 h-8 rounded-full object-cover border border-outline-variant shrink-0 hover:opacity-90 transition-opacity" src="${reply.profile_picture}"></a>`
                : `<img alt="${reply.user_name}" class="w-8 h-8 rounded-full object-cover border border-outline-variant shrink-0" src="${reply.profile_picture}">`;
            
            const nameHtml = reply.user_id
                ? `<a href="${profileLink}" class="hover:underline"><h6 class="font-bold text-primary text-[11px]">${reply.user_name}</h6></a>`
                : `<h6 class="font-bold text-primary text-[11px]">${reply.user_name}</h6>`;

            return `
                <div class="reply-card flex items-start gap-3" data-reply-id="${reply.id}">
                    ${avatarHtml}
                    <div class="flex-1 bg-surface rounded-2xl p-3 border border-primary/5">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                ${nameHtml}
                                <p class="text-[9px] text-on-surface-variant">${reply.created_at}</p>
                            </div>
                        </div>
                        <p class="text-xs text-on-surface leading-relaxed">${reply.content}</p>
                        
                        <div class="flex items-center gap-3 mt-2 pt-1.5 border-t border-primary/5 text-[10px] text-on-surface-variant">
                            <div class="flex items-center gap-1">
                                <button class="reply-like-action flex items-center justify-center w-6 h-6 rounded-full hover:bg-primary/10 hover:text-primary transition-all ${userLiked ? 'text-primary bg-primary/10' : ''}" data-active="${userLiked}">
                                    <span class="material-symbols-outlined text-[13px] ${userLiked ? 'fill-1' : ''}">thumb_up</span>
                                </button>
                                <button class="reply-likers-trigger font-bold hover:underline hover:text-primary px-1 rounded hover:bg-primary/5 shrink-0">
                                    <span class="like-count">${reply.reaction_count || 0}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Render replies list helper
        function renderPageReplies(replies) {
            if (!replies || replies.length === 0) return '';
            return replies.map(reply => buildReplyHtml(reply)).join('');
        }

        // Load comments on startup
        loadPageComments();

        // Submit comment
        $('#page-new-comment-form').on('submit', function(e) {
            e.preventDefault();
            const input = $('#page-new-comment-input');
            const content = input.val().trim();
            if (!content) return;

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true);

            $.ajax({
                url: `/posts/${postId}/comments`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    content: content
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        input.val('');
                        
                        // Remove placeholder if present
                        $('#page-no-comments-placeholder').remove();

                        // Add to local array
                        pageComments.push(response.comment);

                        // Build and append with slideDown animation
                        const html = buildCommentHtml(response.comment);
                        const $newEl = $(html).css('display', 'none');
                        $('#page-comments-list').append($newEl);
                        $newEl.slideDown(400);
                    } else {
                        alert(response.message || '{{ __t("comment_submit_failed") }}');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || '{{ __t("comment_submit_error") }}');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });

        // Submit Reply
        $(document).on('submit', '#page-comments-list .new-reply-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const input = form.find('.new-reply-input');
            const content = input.val().trim();
            const card = form.closest('.comment-card');
            const commentId = card.attr('data-comment-id');
            
            if (!content || !commentId) return;

            const btn = form.find('button[type="submit"]');
            btn.prop('disabled', true);

            $.ajax({
                url: `/posts/${postId}/comments`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    content: content,
                    parent_id: commentId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        input.val('');
                        
                        const comment = pageComments.find(c => c.id == commentId);
                        if (comment) {
                            if (!comment.replies) comment.replies = [];
                            comment.replies.push(response.comment);
                            
                            // Build and append reply with slideDown animation
                            const html = buildReplyHtml(response.comment);
                            const $newEl = $(html).css('display', 'none');
                            card.find('.replies-list').append($newEl);
                            
                            // Open replies container if hidden
                            const container = card.find('.replies-container');
                            if (container.hasClass('hidden')) {
                                container.removeClass('hidden').show();
                            }
                            
                            $newEl.slideDown(300);
                            card.find('.replies-count').text(`{{ __t('replies') }} (${comment.replies.length})`);
                        }
                    } else {
                        alert(response.message || '{{ __t("reply_submit_failed") }}');
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || '{{ __t("reply_submit_error") }}');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });

        // Click handlers for guest prompts
        $(document).on('click', '.guest-comment-trigger, .guest-reply-trigger', function() {
            if (typeof window.openGuestModal === 'function') {
                window.openGuestModal();
            }
        });

        // Toggle Nested Replies Display
        $(document).on('click', '#page-comments-list .toggle-replies-btn', function() {
            const container = $(this).closest('.comment-card').find('.replies-container');
            container.slideToggle(250).toggleClass('hidden');
        });

        // Toggle Like Comment Action
        $(document).on('click', '#page-comments-list .comment-like-action', function() {
            @guest
                if (typeof window.openGuestModal === 'function') {
                    window.openGuestModal();
                }
                return;
            @endguest

            const btn = $(this);
            const card = btn.closest('.comment-card');
            const commentId = card.attr('data-comment-id');
            const comment = pageComments.find(c => c.id == commentId);

            if (!comment) return;

            const isLiked = btn.attr('data-active') === 'true';
            const newReactionType = isLiked ? 'remove' : 'like';

            btn.prop('disabled', true);

            $.ajax({
                url: `/comments/${commentId}/react`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    reaction_type: newReactionType
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        btn.attr('data-active', (!isLiked).toString());
                        comment.user_liked = !isLiked;
                        comment.reaction_count = response.reaction_count;

                        // Update UI
                        if (!isLiked) {
                            btn.addClass('text-primary bg-primary/10');
                            btn.find('.material-symbols-outlined').addClass('fill-1');
                        } else {
                            btn.removeClass('text-primary bg-primary/10');
                            btn.find('.material-symbols-outlined').removeClass('fill-1');
                        }
                        card.find('.comment-likers-trigger .like-count').text(response.reaction_count);
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('{{ __t("comment_like_failed") }}');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });

        // Show Comment Likers popup list
        $(document).on('click', '#page-comments-list .comment-likers-trigger', function() {
            const commentId = $(this).closest('.comment-card').attr('data-comment-id');
            if (typeof showCommentLikers === 'function') {
                showCommentLikers(commentId, '{{ __t("comment_word") }}');
            }
        });

        // Toggle Like Reply Action
        $(document).on('click', '#page-comments-list .reply-like-action', function() {
            @guest
                if (typeof window.openGuestModal === 'function') {
                    window.openGuestModal();
                }
                return;
            @endguest

            const btn = $(this);
            const card = btn.closest('.comment-card');
            const replyCard = btn.closest('.reply-card');
            const commentId = card.attr('data-comment-id');
            const replyId = replyCard.attr('data-reply-id');
            const comment = pageComments.find(c => c.id == commentId);

            if (!comment || !comment.replies) return;
            const reply = comment.replies.find(r => r.id == replyId);
            if (!reply) return;

            const isLiked = btn.attr('data-active') === 'true';
            const newReactionType = isLiked ? 'remove' : 'like';

            btn.prop('disabled', true);

            $.ajax({
                url: `/comments/${replyId}/react`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    reaction_type: newReactionType
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        btn.attr('data-active', (!isLiked).toString());
                        reply.user_liked = !isLiked;
                        reply.reaction_count = response.reaction_count;

                        // Update UI
                        if (!isLiked) {
                            btn.addClass('text-primary bg-primary/10');
                            btn.find('.material-symbols-outlined').addClass('fill-1');
                        } else {
                            btn.removeClass('text-primary bg-primary/10');
                            btn.find('.material-symbols-outlined').removeClass('fill-1');
                        }
                        replyCard.find('.reply-likers-trigger .like-count').text(response.reaction_count);
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('{{ __t("reply_like_failed") }}');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        });

        // Show Reply Likers popup list
        $(document).on('click', '#page-comments-list .reply-likers-trigger', function() {
            const replyId = $(this).closest('.reply-card').attr('data-reply-id');
            if (typeof showCommentLikers === 'function') {
                showCommentLikers(replyId, '{{ __t("reply_word") }}');
            }
        });
    });
</script>
@endpush
