@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24" style="direction: {{ $dir }};">
    
    @if(session('message'))
        <div class="mb-4 p-4 rounded-xl {{ session('alert-type') == 'success' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-error/10 text-error border border-error/20' }} {{ $textAlign }} font-semibold text-xs">
            {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Right Column: Network Directory Grid (RTL: right side on Desktop) -->
        <section class="lg:col-span-9 order-2 lg:order-1 space-y-6">
            <!-- Page Header & Filtering -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 {{ $textAlign }}">
                <div class="{{ $textAlign }}">
                    <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">{{ __t('my_network') }}</h1>
                    <p class="font-body-md text-xs text-on-surface-variant mt-1">{{ __t('my_network_desc') }}</p>
                </div>
                <form id="network-search-form" method="GET" action="{{ route('frontend.my_network') }}" class="w-full md:w-auto relative">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <input id="network-search-input" class="w-full md:w-64 bg-surface py-2 {{ $dir === 'rtl' ? 'pl-4 pr-10' : 'pr-4 pl-10' }} rounded-full border border-primary/10 focus:border-primary focus:ring-1 focus:ring-primary outline-none font-body-md text-xs text-on-surface placeholder:text-outline {{ $textAlign }}" dir="{{ $dir }}" name="search" value="{{ $search }}" placeholder="{{ __t('search_friends_placeholder') }}" type="text" autocomplete="off">
                    <button type="submit" class="absolute {{ $dir === 'rtl' ? 'right-3' : 'left-3' }} top-2.5 text-on-surface-variant hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">search</span>
                    </button>
                </form>
            </div>

            <!-- Filter Chips (Tabs) -->
            <div class="flex flex-wrap gap-2 pb-3 border-b border-primary/5 justify-start" id="network-filter-tabs">
                <a href="{{ route('frontend.my_network', ['filter' => 'suggested', 'search' => $search]) }}" class="px-4 py-1.5 rounded-full font-label-md text-xs font-bold transition-all {{ $filter === 'suggested' ? 'bg-primary text-white' : 'bg-white text-on-surface border border-primary/10 hover:bg-primary/5' }}">{{ __t('suggested_friends') }}</a>
                <a href="{{ route('frontend.my_network', ['filter' => 'all', 'search' => $search]) }}" class="px-4 py-1.5 rounded-full font-label-md text-xs font-bold transition-all {{ $filter === 'all' ? 'bg-primary text-white' : 'bg-white text-on-surface border border-primary/10 hover:bg-primary/5' }}">{{ __t('all_friends') }}</a>
                <a href="{{ route('frontend.my_network', ['filter' => 'pending', 'search' => $search]) }}" class="px-4 py-1.5 rounded-full font-label-md text-xs font-bold transition-all {{ $filter === 'pending' ? 'bg-primary text-white' : 'bg-white text-on-surface border border-primary/10 hover:bg-primary/5' }}">{{ __t('incoming_requests') }} (<span class="pending-count-badge">{{ $pendingRequestsCount }}</span>)</a>
                <a href="{{ route('frontend.my_network', ['filter' => 'sent_requests', 'search' => $search]) }}" class="px-4 py-1.5 rounded-full font-label-md text-xs font-bold transition-all {{ $filter === 'sent_requests' ? 'bg-primary text-white' : 'bg-white text-on-surface border border-primary/10 hover:bg-primary/5' }}">{{ __t('sent_requests_tab') }} (<span class="sent-count-badge">{{ $sentRequestsCount }}</span>)</a>
                <a href="{{ route('frontend.my_network', ['filter' => 'recent', 'search' => $search]) }}" class="px-4 py-1.5 rounded-full font-label-md text-xs font-bold transition-all {{ $filter === 'recent' ? 'bg-primary text-white' : 'bg-white text-on-surface border border-primary/10 hover:bg-primary/5' }}">{{ __t('recently_added') }}</a>
            </div>

            <!-- Network Grid Wrapper -->
            <div id="network-grid-container" class="w-full">
                @include('frontend.wiselook.pages.my_network_grid')
            </div>

            <!-- Scroll Loader -->
            <div id="network-scroll-loader" class="hidden w-full text-center py-6 col-span-full">
                <div class="inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>
        </section>

        <!-- Left Column: Sidebar / Stats (RTL: left side on Desktop) -->
        <aside class="lg:col-span-3 order-1 lg:order-2 space-y-6 lg:sticky lg:top-24 self-start">
            <!-- Stats Card -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm {{ $textAlign }}">
                <h3 class="font-title-lg text-sm font-bold text-primary mb-4 border-b border-primary/5 pb-2">{{ __t('network_stats') }}</h3>
                <div class="space-y-4 text-xs font-semibold">
                    <div class="flex justify-between items-center pb-3 border-b border-primary/5" style="direction: {{ $dir }};">
                        <span class="text-on-surface-variant">{{ __t('total_friends') }}</span>
                        <span class="text-sm font-bold text-primary total-friends-count-val">{{ $totalFriendsCount }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-primary/5" style="direction: {{ $dir }};">
                        <span class="text-on-surface-variant">{{ __t('new_requests') }}</span>
                        <span class="text-sm font-bold text-secondary pending-requests-count-val">{{ $pendingRequestsCount }}</span>
                    </div>
                    <div class="flex justify-between items-center" style="direction: {{ $dir }};">
                        <span class="text-on-surface-variant">{{ __t('active_users') }}</span>
                        <span class="text-sm font-bold text-on-surface">{{ $mutualContactsCount }}</span>
                    </div>
                </div>
            </div>
            
            <!-- Top Scholars List / حكماء الأسبوع -> أعلى الأعضاء تقييماً -->
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-6 shadow-sm {{ $textAlign }}">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-primary/5">
                    <div class="flex items-center space-x-2 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }}">
                        <span class="material-symbols-outlined text-secondary">military_tech</span>
                        <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('top_rated_members') }}</h3>
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
                        <li class="flex items-center justify-between group transition-all duration-300" style="direction: {{ $dir }};">
                            <div class="flex items-center space-x-3 {{ $dir === 'rtl' ? 'space-x-reverse' : '' }}">
                                <div class="relative shrink-0">
                                    <a href="{{ route('profile.edit', $topUser->id) }}">
                                        <img alt="{{ $topUserName }}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="{{ $topUserAvatar }}">
                                    </a>
                                    <span class="absolute -top-1 {{ $dir === 'rtl' ? '-right-1' : '-left-1' }} {{ $index == 0 ? 'bg-secondary text-on-secondary' : ($index < 3 ? 'bg-primary/80 text-white' : 'bg-surface-dim text-on-surface') }} text-[9px] w-4 h-4 flex items-center justify-center rounded-full font-extrabold shadow-sm border border-white">
                                        {{ $index + 1 }}
                                    </span>
                                </div>
                                <div class="{{ $textAlign }}">
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
                                            {{ __t('points_label_with_colon', ['points' => $topUser->points ?? 0]) }}
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
                                    <span class="text-on-surface-variant/40 p-1.5 rounded-full flex items-center justify-center" title="{{ __t('request_sent_title') }}">
                                        <span class="material-symbols-outlined text-[18px]">pending</span>
                                    </span>
                                @elseif($topUser->friendship_status === 'pending_received')
                                    <a href="{{ route('profile.friends', auth()->id()) }}" class="text-secondary hover:bg-secondary/10 p-1.5 rounded-full transition-colors flex items-center justify-center animate-pulse" title="{{ __t('pending_request_from_member') }}">
                                        <span class="material-symbols-outlined text-[18px]">group</span>
                                    </a>
                                @elseif($topUser->friendship_status === 'friends')
                                    <span class="text-green-600 p-1.5 rounded-full flex items-center justify-center" title="{{ __t('friend_status') }}">
                                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    </span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <p class="text-xs text-on-surface-variant text-center py-2">{{ __t('no_records_found') }}</p>
                    @endforelse
                </ul>
            </div>
        </aside>
        
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
    let currentPage = 1;
    let hasMore = false;
    let gridLoading = false;

    // Translation strings for JS
    const _tp = {
        direction:                   '{!! current_language()->direction ?? "rtl" !!}',
        loadingData:                 {!! json_encode(__t('loading_data')) !!},
        errorLoadingData:            {!! json_encode(__t('error_loading_data')) !!},
        pleaseTryAgainLater:         {!! json_encode(__t('please_try_again_later')) !!},
        friendRequestError:          {!! json_encode(__t('friend_request_error')) !!},
        friendActionError:           {!! json_encode(__t('friend_action_error')) !!},
        noFriendsMatchingFilter:     {!! json_encode(__t('no_friends_matching_filter')) !!},
        noIncomingRequestsMsg:       {!! json_encode(__t('no_incoming_requests_msg')) !!},
        noSentRequestsMsg:           {!! json_encode(__t('no_sent_requests_msg')) !!},
        noSuggestedFriendsMsg:       {!! json_encode(__t('no_suggested_friends_msg')) !!},
        mutualFriendsWith:           {!! json_encode(__t('mutual_friends_with')) !!},
        noMutualContactsBetween:     {!! json_encode(__t('no_mutual_contacts_between')) !!},
        noContactsTitle:             {!! json_encode(__t('no_contacts_title')) !!},
    };

    // Read initial pagination meta
    const initialMeta = $('#network-grid-meta');
    if (initialMeta.length) {
        hasMore = initialMeta.attr('data-has-more') === 'true';
        currentPage = parseInt(initialMeta.attr('data-current-page')) || 1;
    }

    // Helper: load network grid via AJAX
    function loadNetworkGrid(url, pushToHistory = true) {
        // Show premium spinner
        $('#network-grid-container').html(`
            <div class="col-span-full bg-white rounded-2xl p-12 border border-primary/10 text-center flex flex-col items-center justify-center">
                <div class="inline-block w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin mb-3"></div>
                <p class="font-body-md text-xs text-on-surface-variant">${_tp.loadingData}</p>
            </div>
        `);
        
        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(html) {
                $('#network-grid-container').html(html);
                if (pushToHistory) {
                    history.pushState(null, '', url);
                }
                updateActiveTab(url);
                
                // Read pagination meta
                const meta = $('#network-grid-meta');
                hasMore = meta.attr('data-has-more') === 'true';
                currentPage = parseInt(meta.attr('data-current-page')) || 1;
            },
            error: function() {
                $('#network-grid-container').html(`
                    <div class="col-span-full bg-white rounded-2xl p-12 border border-primary/10 text-center">
                        <span class="material-symbols-outlined text-[64px] text-error mb-3">error</span>
                        <h3 class="font-headline-lg text-base font-bold text-error">${_tp.errorLoadingData}</h3>
                        <p class="font-body-md text-xs text-on-surface-variant mt-2">${_tp.pleaseTryAgainLater}</p>
                    </div>
                `);
            }
        });
    }

    // Helper: update tab styles and search hidden filter input
    function updateActiveTab(url) {
        const urlObj = new URL(url, window.location.origin);
        const filter = urlObj.searchParams.get('filter') || 'suggested';
        
        $('#network-filter-tabs a').each(function() {
            const tabUrl = new URL($(this).attr('href'), window.location.origin);
            const tabFilter = tabUrl.searchParams.get('filter') || 'suggested';
            
            if (tabFilter === filter) {
                $(this).removeClass('bg-white text-on-surface border border-primary/10 hover:bg-primary/5')
                       .addClass('bg-primary text-white');
            } else {
                $(this).addClass('bg-white text-on-surface border border-primary/10 hover:bg-primary/5')
                       .removeClass('bg-primary text-white');
            }
        });
        
        // Update hidden filter in search form
        $('#network-search-form input[name="filter"]').val(filter);
    }

    // 1. Tab Clicks Interception
    $(document).on('click', '#network-filter-tabs a', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');
        loadNetworkGrid(url);
    });

    let searchDebounceTimer;
    // 2. Search Input Auto Search
    $(document).on('keyup input', '#network-search-input', function() {
        clearTimeout(searchDebounceTimer);
        const input = $(this);
        const form = $('#network-search-form');
        const val = input.val();
        
        if (val.trim() === '' || val.trim().length >= 2) {
            searchDebounceTimer = setTimeout(function() {
                const url = form.attr('action') + '?' + form.serialize();
                loadNetworkGrid(url);
            }, 300);
        }
    });

    // 3. Search Form Interception
    $(document).on('submit', '#network-search-form', function(e) {
        e.preventDefault();
        clearTimeout(searchDebounceTimer);
        const form = $(this);
        const url = form.attr('action') + '?' + form.serialize();
        loadNetworkGrid(url);
    });

    // 4. Browser Back/Forward History Support (Popstate)
    $(window).on('popstate', function() {
        loadNetworkGrid(window.location.href, false);
    });

    // Helper: Update UI counts globally
    function updateStatsCounts(stats) {
        if (stats) {
            if (stats.pendingRequestsCount !== undefined) {
                $('.pending-count-badge').text(stats.pendingRequestsCount);
                $('.pending-requests-count-val').text(stats.pendingRequestsCount);
            }
            if (stats.sentRequestsCount !== undefined) {
                $('.sent-count-badge').text(stats.sentRequestsCount);
            }
            if (stats.totalFriendsCount !== undefined) {
                $('.total-friends-count-val').text(stats.totalFriendsCount);
            }
        }
    }

    // 4. Send Friend Request (Suggested tab) via AJAX
    $(document).on('click', '.send-friend-request-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const receiverId = btn.attr('data-receiver-id');
        const card = btn.closest('.network-card');

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
                      
                      // Update stats counts
                      updateStatsCounts(response);

                      // Slide out/fade out card
                      card.addClass('scale-95 opacity-0');
                      setTimeout(() => {
                          card.remove();
                          checkEmptyState();
                      }, 300);
                  }
              },
              error: function(xhr) {
                  let msg = _tp.friendRequestError;
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

      // 5. Intercept Friendship Action Forms (Accept, Reject, Cancel, Delete) via AJAX
      $(document).on('submit', '.friendship-action-form', function(e) {
          e.preventDefault();
          const form = $(this);
          const card = form.closest('.network-card');
          const submitBtn = form.find('button[type="submit"]');

          if (submitBtn.hasClass('pointer-events-none')) return;
          submitBtn.addClass('pointer-events-none');

          $.ajax({
              url: form.attr('action'),
              type: "POST",
              data: form.serialize(),
              success: function(response) {
                  if (response.success) {
                      if (typeof toastr !== "undefined") {
                          toastr.success(response.message);
                      } else {
                          alert(response.message);
                      }

                      // Update stats counts
                      updateStatsCounts(response);

                      // Slide out/fade out card
                      card.addClass('scale-95 opacity-0');
                      setTimeout(() => {
                          card.remove();
                          checkEmptyState();
                      }, 300);
                  }
              },
              error: function(xhr) {
                  let msg = _tp.friendActionError;
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                      msg = xhr.responseJSON.message;
                  }
                  if (typeof toastr !== "undefined") {
                      toastr.error(msg);
                  } else {
                      alert(msg);
                  }
                  submitBtn.removeClass('pointer-events-none');
              }
          });
      });

      // Helper to check if grid is empty after removing cards dynamically
      function checkEmptyState() {
          if ($('#network-grid-container').find('.network-card').length === 0) {
              const urlParams = new URLSearchParams(window.location.search);
              const filter = urlParams.get('filter') || 'suggested';
              
              let emptyMsg = _tp.noFriendsMatchingFilter;
              if (filter === 'pending') {
                  emptyMsg = _tp.noIncomingRequestsMsg;
              } else if (filter === 'sent_requests') {
                  emptyMsg = _tp.noSentRequestsMsg;
              } else if (filter === 'suggested') {
                  emptyMsg = _tp.noSuggestedFriendsMsg;
              }

              $('#network-grid-container').html(`
                  <div class="col-span-full bg-white rounded-2xl p-12 border border-primary/10 text-center w-full">
                      <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">group</span>
                      <h3 class="font-headline-lg text-base font-bold text-primary">${_tp.noContactsTitle}</h3>
                      <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">${emptyMsg}</p>
                  </div>
              `);
          }
      }

      // 6. Mutual Friends Modal Handlers
      const mutualModal = $('#mutual-friends-modal');
      const mutualList = $('#mutual-friends-list');
      const mutualTitle = $('#mutual-friends-title');

      $(document).on('click', '.mutual-friends-trigger', function() {
          const btn = $(this);
          const userId = btn.attr('data-user-id');
          const userName = btn.attr('data-user-name');

          mutualTitle.text(_tp.mutualFriendsWith + ' ' + userName);
          mutualList.html(`
              <div class="flex items-center justify-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
              </div>
          `);

          // Open Modal
          mutualModal.removeClass('hidden').addClass('flex');
          setTimeout(() => {
              mutualModal.addClass('modal-show');
          }, 10);

          // Fetch mutual friends via AJAX
          $.ajax({
              url: "/friends/mutual/" + userId,
              type: "GET",
              success: function(response) {
                  mutualList.empty();
                  if (response.length === 0) {
                      mutualList.append('<p class="text-xs text-on-surface-variant text-center py-4">' + _tp.noMutualContactsBetween + '</p>');
                      return;
                  }
                  response.forEach(function(friend) {
                      mutualList.append(`
                          <div class="flex items-center justify-between group" style="direction: ${_tp.direction};">
                              <div class="flex items-center space-x-3 ${_tp.direction === 'rtl' ? 'space-x-reverse' : ''}">
                                  <a href="${friend.profile_url}" class="shrink-0">
                                      <img alt="${friend.name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" src="${friend.avatar}">
                                  </a>
                                  <div class="${_tp.direction === 'rtl' ? 'text-right' : 'text-left'}">
                                      <a href="${friend.profile_url}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">${friend.name}</a>
                                  </div>
                              </div>
                          </div>
                      `);
                  });
              },
              error: function() {
                  mutualList.html('<p class="text-xs text-error text-center py-4">' + _tp.errorLoadingData + '</p>');
              }
          });
      });

      // Close Mutual Modal
      function closeMutualModal() {
          mutualModal.removeClass('modal-show');
          setTimeout(() => {
              mutualModal.addClass('hidden').removeClass('flex');
          }, 300);
      }

      $(document).on('click', '#close-mutual-friends-btn, .modal-backdrop', function() {
          closeMutualModal();
      });

      $(document).on('keydown', function(e) {
          if (e.key === 'Escape' && !mutualModal.hasClass('hidden')) {
              closeMutualModal();
          }
      });

      // --- Infinite Scroll / Lazy Loading for Network Grid ---
      $(window).on('scroll', function() {
          if ($(window).scrollTop() + $(window).height() >= $(document).height() - 250) {
              if (!gridLoading && hasMore) {
                  loadMoreNetworkGrid();
              }
          }
      });

      function loadMoreNetworkGrid() {
          gridLoading = true;
          $('#network-scroll-loader').removeClass('hidden');

          const nextPage = currentPage + 1;
          const currentUrl = new URL(window.location.href);
          currentUrl.searchParams.set('page', nextPage);

          $.ajax({
              url: currentUrl.toString(),
              type: 'GET',
              headers: {
                  'X-Requested-With': 'XMLHttpRequest'
              },
              success: function(html) {
                  $('#network-scroll-loader').addClass('hidden');
                  gridLoading = false;

                  const newCards = $(html).find('.network-card');
                  if (newCards.length) {
                      let grid = $('#network-grid-container .grid');
                      if (grid.length) {
                          grid.append(newCards);
                      } else {
                          $('#network-grid-container').html(html);
                      }
                      
                      currentPage = nextPage;
                      
                      const meta = $(html).find('#network-grid-meta').length ? $(html).find('#network-grid-meta') : $(html).filter('#network-grid-meta');
                      hasMore = meta.attr('data-has-more') === 'true';
                  } else {
                      hasMore = false;
                  }
              },
              error: function() {
                  $('#network-scroll-loader').addClass('hidden');
                  gridLoading = false;
              }
          });
      }
});
</script>
@endpush
