<!-- Comments Modal / نافذة النقاشات المنبثقة -->
<div id="comments-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0"></div>
    
    <!-- Modal Content -->
    <div class="modal-container bg-surface-container-lowest border border-primary/10 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden relative z-10">
        
        <!-- Header -->
        <div class="p-4 border-b border-primary/10 flex items-center justify-between bg-surface-container-low">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="material-symbols-outlined text-primary text-[24px]">forum</span>
                <div>
                    <h3 class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('discussion_council') }}</h3>
                    <p id="modal-post-author" class="text-[11px] text-on-surface-variant font-medium"></p>
                </div>
            </div>
            <button id="close-modal-btn" class="text-on-surface-variant hover:text-primary p-1.5 rounded-full hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Post Context Brief -->
        <div id="modal-post-brief" class="p-4 bg-primary/5 border-b border-primary/10">
            <h4 id="modal-post-title" class="font-bold text-primary text-sm mb-1 leading-snug"></h4>
            <p id="modal-post-snippet" class="text-xs text-on-surface-variant line-clamp-2 leading-relaxed"></p>
        </div>

        <!-- Comments List (Scrollable) -->
        <div id="modal-comments-list" class="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-hide">
            <!-- Loaded dynamically by JS -->
        </div>

        <!-- Add Comment Footer Form -->
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
            <div class="p-4 border-t border-primary/10 bg-surface-container-low">
                <form id="new-comment-form" class="flex items-center space-x-2 space-x-reverse">
                    <img alt="User" class="w-9 h-9 rounded-full object-cover shrink-0" src="{{ $currentUserAvatar }}">
                    <div class="flex-1 relative">
                        <input type="text" id="new-comment-input" class="w-full bg-surface border border-primary/10 rounded-full py-2.5 px-4 pr-4 pl-12 text-sm text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('write_comment_or_counter') }}">
                        <button type="submit" class="absolute left-1.5 top-1/2 -translate-y-1/2 bg-primary text-white hover:bg-primary-dark p-1.5 rounded-full flex items-center justify-center transition-colors">
                            <span class="material-symbols-outlined text-[18px]">send</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-4 border-t border-primary/10 bg-surface-container-low text-center">
                <p class="text-sm text-on-surface-variant">
                    {{ __t('login_to_comment') }} <a href="{{ route('user.login') }}" class="text-primary hover:underline font-bold">{{ __t('login_to_comment_link') }}</a> {{ __t('login_to_comment_suffix') }}
                </p>
            </div>
        @endauth
    </div>
</div>

<!-- Supporters Modal / نافذة المؤيدين المنبثقة -->
<div id="supporters-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0"></div>
    
    <!-- Modal Content -->
    <div class="modal-container bg-surface-container-lowest border border-primary/10 rounded-2xl shadow-2xl max-w-md w-full max-h-[60vh] flex flex-col overflow-hidden relative z-10">
        
        <!-- Header -->
        <div class="p-4 border-b border-primary/10 flex items-center justify-between bg-surface-container-low">
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="material-symbols-outlined text-secondary text-[24px]">workspace_premium</span>
                <div>
                    <h3 id="modal-supporters-title" class="font-headline-lg-mobile text-[16px] font-bold text-primary">{{ __t('supporters_title') }}</h3>
                    <p id="modal-supporters-count" class="text-[11px] text-on-surface-variant font-medium"></p>
                </div>
            </div>
            <button id="close-supporters-modal-btn" class="text-on-surface-variant hover:text-primary p-1.5 rounded-full hover:bg-primary/5 transition-all">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <!-- Supporters List (Scrollable) -->
        <div id="modal-supporters-list" class="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-hide">
        <!-- Loaded dynamically by JS -->
        </div>
    </div>
</div>

<!-- Image Viewer Lightbox Modal -->
<div id="image-viewer-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/90 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-5xl w-full max-h-[85vh] flex flex-col items-center justify-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300">
        <!-- Close Button -->
        <button id="close-image-viewer-btn" class="absolute -top-12 right-2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">close</span>
        </button>
        
        <!-- Image Container -->
        <div class="w-full bg-transparent rounded-2xl overflow-hidden flex items-center justify-center">
            <img id="viewer-image-element" class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl" src="" alt="Post Image Preview">
        </div>
    </div>
</div>

<!-- Video Viewer Lightbox Modal -->
<div id="video-viewer-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/90 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-4xl w-full max-h-[85vh] flex flex-col items-center justify-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300">
        <!-- Close Button -->
        <button id="close-video-viewer-btn" class="absolute -top-12 right-2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">close</span>
        </button>
        
        <!-- Video Player Card -->
        <div class="w-full bg-black rounded-2xl border border-white/10 shadow-2xl overflow-hidden aspect-video flex items-center justify-center">
            <video id="viewer-video-player" class="w-full h-full object-contain" controls autoplay>
                <source src="" type="video/mp4">
                متصفحك لا يدعم تشغيل الفيديو.
            </video>
        </div>
    </div>
</div>

<style>
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    /* Modal Backdrop Transitions */
    .modal-backdrop {
        background-color: rgba(15, 23, 42, 0.5); /* Slate 900 with 50% opacity */
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        opacity: 0;
        transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    /* Modal Content Container Transitions */
    .modal-container {
        opacity: 0;
        transform: scale(0.94) translateY(24px);
        transition: transform 0.45s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.35s ease-out;
    }
    
    /* Active States */
    #comments-modal.modal-show,
    #supporters-modal.modal-show {
        display: flex !important;
    }
    #comments-modal.modal-show .modal-backdrop,
    #supporters-modal.modal-show .modal-backdrop {
        opacity: 1 !important;
    }
    #comments-modal.modal-show .modal-container,
    #supporters-modal.modal-show .modal-container {
        opacity: 1 !important;
        transform: scale(1) translateY(0) !important;
    }
    .modal-active {
        overflow: hidden !important;
    }
    
    /* Material symbol filled state utility */
    .fill-1 {
        font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    /* Micro-interactions for liking */
    @keyframes like-bounce {
        0% { transform: scale(1); }
        25% { transform: scale(1.35) rotate(-10deg); }
        50% { transform: scale(0.85) rotate(5deg); }
        70% { transform: scale(1.15) rotate(-3deg); }
        100% { transform: scale(1) rotate(0deg); }
    }
    .animate-like {
        display: inline-block;
        animation: like-bounce 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    /* Unique lightbulb glow animation for post support */
    @keyframes bulb-glow {
        0% {
            transform: scale(1);
            filter: drop-shadow(0 0 0px rgba(245, 158, 11, 0));
        }
        30% {
            transform: scale(1.45) rotate(12deg);
            filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.8));
            color: #f59e0b; /* Amber-500 */
        }
        50% {
            transform: scale(0.85) rotate(-8deg);
            filter: drop-shadow(0 0 15px rgba(245, 158, 11, 1));
        }
        75% {
            transform: scale(1.15) rotate(4deg);
            filter: drop-shadow(0 0 8px rgba(245, 158, 11, 0.5));
        }
        100% {
            transform: scale(1) rotate(0deg);
            filter: drop-shadow(0 0 0px rgba(245, 158, 11, 0));
        }
    }
    .animate-bulb {
        display: inline-block;
        animation: bulb-glow 0.55s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    /* Image Viewer Modal Transitions */
    #image-viewer-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #image-viewer-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Video Viewer Modal Transitions */
    #video-viewer-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #video-viewer-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Translation strings for use in JS
    const _t = {
        postByAuthor:         {!! json_encode(__t("post_by_author")) !!},
        noDiscussionsYet:     {!! json_encode(__t("no_discussions_yet")) !!},
        loadingDiscussions:   {!! json_encode(__t("loading_discussions")) !!},
        errorDiscussions:     {!! json_encode(__t("error_loading_discussions")) !!},
        connectionFailed:     {!! json_encode(__t("connection_failed")) !!},
        repliesLabel:         {!! json_encode(__t("replies_label")) !!},
        writeReply:           {!! json_encode(__t("write_reply_input")) !!},
        sendReply:            {!! json_encode(__t("send_reply")) !!},
        supportersTitle:      {!! json_encode(__t("supporters_title")) !!},
        loadingList:          {!! json_encode(__t("loading_list")) !!},
        totalSupports:        {!! json_encode(__t("total_supports")) !!},
        totalLikes:           {!! json_encode(__t("total_likes")) !!},
        likedByComment:       {!! json_encode(__t("liked_by_comment")) !!},
        likedByReply:         {!! json_encode(__t("liked_by_reply")) !!},
        noSupportersYet:      {!! json_encode(__t("no_supporters_yet")) !!},
        noLikesYet:           {!! json_encode(__t("no_likes_yet")) !!},
        errorLoadingSupps:    {!! json_encode(__t("error_loading_supporters")) !!},
        errorLoadingLikes:    {!! json_encode(__t("error_loading_likes")) !!},
        memberWord:           {!! json_encode(__t("member_word")) !!},
    };

    // Dynamic Comments and Nested Replies List
    // Current logged-in user ID
    const currentUserId = {{ auth()->check() ? auth()->id() : 'null' }};
    let activeComments = [];
    let activePostId = null;
    let isGroupSubject = false;

    // Helper function to render nested replies
    function renderReplies(replies) {
        if (!replies) return '';
        return replies.map(reply => {
            const isOwner = currentUserId && (currentUserId == reply.user_id);
            const deleteBtnHtml = isOwner ? `
                <button class="delete-reply-btn text-on-surface-variant/60 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50 border-none bg-transparent cursor-pointer flex items-center justify-center shrink-0" title="حذف الرد" data-reply-id="${reply.id}">
                    <span class="material-symbols-outlined text-[15px] text-red-500">delete</span>
                </button>
            ` : '';

            return `
                <div class="reply-card flex items-start space-x-3 space-x-reverse" data-reply-id="${reply.id}">
                    <img alt="${reply.user_name}" class="w-8 h-8 rounded-full object-cover border border-outline-variant shrink-0" src="${reply.profile_picture}">
                    <div class="flex-1 bg-surface-container-low/80 rounded-2xl p-3 border border-primary/5">
                        <div class="flex items-center justify-between mb-1">
                            <div>
                                <h6 class="font-bold text-primary text-xs">${reply.user_name}</h6>
                                <p class="text-[9px] text-on-surface-variant">${reply.created_at}</p>
                            </div>
                            ${deleteBtnHtml}
                        </div>
                        <p class="text-xs text-on-surface leading-relaxed">${reply.content}</p>
                        <div class="flex items-center space-x-3 space-x-reverse mt-2 pt-1 border-t border-primary/5 text-[10px] text-on-surface-variant">
                            <div class="flex items-center space-x-0.5 space-x-reverse">
                                <button class="reply-like-action flex items-center justify-center w-6 h-6 rounded-full hover:bg-primary/10 hover:text-primary transition-all ${reply.user_liked ? 'text-primary bg-primary/10' : ''}" data-active="${reply.user_liked}">
                                    <span class="material-symbols-outlined text-[13px] ${reply.user_liked ? 'fill-1' : ''}">thumb_up</span>
                                </button>
                                <button class="reply-likers-trigger text-[10px] font-bold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0">
                                    <span class="like-count">${reply.reaction_count || 0}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // Helper function to render comments list
    function renderCommentsList(comments) {
        const container = $('#modal-comments-list');
        container.empty();

        if (!comments || comments.length === 0) {
            container.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.noDiscussionsYet}</p>`);
            return;
        }

        comments.forEach(comment => {
            const repliesHtml = renderReplies(comment.replies);
            const repliesCount = comment.replies ? comment.replies.length : 0;
            const isOwner = currentUserId && (currentUserId == comment.user_id);
            const deleteBtnHtml = isOwner ? `
                <button class="delete-comment-btn text-on-surface-variant/60 hover:text-red-600 transition-colors p-1 rounded-full hover:bg-red-50 border-none bg-transparent cursor-pointer flex items-center justify-center shrink-0" title="حذف التعليق" data-comment-id="${comment.id}">
                    <span class="material-symbols-outlined text-[17px] text-red-500">delete</span>
                </button>
            ` : '';

            const commentHtml = `
                <div class="comment-card space-y-3" data-comment-id="${comment.id}">
                    <div class="flex items-start space-x-3 space-x-reverse">
                        <img alt="${comment.user_name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0" src="${comment.profile_picture}">
                        <div class="flex-1 bg-surface-container-low/50 rounded-2xl p-4 border border-primary/5">
                            <div class="flex items-center justify-between mb-1">
                                <div>
                                    <h5 class="font-bold text-primary text-xs">${comment.user_name}</h5>
                                    <p class="text-[10px] text-on-surface-variant">${comment.created_at}</p>
                                </div>
                                ${deleteBtnHtml}
                            </div>
                            <p class="text-sm text-on-surface leading-relaxed">${comment.content}</p>
                            
                            <div class="flex items-center space-x-4 space-x-reverse mt-3 pt-2 border-t border-primary/5 text-xs text-on-surface-variant">
                                <div class="flex items-center space-x-0.5 space-x-reverse">
                                    <button class="comment-like-action flex items-center justify-center w-7 h-7 rounded-full hover:bg-primary/10 hover:text-primary transition-all ${comment.user_liked ? 'text-primary bg-primary/10' : ''}" data-active="${comment.user_liked}">
                                        <span class="material-symbols-outlined text-[15px] ${comment.user_liked ? 'fill-1' : ''}">thumb_up</span>
                                    </button>
                                    <button class="comment-likers-trigger text-[11px] font-bold hover:underline hover:text-primary px-1.5 py-0.5 rounded hover:bg-primary/5 shrink-0">
                                        <span class="like-count">${comment.reaction_count || 0}</span>
                                    </button>
                                </div>
                                <button class="toggle-replies-btn flex items-center space-x-1 space-x-reverse hover:text-secondary transition-all">
                                    <span class="material-symbols-outlined text-[16px]">chat_bubble</span>
                                    <span class="replies-count font-bold">${_t.repliesLabel} (${repliesCount})</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="replies-container mr-10 pr-4 border-r-2 border-primary/10 space-y-3 hidden">
                        <div class="replies-list space-y-3">
                            ${repliesHtml}
                        </div>
                        @auth
                        <form class="new-reply-form flex items-center space-x-2 space-x-reverse mt-3">
                            <input type="text" class="new-reply-input flex-1 bg-surface border border-primary/10 rounded-full py-1.5 px-3.5 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="${_t.writeReply}">
                            <button type="submit" class="bg-primary text-white hover:bg-primary-dark px-4 py-1.5 rounded-full text-[10px] font-bold transition-all shrink-0">${_t.sendReply}</button>
                        </form>
                        @endauth
                    </div>
                </div>
            `;
            container.append(commentHtml);
        });
    }

    // Load comments via AJAX
    function renderComments(postId) {
        const container = $('#modal-comments-list');
        container.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.loadingDiscussions}</p>`);
        
        let url = isGroupSubject ? `/groups/subjects/${postId}/comments` : `/posts/${postId}/comments`;
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    activeComments = response.comments;
                    renderCommentsList(activeComments);
                } else {
                    container.html(`<p class="text-center text-sm text-error py-8">${_t.errorDiscussions}</p>`);
                }
            },
            error: function(xhr) {
                console.error(xhr);
                container.html(`<p class="text-center text-sm text-error py-8">${_t.connectionFailed}</p>`);
            }
        });
    }

    // Open Discussion Modal Events
    $(document).on('click', '.open-discussion-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        activePostId = btn.attr('data-post-id');
        isGroupSubject = btn.attr('data-is-group-subject') === 'true';

        // Populate Modal Headers and Content Brief
        $('#modal-post-author').text(_t.postByAuthor + ': ' + btn.attr('data-author-name'));
        $('#modal-post-title').text(btn.attr('data-post-title'));
        $('#modal-post-snippet').text(btn.attr('data-post-snippet'));

        // Render Comments List
        renderComments(activePostId);

        // Transition Slide Up / Open modal
        $('#comments-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        setTimeout(() => {
            $('#comments-modal').addClass('modal-show');
        }, 20);
    });

    // Close Discussion Modal helper
    function closeModal() {
        $('#comments-modal').removeClass('modal-show');
        $('body').removeClass('modal-active');
        setTimeout(() => {
            $('#comments-modal').addClass('hidden').removeClass('flex');
        }, 400);
    }

    $(document).on('click', '#close-modal-btn, #comments-modal .modal-backdrop', function() {
        closeModal();
    });

    // Open Supporters Modal Events
    $(document).on('click', '.open-supporters-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const postId = btn.attr('data-post-id');
        const isGroupSupporter = btn.attr('data-is-group-subject') === 'true';
        const totalSupports = btn.attr('data-total-supports');

        $('#modal-supporters-title').text(_t.supportersTitle);
        $('#modal-supporters-count').text(_t.loadingList);

        const listContainer = $('#modal-supporters-list');
        listContainer.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.loadingList}</p>`);

        // Show supporters modal
        $('#supporters-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        setTimeout(() => {
            $('#supporters-modal').addClass('modal-show');
        }, 20);

        let url = isGroupSupporter ? `/groups/subjects/${postId}/reactions` : `/posts/${postId}/reactions`;

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modal-supporters-count').text(`${_t.totalSupports}: ${response.reactions.length}`);
                    listContainer.empty();

                    if (response.reactions.length === 0) {
                        listContainer.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.noSupportersYet}</p>`);
                    } else {
                        response.reactions.forEach(supporter => {
                            const rowHtml = `
                                <div class="flex items-center justify-between border-b border-primary/5 pb-3 last:border-0 last:pb-0">
                                    <div class="flex items-center space-x-3 space-x-reverse">
                                        <img alt="${supporter.user_name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0" src="${supporter.profile_picture}">
                                        <div>
                                            <p class="font-body-md text-sm font-bold text-on-surface">${supporter.user_name}</p>
                                            <p class="text-[11px] text-on-surface-variant">${supporter.rank}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <span class="text-[10px] text-on-surface-variant">${supporter.created_at}</span>
                                    </div>
                                </div>
                            `;
                            listContainer.append(rowHtml);
                        });
                    }
                } else {
                    listContainer.html(`<p class="text-center text-sm text-error py-8">${_t.errorLoadingSupps}</p>`);
                }
            },
            error: function(xhr) {
                console.error(xhr);
                listContainer.html(`<p class="text-center text-sm text-error py-8">${_t.connectionFailed}</p>`);
            }
        });
    });

    // Close Supporters Modal Helper
    function closeSupportersModal() {
        $('#supporters-modal').removeClass('modal-show');
        $('body').removeClass('modal-active');
        setTimeout(() => {
            $('#supporters-modal').addClass('hidden').removeClass('flex');
        }, 400);
    }

    // Show Comment/Reply Likers list via AJAX
    window.showCommentLikers = function(commentId, typeLabel) {
        $('#modal-supporters-title').text(typeLabel);
        $('#modal-supporters-count').text(_t.loadingList);
        
        const listContainer = $('#modal-supporters-list');
        listContainer.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.loadingList}</p>`);

        // Open supporters modal
        $('#supporters-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        setTimeout(() => {
            $('#supporters-modal').addClass('modal-show');
        }, 20);

        let url = isGroupSubject ? `/groups/subjects/comments/${commentId}/reactions` : `/comments/${commentId}/reactions`;

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modal-supporters-count').text(`${_t.totalLikes}: ${response.reactions.length}`);
                    listContainer.empty();

                    if (response.reactions.length === 0) {
                        listContainer.html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.noLikesYet}</p>`);
                    } else {
                        response.reactions.forEach(supporter => {
                            const rowHtml = `
                                <div class="flex items-center justify-between border-b border-primary/5 pb-3 last:border-0 last:pb-0">
                                    <div class="flex items-center space-x-3 space-x-reverse">
                                        <img alt="${supporter.user_name}" class="w-10 h-10 rounded-full object-cover border border-outline-variant shrink-0" src="${supporter.profile_picture}">
                                        <div>
                                            <p class="font-body-md text-sm font-bold text-on-surface">${supporter.user_name}</p>
                                            <p class="text-[11px] text-on-surface-variant">${supporter.rank}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2 space-x-reverse">
                                        <span class="text-[10px] text-on-surface-variant">${supporter.created_at}</span>
                                    </div>
                                </div>
                            `;
                            listContainer.append(rowHtml);
                        });
                    }
                } else {
                    listContainer.html(`<p class="text-center text-sm text-error py-8">${_t.errorLoadingLikes}</p>`);
                }
            },
            error: function(xhr) {
                console.error(xhr);
                listContainer.html(`<p class="text-center text-sm text-error py-8">${_t.connectionFailed}</p>`);
            }
        });
    }

    $(document).on('click', '#close-supporters-modal-btn, #supporters-modal .modal-backdrop', function() {
        closeSupportersModal();
    });

    // Toggle follow button in supporters modal (Leftover stub)
    $(document).on('click', '.follow-toggle-btn', function() {
        const btn = $(this);
        const followed = btn.attr('data-followed') === 'true';

        if (followed) {
            btn.attr('data-followed', 'false');
            btn.removeClass('text-secondary').addClass('text-primary');
            btn.find('.material-symbols-outlined').text('person_add');
        } else {
            btn.attr('data-followed', 'true');
            btn.removeClass('text-primary').addClass('text-secondary');
            btn.find('.material-symbols-outlined').text('check_circle');
        }
    });

    // Toggle Nested Replies Display
    $(document).on('click', '#comments-modal .toggle-replies-btn', function() {
        const container = $(this).closest('.comment-card').find('.replies-container');
        container.slideToggle(250).toggleClass('hidden');
    });

    // Toggle Like Comment Action
    $(document).on('click', '#comments-modal .comment-like-action', function() {
        @guest
            if (typeof window.openGuestModal === 'function') {
                window.openGuestModal();
            }
            return;
        @endguest

        const btn = $(this);
        const card = btn.closest('.comment-card');
        const commentId = card.attr('data-comment-id');
        const comment = activeComments.find(c => c.id == commentId);

        if (!comment) return;

        const isLiked = btn.attr('data-active') === 'true';
        const newReactionType = isLiked ? 'remove' : 'like';

        btn.prop('disabled', true);

        let url = isGroupSubject ? `/groups/subjects/comments/${commentId}/react` : `/comments/${commentId}/react`;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reaction_type: newReactionType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    comment.reaction_count = response.reaction_count;
                    comment.user_liked = !isLiked;

                    btn.attr('data-active', (!isLiked).toString());
                    card.find('.comment-likers-trigger .like-count').text(response.reaction_count);
                    
                    if (!isLiked) {
                        btn.addClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').addClass('fill-1 animate-like').one('animationend', function() {
                            $(this).removeClass('animate-like');
                        });
                    } else {
                        btn.removeClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').removeClass('fill-1');
                    }
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert('فشل تحديث الإعجاب بالتعليق.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Show Comment Likers popup list
    $(document).on('click', '#comments-modal .comment-likers-trigger', function() {
        const commentId = $(this).closest('.comment-card').attr('data-comment-id');
        showCommentLikers(commentId, _t.likedByComment);
    });

    // Toggle Like Reply Action
    $(document).on('click', '#comments-modal .reply-like-action', function() {
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
        const comment = activeComments.find(c => c.id == commentId);

        if (!comment || !comment.replies) return;
        const reply = comment.replies.find(r => r.id == replyId);
        if (!reply) return;

        const isLiked = btn.attr('data-active') === 'true';
        const newReactionType = isLiked ? 'remove' : 'like';

        btn.prop('disabled', true);

        let replyUrl = isGroupSubject ? `/groups/subjects/comments/${replyId}/react` : `/comments/${replyId}/react`;

        $.ajax({
            url: replyUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reaction_type: newReactionType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    reply.reaction_count = response.reaction_count;
                    reply.user_liked = !isLiked;

                    btn.attr('data-active', (!isLiked).toString());
                    replyCard.find('.reply-likers-trigger .like-count').text(response.reaction_count);
                    
                    if (!isLiked) {
                        btn.addClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').addClass('fill-1 animate-like').one('animationend', function() {
                            $(this).removeClass('animate-like');
                        });
                    } else {
                        btn.removeClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').removeClass('fill-1');
                    }
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert('فشل تحديث الإعجاب بالرد.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Show Reply Likers popup list
    $(document).on('click', '#comments-modal .reply-likers-trigger', function() {
        const replyId = $(this).closest('.reply-card').attr('data-reply-id');
        showCommentLikers(replyId, _t.likedByReply);
    });

    // Toggle Support Post Action (with bulb glow animation)
    $(document).on('click', '.post-support-action', function(e) {
        e.preventDefault();
        e.stopPropagation();
        @guest
            if (typeof window.openGuestModal === 'function') {
                window.openGuestModal();
            } else {
                alert('يرجى تسجيل الدخول أو إنشاء حساب جديد لتتمكن من المشاركة في الموقع.');
            }
            return false;
        @endguest

        const btn = $(this);
        const postId = btn.attr('data-post-id');
        const isActive = btn.attr('data-active') === 'true';
        const newReactionType = isActive ? 'remove' : 'like';

        btn.prop('disabled', true);

        $.ajax({
            url: `/posts/${postId}/react`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reaction_type: newReactionType
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    btn.attr('data-active', (!isActive).toString());
                    
                    // Update text inside open-supporters-btn trigger
                    const container = btn.parent();
                    container.find('.open-supporters-btn .support-count').text(response.like_count);
                    container.find('.open-supporters-btn').attr('data-total-supports', response.like_count);

                    if (!isActive) {
                        btn.addClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').addClass('fill-1 animate-bulb').one('animationend', function() {
                            $(this).removeClass('animate-bulb');
                        });
                    } else {
                        btn.removeClass('text-primary bg-primary/10');
                        btn.find('.material-symbols-outlined').removeClass('fill-1');
                    }
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert('فشل تحديث تأييد المنشور.');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Add New Comment Form Handler
    $(document).on('submit', '#new-comment-form', function(e) {
        e.preventDefault();
        const input = $('#new-comment-input');
        const text = input.val().trim();
        if (!text) return;

        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        input.prop('disabled', true);
        submitBtn.prop('disabled', true);

        let url = isGroupSubject ? `/groups/subjects/${activePostId}/comments` : `/posts/${activePostId}/comments`;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                content: text
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    activeComments.push(response.comment);
                    input.val('');
                    
                    // Re-render Comments List and scroll down smoothly
                    renderCommentsList(activeComments);
                    const list = $('#modal-comments-list');
                    list.animate({ scrollTop: list[0].scrollHeight }, 400);

                    // Update display counter on original post page
                    const countSpan = $(`.open-discussion-btn[data-post-id="${activePostId}"] .font-label-sm`);
                    const currentCount = parseInt(countSpan.text());
                    if (!isNaN(currentCount)) {
                        countSpan.text((currentCount + 1) + ' نقاش');
                    }
                } else {
                    alert(response.message || 'حدث خطأ أثناء إضافة التعليق.');
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert(xhr.responseJSON?.message || 'فشل إضافة التعليق. يرجى التحقق من تسجيل الدخول.');
            },
            complete: function() {
                input.prop('disabled', false);
                submitBtn.prop('disabled', false);
                input.focus();
            }
        });
    });

    // Add New Nested Reply Handler
    $(document).on('submit', '#comments-modal .new-reply-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const input = form.find('.new-reply-input');
        const text = input.val().trim();
        if (!text) return;

        const card = form.closest('.comment-card');
        const commentId = card.attr('data-comment-id');
        
        const submitBtn = form.find('button[type="submit"]');
        input.prop('disabled', true);
        submitBtn.prop('disabled', true);

        let replyUrl = isGroupSubject ? `/groups/subjects/${activePostId}/comments` : `/posts/${activePostId}/comments`;

        $.ajax({
            url: replyUrl,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                content: text,
                parent_id: commentId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const comment = activeComments.find(c => c.id == commentId);
                    if (comment) {
                        if (!comment.replies) comment.replies = [];
                        comment.replies.push(response.comment);
                        
                        input.val('');
                        
                        // Re-render replies
                        card.find('.replies-list').html(renderReplies(comment.replies));
                        card.find('.replies-count').text(`${_t.repliesLabel} (${comment.replies.length})`);
                    }
                } else {
                    alert(response.message || 'حدث خطأ أثناء إضافة الرد.');
                }
            },
            error: function(xhr) {
                console.error(xhr);
                alert(xhr.responseJSON?.message || 'فشل إضافة الرد.');
            },
            complete: function() {
                input.prop('disabled', false);
                submitBtn.prop('disabled', false);
                input.focus();
            }
        });
    });

    // Delete Comment Action
    $(document).on('click', '#comments-modal .delete-comment-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const card = btn.closest('.comment-card');
        const commentId = btn.attr('data-comment-id');

        let url = isGroupSubject ? `/groups/subjects/comments/${commentId}/delete` : `/comments/${commentId}/delete`;

        if (typeof window.openDeleteActionModal === 'function') {
            window.openDeleteActionModal(
                'حذف التعليق',
                'هل أنت متأكد من رغبتك في حذف هذا التعليق نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.',
                function(done, fail) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                done();
                                card.slideUp(300, function() {
                                    $(this).remove();
                                    activeComments = activeComments.filter(c => c.id != commentId);
                                    if (activeComments.length === 0) {
                                        $('#modal-comments-list').html(`<p class="text-center text-sm text-on-surface-variant py-8">${_t.noDiscussionsYet}</p>`);
                                    }
                                });

                                // Update comment count on post card
                                if (activePostId) {
                                    const countSpan = $(`.open-discussion-btn[data-post-id="${activePostId}"] .font-label-sm`);
                                    if (countSpan.length) {
                                        let currentCount = parseInt(countSpan.text());
                                        if (!isNaN(currentCount) && currentCount > 0) {
                                            countSpan.text((currentCount - 1) + ' نقاش');
                                        }
                                    }
                                }
                            } else {
                                alert(response.message || 'فشل حذف التعليق.');
                                fail();
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('حدث خطأ أثناء حذف التعليق.');
                            fail();
                        }
                    });
                }
            );
        }
    });

    // Delete Reply Action
    $(document).on('click', '#comments-modal .delete-reply-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const replyCard = btn.closest('.reply-card');
        const commentCard = btn.closest('.comment-card');
        const replyId = btn.attr('data-reply-id');

        let url = isGroupSubject ? `/groups/subjects/comments/${replyId}/delete` : `/comments/${replyId}/delete`;

        if (typeof window.openDeleteActionModal === 'function') {
            window.openDeleteActionModal(
                'حذف الرد',
                'هل أنت متأكد من رغبتك في حذف هذا الرد نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.',
                function(done, fail) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                done();
                                replyCard.slideUp(300, function() {
                                    $(this).remove();
                                    const commentId = commentCard.attr('data-comment-id');
                                    const comment = activeComments.find(c => c.id == commentId);
                                    if (comment && comment.replies) {
                                        comment.replies = comment.replies.filter(r => r.id != replyId);
                                        commentCard.find('.replies-count').text(`${_t.repliesLabel} (${comment.replies.length})`);
                                    }
                                });
                            } else {
                                alert(response.message || 'فشل حذف الرد.');
                                fail();
                            }
                        },
                        error: function(xhr) {
                            console.error(xhr);
                            alert('حدث خطأ أثناء حذف الرد.');
                            fail();
                        }
                    });
                }
            );
        }
    });

    // --- Image Viewer Modal Handlers ---
    const imageModal = $('#image-viewer-modal');
    const imageElement = $('#viewer-image-element');

    function openImageModal(imgSrc) {
        imageElement.attr('src', imgSrc);
        imageModal.removeClass('hidden').addClass('flex');
        setTimeout(() => {
            imageModal.addClass('modal-show');
        }, 20);
    }

    function closeImageModal() {
        imageModal.removeClass('modal-show');
        setTimeout(() => {
            imageModal.removeClass('flex').addClass('hidden');
            imageElement.attr('src', '');
        }, 300);
    }

    $(document).on('click', '.post-image-trigger', function(e) {
        e.preventDefault();
        const imgSrc = $(this).attr('data-image-src');
        if (imgSrc) {
            openImageModal(imgSrc);
        }
    });

    $(document).on('click', '#close-image-viewer-btn, #image-viewer-modal .modal-backdrop', function(e) {
        closeImageModal();
    });

    // --- Video Viewer Lightbox Handlers (Global) ---
    const videoModal = $('#video-viewer-modal');
    const videoPlayer = $('#viewer-video-player');

    $(document).on('click', '.video-preview-trigger', function(e) {
        e.preventDefault();
        const videoSrc = $(this).attr('data-video-src');
        if (videoSrc) {
            videoPlayer.find('source').attr('src', videoSrc);
            videoPlayer[0].load(); // Load the new video source
            
            videoModal.removeClass('hidden').addClass('flex');
            $('body').addClass('modal-active');
            
            setTimeout(() => {
                videoModal.addClass('modal-show');
                videoPlayer[0].play(); // Autoplay
            }, 20);
        }
    });

    function closeVideoViewer() {
        if (videoPlayer.length && videoPlayer[0]) {
            videoPlayer[0].pause(); // Pause
        }
        videoModal.removeClass('modal-show');
        $('body').removeClass('modal-active');
        setTimeout(() => {
            videoModal.addClass('hidden').removeClass('flex');
            videoPlayer.find('source').attr('src', ''); // Clear source
        }, 300);
    }

    $(document).on('click', '#close-video-viewer-btn, #video-viewer-modal .modal-backdrop', function(e) {
        closeVideoViewer();
    });

    // Close on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (!videoModal.hasClass('hidden')) {
                closeVideoViewer();
            }
            if (!imageModal.hasClass('hidden')) {
                closeImageModal();
            }
        }
    });
});
</script>
@endpush
