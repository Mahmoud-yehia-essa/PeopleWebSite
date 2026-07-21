@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24 {{ $textAlign }}" style="direction: {{ $dir }};">
    
    <!-- Page Wrapper -->
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm" style="direction: {{ $dir }};">
            <div class="{{ $textAlign }}">
                <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">{{ __t('all_notifications') }}</h1>
                <p class="font-body-md text-xs text-on-surface-variant mt-1">{{ __t('all_notifications_desc') }}</p>
            </div>
            
            <button id="page-mark-all-read" class="px-4 py-2 border border-primary text-primary hover:bg-primary/5 rounded-xl font-label-md text-xs font-bold transition-all cursor-pointer shadow-sm">
                {{ __t('mark_all_read') }}
            </button>
        </div>

        <!-- Notifications List -->
        <div id="notifications-page-list" class="bg-white rounded-2xl border border-primary/10 shadow-sm overflow-hidden divide-y divide-primary/5 {{ $textAlign }}" style="direction: {{ $dir }};">
            <!-- Loaded dynamically via AJAX -->
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let page = 1;
    let loading = false;
    let hasMore = true;

    // Translation values for JS
    const _tp = {
        direction:                     '{!! current_language()->direction ?? "rtl" !!}',
        loadingNotifications:          {!! json_encode(__t('loading_notifications')) !!},
        noNotifications:               {!! json_encode(__t('no_notifications')) !!},
        errorLoadingNotifications:     {!! json_encode(__t('error_loading_notifications')) !!},
        allNotificationsMarkedRead:    {!! json_encode(__t('all_notifications_marked_read')) !!}
    };

    function loadMoreNotifications() {
        if (loading || !hasMore) return;
        loading = true;
        
        // Append loader
        if (page === 1) {
            $('#notifications-page-list').html('<div class="p-8 text-center text-xs text-on-surface-variant font-medium">' + _tp.loadingNotifications + '</div>');
        } else {
            if ($('#page-notifications-loader').length === 0) {
                $('#notifications-page-list').append('<div id="page-notifications-loader" class="p-4 text-center"><div class="animate-spin inline-block rounded-full h-6 w-6 border-b-2 border-primary"></div></div>');
            }
        }

        $.ajax({
            url: "{{ route('frontend.notifications.api') }}?page=" + page + "&per_page=10",
            type: "GET",
            success: function(response) {
                $('#page-notifications-loader').remove();
                loading = false;
                hasMore = response.has_more;
                
                var notifications = response.notifications;
                var html = '';
                
                if (notifications.length === 0 && page === 1) {
                    html = '<div class="p-8 text-center text-xs text-on-surface-variant font-medium flex flex-col items-center justify-center gap-2">';
                    html += '  <span class="material-symbols-outlined text-[48px] text-primary/30 mb-2">notifications_off</span>';
                    html += '  <p>' + _tp.noNotifications + '</p>';
                    html += '</div>';
                    $('#notifications-page-list').html(html);
                } else {
                    $.each(notifications, function(i, item) {
                        var icon = 'notifications';
                        var iconColor = 'text-primary bg-primary/5';
                        if (item.type === 'friend_request') {
                            icon = 'group_add';
                            iconColor = 'text-secondary bg-secondary/5';
                        } else if (item.type === 'friend_accept') {
                            icon = 'check_circle';
                            iconColor = 'text-green-600 bg-green-50';
                        } else if (item.type === 'like') {
                            icon = 'thumb_up';
                            iconColor = 'text-blue-600 bg-blue-50';
                        } else if (item.type === 'comment') {
                            icon = 'chat_bubble';
                            iconColor = 'text-[#caa800] bg-[#ffe174]/10';
                        } else if (item.type === 'comment_reply' || item.type === 'reply_to_reply') {
                            icon = 'reply';
                            iconColor = 'text-purple-600 bg-purple-50';
                        } else if (item.type === 'mention') {
                            icon = 'alternate_email';
                            iconColor = 'text-blue-600 bg-blue-50';
                        }
                        
                        var avatarUrl = "{{ url('upload/no_image.jpg') }}";
                        if (item.avatar && item.avatar !== 'non') {
                            if (item.avatar.indexOf('http') === 0) {
                                avatarUrl = item.avatar;
                            } else {
                                avatarUrl = "{{ asset('new_wiselook/uploads') }}/" + item.avatar;
                            }
                        }
                        
                        var activeClass = !item.is_seen ? 'bg-primary/[0.02]' : '';
                        var unreadDot = !item.is_seen ? '<div class="w-2.5 h-2.5 bg-primary rounded-full shrink-0 mt-2.5"></div>' : '';
                        var alignClass = _tp.direction === 'rtl' ? 'text-right' : 'text-left';

                        html += '<a href="' + item.url + '" class="p-4 flex items-start gap-4 transition-colors duration-200 hover:bg-primary/5 notification-link-item ' + activeClass + '" data-id="' + item.id + '" data-type="' + item.type + '" style="direction: ' + _tp.direction + ';">';
                        html += '  <div class="relative shrink-0">';
                        html += '    <img src="' + avatarUrl + '" class="w-12 h-12 rounded-full object-cover border border-outline-variant">';
                        html += '    <div class="absolute -bottom-1 ' + (_tp.direction === 'rtl' ? '-left-1' : '-right-1') + ' w-6 h-6 rounded-full flex items-center justify-center border-2 border-white shadow-sm ' + iconColor + '">';
                        html += '      <span class="material-symbols-outlined text-[12px] font-variation-settings-fill">' + icon + '</span>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '  <div class="flex-grow min-w-0 ' + alignClass + '">';
                        html += '    <p class="text-xs font-semibold text-on-surface leading-relaxed">' + item.message + '</p>';
                        html += '    <span class="text-[10px] text-on-surface-variant font-medium mt-1 block">' + item.diff + '</span>';
                        html += '  </div>';
                        html +=    unreadDot;
                        html += '</a>';
                    });
                    
                    if (page === 1) {
                        $('#notifications-page-list').html(html);
                    } else {
                        $('#notifications-page-list').append(html);
                    }
                }
            },
            error: function() {
                $('#page-notifications-loader').remove();
                loading = false;
                if (page === 1) {
                    $('#notifications-page-list').html('<div class="p-8 text-center text-xs text-error font-medium">' + _tp.errorLoadingNotifications + '</div>');
                }
            }
        });
    }

    // Initial load
    loadMoreNotifications();

    // Trigger on scroll near bottom of window
    $(window).on('scroll', function() {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {
            if (!loading && hasMore) {
                page++;
                loadMoreNotifications();
            }
        }
    });

    $('#page-mark-all-read').on('click', function() {
        var $btn = $(this);
        $.ajax({
            url: "{{ route('frontend.notifications.mark_read') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if(response.success) {
                    if (typeof toastr !== "undefined") {
                        toastr.success(_tp.allNotificationsMarkedRead);
                    } else {
                        alert(_tp.allNotificationsMarkedRead);
                    }
                    
                    // Reset pagination variables and reload page 1
                    page = 1;
                    hasMore = true;
                    loadMoreNotifications();
                    
                    // Update header bell unread count badge
                    if (typeof checkUnreadNotifications === 'function') {
                        checkUnreadNotifications();
                    }
                }
            }
        });
    });
});
</script>
@endpush
