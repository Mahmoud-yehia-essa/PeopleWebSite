@extends('frontend.wiselook.master_dashboard')

@section('title', 'قضايا ملحة ورائجة')

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
                <span class="material-symbols-outlined text-secondary text-sm">person_add_disabled</span>
            </div>
            <div class="space-y-4">
                @forelse($friendRequests as $req)
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
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center space-x-3 space-x-reverse">
                                <a href="{{ route('profile.edit', $req->sender->id) }}" class="shrink-0">
                                    <img alt="{{ $senderName }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="{{ $senderAvatar }}">
                                </a>
                                <div class="text-right">
                                    <a href="{{ route('profile.edit', $req->sender->id) }}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">{{ $senderName }}</a>
                                    <p class="text-[11px] text-on-surface-variant">{{ $req->sender->email ?? 'عضو حكمة' }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-1 space-x-reverse shrink-0">
                                <a href="{{ route('active.friendship', $req->id) }}" class="text-secondary hover:text-primary transition-colors flex items-center justify-center" title="قبول">
                                    <span class="material-symbols-outlined text-[22px]">check_circle</span>
                                </a>
                                <a href="{{ route('delete.friendship', $req->id) }}" class="text-error hover:text-red-700 transition-colors flex items-center justify-center" title="رفض">
                                    <span class="material-symbols-outlined text-[22px]">cancel</span>
                                </a>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_friend_requests') }}</p>
                @endforelse
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
                                <p class="text-[11px] text-on-surface-variant">{{ __t('honorary_member') }}</p>
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
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_suggestions') }}</p>
                @endforelse
            </div>
            <a class="block text-center mt-4 pt-3 border-t border-primary/5 text-primary font-bold text-xs hover:underline" href="{{ route('frontend.my_network') }}">{{ __t('explore_more') }}</a>
        </div>
    </aside>

    <!-- Center Feed Column (6 cols on XL) -->
    <div class="order-1 xl:order-2 xl:col-span-6 space-y-stack-md">
        <!-- Professional Header Card -->
        <div class="bg-gradient-to-r from-primary to-primary-container text-white p-6 rounded-xl shadow-md mb-6 border border-primary/20 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 text-[180px] pointer-events-none material-symbols-outlined">
                campaign
            </div>
            <div class="flex items-center space-x-4 space-x-reverse relative z-10">
                <div class="bg-white/10 backdrop-blur-md p-3 rounded-full flex items-center justify-center shadow-inner">
                    <span class="material-symbols-outlined text-[32px] text-white">campaign</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold font-headline text-white">{{ __t('trending_issues') }}</h1>
                    <p class="text-xs text-white/80 mt-1">{{ __t('trending_issues_sub') }}</p>
                </div>
            </div>
        </div>

        <!-- Posts Feed Container -->
        <div id="posts-feed-container" class="space-y-stack-md animate-fade-in">
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
                <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('trending_issues_sidebar') }}</h3>
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
                            $titleSnippet = 'discussion topic #' . $tPost->id;
                        }
                    @endphp
                    <a class="group block {{ !$loop->first ? 'border-t border-primary/5 pt-3' : '' }}" href="{{ route('frontend.posts.show', $tPost->id) }}">
                        <p class="text-xs text-secondary font-bold mb-0.5">{{ $hashtagName }}</p>
                        <p class="font-body-md text-sm text-on-surface group-hover:text-primary transition-colors">{{ $titleSnippet }}</p>
                        <p class="text-xs text-on-surface-variant mt-0.5">{{ $tPost->comment_count }} {{ __t('comments') }}</p>
                    </a>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_trending_groups') }}</p>
                @endforelse
            </div>
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
                    <p class="text-xs text-on-surface-variant text-center py-2 w-full">{{ __t('no_trending_hashtags') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Trending Groups -->
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
                        <p class="text-xs text-on-surface-variant mt-0.5">{{ $tGroup->members_count }} {{ __t('honorary_member') }}</p>
                    </a>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_trending_groups') }}</p>
                @endforelse
            </div>
            <a href="{{ route('frontend.groups') }}" class="block w-full mt-4 text-primary font-bold text-xs hover:underline text-center">{{ __t('view_all_groups') }}</a>
        </div>

        <!-- Top Scholars List / أعضاء حكمة الأعلى تقييماً -->
        <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-primary/5">
                <div class="flex items-center space-x-2 space-x-reverse">
                    <span class="material-symbols-outlined text-secondary">military_tech</span>
                    <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">أعضاء حكمة الأعلى تقييماً</h3>
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
                                        <span>{{ __t('honorary_member') }}</span>
                                    @endif
                                    
                                    <span class="opacity-50">•</span>
                                    
                                    <button type="button" class="user-points-trigger inline-flex items-center gap-0.5 font-extrabold text-secondary hover:text-primary hover:underline cursor-pointer bg-secondary/5 px-1.5 py-0.5 rounded text-[10px]" data-user-id="{{ $topUser->id }}">
                                        النقاط: {{ $topUser->points ?? 0 }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="shrink-0 flex items-center justify-end">
                            @if($topUser->friendship_status === 'none')
                                @auth
                                    <button class="send-friend-request-btn text-primary hover:bg-primary/10 p-1.5 rounded-full transition-colors cursor-pointer flex items-center justify-center" data-receiver-id="{{ $topUser->id }}" title="إضافة صديق">
                                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                                    </button>
                                @else
                                    <a href="{{ route('user.login') }}" class="text-primary hover:bg-primary/10 p-1.5 rounded-full transition-colors flex items-center justify-center" title="إضافة صديق">
                                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                                    </a>
                                @endauth
                            @elseif($topUser->friendship_status === 'pending_sent')
                                <span class="text-on-surface-variant/40 p-1.5 rounded-full flex items-center justify-center" title="تم إرسال الطلب">
                                    <span class="material-symbols-outlined text-[18px]">pending</span>
                                </span>
                            @elseif($topUser->friendship_status === 'pending_received')
                                <a href="{{ route('profile.friends', auth()->id()) }}" class="text-secondary hover:bg-secondary/10 p-1.5 rounded-full transition-colors flex items-center justify-center animate-pulse" title="لديك طلب معلق من هذا العضو">
                                    <span class="material-symbols-outlined text-[18px]">group</span>
                                </a>
                            @elseif($topUser->friendship_status === 'friends')
                                <span class="text-green-600 p-1.5 rounded-full flex items-center justify-center" title="صديق">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                </span>
                            @endif
                        </div>
                    </li>
                @empty
                    <p class="text-xs text-on-surface-variant text-center py-2">لا توجد سجلات</p>
                @endforelse
            </ul>
        </div>
    </aside>
</div>

<script>
    $(document).ready(function() {
        let page = 1;
        let isLoading = false;
        let hasMore = true;

        $(window).on('scroll', function() {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 600) {
                if (!isLoading && hasMore) {
                    if (window.innerWidth < 1024) return;
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
                url: "{{ route('frontend.trending') }}?page=" + page,
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
    });
</script>
@endsection
