@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24" style="direction: {{ $dir }};">
    
    <!-- Top Search Header -->
    <div class="mb-8 bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm {{ $textAlign }}" style="direction: {{ $dir }};">
        <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">{{ __t('search_results') }}</h1>
        <p class="font-body-md text-xs text-on-surface-variant mt-1 mb-4">{{ __t('search_results_desc') }}</p>
        
        <form method="GET" action="{{ route('frontend.search') }}" class="relative w-full max-w-2xl" id="search-form">
            @if(request()->has('types'))
                @foreach(request()->get('types') as $type)
                    <input type="hidden" name="types[]" value="{{ $type }}" class="search-type-input">
                @endforeach
            @endif
            <input id="search-input" class="w-full bg-surface py-3 {{ $dir === 'rtl' ? 'pl-5 pr-12' : 'pr-5 pl-12' }} rounded-full border border-primary/10 focus:border-primary focus:ring-1 focus:ring-primary outline-none font-body-md text-sm text-on-surface placeholder:text-outline {{ $textAlign }}" dir="{{ $dir }}" type="text" name="query" value="{{ $query }}" placeholder="{{ __t('search_input_placeholder') }}" autocomplete="off">
            <button type="submit" class="absolute {{ $dir === 'rtl' ? 'right-4' : 'left-4' }} top-3.5 text-on-surface-variant hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-[24px]">search</span>
            </button>
        </form>
    </div>

    <!-- Results Wrapper with relative positioning for loading overlay -->
    <div class="relative min-h-[300px]">
        <!-- Strong loading indicator -->
        <div id="search-loading-overlay" class="absolute inset-0 bg-white/75 backdrop-blur-[2px] flex items-center justify-center hidden z-50 rounded-2xl transition-all duration-300">
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
                <p class="text-xs font-bold text-primary">{{ __t('searching_results_msg') }}</p>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" id="search-main-grid">
            <!-- Meta Data for lazyloading -->
            <div id="search-grid-meta" data-has-more="{{ ($hasMore ?? false) ? 'true' : 'false' }}" data-current-page="{{ $page ?? 1 }}" class="hidden"></div>
        
        <!-- Right Column: Search Results Feed (RTL: right side on Desktop) -->
        <section class="lg:col-span-9 order-2 lg:order-1 space-y-8 {{ $textAlign }}">
            
            <!-- Search Category Tabs -->
            <div class="flex gap-6 border-b border-primary/5 mb-6 overflow-x-auto pb-2 justify-start scrollbar-none">
                <a href="{{ route('frontend.search', ['query' => $query]) }}" class="font-title-lg text-xs font-bold pb-2 px-1 transition-all whitespace-nowrap {{ count($types) === 3 ? 'text-secondary border-b-2 border-secondary' : 'text-on-surface-variant hover:text-primary' }}">{{ __t('search_tab_all') }}</a>
                <a href="{{ route('frontend.search', ['query' => $query, 'types' => ['users']]) }}" class="font-title-lg text-xs font-bold pb-2 px-1 transition-all whitespace-nowrap {{ count($types) === 1 && in_array('users', $types) ? 'text-secondary border-b-2 border-secondary' : 'text-on-surface-variant hover:text-primary' }}">{{ __t('search_tab_people') }}</a>
                <a href="{{ route('frontend.search', ['query' => $query, 'types' => ['posts']]) }}" class="font-title-lg text-xs font-bold pb-2 px-1 transition-all whitespace-nowrap {{ count($types) === 1 && in_array('posts', $types) ? 'text-secondary border-b-2 border-secondary' : 'text-on-surface-variant hover:text-primary' }}">{{ __t('search_tab_topics') }}</a>
                <a href="{{ route('frontend.search', ['query' => $query, 'types' => ['groups']]) }}" class="font-title-lg text-xs font-bold pb-2 px-1 transition-all whitespace-nowrap {{ count($types) === 1 && in_array('groups', $types) ? 'text-secondary border-b-2 border-secondary' : 'text-on-surface-variant hover:text-primary' }}">{{ __t('search_tab_groups') }}</a>
            </div>

            @if(empty($query))
                <div class="bg-white rounded-2xl p-12 border border-primary/10 text-center">
                    <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">search</span>
                    <h3 class="font-headline-lg text-base font-bold text-primary">{{ __t('start_search_title') }}</h3>
                    <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">{{ __t('start_search_desc') }}</p>
                </div>
            @elseif($users->isEmpty() && $posts->isEmpty() && $groups->isEmpty() && $groupSites->isEmpty())
                <div class="bg-white rounded-2xl p-12 border border-primary/10 text-center">
                    <span class="material-symbols-outlined text-[64px] text-error opacity-40 mb-3">search_off</span>
                    <h3 class="font-headline-lg text-base font-bold text-primary">{{ __t('no_search_results_title') }}</h3>
                    <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">{{ __t('no_search_results_desc_prefix') }}{{ $query }}{{ __t('no_search_results_desc_suffix') }}</p>
                </div>
            @else

                <!-- Section: People (الأشخاص) -->
                @if(in_array('users', $types) && $users->count() > 0)
                <section class="space-y-4">
                    <h2 class="font-headline-lg text-base font-bold text-primary flex items-center gap-2 {{ $dir === 'rtl' ? 'flex-row' : 'flex-row-reverse justify-end' }}">
                        <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                        <span>{{ __t('people_label') }}</span>
                    </h2>
                    
                    <div id="search-people-list" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($users as $user)
                            @php
                                $userPhoto = (!empty($user->profile_picture) && $user->profile_picture != 'non') 
                                    ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : asset('new_wiselook/uploads/'.$user->profile_picture)) 
                                    : asset('upload/no_image.jpg');
                            @endphp
                            <div class="user-card-item bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex items-start gap-4 hover:shadow-md transition-shadow cursor-pointer animate-fade-in {{ $textAlign }}" style="direction: {{ $dir }};" data-url="{{ route('profile.edit', $user->id) }}">
                                <a href="{{ route('profile.edit', $user->id) }}" class="shrink-0">
                                    <img class="w-14 h-14 rounded-full object-cover border border-primary/10 hover:opacity-90 transition-opacity" src="{{ $userPhoto }}" alt="{{ $user->first_name }}">
                                </a>
                                <div class="flex-grow min-w-0">
                                    <div class="flex justify-between items-start">
                                        <div class="min-w-0">
                                            <a href="{{ route('profile.edit', $user->id) }}" class="hover:underline hover:text-primary transition-all">
                                                <h3 class="font-title-lg text-xs font-bold text-primary truncate">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                            </a>
                                            <p class="font-body-md text-[10px] text-on-surface-variant flex items-center gap-1 mt-0.5">
                                                <span>{{ $user->role == 'admin' ? __t('platform_admin') : __t('member_role') }}</span>
                                                @if($user->points)
                                                    <span class="material-symbols-outlined text-secondary text-[14px]">workspace_premium</span>
                                                    <span class="text-secondary font-bold">{{ $user->points }} {{ __t('point_unit') }}</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <p class="font-body-md text-on-surface mt-2 text-[11px] leading-relaxed">{{ __t('joined_date_prefix', ['date' => $user->created_at ? $user->created_at->format('Y-m-d') : '']) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Section: Topics (المواضيع) -->
                @if(in_array('posts', $types) && $posts->count() > 0)
                <section class="space-y-4">
                    <h2 class="font-headline-lg text-base font-bold text-primary flex items-center gap-2 {{ $dir === 'rtl' ? 'flex-row' : 'flex-row-reverse justify-end' }}">
                        <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                        <span>{{ __t('topics_label') }}</span>
                    </h2>
                    
                    <div id="search-topics-list" class="space-y-4">
                        @foreach($posts as $post)
                            @php
                                $postOwnerPhoto = (!empty($post->user->profile_picture) && $post->user->profile_picture != 'non') 
                                    ? (filter_var($post->user->profile_picture, FILTER_VALIDATE_URL) ? $post->user->profile_picture : asset('new_wiselook/uploads/'.$post->user->profile_picture)) 
                                    : asset('upload/no_image.jpg');
                                $ownerName = $post->user ? ($post->user->first_name . ' ' . $post->user->last_name) : __t('unknown_user');
                                
                                $userLikedPost = false;
                                if (Auth::check()) {
                                    $userLikedPost = \App\Models\Reaction::where('user_id', Auth::id())
                                        ->where('content_id', $post->id)
                                        ->where('content_type_id', 1)
                                        ->where('is_active', 1)
                                        ->exists();
                                }
                            @endphp
                            <div class="search-post-item bg-white rounded-2xl p-6 border border-primary/10 shadow-sm hover:shadow-md transition-shadow {{ $textAlign }}" style="direction: {{ $dir }};">
                                <div class="flex items-center gap-3 mb-3 border-b border-primary/5 pb-3">
                                    <img src="{{ $postOwnerPhoto }}" alt="owner" class="rounded-circle w-9 h-9 object-cover border border-primary/10">
                                    <div>
                                        <h4 class="font-title-lg text-xs font-bold text-primary">{{ $ownerName }}</h4>
                                        <small class="text-on-surface-variant text-[10px]">{{ $post->created_at ? $post->created_at->diffForHumans() : '' }}</small>
                                    </div>
                                </div>
                                <div class="post-text-container mb-4">
                                    <div class="post-text-content line-clamp-4 overflow-hidden font-body-md text-sm text-on-surface leading-[1.8] {{ $textAlign }}">
                                        {!! nl2br(\App\Models\Post::formatContent($post->content)) !!}
                                    </div>
                                    <button class="show-more-btn hidden text-primary font-bold text-xs mt-2 hover:underline focus:outline-none bg-transparent border-0 cursor-pointer block">{{ __t('show_more') }}</button>
                                </div>
                                
                                @if($post->image)
                                    <div class="post-image-trigger w-full h-48 rounded-xl overflow-hidden mb-4 relative bg-surface-container-high border border-outline-variant cursor-pointer hover:opacity-95 transition-opacity" style="background-image: url('{{ asset('new_wiselook/uploads/' . $post->image) }}'); background-size: cover; background-position: center;" data-image-src="{{ asset('new_wiselook/uploads/' . $post->image) }}"></div>
                                @endif

                                @if($post->video)
                                    <div class="video-preview-trigger relative w-full h-48 rounded-xl overflow-hidden mb-4 bg-black cursor-pointer group/video-card border border-outline-variant" data-video-src="{{ asset('new_wiselook/uploads/' . $post->video) }}">
                                        <video class="w-full h-full object-cover opacity-75 transition-transform duration-500 group-hover/video-card:scale-103" preload="metadata">
                                            <source src="{{ asset('new_wiselook/uploads/' . $post->video) }}#t=0.5" type="video/mp4">
                                        </video>
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-black/10 to-transparent"></div>
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-white transition-all duration-300 shadow-lg group-hover/video-card:scale-110 group-hover/video-card:bg-primary group-hover/video-card:border-primary/50 group-hover/video-card:rotate-6">
                                                <span class="material-symbols-outlined text-[28px]" style="font-variation-settings:'FILL' 1;">play_arrow</span>
                                            </div>
                                        </div>
                                        <div class="absolute bottom-3 left-3 bg-black/60 px-2 py-1 rounded text-[10px] text-white/90 font-bold backdrop-blur-sm">
                                            {{ __t('video_label') }}
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between text-on-surface-variant pt-3 border-t border-primary/5">
                                    <div class="flex items-center space-x-6 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }}">
                                        <div class="flex items-center space-x-1 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }}">
                                            <button class="post-support-action flex items-center justify-center w-8 h-8 rounded-full hover:bg-primary/10 hover:text-primary transition-all {{ $userLikedPost ? 'text-primary bg-primary/10' : '' }}" data-post-id="{{ $post->id }}" data-active="{{ $userLikedPost ? 'true' : 'false' }}" title="{{ __t('support_action') }}">
                                                <span class="material-symbols-outlined text-[20px] {{ $userLikedPost ? 'fill-1' : '' }}">lightbulb</span>
                                            </button>
                                            <button class="open-supporters-btn text-xs font-semibold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0" data-post-id="{{ $post->id }}" data-total-supports="{{ $post->like_count ?? 0 }}" title="{{ __t('show_supporters') }}">
                                                <span class="support-count">{{ $post->like_count ?? 0 }}</span> {{ __t('support_action') }}
                                            </button>
                                        </div>
                                        <button class="open-discussion-btn flex items-center space-x-2 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }} hover:text-primary transition-colors group"
                                                data-post-id="{{ $post->id }}"
                                                data-author-name="{{ $ownerName }}"
                                                data-author-avatar="{{ $postOwnerPhoto }}"
                                                data-post-title="{{ $ownerName }}"
                                                data-post-snippet="{{ Str::limit($post->content, 120) }}">
                                            <span class="material-symbols-outlined group-hover:scale-110 transition-transform">forum</span>
                                            <span class="font-label-sm text-label-sm">{{ $post->comment_count ?? 0 }} {{ __t('discussion_label') }}</span>
                                        </button>
                                    </div>
                                    @php
                                        $isSaved = false;
                                        if (Auth::check()) {
                                            $isSaved = \App\Models\SavedPost::where('user_id', Auth::id())->where('post_id', $post->id)->exists();
                                        }
                                    @endphp
                                    <div class="flex items-center space-x-4 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }}">
                                        <button class="toggle-save-post-btn flex items-center space-x-2 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }} hover:text-primary transition-colors {{ $isSaved ? 'text-primary' : '' }} cursor-pointer" data-post-id="{{ $post->id }}" data-saved="{{ $isSaved ? 'true' : 'false' }}">
                                            <span class="material-symbols-outlined {{ $isSaved ? 'fill-1' : '' }}">{{ $isSaved ? 'bookmark' : 'bookmark_border' }}</span>
                                            <span class="font-label-sm text-label-sm save-text">{{ $isSaved ? __t('saved_status') : __t('save_action') }}</span>
                                        </button>
                                        <button class="share-post-btn flex items-center space-x-2 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }} hover:text-primary transition-colors cursor-pointer" data-post-id="{{ $post->id }}" data-post-content="{{ rawurlencode($post->content) }}">
                                            <span class="material-symbols-outlined">share</span>
                                            <span class="font-label-sm text-label-sm">{{ __t('share_post') }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Section: Groups (المجموعات) -->
                @if(in_array('groups', $types) && ($groups->count() + $groupSites->count()) > 0)
                <section class="space-y-4">
                    <h2 class="font-headline-lg text-base font-bold text-primary flex items-center gap-2 {{ $dir === 'rtl' ? 'flex-row' : 'flex-row-reverse justify-end' }}">
                        <span class="w-1.5 h-6 bg-primary rounded-full"></span>
                        <span>{{ __t('groups_label') }}</span>
                    </h2>
                    
                    <div id="search-groups-list" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Standard Groups -->
                        @foreach($groups as $group)
                            @php
                                $groupPhoto = (!empty($group->image) && $group->image != 'non') 
                                    ? asset('new_wiselook/uploads/'.basename($group->image)) 
                                    : asset('upload/no_image.jpg');
                            @endphp
                            <div class="search-group-item bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow {{ $textAlign }}" style="direction: {{ $dir }};">
                                <div class="flex items-center gap-4 min-w-0 {{ $textAlign }}">
                                     <img src="{{ $groupPhoto }}" alt="group" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-primary/10">
                                    <div class="truncate">
                                        <h3 class="font-title-lg text-xs font-bold text-primary truncate">{{ $group->name }}</h3>
                                        <p class="font-body-md text-[10px] text-on-surface-variant mt-0.5">{{ trans_choice('member_unit', $group->member_count ?? 0, ['count' => $group->member_count ?? 0]) }}</p>
                                    </div>
                                </div>
                                <span class="bg-warning/10 text-warning rounded-full px-2.5 py-1 text-[9px] font-bold">{{ __t('public_label') }}</span>
                            </div>
                        @endforeach

                        <!-- Group Sites -->
                        @foreach($groupSites as $gs)
                            @php
                                $gsPhoto = (!empty($gs->image_path) && $gs->image_path != 'non') 
                                    ? asset('new_wiselook/uploads/'.basename($gs->image_path)) 
                                    : asset('upload/no_image.jpg');
                            @endphp
                            <div class="search-group-item bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow {{ $textAlign }}" style="direction: {{ $dir }};">
                                <div class="flex items-center gap-4 min-w-0 {{ $textAlign }}">
                                    <img src="{{ $gsPhoto }}" alt="group site" class="w-12 h-12 rounded-xl object-cover shrink-0 border border-primary/10">
                                    <div class="truncate">
                                        <h3 class="font-title-lg text-xs font-bold text-primary truncate">{{ $gs->title }}</h3>
                                        <p class="font-body-md text-[10px] text-on-surface-variant mt-0.5">{{ $gs->status == 1 ? __t('public_group') : __t('private_group') }}</p>
                                    </div>
                                </div>
                                <span class="bg-primary/10 text-primary rounded-full px-2.5 py-1 text-[9px] font-bold">{{ $gs->status == 1 ? __t('public_label') : __t('private_label') }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

            @endif
        </section>

        <!-- Left Column: Sidebar / Filter Options (RTL: left side on Desktop) -->
        <aside class="lg:col-span-3 order-1 lg:order-2 space-y-6 lg:sticky lg:top-24 self-start {{ $textAlign }}">
            <!-- Filter Options Card -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm" style="direction: {{ $dir }};">
                <div class="flex flex-col gap-2 mb-4 pb-4 border-b border-primary/5">
                    <h2 class="font-title-lg text-sm font-bold text-primary">{{ __t('filter_results') }}</h2>
                    <p class="font-body-md text-[10px] text-on-surface-variant">{{ __t('filter_results_desc') }}</p>
                </div>
                
                <!-- Filter Type Links -->
                <nav class="flex flex-col gap-1 text-xs font-semibold">
                    <a class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 transition-all {{ count($types) === 1 && in_array('users', $types) ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/5' }}" href="{{ route('frontend.search', ['query' => $query, 'types' => ['users']]) }}">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">person_search</span>
                            <span>{{ __t('people_nav') }}</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">{{ $dir === 'rtl' ? 'chevron_left' : 'chevron_right' }}</span>
                    </a>
                    <a class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 transition-all {{ count($types) === 1 && in_array('posts', $types) ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/5' }}" href="{{ route('frontend.search', ['query' => $query, 'types' => ['posts']]) }}">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">topic</span>
                            <span>{{ __t('topics_label') }}</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">{{ $dir === 'rtl' ? 'chevron_left' : 'chevron_right' }}</span>
                    </a>
                    <a class="flex items-center justify-between gap-2 rounded-xl px-3 py-2.5 transition-all {{ count($types) === 1 && in_array('groups', $types) ? 'bg-primary text-white' : 'text-on-surface-variant hover:bg-primary/5' }}" href="{{ route('frontend.search', ['query' => $query, 'types' => ['groups']]) }}">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">groups</span>
                            <span>{{ __t('groups_label') }}</span>
                        </span>
                        <span class="material-symbols-outlined text-[16px]">{{ $dir === 'rtl' ? 'chevron_left' : 'chevron_right' }}</span>
                    </a>
                </nav>
            </div>
        </aside>

    </div>
    <!-- Scroll Loader -->
    <div id="search-scroll-loader" class="hidden w-full text-center py-6 col-span-full">
        <div class="inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let debounceTimer;
    const searchForm = $('#search-form');
    const searchInput = $('#search-input');
    const mainGrid = $('#search-main-grid');
    const loadingOverlay = $('#search-loading-overlay');

    let currentPage = 1;
    let hasMore = false;
    let gridLoading = false;

    // Translation values for JS
    const _tp = {
        searchingResultsMsg: {!! json_encode(__t('searching_results_msg')) !!},
        showMore: {!! json_encode(__t('show_more')) !!}
    };

    function updateMetaVars() {
        const meta = $('#search-grid-meta');
        if (meta.length) {
            hasMore = meta.attr('data-has-more') === 'true';
            currentPage = parseInt(meta.attr('data-current-page')) || 1;
        } else {
            hasMore = false;
            currentPage = 1;
        }
    }

    updateMetaVars();

    // Helper to highlight searched term in text nodes without breaking Arabic cursive rendering
    function highlightQueryText(query) {
        if (!query || query.trim() === '') return;
        
        // Remove existing marks
        mainGrid.find('mark').each(function() {
            const parent = this.parentNode;
            if (parent) {
                parent.replaceChild(document.createTextNode(this.textContent), this);
                parent.normalize();
            }
        });

        const cleanQuery = query.trim().replace(/\s+/g, ' ');
        const escapedQuery = cleanQuery.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        
        // Build regex variations for Arabic characters compatibility (ى/ي, ة/ه)
        const variations = [escapedQuery];
        if (escapedQuery.includes('ى') || escapedQuery.includes('ي')) {
            variations.push(escapedQuery.replace(/ى/g, 'ي').replace(/ي/g, 'ى'));
        }
        if (escapedQuery.includes('ة') || escapedQuery.includes('ه')) {
            variations.push(escapedQuery.replace(/ة/g, 'ه').replace(/ه/g, 'ة'));
        }
        
        // Match whole word containing the query variations to prevent Arabic cursive text fragmentation
        const pattern = '([^\\s\\,\\.\\!\\?\\-\\(\\)\\[\\]\\{\\}]*(?:' + variations.join('|') + ')[^\\s\\,\\.\\!\\?\\-\\(\\)\\[\\]\\{\\}]*)';
        const regex = new RegExp(pattern, 'gi');

        const elementsToHighlight = mainGrid.find('h3, p, span, small');
        
        elementsToHighlight.each(function() {
            const $el = $(this);
            if ($el.closest('button, svg, .material-symbols-outlined, video, audio, script, style').length > 0) return;
            
            $el.contents().filter(function() {
                return this.nodeType === 3; // text node
            }).each(function() {
                const text = this.nodeValue;
                if (regex.test(text)) {
                    const span = document.createElement('span');
                    span.innerHTML = text.replace(regex, '<mark class="bg-amber-100 text-amber-950 px-1 rounded font-bold">$1</mark>');
                    this.parentNode.replaceChild(span, this);
                }
            });
        });
    }

    // Run highlight on initial load
    highlightQueryText(searchInput.val());

    function performLiveSearch() {
        const formData = searchForm.serialize();
        const url = searchForm.attr('action') + '?' + formData;

        // Update browser URL
        window.history.pushState({ path: url }, '', url);

        // Show loading state
        loadingOverlay.removeClass('hidden');
        mainGrid.css('opacity', '0.4');

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                // Parse the response and extract the grid contents
                const newGridHtml = $(response).find('#search-main-grid').html();
                mainGrid.html(newGridHtml);
                highlightQueryText(searchInput.val());
                updateMetaVars();
            },
            complete: function() {
                loadingOverlay.addClass('hidden');
                mainGrid.css('opacity', '1.0');
            }
        });
    }

    // Trigger on typing
    searchInput.on('keyup input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(performLiveSearch, 300);
    });

    // Also trigger on form submission
    searchForm.on('submit', function(e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        performLiveSearch();
    });

    // Handle AJAX pagination or tab filters clicks inside the main grid
    $(document).on('click', '#search-main-grid a', function(e) {
        const url = $(this).attr('href');
        // Only prevent default and perform AJAX if the link is a search tab or pagination
        if (!url || url === '#' || !url.includes('/search')) return;

        e.preventDefault();

        // Parse query parameters to update search input
        const urlObj = new URL(url, window.location.origin);
        const newQuery = urlObj.searchParams.get('query') || '';
        searchInput.val(newQuery);

        // Update hidden inputs if filter type changed
        searchForm.find('.search-type-input').remove();
        urlObj.searchParams.getAll('types[]').forEach(function(type) {
            searchForm.append('<input type="hidden" name="types[]" value="' + type + '" class="search-type-input">');
        });

        window.history.pushState({ path: url }, '', url);

        loadingOverlay.removeClass('hidden');
        mainGrid.css('opacity', '0.4');
        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                const newGridHtml = $(response).find('#search-main-grid').html();
                mainGrid.html(newGridHtml);
                highlightQueryText(searchInput.val());
                updateMetaVars();
            },
            complete: function() {
                loadingOverlay.addClass('hidden');
                mainGrid.css('opacity', '1.0');
            }
        });
    });

    // Make entire User Card clickable to go to user's profile
    $(document).on('click', '.user-card-item', function(e) {
        // Prevent click if clicking on an interactive element like buttons or links
        if (!$(e.target).closest('a, button, input, select, textarea').length) {
            window.location.href = $(this).data('url');
        }
    });

    // --- Infinite Scroll / Lazy Loading for Search Grid ---
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 250) {
            if (!gridLoading && hasMore) {
                loadMoreSearchResults();
            }
        }
    });

    function loadMoreSearchResults() {
        gridLoading = true;
        $('#search-scroll-loader').removeClass('hidden');

        const nextPage = currentPage + 1;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', nextPage);

        $.ajax({
            url: currentUrl.toString(),
            type: 'GET',
            success: function(response) {
                $('#search-scroll-loader').addClass('hidden');
                gridLoading = false;

                const responseHtml = $(response);

                // 1. People
                const newPeople = responseHtml.find('#search-people-list .user-card-item');
                if (newPeople.length) {
                    $('#search-people-list').append(newPeople);
                }

                // 2. Topics
                const newTopics = responseHtml.find('#search-topics-list .search-post-item');
                if (newTopics.length) {
                    $('#search-topics-list').append(newTopics);
                }

                // 3. Groups
                const newGroups = responseHtml.find('#search-groups-list .search-group-item');
                if (newGroups.length) {
                    $('#search-groups-list').append(newGroups);
                }

                currentPage = nextPage;

                // Read next hasMore status
                const meta = responseHtml.find('#search-grid-meta').length 
                    ? responseHtml.find('#search-grid-meta') 
                    : responseHtml.filter('#search-grid-meta');
                hasMore = meta.attr('data-has-more') === 'true';

                highlightQueryText(searchInput.val());
            },
            error: function() {
                $('#search-scroll-loader').addClass('hidden');
                gridLoading = false;
            }
        });
    }
});
</script>
@endpush
