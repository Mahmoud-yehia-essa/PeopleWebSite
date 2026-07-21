@foreach($posts as $post)
    @php
        $fullName = $post->user ? ($post->user->first_name . ' ' . $post->user->last_name) : 'مستخدم غير معروف';
        $avatarUrl = url('upload/no_image.jpg');
        if ($post->user && $post->user->profile_picture && $post->user->profile_picture !== 'non') {
            $avatarUrl = filter_var($post->user->profile_picture, FILTER_VALIDATE_URL)
                ? $post->user->profile_picture
                : asset('new_wiselook/uploads/' . $post->user->profile_picture);
        }
        $isPinned = $post->pin && $post->pin->pin_scope === 'home';
    @endphp
    <article class="relative overflow-hidden rounded-xl transition-all duration-300 {{ $isPinned ? 'bg-gradient-to-b from-secondary/5 via-surface-container-lowest/90 to-surface-container-lowest/90 border-2 border-secondary/30 shadow-[0_8px_30px_rgba(115,92,0,0.1)]' : 'bg-surface-container-lowest/70 backdrop-blur-[20px] border border-primary/10 shadow-[0_4px_20px_rgba(27,67,50,0.05)]' }}">
        @if($isPinned)
            <div class="bg-gradient-to-l from-secondary/15 via-secondary/5 to-transparent px-6 py-3 flex items-center justify-between border-b border-secondary/10">
                <div class="flex items-center gap-2 text-secondary font-bold">
                    <span class="material-symbols-outlined text-[20px] rotate-[30deg] animate-pulse" style="font-variation-settings: 'FILL' 1;">push_pin</span>
                    <span class="text-xs font-bold font-headline-lg-mobile">{{ __t('pinned_post') }}</span>
                </div>
                <span class="text-[10px] bg-secondary/10 text-secondary px-2.5 py-0.5 rounded-full font-bold">{{ __t('featured') }}</span>
            </div>
        @endif
        <div class="p-6">
            <!-- Post Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <div class="relative">
                        @if($post->user)
                            <a href="{{ route('profile.edit', $post->user->id) }}">
                                <img alt="{{ $fullName }}" class="w-14 h-14 rounded-full object-cover border-2 border-outline-variant hover:opacity-90 transition-opacity" src="{{ $avatarUrl }}"/>
                            </a>
                        @else
                            <img alt="{{ $fullName }}" class="w-14 h-14 rounded-full object-cover border-2 border-outline-variant" src="{{ $avatarUrl }}"/>
                        @endif
                        @if($post->user && $post->user->role == 'admin')
                            <span class="absolute -bottom-1 -right-1 bg-secondary-container text-on-secondary-container rounded-full p-1 border-2 border-surface-container-lowest flex items-center justify-center">
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
                        <div class="flex items-center text-on-surface-variant font-label-sm text-label-sm space-x-2 space-x-reverse" style="margin-top: 8px;">
                            @if($post->user && $post->user->rank)
                                @php
                                    $rankPhoto = $post->user->rank->photo;
                                    $rankPhotoPath = url('upload/no_image.jpg');
                                    if (!empty($rankPhoto) && file_exists(public_path('upload/rankings/' . $rankPhoto))) {
                                        $rankPhotoPath = asset('upload/rankings/' . $rankPhoto);
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1.5" style="display: inline-flex; align-items: center; gap: 6px; vertical-align: middle;">
                                    <img src="{{ $rankPhotoPath }}" alt="{{ __t($post->user->rank->rank_name) }}" style="width: 20px; height: 20px; object-fit: contain; margin-left: 2px;">
                                    <span class="font-bold text-xs" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ __t($post->user->rank->rank_name) }}</span>
                                </span>
                            @else
                                <span>{{ $post->user && $post->user->role == 'admin' ? 'مدير المنصة' : 'مستشار تقني' }}</span>
                            @endif
                        </div>
                        @if($post->user)
                            <div class="mt-1.5 flex items-center justify-start">
                                <button type="button" class="user-points-trigger inline-flex items-center gap-1 text-[10px] font-extrabold text-secondary hover:text-primary transition-colors cursor-pointer bg-secondary/5 hover:bg-secondary/10 px-2 py-0.5 rounded-full border border-secondary/10" data-user-id="{{ $post->user->id }}">
                                    <span class="material-symbols-outlined text-[12px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                    <span>النقاط: {{ $post->user->points ?? 0 }}</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($post->wise_rating)
                        @php
                            $feedWiseRating = floatval($post->wise_rating);
                            $feedRatingBg = $feedWiseRating >= 8 ? 'rgba(22,163,74,0.08)' : ($feedWiseRating >= 6 ? 'rgba(212,175,55,0.08)' : ($feedWiseRating >= 4 ? 'rgba(234,88,12,0.08)' : 'rgba(220,38,38,0.08)'));
                            $feedRatingColor = $feedWiseRating >= 8 ? '#16a34a' : ($feedWiseRating >= 6 ? '#b8922a' : ($feedWiseRating >= 4 ? '#ea580c' : '#dc2626'));
                            $feedRatingBorder = $feedWiseRating >= 8 ? 'rgba(22,163,74,0.2)' : ($feedWiseRating >= 6 ? 'rgba(212,175,55,0.2)' : ($feedWiseRating >= 4 ? 'rgba(234,88,12,0.2)' : 'rgba(220,38,38,0.2)'));
                        @endphp
                        <a href="{{ route('frontend.posts.show', $post->id) }}#wise-rating-section"
                           class="wise-rating-badge inline-flex items-center gap-1 font-bold transition-all hover:scale-105"
                           title="تقييم لجنة الحكماء: {{ number_format($feedWiseRating, 1) }} / 10"
                           style="
                               background: {{ $feedRatingBg }};
                               border: 1px solid {{ $feedRatingBorder }};
                               color: {{ $feedRatingColor }};
                               padding: 4px 9px; border-radius: 20px;
                               font-size: 11px; white-space: nowrap;
                               text-decoration: none;
                           ">
                            <span class="material-symbols-outlined" style="font-size:13px; font-variation-settings:'FILL' 1;">gavel</span>
                            {{ number_format($feedWiseRating, 1) }}<span style="opacity:0.6; font-size:9px;">/10</span>
                        </a>
                    @endif
                    @if(Auth::check() && $post->user_id === Auth::id())
                        <button type="button" class="delete-post-btn text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded-full transition-all shrink-0 cursor-pointer flex items-center justify-center" data-post-id="{{ $post->id }}" title="{{ __t('delete_post') }}">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Post Content -->
            <div class="space-y-4">
                <div class="post-text-container">
                    <div class="post-text-content line-clamp-4 overflow-hidden font-body-lg text-body-lg text-on-surface leading-[1.8]">
                        {!! \App\Models\Post::formatContent($post->content) !!}
                    </div>
                    <button class="show-more-btn hidden text-primary font-bold text-xs mt-2 hover:underline focus:outline-none bg-transparent border-0 cursor-pointer block">{{ __t('show_more') }}</button>
                </div>
                
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
                                    <div class="progress-bar absolute inset-y-0 right-0 transition-all duration-500 {{ $isUserVoted ? 'bg-primary/10' : 'bg-primary/5' }}" style="width: {{ $percent }}%"></div>
                                    <span class="relative z-10 font-body-md text-xs font-semibold text-on-surface select-none pr-1 option-text">
                                        {{ $option->content }}
                                    </span>
                                    <span class="relative z-10 font-label-sm text-xs font-bold text-primary select-none pl-1 option-percent">
                                        {{ $percent }}% ({{ $votes }})
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-[10px] text-on-surface-variant font-medium pt-1 text-left flex justify-between">
                            <span class="total-votes-count">{{ __t('total_votes') }}: {{ $totalVotes }}</span>
                            <span>* {{ __t('poll_label') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Post Time at the end of the card -->
            <div class="mt-4 text-left px-6">
                <span class="text-on-surface-variant/75 text-[11px] font-semibold bg-surface-variant/40 px-2.5 py-1 rounded-full inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]" style="font-variation-settings:'wght' 300;">schedule</span>
                    {{ $post->created_at->diffForHumans() }}
                </span>
            </div>

            <!-- Post Actions -->
            <div class="mt-6 pt-4 border-t border-surface-variant flex items-center justify-between text-on-surface-variant">
                <div class="flex space-x-6 space-x-reverse">
                    <div class="flex items-center space-x-1 space-x-reverse">
                        @php
                            $userLikedPost = false;
                            if (Auth::check()) {
                                $userLikedPost = \App\Models\Reaction::where('user_id', Auth::id())
                                    ->where('content_id', $post->id)
                                    ->where('content_type_id', 1) // 1 للمنشور
                                    ->where('is_active', 1)
                                    ->exists();
                            }
                        @endphp
                        <button class="post-support-action flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 hover:text-primary transition-all {{ $userLikedPost ? 'text-primary bg-primary/10' : '' }}" data-post-id="{{ $post->id }}" data-active="{{ $userLikedPost ? 'true' : 'false' }}" title="تأييد">
                            <span class="material-symbols-outlined text-[20px] {{ $userLikedPost ? 'fill-1' : '' }}">lightbulb</span>
                        </button>
                        <button class="open-supporters-btn text-xs font-semibold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0" data-post-id="{{ $post->id }}" data-total-supports="{{ $post->like_count ?? 0 }}" title="عرض المؤيدين">
                            <span class="support-count">{{ $post->like_count ?? 0 }}</span> {{ __t('support') }}
                        </button>
                    </div>
                    <button class="open-discussion-btn flex items-center space-x-2 space-x-reverse hover:text-primary transition-colors group"
                            data-post-id="{{ $post->id }}"
                            data-author-name="{{ $fullName }}"
                            data-author-avatar="{{ $avatarUrl }}"
                            data-post-title="{{ $fullName }}"
                            data-post-snippet="{{ Str::limit($post->content, 120) }}">
                        <span class="material-symbols-outlined group-hover:scale-110 transition-transform">forum</span>
                        <span class="font-label-sm text-label-sm">{{ $post->comment_count ?? 0 }} {{ __t('comments') }}</span>
                    </button>
                </div>
                @php
                    $isSaved = false;
                    if (Auth::check()) {
                        $isSaved = \App\Models\SavedPost::where('user_id', Auth::id())->where('post_id', $post->id)->exists();
                    }
                @endphp
                <div class="flex items-center space-x-4 space-x-reverse">
                    <button class="toggle-save-post-btn flex items-center space-x-2 space-x-reverse hover:text-primary transition-colors {{ $isSaved ? 'text-primary' : '' }} cursor-pointer" data-post-id="{{ $post->id }}" data-saved="{{ $isSaved ? 'true' : 'false' }}">
                        <span class="material-symbols-outlined {{ $isSaved ? 'fill-1' : '' }}">{{ $isSaved ? 'bookmark' : 'bookmark_border' }}</span>
                        <span class="font-label-sm text-label-sm save-text">{{ $isSaved ? __t('saved') : __t('save') }}</span>
                    </button>
                    <button class="share-post-btn flex items-center space-x-2 space-x-reverse hover:text-primary transition-colors cursor-pointer" data-post-id="{{ $post->id }}" data-post-content="{{ urlencode($post->content) }}">
                        <span class="material-symbols-outlined">share</span>
                        <span class="font-label-sm text-label-sm">{{ __t('share_post') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </article>
@endforeach
