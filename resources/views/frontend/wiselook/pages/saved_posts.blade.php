@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24 {{ $textAlign }}" style="direction: {{ $dir }};">
    
    <!-- Top Header -->
    <div class="mb-8 bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm {{ $textAlign }}">
        <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary flex items-center gap-2">
            <span class="material-symbols-outlined text-[28px] text-secondary">bookmark</span>
            <span>{{ __t('saved_posts') }}</span>
        </h1>
        <p class="font-body-md text-xs text-on-surface-variant mt-1">{{ __t('saved_posts_sub') }}</p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Right Column: Saved Topics Feed (RTL: right side on Desktop) -->
        <section class="lg:col-span-9 order-2 lg:order-1 space-y-6 {{ $textAlign }}">
            @if($posts->isEmpty())
                <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-12 text-center text-on-surface-variant shadow-sm w-full">
                    <span class="material-symbols-outlined text-5xl text-primary mb-3">bookmark_border</span>
                    <h3 class="font-headline-lg text-base font-bold text-primary">{{ __t('empty_saved_list') }}</h3>
                    <p class="font-body-md text-xs text-on-surface-variant mt-2">{{ __t('empty_saved_list_desc') }}</p>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($posts as $post)
                        @php
                            $fullName = $post->user ? ($post->user->first_name . ' ' . $post->user->last_name) : __t('unknown_user');
                            $avatarUrl = url('upload/no_image.jpg');
                            if ($post->user && $post->user->profile_picture && $post->user->profile_picture !== 'non') {
                                $avatarUrl = filter_var($post->user->profile_picture, FILTER_VALIDATE_URL)
                                    ? $post->user->profile_picture
                                    : asset('new_wiselook/uploads/' . $post->user->profile_picture);
                            }
                            
                            $userLikedPost = false;
                            if (Auth::check()) {
                                $userLikedPost = \App\Models\Reaction::where('user_id', Auth::id())
                                    ->where('content_id', $post->id)
                                    ->where('content_type_id', 1)
                                    ->where('is_active', 1)
                                    ->exists();
                            }
                        @endphp
                        <article class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm ambient-shadow-low hover:ambient-shadow-mid transition-all duration-300">
                            <!-- Post Header -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-4">
                                    <img alt="{{ $fullName }}" class="w-12 h-12 rounded-full object-cover border border-outline-variant" src="{{ $avatarUrl }}">
                                    <div>
                                        <h4 class="font-title-lg text-sm font-bold text-on-surface">{{ $fullName }}</h4>
                                        <span class="font-label-sm text-xs text-on-surface-variant">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if(Auth::check() && $post->user_id === Auth::id())
                                    <button type="button" class="delete-post-btn text-on-surface-variant hover:text-error hover:bg-error/10 p-1.5 rounded-full transition-all shrink-0 cursor-pointer flex items-center justify-center" data-post-id="{{ $post->id }}" title="{{ __t('delete_post') }}">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Post Content -->
                            <div class="post-text-container mb-4">
                                <div class="post-text-content line-clamp-4 overflow-hidden font-body-md text-base text-on-surface leading-[1.8]">
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
                                    
                                    <div class="text-[10px] text-on-surface-variant font-medium pt-1 text-left flex justify-between">
                                        <span class="total-votes-count">{{ __t('total_votes') }}: {{ $totalVotes }}</span>
                                        <span>* {{ __t('poll_label') }}</span>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Post Actions -->
                            <div class="mt-6 pt-4 border-t border-surface-variant flex items-center justify-between text-on-surface-variant">
                                <div class="flex gap-6">
                                    <div class="flex items-center gap-1">
                                        <button class="post-support-action flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 hover:text-primary transition-all {{ $userLikedPost ? 'text-primary bg-primary/10' : '' }}" data-post-id="{{ $post->id }}" data-active="{{ $userLikedPost ? 'true' : 'false' }}" title="{{ __t('support') }}">
                                            <span class="material-symbols-outlined text-[20px] {{ $userLikedPost ? 'fill-1' : '' }}">lightbulb</span>
                                        </button>
                                        <button class="open-supporters-btn text-xs font-semibold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0" data-post-id="{{ $post->id }}" data-total-supports="{{ $post->like_count ?? 0 }}" title="{{ __t('view_supporters') }}">
                                            <span class="support-count">{{ $post->like_count ?? 0 }}</span> {{ __t('support') }}
                                        </button>
                                    </div>
                                    <button class="open-discussion-btn flex items-center gap-2 hover:text-primary transition-colors group"
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
                                    $isSaved = true; // Since we are on saved posts list
                                @endphp
                                <div class="flex items-center gap-4">
                                    <button class="toggle-save-post-btn flex items-center gap-2 hover:text-primary transition-colors text-primary cursor-pointer" data-post-id="{{ $post->id }}" data-saved="true">
                                        <span class="material-symbols-outlined fill-1">bookmark</span>
                                        <span class="font-label-sm text-label-sm save-text">{{ __t('saved') }}</span>
                                    </button>
                                    <button class="share-post-btn flex items-center gap-2 hover:text-primary transition-colors cursor-pointer" data-post-id="{{ $post->id }}" data-post-content="{{ urlencode($post->content) }}">
                                        <span class="material-symbols-outlined">share</span>
                                        <span class="font-label-sm text-label-sm">{{ __t('share_post') }}</span>
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- Left Column: Sidebar details (RTL: left side on Desktop) -->
        <aside class="lg:col-span-3 order-1 lg:order-2 space-y-6 lg:sticky lg:top-24 self-start {{ $textAlign }}">
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm">
                <div class="flex flex-col gap-2 mb-4 pb-4 border-b border-primary/5">
                    <h2 class="font-title-lg text-sm font-bold text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-secondary">info</span>
                        <span>{{ __t('information') }}</span>
                    </h2>
                </div>
                <p class="font-body-md text-xs text-on-surface-variant leading-relaxed">{{ __t('saved_info_desc') }}</p>
            </div>
        </aside>

    </div>
</div>
@endsection
