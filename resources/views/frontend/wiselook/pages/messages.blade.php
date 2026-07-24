@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
    $borderSide = $dir === 'rtl' ? 'border-l' : 'border-r';
@endphp
<!-- Chat Shell Wrapper with Padding Top to avoid Header overlap -->
<div class="pt-20 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-12 {{ $textAlign }}" style="direction: {{ $dir }};">
    
    <!-- Chat Container Card: Flex layout in RTL renders right-to-left -->
    <div class="flex flex-row h-[calc(100vh-140px)] min-h-[450px] w-full overflow-hidden bg-white rounded-2xl border border-primary/10 shadow-sm" style="direction: {{ $dir }};">
        
        <!-- Backdrop Overlay for Mobile Chat Sidebar -->
        <div id="chat-sidebar-backdrop" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 hidden opacity-0 transition-opacity duration-300 lg:hidden"></div>

        <!-- Conversations Sidebar (Right side in RTL) -->
        <aside id="chat-sidebar" class="bg-surface-container-low/80 {{ $borderSide }} border-primary/5 w-80 shrink-0 hidden lg:flex flex-col h-full {{ $textAlign }}">
            <!-- Sidebar Header & Add Button -->
            <div class="p-4 border-b border-primary/5 shrink-0">
                <div class="flex items-center justify-between mb-4">
                    <div class="{{ $textAlign }}">
                        <h2 class="font-title-lg text-base font-bold text-primary">التواصل المباشر</h2>
                        <p class="font-label-sm text-[10px] text-on-surface-variant">{{ __t('direct_messages') }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button id="open-search-modal-btn" class="bg-primary text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-primary-container transition-colors shadow-sm cursor-pointer border-none" title="{{ __t('start_new_chat') }}">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                        </button>
                        <button type="button" id="close-chat-sidebar-btn" class="lg:hidden text-on-surface-variant hover:text-error p-1 rounded-full transition-colors cursor-pointer border-none bg-transparent flex items-center justify-center" title="إغلاق">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>
                </div>
                <!-- Sub-navigation Tabs -->
                <nav class="flex gap-2 overflow-x-auto scrollbar-none mb-3">
                    <button id="tab-messages-btn" class="tab-btn bg-primary text-white rounded-lg font-bold px-3 py-1.5 flex items-center gap-1 shrink-0 text-xs shadow-sm cursor-pointer border-none">
                        <span class="material-symbols-outlined text-sm">chat</span> {{ __t('messages_tab') }}
                    </button>
                    <button id="tab-groups-btn" class="tab-btn text-on-surface-variant hover:bg-surface-container-high px-3 py-1.5 rounded-lg flex items-center gap-1 shrink-0 text-xs transition-colors cursor-pointer border-none">
                        <span class="material-symbols-outlined text-sm">group</span> {{ __t('groups_tab') }}
                    </button>
                </nav>
            </div>

            <!-- Conversations List (Scrollable) -->
            <div class="flex-1 overflow-y-auto py-2 space-y-1 scrollbar-none" id="conversations-list">
                <!-- DM List Container -->
                <div id="dm-conversations-container" class="space-y-1">
                    @forelse($chatUsers as $user)
                        <div class="conversation-item relative flex items-center gap-3 p-3 mx-2 rounded-xl transition-colors cursor-pointer {{ $activeUser && $activeUser->id == $user->id ? 'bg-white border border-primary/10 shadow-sm active-chat' : 'hover:bg-white/40' }}" 
                             data-user-id="{{ $user->id }}"
                             data-user-name="{{ $user->first_name }} {{ $user->last_name }}"
                             data-user-avatar="{{ $user->avatar_url }}">
                            <div class="relative shrink-0">
                                <div class="w-11 h-11 rounded-full overflow-hidden border border-outline-variant">
                                    <img alt="{{ $user->first_name }}" class="w-full h-full object-cover" src="{{ $user->avatar_url }}">
                                </div>
                                <div class="status-indicator absolute top-0 right-0 w-3 h-3 bg-secondary rounded-full border-2 border-white z-10"></div>
                            </div>
                            <div class="flex-grow min-w-0 {{ $textAlign }}">
                                <h3 class="font-title-lg text-xs font-bold text-primary truncate mb-0.5">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                <p class="last-message-text font-body-md text-[11px] text-on-surface-variant truncate leading-normal">
                                    {{ $user->last_message ? $user->last_message->message : __t('no_messages_yet') }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end justify-between shrink-0 self-stretch {{ $textAlignInverse }} select-none">
                                <span class="last-message-time font-label-sm text-[9px] text-on-surface-variant whitespace-nowrap">
                                    @if($user->last_message)
                                        {{ $user->last_message->created_at->diffForHumans() }}
                                    @endif
                                </span>
                                @php
                                    $unreadCount = $user->unread_messages_count ?? 0;
                                @endphp
                                <span class="unread-badge bg-primary text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full mt-1.5 {{ $unreadCount > 0 ? '' : 'hidden' }} shrink-0 shadow-sm border border-white leading-none">
                                    {{ $unreadCount }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div id="no-conversations-placeholder" class="p-6 text-center text-on-surface-variant text-xs">
                            {{ __t('no_active_conversations') }}
                        </div>
                    @endforelse

                    <!-- Lazy load sentinel and spinner -->
                    <div id="contacts-sentinel" class="h-4"></div>
                    <div id="contacts-loading-spinner" class="hidden justify-center items-center py-3">
                        <div class="w-5 h-5 border-2 border-primary/30 border-t-primary rounded-full animate-spin"></div>
                    </div>
                </div>

                <!-- Group List Container -->
                <div id="group-conversations-container" class="space-y-1 hidden">
                    <!-- Create Group Action Button -->
                    <div class="p-2 mx-2">
                        <button id="open-create-group-modal-btn" class="w-full bg-primary hover:bg-primary/90 text-white rounded-xl py-2.5 px-4 font-bold text-xs flex items-center justify-center gap-2 cursor-pointer border-none shadow-sm transition-all">
                            <span class="material-symbols-outlined text-sm">group_add</span>
                            <span>{{ __t('create_new_group') }}</span>
                        </button>
                    </div>
                    <div id="groups-list-wrapper" class="space-y-1">
                        <!-- Loaded Dynamically -->
                    </div>
                </div>
            </div>

            <!-- Sidebar footer help link -->
            <div class="p-4 border-t border-primary/5 shrink-0 mt-auto">
                <button class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors text-xs font-bold w-full cursor-pointer justify-start bg-transparent border-none">
                    <span class="material-symbols-outlined text-[18px]">help_outline</span>
                    <span>{{ __t('help_and_support') }}</span>
                </button>
            </div>
        </aside>

        <!-- Main Message Thread area (Left side in RTL) -->
        <main class="flex-1 flex flex-col bg-[#f8faf5] h-full relative {{ $activeUser ? '' : 'hidden' }}" id="chat-main-panel">
                <!-- Message Panel Header -->
                <div class="h-16 border-b border-primary/5 bg-white px-3 md:px-6 flex items-center justify-between shrink-0 shadow-sm z-10" style="direction: {{ $dir }};">
                    <div class="flex items-center gap-2.5 {{ $textAlign }}">
                        <!-- Mobile Sidebar Toggle Button -->
                        <button type="button" id="toggle-chat-sidebar-btn" class="lg:hidden text-primary hover:bg-primary/10 py-1.5 px-2.5 rounded-xl transition-all flex items-center gap-1.5 font-bold text-xs cursor-pointer border-none bg-primary/5 shrink-0" title="المحادثات">
                            <span class="material-symbols-outlined text-[20px]">forum</span>
                            <span class="text-[11px] font-bold">المحادثات</span>
                        </button>

                        <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-outline-variant">
                            <img id="active-user-avatar" alt="{{ $activeUser ? $activeUser->first_name : '' }}" class="w-full h-full object-cover" src="{{ $activeUser ? $activeUser->avatar_url : asset('upload/no_image.jpg') }}">
                        </div>
                        <div>
                            <h2 id="active-user-name" class="font-title-lg text-xs font-bold text-primary leading-tight">{{ $activeUser ? ($activeUser->first_name . ' ' . $activeUser->last_name) : '' }}</h2>
                            <p class="font-label-sm text-[9px] text-secondary flex items-center gap-1 mt-0.5 {{ $textAlign === 'text-right' ? 'justify-start' : 'justify-end' }}" id="active-user-status-container">
                                <span class="w-1.5 h-1.5 bg-secondary rounded-full inline-block" id="active-user-status-dot"></span> 
                                <span id="active-user-status-text">{{ __t('online_now') }}</span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button id="toggle-message-search-btn" class="w-9 h-9 rounded-full border border-primary/10 flex items-center justify-center text-primary hover:bg-primary/5 transition-all cursor-pointer bg-transparent" title="{{ __t('search_messages_title') }}">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                        </button>
                        <button id="call-btn" class="w-9 h-9 rounded-full border border-primary/10 flex items-center justify-center text-primary hover:bg-primary/5 transition-all cursor-pointer bg-transparent" title="{{ __t('direct_call_title') }}">
                            <span class="material-symbols-outlined text-[18px]">call</span>
                        </button>
                        <button id="group-info-btn" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-low transition-colors cursor-pointer bg-transparent border-none hidden" title="{{ __t('group_info_title') }}">
                            <span class="material-symbols-outlined text-[18px]">info</span>
                        </button>
                    </div>
                </div>

                <!-- Message Search Bar (Hidden by default, slides down) -->
                <div id="message-search-container" class="hidden items-center justify-between gap-3 p-3 bg-white border-b border-primary/5 shadow-inner transition-all duration-300 relative" style="direction: {{ $dir }};">
                    <div class="flex-1 flex items-center gap-2 bg-[#f2f4f0]/60 rounded-full px-3 py-1.5 border border-primary/10 focus-within:border-primary/30 transition-all">
                        <span class="material-symbols-outlined text-[18px] text-outline">search</span>
                        <input type="text" id="message-search-input" class="w-full bg-transparent border-none focus:ring-0 focus:outline-none text-xs text-on-surface" placeholder="{{ __t('search_messages_placeholder') }}">
                    </div>
                    <!-- Close search bar -->
                    <button id="close-message-search-btn" class="w-7 h-7 rounded-full flex items-center justify-center hover:bg-slate-100 text-on-surface-variant cursor-pointer border-none shrink-0" title="{{ __t('close_search') }}">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                    
                    <!-- Search Results Dropdown -->
                    <div id="search-results-dropdown" class="hidden absolute left-3 right-3 top-full bg-white border border-primary/10 rounded-b-xl shadow-lg z-30 max-h-60 overflow-y-auto p-2">
                        <!-- Results will be loaded here -->
                    </div>
                </div>

                <!-- Scrollable Message List -->
                <div class="flex-1 overflow-y-auto p-6 flex flex-col gap-6 scrollbar-none" id="messages-container">
                    <!-- Loading Spinner (Hidden by default) -->
                    <div id="messages-loading" class="hidden flex justify-center items-center py-4">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                    </div>

                    <!-- Lazy loading spinner (Hidden by default) -->
                    <div id="lazy-loading-spinner" class="hidden flex justify-center items-center py-2 shrink-0">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-primary border-t-transparent"></div>
                    </div>

                    <!-- Messages list will be populated dynamically -->
                    <div id="messages-list-wrapper" class="flex flex-col gap-6">
                        <!-- Messages go here -->
                    </div>
                </div>

                <!-- Upload Progress Bar Container (Hidden by default) -->
                <div id="upload-progress-container" class="hidden flex-col gap-1.5 p-3 bg-white border-t border-primary/5 shrink-0 z-20" style="direction: {{ $dir }};">
                    <div class="max-w-3xl mx-auto w-full flex justify-between items-center text-[10px] font-bold text-primary">
                        <span id="upload-status-text">جاري رفع الملف...</span>
                        <span id="upload-progress-percent">0%</span>
                    </div>
                    <div class="max-w-3xl mx-auto w-full bg-[#f2f4f0] rounded-full h-1.5 overflow-hidden">
                        <div id="upload-progress-bar" class="bg-primary h-full w-0 transition-all duration-150"></div>
                    </div>
                </div>

                <!-- Chat Input Form (Bottom) -->
                <div class="bg-white border-t border-primary/5 p-4 shrink-0 shadow-sm z-10" style="direction: {{ $dir }};">
                    <div class="max-w-3xl mx-auto flex flex-col bg-[#f2f4f0]/60 rounded-2xl p-2 border border-primary/10 focus-within:border-primary/30 transition-all">
                        <!-- Reply Preview Bar (Hidden by default) -->
                        <div id="reply-preview-container" class="hidden items-center justify-between gap-3 p-3 bg-white/80 rounded-t-xl border-b border-primary/5 mb-2">
                            <div class="flex items-center gap-2 {{ $dir === 'rtl' ? 'border-r-4 pr-3' : 'border-l-4 pl-3' }} border-primary min-w-0">
                                <div class="flex flex-col min-w-0 {{ $textAlign }}">
                                    <span class="text-[10px] font-bold text-primary" id="reply-sender-name">{{ __t('username_placeholder') }}</span>
                                    <span class="text-xs text-on-surface-variant truncate" id="reply-message-text">{{ __t('message_content_placeholder') }}</span>
                                </div>
                            </div>
                            <button id="cancel-reply-btn" class="w-7 h-7 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-slate-200 transition-colors cursor-pointer bg-transparent border-none shrink-0">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                        </div>
                        <!-- Media Preview Container (Hidden by default) -->
                        <div id="media-preview-container" class="hidden items-center gap-3 p-2 mb-2 bg-white rounded-xl border border-primary/5 w-fit relative">
                            <!-- Image Preview -->
                            <img id="image-preview" src="" class="hidden h-16 w-16 object-cover rounded-lg border border-outline-variant">
                            
                            <!-- Video Preview -->
                            <div id="video-preview-wrapper" class="hidden h-16 w-24 relative rounded-lg border border-outline-variant bg-black overflow-hidden">
                                <video id="video-preview" src="" class="h-full w-full object-cover"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                                    <span class="material-symbols-outlined text-white text-[16px]">play_circle</span>
                                </div>
                            </div>

                            <button id="remove-media-btn" class="absolute -top-2 -left-2 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600 transition-colors flex items-center justify-center cursor-pointer border-none h-5 w-5 z-10">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                        </div>
                        
                        <!-- Audio Recording Bar (Hidden by default) -->
                        <div id="audio-recording-container" class="hidden items-center justify-between gap-4 w-full py-1">
                            <div class="flex items-center gap-2 text-red-500 font-bold text-xs shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 animate-pulse"></span>
                                <span id="recording-timer">00:00</span>
                            </div>
                            
                            <!-- Voice Wave Canvas -->
                            <div class="flex-1 h-8 flex items-center bg-[#f2f4f0]/30 rounded-xl px-2 overflow-hidden">
                                <canvas id="audio-wave-canvas" class="w-full h-full"></canvas>
                            </div>
                            
                            <div class="flex items-center gap-2 shrink-0">
                                <button id="cancel-record-btn" class="w-9 h-9 rounded-full flex items-center justify-center text-red-500 hover:bg-red-50 transition-colors cursor-pointer bg-transparent border-none" title="{{ __t('cancel_recording') }}">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                                <button id="stop-send-record-btn" class="w-9 h-9 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-container transition-all shadow-md cursor-pointer border-none" title="{{ __t('stop_and_send') }}">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                </button>
                            </div>
                        </div>

                        <div id="regular-input-row" class="flex items-end gap-3 w-full">
                            <div class="flex-1 min-h-[38px] flex items-center">
                                <textarea id="message-textarea" class="w-full bg-transparent border-none focus:ring-0 focus:outline-none resize-none text-xs text-on-surface placeholder:text-outline py-2 px-1 max-h-24 scrollbar-none" placeholder="{{ __t('type_message_placeholder') }}" rows="1"></textarea>
                            </div>
                            
                            <div class="flex items-center gap-1.5 mb-1 shrink-0">
                                <button id="image-select-btn" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface hover:text-primary transition-colors cursor-pointer bg-transparent border-none" title="{{ __t('send_image_title') }}">
                                    <span class="material-symbols-outlined text-[20px]">image</span>
                                </button>
                                <input type="file" id="image-upload-input" accept="image/*" class="hidden">
                                
                                <button id="video-select-btn" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface hover:text-primary transition-colors cursor-pointer bg-transparent border-none" title="{{ __t('send_video_title') }}">
                                    <span class="material-symbols-outlined text-[20px]">videocam</span>
                                </button>
                                <input type="file" id="video-upload-input" accept="video/*" class="hidden">
                                
                                <button id="mic-btn" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface hover:text-primary transition-colors cursor-pointer bg-transparent border-none" title="{{ __t('send_voice_title') }}">
                                    <span class="material-symbols-outlined text-[20px]">mic</span>
                                </button>
                                <button id="send-message-btn" class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center hover:bg-primary-container transition-all shadow-md shrink-0 {{ $dir === 'rtl' ? 'mr-1' : 'ml-1' }} cursor-pointer border-none">
                                    <span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">send</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
        </main>

        <!-- Empty State (Shown when no active chat is selected or exists) -->
        <div class="flex-1 flex flex-col bg-[#f8faf5] h-full relative items-center justify-center p-8 text-center {{ $activeUser ? 'hidden' : '' }}" id="chat-empty-state" style="direction: {{ $dir }};">
            <div class="w-20 h-20 rounded-full bg-primary/5 flex items-center justify-center text-primary mb-4 border border-primary/10">
                <span class="material-symbols-outlined text-[40px]">forum</span>
            </div>
            <h2 class="text-lg font-bold text-primary mb-2">مرحباً بك في التواصل المباشر</h2>
            <p class="text-xs text-on-surface-variant max-w-sm leading-relaxed mb-6">{{ __t('welcome_wisdom_council_desc') }}</p>
            <div class="flex flex-col sm:flex-row gap-2.5 items-center justify-center">
                <button id="start-chat-btn" class="bg-primary text-white rounded-xl px-5 py-2.5 font-bold text-xs hover:bg-primary-container transition-all shadow-md flex items-center gap-2 cursor-pointer border-none">
                    <span class="material-symbols-outlined text-sm">chat</span>
                    <span>{{ __t('start_new_chat') }}</span>
                </button>
                <button id="mobile-empty-show-sidebar-btn" class="lg:hidden bg-primary/10 text-primary hover:bg-primary/20 rounded-xl px-5 py-2.5 font-bold text-xs transition-all flex items-center gap-2 cursor-pointer border border-primary/20">
                    <span class="material-symbols-outlined text-sm">forum</span>
                    <span>عرض قائمة المحادثات</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Group Info Modal -->
<div id="group-info-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop with high-end glassmorphism -->
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container (Premium Glassmorphism Design) -->
    <div class="modal-container relative max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 {{ $textAlign }} overflow-hidden" style="direction: {{ $dir }};">
        <!-- Ambient Glow Effects inside Modal -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/15 rounded-full blur-3xl pointer-events-none"></div>
        
        <!-- Close Button -->
        <button id="close-group-info-modal-btn" type="button" class="absolute top-5 {{ $dir === 'rtl' ? 'left-5' : 'right-5' }} text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
        
        <!-- Header -->
        <div class="mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shrink-0 border border-primary/10">
                <span class="material-symbols-outlined text-[24px]">info</span>
            </div>
            <div class="{{ $textAlign }}">
                <h3 class="font-headline-lg text-base font-extrabold text-primary leading-tight">{{ __t('group_details_title') }}</h3>
                <p class="font-body-md text-[11px] text-on-surface-variant/80 mt-0.5">{{ __t('group_details_desc') }}</p>
            </div>
        </div>
        
        <!-- Group Identity Profile -->
        <div class="flex flex-col items-center justify-center gap-2 mb-4 border-b border-primary/5 pb-4">
            <div class="w-20 h-20 rounded-full border border-primary/10 overflow-hidden bg-slate-50 flex items-center justify-center shadow-md">
                <img id="info-group-avatar" src="{{ asset('upload/no_image.jpg') }}" class="w-full h-full object-cover">
            </div>
            <h4 id="info-group-name" class="text-sm font-extrabold text-primary mt-1">{{ __t('group_name_label') }}</h4>
            <p id="info-group-desc" class="text-xs text-on-surface-variant text-center max-w-[280px] leading-relaxed">{{ __t('group_desc_placeholder') }}</p>
        </div>

        <!-- Members Section -->
        <div class="space-y-2">
            <div class="flex items-center justify-between px-1">
                <span class="text-[11px] font-bold text-primary">{{ __t('group_members_label') }} (<span id="info-group-members-count">0</span>)</span>
            </div>
            <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 scrollbar-none border border-primary/5 rounded-2xl p-2.5 bg-slate-50/50" id="info-group-members-list">
                <!-- Loaded Dynamically -->
            </div>
        </div>

        <!-- Action Footer Buttons -->
        <div class="pt-4 border-t border-primary/5 mt-4 flex items-center gap-3" id="info-group-actions-container">
            <!-- Populated dynamically via JS (Delete Group / Leave Group) -->
        </div>
    </div>
</div>

<!-- Create Group Modal -->
<div id="create-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop with high-end glassmorphism -->
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container (Premium Glassmorphism Design) -->
    <div class="modal-container relative max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 {{ $textAlign }} overflow-hidden" style="direction: {{ $dir }};">
        <!-- Ambient Glow Effects inside Modal -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/15 rounded-full blur-3xl pointer-events-none"></div>
        
        <!-- Close Button -->
        <button id="close-create-group-modal-btn" type="button" class="absolute top-5 {{ $dir === 'rtl' ? 'left-5' : 'right-5' }} text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
        
        <!-- Header -->
        <div class="mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shrink-0 border border-primary/10">
                <span class="material-symbols-outlined text-[24px]">group_add</span>
            </div>
            <div class="{{ $textAlign }}">
                <h3 class="font-headline-lg text-base font-extrabold text-primary leading-tight">{{ __t('create_new_group') }}</h3>
                <p class="font-body-md text-[11px] text-on-surface-variant/80 mt-0.5">{{ __t('create_new_group_desc') }}</p>
            </div>
        </div>
        
        <form id="create-group-form" enctype="multipart/form-data" class="space-y-4">
            <!-- Group Image Preview & Selector -->
            <div class="flex flex-col items-center justify-center gap-2 mb-2">
                <div class="relative w-20 h-20 rounded-full border border-primary/10 overflow-hidden bg-slate-50 flex items-center justify-center shadow-inner group">
                    <img id="group-image-preview" src="{{ asset('upload/no_image.jpg') }}" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer" onclick="$('#group-image-input').click()">
                        <span class="material-symbols-outlined text-white text-[20px]">photo_camera</span>
                    </div>
                </div>
                <button type="button" onclick="$('#group-image-input').click()" class="text-primary text-[11px] font-bold bg-transparent border-none cursor-pointer">{{ __t('choose_group_image') }}</button>
                <input id="group-image-input" type="file" name="image" accept="image/*" class="hidden">
            </div>

            <!-- Group Name Field -->
            <div class="space-y-1 {{ $textAlign }}">
                <label class="text-[11px] font-bold text-primary block">{{ __t('group_name_label') }}</label>
                <div class="relative w-full rounded-2xl bg-surface-container-low border border-primary/10 p-1.5 flex items-center gap-2">
                    <input id="group-name-input" type="text" name="name" placeholder="{{ __t('type_group_name') }}" required class="flex-1 bg-transparent py-2 px-3 font-body-md text-xs text-on-surface focus:outline-none border-none">
                </div>
            </div>

            <!-- Group Descriptions Field -->
            <div class="space-y-1 {{ $textAlign }}">
                <label class="text-[11px] font-bold text-primary block">{{ __t('group_desc_label') }}</label>
                <div class="relative w-full rounded-2xl bg-surface-container-low border border-primary/10 p-1.5 flex items-center gap-2">
                    <textarea id="group-desc-input" name="descriptions" placeholder="{{ __t('type_group_desc') }}" rows="2" class="flex-1 bg-transparent py-2 px-3 font-body-md text-xs text-on-surface focus:outline-none border-none resize-none"></textarea>
                </div>
            </div>

            <!-- Friends Selector (Multi-select) -->
            <div class="space-y-1 {{ $textAlign }}">
                <label class="text-[11px] font-bold text-primary block font-bold">{{ __t('choose_members_to_add') }}</label>
                <div class="relative w-full rounded-2xl bg-surface-container-low border border-primary/10 p-1.5 flex items-center gap-2 mb-2">
                    <span class="material-symbols-outlined text-[18px] text-on-surface-variant mr-1">search</span>
                    <input id="group-friend-search" type="text" placeholder="{{ __t('search_friend_placeholder') }}" class="flex-1 bg-transparent py-1 px-1 font-body-md text-[11px] text-on-surface-variant focus:outline-none border-none">
                </div>
                <div class="max-h-40 overflow-y-auto space-y-1 pr-1 scrollbar-none border border-primary/5 rounded-2xl p-2 bg-slate-50/50" id="group-friends-list">
                    <!-- List of selectable friends -->
                    @forelse($chatUsers as $friend)
                        <div class="group-friend-item flex items-center justify-between p-2 rounded-xl hover:bg-primary/5 transition-colors cursor-pointer" data-id="{{ $friend->id }}" data-name="{{ $friend->first_name }} {{ $friend->last_name }}">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full overflow-hidden border border-outline-variant">
                                    <img alt="{{ $friend->first_name }}" class="w-full h-full object-cover" src="{{ $friend->avatar_url }}">
                                </div>
                                <span class="text-xs font-bold text-on-surface">{{ $friend->first_name }} {{ $friend->last_name }}</span>
                            </div>
                            <input type="checkbox" name="members[]" value="{{ $friend->id }}" class="group-member-checkbox rounded border-primary/20 text-primary focus:ring-primary/30 w-4 h-4 cursor-pointer">
                        </div>
                    @empty
                        <div class="text-center py-4 text-on-surface-variant text-[11px]">{{ __t('no_active_friends_to_select') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" id="submit-create-group-btn" class="w-full bg-primary hover:bg-primary/95 text-white font-bold text-xs py-3 px-4 rounded-2xl shadow-md cursor-pointer border-none flex items-center justify-center gap-2 transition-all">
                    <span class="material-symbols-outlined text-sm">check</span>
                    <span>{{ __t('create_group_submit') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Search / Start Conversation Modal -->
<div id="search-chat-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop with high-end glassmorphism -->
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container (Premium Glassmorphism Design) -->
    <div class="modal-container relative max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 {{ $textAlign }} overflow-hidden" style="direction: {{ $dir }};">
        <!-- Ambient Glow Effects inside Modal -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/15 rounded-full blur-3xl pointer-events-none"></div>
        
        <!-- Close Button -->
        <button id="close-search-chat-modal-btn" class="absolute top-5 {{ $dir === 'rtl' ? 'left-5' : 'right-5' }} text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
            <span class="material-symbols-outlined text-[20px]">close</span>
        </button>
        
        <!-- Header -->
        <div class="mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shrink-0 border border-primary/10">
                <span class="material-symbols-outlined text-[24px]">chat_bubble</span>
            </div>
            <div class="{{ $textAlign }}">
                <h3 class="font-headline-lg text-base font-extrabold text-primary leading-tight">{{ __t('start_new_chat') }}</h3>
                <p class="font-body-md text-[11px] text-on-surface-variant/80 mt-0.5">{{ __t('choose_friend_chat_desc') }}</p>
            </div>
        </div>
        
        <!-- Search Field -->
        <div class="relative w-full rounded-2xl bg-surface-container-low border border-primary/10 p-1.5 flex items-center justify-between gap-2 mb-4">
            <span class="material-symbols-outlined text-[20px] text-on-surface-variant mr-2">search</span>
            <input id="friend-search-input" type="text" placeholder="{{ __t('search_friend_by_name') }}" class="flex-1 bg-transparent py-2 px-1 font-body-md text-xs text-on-surface-variant/90 focus:outline-none border-none">
        </div>
        
        <!-- Friends List container -->
        <div class="max-h-60 overflow-y-auto space-y-1 scrollbar-none" id="friends-search-results">
            <div class="text-center py-4 text-on-surface-variant text-xs">{{ __t('loading_friends') }}</div>
        </div>
    </div>
</div>

<!-- Image Lightbox Popup Modal -->
<!-- Delete Message Confirmation Modal -->
<div id="delete-message-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-black/40 backdrop-blur-sm" id="delete-modal-backdrop"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 text-center z-10 flex flex-col gap-4" style="direction: {{ $dir }};">
        <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto">
            <span class="material-symbols-outlined text-red-500 text-[32px]" style="font-variation-settings:'FILL' 1;">delete</span>
        </div>
        <div>
            <h3 class="text-sm font-bold text-on-surface mb-1">{{ __t('delete_message_title') }}</h3>
            <p class="text-xs text-on-surface-variant">{{ __t('delete_message_confirm_msg') }}</p>
        </div>
        <input type="hidden" id="delete-message-id" value="">
        <div class="flex gap-3 justify-center">
            <button id="confirm-delete-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition-colors cursor-pointer border-none">
                {{ __t('yes_delete') }}
            </button>
            <button id="cancel-delete-btn" class="flex-1 bg-surface-container-low hover:bg-surface-container text-on-surface-variant text-xs font-bold py-2.5 px-4 rounded-xl transition-colors cursor-pointer border-none">
                {{ __t('cancel') }}
            </button>
        </div>
    </div>
</div>

<div id="image-lightbox-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop with premium glassmorphism -->
    <div class="modal-backdrop absolute inset-0 bg-slate-900/90 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal container -->
    <div class="modal-container relative max-w-4xl w-full max-h-[85vh] flex items-center justify-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 overflow-hidden">
        <!-- Close button inside the container -->
        <button id="close-lightbox-btn" class="absolute top-4 {{ $dir === 'rtl' ? 'left-4' : 'right-4' }} bg-black/50 text-white hover:bg-black/80 p-2 rounded-full transition-all duration-200 cursor-pointer border-none flex items-center justify-center z-20 h-10 w-10">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
        
        <!-- The Image -->
        <img id="lightbox-image" src="" class="hidden max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border border-white/10">
        
        <!-- The Video -->
        <video id="lightbox-video" controls class="hidden max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl border border-white/10 bg-black"></video>
    </div>
</div>

<!-- Call Modal Interface (Hidden by Default) -->
<div id="call-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <!-- Blurry Backdrop -->
    <div class="absolute inset-0 bg-slate-950/85 backdrop-blur-xl transition-all duration-300"></div>

    <!-- Call Card Content -->
    <div class="relative w-full max-w-md bg-white/10 border border-white/20 backdrop-blur-md rounded-[32px] p-8 shadow-[0_30px_70px_rgba(0,0,0,0.5)] z-10 flex flex-col items-center justify-between text-center overflow-hidden text-white" style="min-height: 480px; direction: {{ $dir }};">
        
        <!-- Ambient light glows inside the dark theme -->
        <div class="absolute -top-32 -right-32 w-64 h-64 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-64 h-64 bg-[#f2f4f0]/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Header Status -->
        <div class="w-full flex flex-col items-center gap-1 mt-4">
            <span class="text-xs font-bold uppercase tracking-wider text-primary bg-primary/10 px-3 py-1 rounded-full border border-primary/20" id="call-status-badge">{{ __t('calling_status') }}</span>
            <span id="call-duration" class="text-sm font-semibold tracking-widest text-[#f2f4f0] hidden">00:00</span>
        </div>

        <!-- Middle Profile / Remote Video View -->
        <div class="relative w-full flex-1 flex flex-col items-center justify-center my-6">
            <!-- Ring/Pulse wave animations around the avatar (hidden when video is streaming) -->
            <div id="avatar-pulse-container" class="relative w-36 h-36 flex items-center justify-center">
                <div class="absolute inset-0 rounded-full bg-primary/20 animate-pulse-ring opacity-75"></div>
                <div class="absolute inset-2 rounded-full bg-primary/30 animate-pulse"></div>
                <div class="w-28 h-28 rounded-full overflow-hidden border-2 border-primary shadow-xl relative z-10">
                    <img id="call-avatar" src="" alt="Avatar" class="w-full h-full object-cover">
                </div>
            </div>

            <!-- Remote Video Frame -->
            <div id="remote-video-container" class="hidden w-full h-64 bg-slate-900/60 border border-white/10 rounded-2xl overflow-hidden relative shadow-inner"></div>
            
            <!-- Local Video Floating Frame (Self View) -->
            <div id="local-video-container" class="hidden absolute bottom-2 left-2 w-28 h-36 bg-slate-800 border-2 border-white/30 rounded-xl overflow-hidden shadow-lg z-20"></div>

            <!-- Contact Name -->
            <h3 id="call-name" class="text-xl font-bold mt-4 tracking-wide text-white">جاري التحميل...</h3>
            <p id="call-type-label" class="text-xs text-slate-400 mt-1">اتصال مباشر</p>
        </div>

        <!-- Bottom Actions Row -->
        <div class="w-full flex items-center justify-center gap-6 mb-4 relative z-10" id="call-actions-row">
            <!-- Mute Audio Button -->
            <button id="call-mute-audio-btn" class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-all cursor-pointer" title="كتم الصوت">
                <span class="material-symbols-outlined text-[20px]">mic</span>
            </button>
            
            <!-- End Call (Reject / Cancel / Hang up) Button -->
            <button id="call-hangup-btn" class="w-16 h-16 rounded-full bg-red-500 hover:bg-red-600 border-none flex items-center justify-center text-white shadow-lg shadow-red-500/30 hover:scale-105 transition-all cursor-pointer" title="إنهاء المكالمة">
                <span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">call_end</span>
            </button>

            <!-- Toggle Video Button -->
            <button id="call-toggle-video-btn" class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-all cursor-pointer" title="تشغيل الكاميرا">
                <span class="material-symbols-outlined text-[20px]">videocam_off</span>
            </button>
        </div>

        <!-- Incoming Call Actions (Accept / Decline) -->
        <div class="w-full flex items-center justify-center gap-8 mb-4 relative z-10 hidden" id="incoming-call-actions-row">
            <!-- Decline Button -->
            <button id="incoming-decline-btn" class="flex-1 max-w-[140px] h-12 rounded-2xl bg-red-500 hover:bg-red-600 border-none flex items-center justify-center gap-2 text-white font-bold transition-all shadow-lg shadow-red-500/20 hover:scale-105 cursor-pointer">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">call_end</span>
                <span>{{ __t('decline_call') }}</span>
            </button>
            
            <!-- Accept Button -->
            <button id="incoming-accept-btn" class="flex-1 max-w-[140px] h-12 rounded-2xl bg-green-500 hover:bg-green-600 border-none flex items-center justify-center gap-2 text-white font-bold transition-all shadow-lg shadow-green-500/20 hover:scale-105 cursor-pointer">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">call</span>
                <span>{{ __t('accept_call') }}</span>
            </button>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Call Modal Animations */
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 0.85; }
        50% { transform: scale(1.15); opacity: 0.4; }
        100% { transform: scale(1.35); opacity: 0; }
    }
    .animate-pulse-ring {
        animation: pulse-ring 2.2s infinite ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out forwards;
    }

    #search-chat-modal, #create-group-modal, #group-info-modal {
        transition: visibility 0.3s;
    }
    #search-chat-modal.modal-show .modal-backdrop, #create-group-modal.modal-show .modal-backdrop, #group-info-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #search-chat-modal.modal-show .modal-container, #create-group-modal.modal-show .modal-container, #group-info-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    #image-lightbox-modal {
        transition: visibility 0.3s;
    }
    #image-lightbox-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #image-lightbox-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<!-- Load Agora Web SDK -->
<script src="https://download.agora.io/sdk/release/AgoraRTC_N-4.20.0.js"></script>

<script>
    $(document).ready(function () {
        // Setup AJAX CSRF
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // ===== Mobile Chat Sidebar Drawer Toggle =====
        function openMobileChatSidebar() {
            const $sidebar = $('#chat-sidebar');
            const $backdrop = $('#chat-sidebar-backdrop');
            
            $sidebar.removeClass('hidden lg:flex').addClass('mobile-drawer-open fixed inset-y-0 {{ $dir === "rtl" ? "right-0 border-l" : "left-0 border-r" }} z-50 w-80 max-w-[85vw] bg-white shadow-2xl flex flex-col h-full');
            $backdrop.removeClass('hidden');
            setTimeout(() => {
                $backdrop.removeClass('opacity-0');
            }, 10);
        }

        function closeMobileChatSidebar() {
            const $sidebar = $('#chat-sidebar');
            const $backdrop = $('#chat-sidebar-backdrop');
            
            $backdrop.addClass('opacity-0');
            setTimeout(() => {
                $backdrop.addClass('hidden');
                $sidebar.removeClass('mobile-drawer-open fixed inset-y-0 right-0 left-0 border-l border-r z-50 w-80 max-w-[85vw] shadow-2xl').addClass('hidden lg:flex');
            }, 200);
        }

        $(document).on('click', '#toggle-chat-sidebar-btn, #mobile-empty-show-sidebar-btn', function(e) {
            e.preventDefault();
            if ($('#chat-sidebar').hasClass('mobile-drawer-open')) {
                closeMobileChatSidebar();
            } else {
                openMobileChatSidebar();
            }
        });

        $(document).on('click', '#close-chat-sidebar-btn, #chat-sidebar-backdrop', function() {
            closeMobileChatSidebar();
        });

        $(document).on('click', '.conversation-item, .group-item-clickable', function() {
            if (window.innerWidth < 1024) {
                closeMobileChatSidebar();
            }
        });

        const authUserId = {{ auth()->id() }};
        let activeUserId = {{ $activeUser ? $activeUser->id : 'null' }};
        let activeGroupId = null;
        let activeReplyParentId = null;
        let oldestMessageId = null;
        let hasMoreMessages = true;
        let isLoadingMore = false;
        let activeConversationChannel = null;
        let typingTimeout = null;
        const onlineUsers = new Set();

        // Translation values for JS
        const _tp = {
            direction:                     '{!! current_language()->direction ?? "rtl" !!}',
            incomingCall:                  {!! json_encode(__t('incoming_call')) !!},
            incomingGroupCall:             {!! json_encode(__t('incoming_group_call')) !!},
            incomingGroupCallFrom:         {!! json_encode(__t('incoming_group_call_from')) !!},
            connectingToChannel:           {!! json_encode(__t('connecting_to_channel')) !!},
            callDeclined:                  {!! json_encode(__t('call_declined')) !!},
            callEnded:                     {!! json_encode(__t('call_ended')) !!},
            agoraNotLoaded:                {!! json_encode(__t('agora_not_loaded')) !!},
            activeStatus:                  {!! json_encode(__t('active_status')) !!},
            failedToJoinCall:              {!! json_encode(__t('failed_to_join_call')) !!},
            chooseFriendFirstCall:         {!! json_encode(__t('choose_friend_first_call')) !!},
            groupCall:                     {!! json_encode(__t('group_call')) !!},
            failedGroupCall:               {!! json_encode(__t('failed_group_call')) !!},
            failedCall:                    {!! json_encode(__t('failed_call')) !!},
            failedJoinGroupCall:           {!! json_encode(__t('failed_join_group_call')) !!},
            failedAcceptCall:              {!! json_encode(__t('failed_accept_call')) !!},
            audioMuted:                    {!! json_encode(__t('audio_muted')) !!},
            audioUnmuted:                  {!! json_encode(__t('audio_unmuted')) !!},
            activeVideo:                   {!! json_encode(__t('active_video')) !!},
            cameraAccessDenied:            {!! json_encode(__t('camera_access_denied')) !!},
            yesterday:                     {!! json_encode(__t('yesterday')) !!},
            imageLabel:                    {!! json_encode(__t('image_label')) !!},
            videoLabel:                    {!! json_encode(__t('video_label')) !!},
            voiceMessageLabel:             {!! json_encode(__t('voice_message_label')) !!},
            youLabel:                      {!! json_encode(__t('you_label')) !!},
            failedSendMessage:             {!! json_encode(__t('failed_send_message')) !!},
            searchingFriends:              {!! json_encode(__t('searching_friends')) !!},
            noMatchingActiveFriends:       {!! json_encode(__t('no_matching_active_friends')) !!},
            failedFetchFriends:            {!! json_encode(__t('failed_fetch_friends')) !!},
            typingIndicator:               {!! json_encode(__t('typing_indicator')) !!},
            offlineNow:                    {!! json_encode(__t('offline_now')) !!},
            noGroupMessagesYet:            {!! json_encode(__t('no_group_messages_yet')) !!},
            errorLoadingPreviousMessages:  {!! json_encode(__t('error_loading_previous_messages')) !!},
            loadingGroups:                 {!! json_encode(__t('loading_groups')) !!},
            noActiveGroups:                {!! json_encode(__t('no_active_groups')) !!},
            failedLoadingGroups:           {!! json_encode(__t('failed_loading_groups')) !!},
            enterGroupNameWarn:            {!! json_encode(__t('enter_group_name_warn')) !!},
            chooseAtLeastOneMemberWarn:    {!! json_encode(__t('choose_at_least_one_member_warn')) !!},
            creatingGroupStatus:           {!! json_encode(__t('creating_group_status')) !!},
            groupCreatedSuccess:           {!! json_encode(__t('group_created_success')) !!},
            groupCreationFailed:           {!! json_encode(__t('group_creation_failed')) !!},
            unexpectedErrorGroupCreation:  {!! json_encode(__t('unexpected_error_group_creation')) !!},
            loadingData:                   {!! json_encode(__t('loading_data')) !!},
            noGroupDesc:                   {!! json_encode(__t('no_group_desc')) !!},
            creatorRole:                   {!! json_encode(__t('creator_role')) !!},
            adminRole:                     {!! json_encode(__t('admin_role')) !!},
            memberRole:                    {!! json_encode(__t('member_role')) !!},
            removeMemberTitle:             {!! json_encode(__t('remove_member_title')) !!},
            deleteGroupPermanently:        {!! json_encode(__t('delete_group_permanently')) !!},
            leaveGroup:                    {!! json_encode(__t('leave_group')) !!},
            failedGroupDetails:            {!! json_encode(__t('failed_group_details')) !!},
            failedConnGroupDetails:        {!! json_encode(__t('failed_conn_group_details')) !!},
            confirmRemoveMember:           {!! json_encode(__t('confirm_remove_member')) !!},
            memberRemovedSuccess:          {!! json_encode(__t('member_removed_success')) !!},
            memberRemovalFailed:           {!! json_encode(__t('member_removal_failed')) !!},
            errorRemovingMember:           {!! json_encode(__t('error_removing_member')) !!},
            confirmLeaveGroup:             {!! json_encode(__t('confirm_leave_group')) !!},
            groupLeaveSuccess:             {!! json_encode(__t('group_leave_success')) !!},
            groupLeaveFailed:              {!! json_encode(__t('group_leave_failed')) !!},
            errorLeavingGroup:             {!! json_encode(__t('error_leaving_group')) !!},
            confirmDeleteGroup:            {!! json_encode(__t('confirm_delete_group')) !!},
            groupDeletedSuccess:           {!! json_encode(__t('group_deleted_success')) !!},
            groupDeleteFailed:             {!! json_encode(__t('group_delete_failed')) !!},
            errorDeletingGroup:            {!! json_encode(__t('error_deleting_group')) !!},
            groupLabel:                    {!! json_encode(__t('group_label')) !!},
            originalMsgNotFound:           {!! json_encode(__t('original_msg_not_found')) !!},
            uploadingLargeVideoStatus:     {!! json_encode(__t('uploading_large_video_status')) !!},
            uploadingVideoStatus:          {!! json_encode(__t('uploading_video_status')) !!},
            uploadingImageStatus:          {!! json_encode(__t('uploading_image_status')) !!},
            processingCroppingStatus:      {!! json_encode(__t('processing_cropping_status')) !!},
            savingImageStatus:             {!! json_encode(__t('saving_image_status')) !!},
            videoTooLargeError:            {!! json_encode(__t('video_too_large_error')) !!},
            videoLargeWarning:             {!! json_encode(__t('video_large_warning')) !!},
            browserAudioNotSupported:      {!! json_encode(__t('browser_audio_not_supported')) !!},
            micPermissionRequired:         {!! json_encode(__t('mic_permission_required')) !!},
            uploadingVoiceStatus:          {!! json_encode(__t('uploading_voice_status')) !!},
            failedSendVoice:               {!! json_encode(__t('failed_send_voice')) !!},
            noMatchingSearchResults:       {!! json_encode(__t('no_matching_search_results')) !!},
            errorSearchingMessages:        {!! json_encode(__t('error_searching_messages')) !!},
            errorLoadingMessagesForSearch: {!! json_encode(__t('error_loading_messages_for_search')) !!},
            deletingStatus:                {!! json_encode(__t('deleting_status')) !!},
            messageDeletedSuccess:         {!! json_encode(__t('message_deleted_success')) !!},
            messageDeleteFailed:           {!! json_encode(__t('message_delete_failed')) !!},
            membersCountLabel:             {!! json_encode(__t('members_count_label')) !!},
            groupAudioCall:                {!! json_encode(__t('group_audio_call')) !!},
            fileSizePrefix:                {!! json_encode(__t('file_size_prefix')) !!},
            fileSizeSuffixMb:              {!! json_encode(__t('file_size_suffix_mb')) !!},
            noMessagesYet:                 {!! json_encode(__t('no_messages_yet')) !!},
            noGroupMessagesYet:            {!! json_encode(__t('no_group_messages_yet')) !!},
            onlineNow:                     {!! json_encode(__t('online_now')) !!},
            directCallTitle:               {!! json_encode(__t('direct_call_title')) !!},
            ampmPm:                        {!! json_encode(__t('ampm_pm')) !!},
            ampmAm:                        {!! json_encode(__t('ampm_am')) !!},
            deleteBtnTitle:                {!! json_encode(__t('delete_btn_title')) !!},
            replyBtnTitle:                 {!! json_encode(__t('reply_btn_title')) !!}
        };

        // --- Call System State ---
        let currentCall = null;
        let agoraClient = null;
        let localAudioTrack = null;
        let localVideoTrack = null;
        let isAudioMuted = false;
        let isVideoActive = false;
        let callTimerInterval = null;
        let callStartTime = null;
        let agoraAppId = null;

        // Ringtone Synthesizer Class (Using native Web Audio API)
        class RingtoneSynthesizer {
            constructor() {
                this.ctx = null;
                this.osc1 = null;
                this.osc2 = null;
                this.gainNode = null;
                this.intervalId = null;
            }

            init() {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (!AudioContext) return;
                this.ctx = new AudioContext();
            }

            startRing(type = 'incoming') {
                if (!this.ctx) this.init();
                if (!this.ctx) return;
                this.stop();

                const playRingSequence = () => {
                    if (!this.ctx) return;
                    this.osc1 = this.ctx.createOscillator();
                    this.osc2 = this.ctx.createOscillator();
                    this.gainNode = this.ctx.createGain();

                    if (type === 'incoming') {
                        // USA standard phone ring sound
                        this.osc1.frequency.value = 440;
                        this.osc2.frequency.value = 480;
                    } else {
                        // Ringback sound
                        this.osc1.frequency.value = 400;
                        this.osc2.frequency.value = 450;
                    }

                    this.osc1.connect(this.gainNode);
                    this.osc2.connect(this.gainNode);
                    this.gainNode.connect(this.ctx.destination);

                    this.osc1.start(0);
                    this.osc2.start(0);

                    this.gainNode.gain.setValueAtTime(0, this.ctx.currentTime);
                    this.gainNode.gain.linearRampToValueAtTime(0.12, this.ctx.currentTime + 0.1);

                    const ringTime = type === 'incoming' ? 1.8 : 1.2;
                    this.gainNode.gain.setValueAtTime(0.12, this.ctx.currentTime + ringTime);
                    this.gainNode.gain.linearRampToValueAtTime(0, this.ctx.currentTime + ringTime + 0.1);

                    setTimeout(() => {
                        try {
                            if (this.osc1) this.osc1.stop();
                            if (this.osc2) this.osc2.stop();
                        } catch(e) {}
                    }, (ringTime + 0.3) * 1000);
                };

                const cadence = type === 'incoming' ? 3000 : 4000;
                playRingSequence();
                this.intervalId = setInterval(playRingSequence, cadence);
            }

            stop() {
                if (this.intervalId) {
                    clearInterval(this.intervalId);
                    this.intervalId = null;
                }
                try {
                    if (this.osc1) { this.osc1.stop(); this.osc1 = null; }
                    if (this.osc2) { this.osc2.stop(); this.osc2 = null; }
                } catch(e) {}
                this.gainNode = null;
            }
        }

        const ringtone = new RingtoneSynthesizer();

        // --- Call System Helper Functions ---

        function formatCallDuration(seconds) {
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = (seconds % 60).toString().padStart(2, '0');
            return `${mins}:${secs}`;
        }

        function startCallTimer() {
            if (callTimerInterval) clearInterval(callTimerInterval);
            callStartTime = Date.now();
            $('#call-duration').removeClass('hidden').text('00:00');
            
            callTimerInterval = setInterval(() => {
                const elapsedSeconds = Math.floor((Date.now() - callStartTime) / 1000);
                $('#call-duration').text(formatCallDuration(elapsedSeconds));
            }, 1000);
        }

        function handleIncomingCall(e) {
            console.log('Incoming call received:', e);
            if (currentCall) {
                $.post('/messages/call/decline', { caller_id: e.caller_id });
                return;
            }

            currentCall = {
                role: 'receiver',
                channelName: e.channel_name,
                token: e.token,
                partnerId: e.caller_id,
                partnerName: e.caller_name,
                partnerAvatar: e.caller_avatar
            };

            agoraAppId = e.agora_app_id || 'b21d18d3ad624108b59066895f598699';

            $('#call-avatar').attr('src', e.caller_avatar || '{{ asset("upload/no_image.jpg") }}');
            $('#call-name').text(e.caller_name);
            $('#call-status-badge').text(_tp.incomingCall);
            $('#call-type-label').text(_tp.directCallTitle);

            $('#call-actions-row').addClass('hidden');
            $('#incoming-call-actions-row').removeClass('hidden');
            
            $('#call-modal').removeClass('hidden');
            ringtone.startRing('incoming');
        }

        function handleIncomingGroupCall(e) {
            console.log('Incoming group call received:', e);
            if (currentCall) return;

            currentCall = {
                role: 'receiver',
                isGroup: true,
                groupId: e.group_id,
                channelName: e.channel_name,
                partnerName: e.group_name,
                partnerAvatar: e.caller_avatar
            };

            agoraAppId = e.agora_app_id || 'b21d18d3ad624108b59066895f598699';

            $('#call-avatar').attr('src', e.caller_avatar || '{{ asset("upload/no_image.jpg") }}');
            $('#call-name').text(e.group_name);
            $('#call-status-badge').text(_tp.incomingGroupCall);
            $('#call-type-label').text(_tp.incomingGroupCallFrom + e.caller_name);

            $('#call-actions-row').addClass('hidden');
            $('#incoming-call-actions-row').removeClass('hidden');
            
            $('#call-modal').removeClass('hidden');
            ringtone.startRing('incoming');
        }

        function handleCallAccepted(e) {
            console.log('Call was accepted by partner:', e);
            if (!currentCall || currentCall.role !== 'caller') return;

            ringtone.stop();
            $('#call-status-badge').text(_tp.connectingToChannel);
            joinAgoraCall(agoraAppId, currentCall.channelName, currentCall.token, authUserId);
        }

        function handleCallDeclined(e) {
            console.log('Call was declined by partner:', e);
            if (!currentCall) return;

            ringtone.stop();
            $('#call-status-badge').text(_tp.callDeclined).removeClass('bg-primary/10 text-primary').addClass('bg-red-500/10 text-red-500 border-red-500/20');
            setTimeout(() => {
                resetCallState();
            }, 2000);
        }

        function handleCallEnded(e) {
            console.log('Call was ended by partner:', e);
            if (!currentCall) return;

            $('#call-status-badge').text(_tp.callEnded).removeClass('bg-primary/10 text-primary').addClass('bg-red-500/10 text-red-500 border-red-500/20');
            leaveAgoraCall();
        }

        async function joinAgoraCall(appId, channelName, token, uid) {
            try {
                if (typeof AgoraRTC === 'undefined') {
                    toastr.error(_tp.agoraNotLoaded);
                    resetCallState();
                    return;
                }

                agoraClient = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

                agoraClient.on("user-published", async (user, mediaType) => {
                    await agoraClient.subscribe(user, mediaType);
                    if (mediaType === "audio") {
                        user.audioTrack.play();
                    }
                    if (mediaType === "video") {
                        $('#avatar-pulse-container').addClass('hidden');
                        $('#remote-video-container').removeClass('hidden').empty();
                        user.videoTrack.play("remote-video-container");
                    }
                });

                agoraClient.on("user-unpublished", (user, mediaType) => {
                    if (mediaType === "video") {
                        $('#remote-video-container').addClass('hidden').empty();
                        $('#avatar-pulse-container').removeClass('hidden');
                    }
                });

                await agoraClient.join(appId, channelName, token, String(uid));
                localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
                await agoraClient.publish([localAudioTrack]);

                $('#call-status-badge').text(_tp.activeStatus).removeClass('bg-primary/10 text-primary').addClass('bg-green-500/10 text-green-500 border-green-500/20');
                startCallTimer();
            } catch (error) {
                console.error("Agora join error:", error);
                toastr.error(_tp.failedToJoinCall + (error.message || error.code || JSON.stringify(error)));
                resetCallState();
            }
        }

        async function leaveAgoraCall() {
            if (localAudioTrack) {
                localAudioTrack.stop();
                localAudioTrack.close();
                localAudioTrack = null;
            }
            if (localVideoTrack) {
                localVideoTrack.stop();
                localVideoTrack.close();
                localVideoTrack = null;
            }
            if (agoraClient) {
                try {
                    await agoraClient.leave();
                } catch (e) {}
                agoraClient = null;
            }
            resetCallState();
        }

        function resetCallState() {
            ringtone.stop();
            if (callTimerInterval) {
                clearInterval(callTimerInterval);
                callTimerInterval = null;
            }
            $('#call-duration').addClass('hidden').text('00:00');
            $('#call-modal').addClass('hidden');
            
            $('#call-mute-audio-btn').html('<span class="material-symbols-outlined text-[20px]">mic</span>')
                .removeClass('bg-red-500/20 text-red-500 border-red-500/30');
            $('#call-toggle-video-btn').html('<span class="material-symbols-outlined text-[20px]">videocam_off</span>')
                .removeClass('bg-green-500/20 text-green-500 border-green-500/30');
            
            isAudioMuted = false;
            isVideoActive = false;
            currentCall = null;

            $('#remote-video-container').addClass('hidden').empty();
            $('#local-video-container').addClass('hidden').empty();
            $('#avatar-pulse-container').removeClass('hidden');
        }

        // --- Call System Button Handlers ---
        $(document).on('click', '#call-btn', function () {
            if (!activeUserId && !activeGroupId) {
                toastr.warning(_tp.chooseFriendFirstCall);
                return;
            }

            if (activeGroupId) {
                const groupName = $('#active-user-name').text();
                const groupAvatar = $('#active-user-avatar').attr('src');

                $('#call-avatar').attr('src', groupAvatar || '{{ asset("upload/no_image.jpg") }}');
                $('#call-name').text(groupName);
                $('#call-status-badge').text(_tp.connectingToChannel).removeClass('bg-green-500/10 text-green-500 border-green-500/20').addClass('bg-primary/10 text-primary border-primary/20');
                $('#call-type-label').text(_tp.groupCall);

                $('#call-actions-row').removeClass('hidden');
                $('#incoming-call-actions-row').addClass('hidden');
                $('#call-modal').removeClass('hidden');

                $.post('/messages/call/group/initiate', { group_id: activeGroupId })
                    .done(function (data) {
                        if (data.status === 'success') {
                            agoraAppId = data.agora_app_id;
                            currentCall = {
                                role: 'caller',
                                isGroup: true,
                                groupId: activeGroupId,
                                channelName: data.channel_name,
                                token: data.token,
                                partnerName: data.group_name,
                                partnerAvatar: groupAvatar
                            };
                            joinAgoraCall(agoraAppId, data.channel_name, data.token, authUserId);
                        } else {
                            toastr.error(data.message || _tp.failedGroupCall);
                            resetCallState();
                        }
                    })
                    .fail(function (xhr) {
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.failedGroupCall;
                        toastr.error(errorMsg);
                        resetCallState();
                    });

                return;
            }

            // Direct Call
            const receiverName = $('#active-user-name').text();
            const receiverAvatar = $('#active-user-avatar').attr('src');

            $('#call-avatar').attr('src', receiverAvatar || '{{ asset("upload/no_image.jpg") }}');
            $('#call-name').text(receiverName);
            $('#call-status-badge').text(_tp.callingStatus).removeClass('bg-green-500/10 text-green-500 border-green-500/20').addClass('bg-primary/10 text-primary border-primary/20');
            $('#call-type-label').text(_tp.directCallTitle);

            $('#call-actions-row').removeClass('hidden');
            $('#incoming-call-actions-row').addClass('hidden');
            $('#call-modal').removeClass('hidden');
            ringtone.startRing('outgoing');

            $.post('/messages/call/initiate', { receiver_id: activeUserId })
                .done(function (data) {
                    if (data.status === 'success') {
                        agoraAppId = data.agora_app_id;
                        currentCall = {
                            role: 'caller',
                            channelName: data.channel_name,
                            token: data.token,
                            partnerId: activeUserId,
                            partnerName: data.receiver_name,
                            partnerAvatar: data.receiver_avatar
                        };
                    } else {
                        toastr.error(data.message || _tp.failedCall);
                        resetCallState();
                    }
                })
                .fail(function (xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.failedCall;
                    toastr.error(errorMsg);
                    resetCallState();
                });
        });

        $(document).on('click', '#incoming-decline-btn', function () {
            if (!currentCall) return;
            if (currentCall.isGroup) {
                resetCallState();
            } else {
                $.post('/messages/call/decline', { caller_id: currentCall.partnerId });
                resetCallState();
            }
        });

        $(document).on('click', '#incoming-accept-btn', function () {
            if (!currentCall) return;
            ringtone.stop();
            $('#incoming-call-actions-row').addClass('hidden');
            $('#call-actions-row').removeClass('hidden');
            $('#call-status-badge').text(_tp.callingStatus);

            if (currentCall.isGroup) {
                $.post('/messages/call/group/join', {
                    group_id: currentCall.groupId,
                    channel_name: currentCall.channelName
                })
                .done(function (data) {
                    if (data.status === 'success') {
                        agoraAppId = data.agora_app_id;
                        joinAgoraCall(agoraAppId, currentCall.channelName, data.token, authUserId);
                    } else {
                        toastr.error(data.message || _tp.failedJoinGroupCall);
                        resetCallState();
                    }
                })
                .fail(function (xhr) {
                    const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.failedJoinGroupCall;
                    toastr.error(errorMsg);
                    resetCallState();
                });
            } else {
                $.post('/messages/call/accept', {
                    caller_id: currentCall.partnerId,
                    channel_name: currentCall.channelName
                })
                .done(function () {
                    joinAgoraCall(agoraAppId, currentCall.channelName, currentCall.token, authUserId);
                })
                .fail(function () {
                    toastr.error(_tp.failedAcceptCall);
                    resetCallState();
                });
            }
        });

        $(document).on('click', '#call-hangup-btn', function () {
            if (!currentCall) return;
            if (!currentCall.isGroup) {
                $.post('/messages/call/end', {
                    target_user_id: currentCall.partnerId,
                    channel_name: currentCall.channelName
                });
            }
            leaveAgoraCall();
        });

        $(document).on('click', '#call-mute-audio-btn', async function () {
            if (!localAudioTrack) return;
            try {
                if (!isAudioMuted) {
                    await localAudioTrack.setEnabled(false);
                    isAudioMuted = true;
                    $(this).html('<span class="material-symbols-outlined text-[20px]">mic_off</span>')
                           .addClass('bg-red-500/20 text-red-500 border-red-500/30');
                    toastr.info(_tp.audioMuted);
                } else {
                    await localAudioTrack.setEnabled(true);
                    isAudioMuted = false;
                    $(this).html('<span class="material-symbols-outlined text-[20px]">mic</span>')
                           .removeClass('bg-red-500/20 text-red-500 border-red-500/30');
                    toastr.info(_tp.audioUnmuted);
                }
            } catch (err) {
                console.error("Mute toggle failed:", err);
            }
        });

        $(document).on('click', '#call-toggle-video-btn', async function () {
            if (!agoraClient) return;
            try {
                if (!isVideoActive) {
                    $('#call-status-badge').text(_tp.activeVideo);
                    localVideoTrack = await AgoraRTC.createCameraVideoTrack();
                    await agoraClient.publish([localVideoTrack]);
                    $('#local-video-container').removeClass('hidden').empty();
                    localVideoTrack.play("local-video-container");
                    isVideoActive = true;
                    $(this).html('<span class="material-symbols-outlined text-[20px]">videocam</span>')
                           .addClass('bg-green-500/20 text-green-500 border-green-500/30');
                } else {
                    $('#call-status-badge').text(_tp.activeStatus);
                    if (localVideoTrack) {
                        await agoraClient.unpublish([localVideoTrack]);
                        localVideoTrack.stop();
                        localVideoTrack.close();
                        localVideoTrack = null;
                    }
                    $('#local-video-container').addClass('hidden').empty();
                    isVideoActive = false;
                    $(this).html('<span class="material-symbols-outlined text-[20px]">videocam_off</span>')
                           .removeClass('bg-green-500/20 text-green-500 border-green-500/30');
                }
            } catch (err) {
                console.error("Video toggle failed:", err);
                toastr.error(_tp.cameraAccessDenied);
            }
        });

        // Sidebar lazy loading state
        let contactsPage = 1;
        let contactsHasMore = {{ $totalChatUsers > 15 ? 'true' : 'false' }};
        let contactsIsLoading = false;
        
        // Formatter for time display
        function formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            
            // Check if it is today
            if (date.toDateString() === now.toDateString()) {
                let hours = date.getHours();
                const minutes = date.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? _tp.ampmPm : _tp.ampmAm;
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                return `${hours}:${minutes} ${ampm}`;
            }
            
            // Check if it was yesterday
            const yesterday = new Date(now);
            yesterday.setDate(now.getDate() - 1);
            if (date.toDateString() === yesterday.toDateString()) {
                return _tp.yesterday;
            }
            
            // Otherwise return formatted date
            return date.toLocaleDateString('ar-EG', { month: 'short', day: 'numeric' });
        }

        // HTML Escape helper
        function escapeHtml(text) {
            if (!text) return '';
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Scroll to the bottom of message list
        function scrollToBottom() {
            const container = $('#messages-container');
            if (container.length > 0) {
                container.scrollTop(container[0].scrollHeight);
            }
        }

        // Append message dynamically to the wrap
        function appendMessage(msg, isSelf, prepend = false) {
            const wrapper = $('#messages-list-wrapper');
            if (wrapper.length === 0) return;
            
            // Remove "no messages" placeholder if it exists
            wrapper.find('.no-messages-placeholder').remove();
            
            let messageHtml = '';
            let bubbleContent = '';
            
            // Construct full URLs
            const imageUrl = msg.image_url || (msg.image ? `${window.location.origin}/new_wiselook/uploads/${msg.image}` : null);
            const videoUrl = msg.video_url || (msg.video ? `${window.location.origin}/new_wiselook/uploads/${msg.video}` : null);
            const audioUrl = msg.audio_url || (msg.audio ? `${window.location.origin}/new_wiselook/uploads/${msg.audio}` : null);
            
            if (imageUrl) {
                bubbleContent += `
                    <div class="relative rounded-xl overflow-hidden mb-1.5 border border-white/10">
                        <img alt="${_tp.imageLabel}" class="chat-image-preview w-[280px] h-[180px] object-cover cursor-pointer hover:scale-[1.02] transition-transform duration-200" src="${imageUrl}" data-src="${imageUrl}">
                    </div>
                `;
            } else if (videoUrl) {
                bubbleContent += `
                    <div class="relative rounded-xl overflow-hidden mb-1.5 border border-white/10 max-w-[320px] group cursor-pointer chat-video-preview-trigger" data-src="${videoUrl}">
                        <video class="w-full max-h-[220px] rounded-lg object-cover">
                            <source src="${videoUrl}">
                        </video>
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-[48px] drop-shadow-md hover:scale-110 transition-transform duration-200">play_circle</span>
                        </div>
                    </div>
                `;
            } else if (audioUrl) {
                // Determine layout colors based on sender
                const playBtnBg = isSelf ? 'bg-white text-primary' : 'bg-primary text-white';
                const seekBg = isSelf ? 'bg-white/20' : 'bg-[#f2f4f0]';
                const progressColor = isSelf ? 'bg-white' : 'bg-primary';
                const textCol = isSelf ? 'text-white/80' : 'text-on-surface-variant';
                
                bubbleContent += `
                    <div class="audio-bubble-container flex items-center gap-3 p-2 rounded-xl max-w-[280px] w-56 md:w-64">
                        <button onclick="handleAudioPlayPause(this)" class="w-9 h-9 rounded-full ${playBtnBg} flex items-center justify-center cursor-pointer border-none shrink-0 play-audio-btn hover:scale-105 transition-transform">
                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">play_arrow</span>
                        </button>
                        <div class="flex-1 flex flex-col gap-1 min-w-0">
                            <!-- Custom Seekbar -->
                            <div class="w-full ${seekBg} rounded-full h-1.5 relative overflow-hidden cursor-pointer audio-seekbar-container">
                                <div class="audio-progress ${progressColor} h-full w-0 transition-all duration-75"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] ${textCol} font-bold">
                                <span class="audio-current-time">00:00</span>
                                <span class="audio-duration">00:00</span>
                            </div>
                        </div>
                        <audio class="hidden-audio-player" src="${audioUrl}" ontimeupdate="handleAudioTimeUpdate(this)" onended="handleAudioEnded(this)" onloadedmetadata="handleAudioLoadedMetadata(this)"></audio>
                    </div>
                `;
            }
            
            if (msg.message) {
                const textClass = (imageUrl || videoUrl || audioUrl) ? 'text-xs px-2 pb-1' : 'text-xs leading-relaxed break-words';
                bubbleContent += `<p class="${textClass} ${isSelf ? 'text-white/95' : 'text-on-surface'}">${escapeHtml(msg.message)}</p>`;
            }
            
            // Reply context (WhatsApp Style reply quote)
            const parentMsg = msg.parent || msg.parent_message;
            const activeName = $('#active-user-name').text();

            let bubbleWithParent = bubbleContent;
            if (parentMsg) {
                const parentSenderName = parentMsg.sender ? parentMsg.sender.name : (parentMsg.sender_name || '');
                let parentContent = parentMsg.message || '';
                if (parentMsg.image) {
                    parentContent = '📷 ' + _tp.imageLabel;
                } else if (parentMsg.video) {
                    parentContent = '🎥 ' + _tp.videoLabel;
                } else if (parentMsg.audio) {
                    parentContent = '🎙️ ' + _tp.voiceMessageLabel;
                }
                
                // Styling depends on sender
                const quoteBg = isSelf ? 'bg-white/10 text-white/90 border-white/30' : 'bg-primary/5 text-on-surface-variant border-primary/30';
                const quoteTitleCol = isSelf ? 'text-white font-bold' : 'text-primary font-bold';
                
                bubbleWithParent = `
                    <div class="mb-2 p-2 rounded-lg text-xs border-r-4 ${quoteBg} cursor-pointer reply-quote-click" data-target-id="${parentMsg.id}">
                        <div class="${quoteTitleCol} text-[10px] mb-0.5">${escapeHtml(parentSenderName)}</div>
                        <div class="truncate max-w-[240px]">${escapeHtml(parentContent)}</div>
                    </div>
                    ${bubbleContent}
                `;
            }

            // Create metadata for reply triggers
            let previewText = msg.message || '';
            if (msg.image) previewText = '📷 ' + _tp.imageLabel;
            else if (msg.video) previewText = '🎥 ' + _tp.videoLabel;
            else if (msg.audio) previewText = '🎙️ ' + _tp.voiceMessageLabel;

            const paddingClass = (imageUrl || videoUrl) ? 'p-2' : 'px-4 py-2.5';
            
            if (isSelf) {
                // Sent message style
                messageHtml = `
                    <div class="flex items-center gap-1 group/msg self-start max-w-[80%] text-right message-item animate-fade-in" data-id="${msg.id}" data-sender-name="${_tp.youLabel}" data-preview="${escapeHtml(previewText)}">
                        <div class="flex flex-col gap-1 items-start">
                            <div class="bg-primary text-white rounded-2xl rounded-bl-sm ${paddingClass} shadow-sm relative">
                                ${bubbleWithParent}
                            </div>
                            <div class="flex items-center gap-1 text-[9px] text-on-surface-variant font-medium">
                                <span>${formatTime(msg.created_at)}</span>
                                <span class="material-symbols-outlined text-secondary text-[14px]">done_all</span>
                            </div>
                        </div>
                        <!-- Action Buttons (Visible on Hover) -->
                        <div class="flex items-center gap-0.5 opacity-0 group-hover/msg:opacity-100 transition-opacity">
                            <button class="delete-msg-btn w-8 h-8 rounded-full flex items-center justify-center hover:bg-red-50 text-red-400 cursor-pointer border-none shrink-0" title="${_tp.deleteBtnTitle}" data-msg-id="${msg.id}">
                                <span class="material-symbols-outlined text-[17px]">delete</span>
                            </button>
                            <button class="reply-msg-trigger w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-on-surface-variant cursor-pointer border-none shrink-0" title="${_tp.replyBtnTitle}">
                                <span class="material-symbols-outlined text-[18px]">reply</span>
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Received message style
                let activeAvatar, senderName;
                if (activeGroupId) {
                    senderName = msg.sender_name || (msg.sender ? (msg.sender.first_name + ' ' + msg.sender.last_name) : _tp.memberRole);
                    
                    if (msg.sender_avatar) {
                        activeAvatar = msg.sender_avatar;
                    } else if (msg.sender && msg.sender.avatar_url) {
                        activeAvatar = msg.sender.avatar_url;
                    } else if (msg.sender && msg.sender.profile_picture && msg.sender.profile_picture !== 'non') {
                        const pic = msg.sender.profile_picture;
                        activeAvatar = pic.startsWith('http') ? pic : `${window.location.origin}/new_wiselook/uploads/${pic.split('/').pop()}`;
                    } else {
                        activeAvatar = '{{ url("upload/no_image.jpg") }}';
                    }
                } else {
                    senderName = activeName;
                    activeAvatar = $('#active-user-avatar').attr('src') || '{{ url("upload/no_image.jpg") }}';
                }
                
                let groupSenderNameHtml = '';
                if (activeGroupId) {
                    groupSenderNameHtml = `<span class="text-[10px] text-primary/80 mb-1 block font-bold text-right">${escapeHtml(senderName)}</span>`;
                }
                
                messageHtml = `
                    <div class="flex items-center gap-2 group/msg self-end flex-row-reverse max-w-[80%] text-right message-item animate-fade-in" data-id="${msg.id}" data-sender-name="${escapeHtml(senderName)}" data-preview="${escapeHtml(previewText)}">
                        <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 mt-auto border border-outline-variant">
                            <img alt="${escapeHtml(senderName)}" class="w-full h-full object-cover" src="${activeAvatar}">
                        </div>
                        <div class="flex flex-col gap-1 items-end">
                            <div class="bg-white border border-primary/5 rounded-2xl rounded-br-sm ${paddingClass} shadow-sm text-on-surface relative">
                                ${groupSenderNameHtml}
                                ${bubbleWithParent}
                            </div>
                            <span class="text-[9px] text-on-surface-variant font-medium">${formatTime(msg.created_at)}</span>
                        </div>
                        <!-- Reply Button (Visible on Hover) -->
                        <button class="reply-msg-trigger opacity-0 group-hover/msg:opacity-100 transition-opacity w-8 h-8 rounded-full flex items-center justify-center hover:bg-slate-100 text-on-surface-variant cursor-pointer border-none shrink-0" title="${_tp.replyBtnTitle}">
                            <span class="material-symbols-outlined text-[18px]">reply</span>
                        </button>
                    </div>
                `;
            }
            
            if (prepend) {
                wrapper.prepend(messageHtml);
            } else {
                wrapper.append(messageHtml);
                scrollToBottom();
            }
        }

        // Fetch messages for active user
        function fetchConversation(receiverId, beforeId = null) {
            if (!receiverId) return;
            
            if (beforeId === null) {
                $('#messages-list-wrapper').empty();
                $('#messages-loading').removeClass('hidden');
                oldestMessageId = null;
                hasMoreMessages = true;
                isLoadingMore = false;
            } else {
                isLoadingMore = true;
                $('#lazy-loading-spinner').removeClass('hidden').addClass('flex');
            }
            
            const url = beforeId 
                ? `/messages/fetch/${receiverId}?before_id=${beforeId}`
                : `/messages/fetch/${receiverId}`;

            $.ajax({
                url: url,
                type: 'GET',
                success: function (messages) {
                    if (beforeId === null) {
                        $('#messages-loading').addClass('hidden');
                    } else {
                        $('#lazy-loading-spinner').removeClass('flex').addClass('hidden');
                        isLoadingMore = false;
                    }
                    
                    if (messages && messages.length > 0) {
                        const container = document.getElementById('messages-container');
                        const oldScrollHeight = container ? container.scrollHeight : 0;
                        const oldScrollTop = container ? container.scrollTop : 0;

                        if (beforeId === null) {
                            messages.forEach(msg => {
                                const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                                appendMessage(msg, isSelf, false);
                                if (oldestMessageId === null || msg.id < oldestMessageId) {
                                    oldestMessageId = msg.id;
                                }
                            });
                            scrollToBottom();
                        } else {
                            // Loop in reverse order to prepend oldest to newest at the top
                            for (let i = messages.length - 1; i >= 0; i--) {
                                const msg = messages[i];
                                const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                                appendMessage(msg, isSelf, true);
                                if (oldestMessageId === null || msg.id < oldestMessageId) {
                                    oldestMessageId = msg.id;
                                }
                            }
                            
                            // Adjust scroll top to preserve scroll position
                            if (container) {
                                container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);
                            }
                        }

                        if (messages.length < 20) {
                            hasMoreMessages = false;
                        }
                    } else {
                        hasMoreMessages = false;
                        if (beforeId === null) {
                            $('#messages-list-wrapper').html(`
                                <div class="no-messages-placeholder text-center py-12 text-on-surface-variant text-xs font-medium">
                                    ${_tp.noMessagesYet}
                                </div>
                            `);
                        }
                    }
                },
                error: function (xhr) {
                    if (beforeId === null) {
                        $('#messages-loading').addClass('hidden');
                    } else {
                        $('#lazy-loading-spinner').removeClass('flex').addClass('hidden');
                        isLoadingMore = false;
                    }
                    toastr.error(_tp.errorLoadingPreviousMessages);
                }
            });
        }

        // Select a conversation from sidebar or search
        function selectConversation(userId, userName, userAvatar) {
            // Unsubscribe from previous conversation channel
            if (activeUserId && window.Echo) {
                window.Echo.leave('chat.' + activeUserId);
            }

            activeUserId = parseInt(userId);
            activeGroupId = null; // Reset group ID since we are in DM
            
            // Switch view panels
            $('#chat-empty-state').addClass('hidden');
            $('#chat-main-panel').removeClass('hidden');

            // Subscribe to the new conversation channel
            if (window.Echo) {
                activeConversationChannel = window.Echo.private('chat.' + activeUserId);
                activeConversationChannel.subscribed(() => {
                    console.log('Successfully subscribed to whisper channel: chat.' + activeUserId);
                }).error((err) => {
                    console.error('Failed to subscribe to whisper channel: chat.' + activeUserId, err);
                });
            }
            
            // Reset status text in header based on online status
            const isOnline = onlineUsers.has(activeUserId);
            const headerDot = $('#active-user-status-dot');
            const headerText = $('#active-user-status-text');
            headerDot.removeClass('hidden'); // Show status dot for DMs
            if (isOnline) {
                headerDot.removeClass('bg-secondary bg-slate-400 bg-primary animate-pulse').addClass('bg-green-500');
                headerText.text(_tp.onlineNow);
            } else {
                headerDot.removeClass('bg-green-500 bg-secondary bg-primary animate-pulse').addClass('bg-slate-400');
                headerText.text(_tp.offlineNow);
            }
            
            // Show call button and hide info button in direct chats
            $('#call-btn').removeClass('hidden').attr('title', _tp.directCallTitle);
            $('#group-info-btn').addClass('hidden');
            
            // Update Active User details in Header
            $('#active-user-avatar').attr('src', userAvatar);
            $('#active-user-name').text(userName);
            
            // Update sidebar selection visual
            $('.conversation-item, .group-item').removeClass('bg-white border border-primary/10 shadow-sm relative active-chat').addClass('hover:bg-white/40');
            const sidebarItem = $(`.conversation-item[data-user-id="${userId}"]`);
            if (sidebarItem.length > 0) {
                sidebarItem.addClass('bg-white border border-primary/10 shadow-sm relative active-chat').removeClass('hover:bg-white/40');
                sidebarItem.find('.unread-badge').addClass('hidden').text('0');
            }
            
            // Fetch messages
            fetchConversation(userId);
            
            // Update URL dynamically
            history.pushState(null, '', `/messages/${userId}`);
        }

        // Click handler for Sidebar conversations
        $(document).on('click', '.conversation-item', function () {
            const userId = $(this).attr('data-user-id');
            const userName = $(this).attr('data-user-name');
            const userAvatar = $(this).attr('data-user-avatar');
            selectConversation(userId, userName, userAvatar);
        });

        // --- Group Conversation Actions ---
        function selectGroupConversation(groupId, groupName, groupAvatar, membersCount) {
            // Unsubscribe from previous conversation channel
            if (activeUserId && window.Echo) {
                window.Echo.leave('chat.' + activeUserId);
            }
            activeUserId = null;
            activeGroupId = parseInt(groupId);
            activeConversationChannel = null;
            
            // Switch view panels
            $('#chat-empty-state').addClass('hidden');
            $('#chat-main-panel').removeClass('hidden');

            // Update Active Group details in Header
            $('#active-user-avatar').attr('src', groupAvatar);
            $('#active-user-name').text(groupName);
            $('#active-user-status-text').text(membersCount + ' ' + _tp.membersCountLabel);
            $('#active-user-status-dot').addClass('hidden');
            
            // Show live call button and group info button in group chats
            $('#call-btn').removeClass('hidden').attr('title', _tp.groupAudioCall);
            $('#group-info-btn').removeClass('hidden');
            
            // Update sidebar selection visual
            $('.conversation-item, .group-item').removeClass('bg-white border border-primary/10 shadow-sm relative active-chat').addClass('hover:bg-white/40');
            const sidebarItem = $(`.group-item[data-group-id="${groupId}"]`);
            if (sidebarItem.length > 0) {
                sidebarItem.addClass('bg-white border border-primary/10 shadow-sm relative active-chat').removeClass('hover:bg-white/40');
                sidebarItem.find('.unread-badge').addClass('hidden').text('0');
            }
            
            // Fetch messages
            fetchGroupConversation(groupId);
            
            // Update URL dynamically
            history.pushState(null, '', `/messages?group_id=${groupId}`);
        }

        // Click handler for Sidebar groups
        $(document).on('click', '.group-item', function () {
            const groupId = $(this).attr('data-group-id');
            const groupName = $(this).attr('data-group-name');
            const groupAvatar = $(this).attr('data-group-avatar');
            const membersCount = $(this).attr('data-group-members-count');
            selectGroupConversation(groupId, groupName, groupAvatar, membersCount);
        });


        // Media Selection change handlers
        let videoDuration = 0;

        $(document).on('change', '#image-upload-input', function () {
            const file = this.files[0];
            if (file) {
                // Clear video input
                $('#video-upload-input').val('');
                $('#video-preview').attr('src', '');
                $('#video-preview-wrapper').hide();
                $('#video-trimmer-container').removeClass('flex').addClass('hidden');

                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
                $('#preview-file-name').text(file.name);
                $('#preview-file-size').text(_tp.fileSizePrefix + fileSizeMB + ' ' + _tp.fileSizeSuffixMb);

                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#image-preview').attr('src', e.target.result).show();
                    $('#media-preview-container').removeClass('hidden').addClass('flex');
                };
                reader.readAsDataURL(file);
            }
        });

        $(document).on('change', '#video-upload-input', function () {
            const file = this.files[0];
            if (file) {
                const maxSizeBytes = 100 * 1024 * 1024; // 100MB
                if (file.size > maxSizeBytes) {
                    toastr.error(_tp.videoTooLargeError);
                    $(this).val('');
                    return;
                }
                
                const fileSizeMB = (file.size / (1024 * 1024)).toFixed(1);
                if (file.size > 15 * 1024 * 1024) { // 15MB warning
                    toastr.warning(_tp.videoLargeWarning.replace('${fileSizeMB}', fileSizeMB));
                }

                // Clear image input
                $('#image-upload-input').val('');
                $('#image-preview').attr('src', '').hide();

                $('#preview-file-name').text(file.name);
                $('#preview-file-size').text(_tp.fileSizePrefix + fileSizeMB + ' ' + _tp.fileSizeSuffixMb);

                const fileURL = URL.createObjectURL(file);
                $('#video-preview').attr('src', fileURL);
                $('#video-preview-wrapper').show();
                
                // Load metadata to get duration
                const tempVideo = document.createElement('video');
                tempVideo.src = fileURL;
                tempVideo.onloadedmetadata = function() {
                    videoDuration = Math.floor(tempVideo.duration);
                    
                    // Setup trimmer sliders
                    $('#trim-start-range').attr('max', videoDuration).val(0);
                    $('#trim-end-range').attr('max', videoDuration).val(Math.min(videoDuration, 120));
                    
                    updateTrimLabels();
                    
                    $('#video-trimmer-container').removeClass('hidden').addClass('flex');
                    $('#media-preview-container').removeClass('hidden').addClass('flex');
                };
            }
        });

        function formatDuration(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function updateTrimLabels() {
            const startVal = parseInt($('#trim-start-range').val());
            let endVal = parseInt($('#trim-end-range').val());
            
            // Enforce constraints
            if (endVal < startVal) {
                endVal = startVal;
                $('#trim-end-range').val(endVal);
            }
            
            const diff = endVal - startVal;
            if (diff > 120) {
                endVal = startVal + 120;
                $('#trim-end-range').val(endVal);
                $('#trim-warning-text').removeClass('hidden');
            } else {
                $('#trim-warning-text').addClass('hidden');
            }
            
            const finalDiff = endVal - startVal;
            
            $('#trim-start-val').text(formatDuration(startVal));
            $('#trim-end-val').text(formatDuration(endVal));
            $('#trim-duration-badge').text(formatDuration(finalDiff));
        }

        $(document).on('input', '#trim-start-range', function() {
            updateTrimLabels();
            const startVal = parseInt($(this).val());
            const video = document.getElementById('video-preview');
            if (video) {
                video.currentTime = startVal;
            }
        });

        $(document).on('input', '#trim-end-range', function() {
            updateTrimLabels();
            const endVal = parseInt($(this).val());
            const video = document.getElementById('video-preview');
            if (video) {
                video.currentTime = endVal;
            }
        });
        
        // Remove Media action
        $(document).on('click', '#remove-media-btn', function (e) {
            e.preventDefault();
            $('#image-upload-input').val('');
            $('#video-upload-input').val('');
            $('#media-preview-container').removeClass('flex').addClass('hidden');
            $('#image-preview').attr('src', '').hide();
            $('#video-preview').attr('src', '');
            $('#video-preview-wrapper').hide();
            $('#video-trimmer-container').removeClass('flex').addClass('hidden');
        });

        // Trigger hidden file clicks
        $(document).on('click', '#image-select-btn', function(e) {
            e.preventDefault();
            $('#image-upload-input').click();
        });

        $(document).on('click', '#video-select-btn', function(e) {
            e.preventDefault();
            $('#video-upload-input').click();
        });

        // Open Image Lightbox
        $(document).on('click', '.chat-image-preview', function(e) {
            e.preventDefault();
            const imageUrl = $(this).attr('data-src');
            
            // Hide video, show image
            $('#lightbox-video').hide().attr('src', '');
            $('#lightbox-image').show().attr('src', imageUrl);
            
            const modal = $('#image-lightbox-modal');
            modal.removeClass('hidden').addClass('flex');
            $('body').addClass('modal-active');
            setTimeout(() => {
                modal.addClass('modal-show');
            }, 20);
        });

        // Open Video Lightbox
        $(document).on('click', '.chat-video-preview-trigger', function(e) {
            e.preventDefault();
            const videoUrl = $(this).attr('data-src');
            
            // Hide image, show video
            $('#lightbox-image').hide().attr('src', '');
            $('#lightbox-video').show().attr('src', videoUrl);
            
            // Play video in lightbox automatically
            const videoElement = document.getElementById('lightbox-video');
            if (videoElement) {
                videoElement.load();
                videoElement.play();
            }
            
            const modal = $('#image-lightbox-modal');
            modal.removeClass('hidden').addClass('flex');
            $('body').addClass('modal-active');
            setTimeout(() => {
                modal.addClass('modal-show');
            }, 20);
        });

        // Close Image/Video Lightbox
        function closeLightbox() {
            const modal = $('#image-lightbox-modal');
            modal.removeClass('modal-show');
            $('body').removeClass('modal-active');
            
            // Pause and clear video
            const videoElement = document.getElementById('lightbox-video');
            if (videoElement) {
                videoElement.pause();
                $('#lightbox-video').attr('src', '').hide();
            }
            
            setTimeout(() => {
                modal.addClass('hidden').removeClass('flex');
                $('#lightbox-image').attr('src', '').hide();
            }, 300);
        }

        $(document).on('click', '#close-lightbox-btn, #image-lightbox-modal .modal-backdrop', function() {
            closeLightbox();
        });

        // --- Custom Audio Recording and Visualizer Logic (WhatsApp Style) ---
        let mediaRecorder = null;
        let audioChunks = [];
        let recordingTimerInterval = null;
        let recordingSeconds = 0;
        let audioContext = null;
        let analyser = null;
        let canvasCtx = null;
        let animationFrameId = null;
        let streamRef = null;

        // Click handler to start recording
        $(document).on('click', '#mic-btn', function(e) {
            e.preventDefault();
            startAudioRecording();
        });

        // Click handler to cancel/discard recording
        $(document).on('click', '#cancel-record-btn', function(e) {
            e.preventDefault();
            stopAudioRecording(true);
        });

        // Click handler to stop and send recording
        $(document).on('click', '#stop-send-record-btn', function(e) {
            e.preventDefault();
            stopAudioRecording(false);
        });

        function startAudioRecording() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                toastr.error(_tp.browserAudioNotSupported);
                return;
            }

            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(function(stream) {
                    streamRef = stream;
                    audioChunks = [];
                    recordingSeconds = 0;

                    // Toggle UI view
                    $('#regular-input-row').hide();
                    $('#audio-recording-container').removeClass('hidden').addClass('flex');
                    $('#recording-timer').text('00:00');

                    // Start timer
                    recordingTimerInterval = setInterval(function() {
                        recordingSeconds++;
                        const mins = Math.floor(recordingSeconds / 60);
                        const secs = recordingSeconds % 60;
                        $('#recording-timer').text(
                            `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
                        );
                    }, 1000);

                    // Setup MediaRecorder
                    let options = { mimeType: 'audio/webm' };
                    if (!MediaRecorder.isTypeSupported('audio/webm')) {
                        options = { mimeType: 'audio/ogg' };
                    }
                    mediaRecorder = new MediaRecorder(stream, options);
                    
                    mediaRecorder.ondataavailable = function(event) {
                        if (event.data && event.data.size > 0) {
                            audioChunks.push(event.data);
                        }
                    };

                    mediaRecorder.onstop = function() {
                        // If we didn't cancel, send the recording!
                        if (!mediaRecorder.isCancelled) {
                            sendRecordedAudio();
                        }
                    };

                    mediaRecorder.isCancelled = false;
                    mediaRecorder.start();

                    // Setup Audio Analyser and Visualizer Canvas
                    setupAudioVisualizer(stream);
                })
                .catch(function(err) {
                    console.error('Microphone access denied:', err);
                    toastr.error(_tp.micPermissionRequired);
                });
        }

        function setupAudioVisualizer(stream) {
            const canvas = document.getElementById('audio-wave-canvas');
            if (!canvas) return;

            // Ensure correct canvas size
            canvas.width = canvas.offsetWidth * window.devicePixelRatio;
            canvas.height = canvas.offsetHeight * window.devicePixelRatio;

            canvasCtx = canvas.getContext('2d');
            canvasCtx.scale(window.devicePixelRatio, window.devicePixelRatio);

            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            audioContext = new AudioContextClass();
            analyser = audioContext.createAnalyser();
            const source = audioContext.createMediaStreamSource(stream);
            source.connect(analyser);

            analyser.fftSize = 64; // Small size for responsive whatsapp-style bars
            const bufferLength = analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);

            const drawWidth = canvas.offsetWidth;
            const drawHeight = canvas.offsetHeight;

            function drawWave() {
                animationFrameId = requestAnimationFrame(drawWave);
                analyser.getByteFrequencyData(dataArray);

                canvasCtx.clearRect(0, 0, drawWidth, drawHeight);

                const barCount = bufferLength;
                const barSpacing = 4;
                const barWidth = (drawWidth - (barSpacing * (barCount - 1))) / barCount;

                for (let i = 0; i < barCount; i++) {
                    // Normalize bar height
                    const percent = dataArray[i] / 255;
                    const barHeight = Math.max(4, percent * drawHeight);

                    // WhatsApp voice message bars style: rounded primary colored bars centered vertically
                    canvasCtx.fillStyle = '#003a23'; // Primary green color
                    
                    const x = i * (barWidth + barSpacing);
                    const y = (drawHeight - barHeight) / 2;

                    // Rounded rect helper
                    canvasCtx.beginPath();
                    canvasCtx.roundRect ? canvasCtx.roundRect(x, y, barWidth, barHeight, 2) : canvasCtx.rect(x, y, barWidth, barHeight);
                    canvasCtx.fill();
                }
            }

            drawWave();
        }

        function stopAudioRecording(cancel = false) {
            // Stop timers
            if (recordingTimerInterval) {
                clearInterval(recordingTimerInterval);
                recordingTimerInterval = null;
            }

            // Stop visualizer animation
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }

            // Close AudioContext
            if (audioContext && audioContext.state !== 'closed') {
                audioContext.close();
            }

            // Stop all audio stream tracks
            if (streamRef) {
                streamRef.getTracks().forEach(track => track.stop());
                streamRef = null;
            }

            // Stop MediaRecorder
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.isCancelled = cancel;
                mediaRecorder.stop();
            }

            // Restore UI
            $('#audio-recording-container').removeClass('flex').addClass('hidden');
            $('#regular-input-row').show();
        }

        function sendRecordedAudio() {
            if (audioChunks.length === 0) return;

            const blob = new Blob(audioChunks, { type: mediaRecorder.mimeType });
            const fileExtension = mediaRecorder.mimeType.split(';')[0].split('/')[1] || 'webm';
            const audioFile = new File([blob], `recording.${fileExtension}`, { type: mediaRecorder.mimeType });

            // Show upload progress indicator
            $('#upload-progress-bar').css('width', '0%');
            $('#upload-progress-percent').text('0%');
            $('#upload-progress-container').removeClass('hidden').addClass('flex');
            $('#upload-status-text').text(_tp.uploadingVoiceStatus);

            const formData = new FormData();
            let postUrl = '/messages';
            if (activeGroupId) {
                postUrl = `/messages/groups/${activeGroupId}/messages`;
            } else {
                formData.append('receiver_id', activeUserId);
            }
            formData.append('audio', audioFile);

            $.ajax({
                url: postUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#upload-progress-container').removeClass('flex').addClass('hidden');
                    
                    if (response.status === 'success') {
                        appendMessage(response.message, true);
                        scrollToBottom();
                        
                        if (activeGroupId) {
                            updateGroupSidebarLastMessage(activeGroupId, '🎙️ ' + _tp.voiceMessageLabel, response.message.created_at, _tp.youLabel);
                        } else {
                            updateSidebarLastMessage(activeUserId, '🎙️ ' + _tp.voiceMessageLabel, response.message.created_at);
                        }
                    }
                },
                error: function (xhr) {
                    $('#upload-progress-container').removeClass('flex').addClass('hidden');
                    let errorMsg = _tp.failedSendVoice;
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    toastr.error(errorMsg);
                }
            });
        }

        // --- Custom Voice Player Functions (WhatsApp Style) ---
        window.handleAudioPlayPause = function(btnElement) {
            const btn = $(btnElement);
            const container = btn.closest('.audio-bubble-container');
            const audio = container.find('.hidden-audio-player')[0];
            const icon = btn.find('.material-symbols-outlined');
            
            if (audio.paused) {
                // Pause all other playing audios first
                $('audio').each(function() {
                    if (this !== audio) {
                        this.pause();
                        $(this).closest('.audio-bubble-container').find('.play-audio-btn .material-symbols-outlined').text('play_arrow');
                    }
                });
                
                audio.play().catch(err => {
                    console.error('Audio play failed:', err);
                });
                icon.text('pause');
            } else {
                audio.pause();
                icon.text('play_arrow');
            }
        };

        window.handleAudioTimeUpdate = function(audioElement) {
            const audio = audioElement;
            const container = $(audio).closest('.audio-bubble-container');
            const progress = container.find('.audio-progress');
            const currentTimeLabel = container.find('.audio-current-time');
            const durationLabel = container.find('.audio-duration');
            
            if (audio.duration) {
                const pct = (audio.currentTime / audio.duration) * 100;
                progress.css('width', pct + '%');
            }
            
            currentTimeLabel.text(formatDuration(Math.floor(audio.currentTime)));
            if (!isNaN(audio.duration) && isFinite(audio.duration)) {
                durationLabel.text(formatDuration(Math.floor(audio.duration)));
            }
        };

        window.handleAudioEnded = function(audioElement) {
            const audio = audioElement;
            const container = $(audio).closest('.audio-bubble-container');
            container.find('.play-audio-btn .material-symbols-outlined').text('play_arrow');
            container.find('.audio-progress').css('width', '0%');
            container.find('.audio-current-time').text('00:00');
        };

        window.handleAudioLoadedMetadata = function(audioElement) {
            const audio = audioElement;
            const container = $(audio).closest('.audio-bubble-container');
            const durationLabel = container.find('.audio-duration');
            if (!isNaN(audio.duration) && isFinite(audio.duration)) {
                durationLabel.text(formatDuration(Math.floor(audio.duration)));
            }
        };

        // Seek audio by clicking on custom seekbar
        $(document).on('click', '.audio-seekbar-container', function(e) {
            const seekbar = $(this);
            const container = seekbar.closest('.audio-bubble-container');
            const audio = container.find('.hidden-audio-player')[0];
            
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const pct = clickX / rect.width;
            
            if (!isNaN(audio.duration) && isFinite(audio.duration)) {
                audio.currentTime = pct * audio.duration;
            }
        });

        // Scroll listener for lazy loading older messages
        $('#messages-container').on('scroll', function() {
            const container = $(this);
            if (container.scrollTop() === 0) {
                if (hasMoreMessages && !isLoadingMore && oldestMessageId !== null) {
                    if (activeUserId !== null) {
                        fetchConversation(activeUserId, oldestMessageId);
                    } else if (activeGroupId !== null) {
                        fetchGroupConversation(activeGroupId, oldestMessageId);
                    }
                }
            }
        });

        // --- Message Search Event Handlers ---
        // Toggle search bar
        $(document).on('click', '#toggle-message-search-btn', function(e) {
            e.preventDefault();
            const container = $('#message-search-container');
            if (container.hasClass('hidden')) {
                container.removeClass('hidden').addClass('flex');
                $('#message-search-input').focus();
            } else {
                container.removeClass('flex').addClass('hidden');
                $('#message-search-input').val('');
                $('#search-results-dropdown').removeClass('block').addClass('hidden').empty();
            }
        });

        // Close search bar
        $(document).on('click', '#close-message-search-btn', function(e) {
            e.preventDefault();
            $('#message-search-container').removeClass('flex').addClass('hidden');
            $('#message-search-input').val('');
            $('#search-results-dropdown').removeClass('block').addClass('hidden').empty();
        });

        // Search input keyup/change with debounce
        let searchDebounceTimeout = null;
        $(document).on('input', '#message-search-input', function() {
            const query = $(this).val().trim();
            clearTimeout(searchDebounceTimeout);
            
            if (query.length < 2) {
                $('#search-results-dropdown').removeClass('block').addClass('hidden').empty();
                return;
            }
            
            searchDebounceTimeout = setTimeout(function() {
                if (!activeUserId) return;
                
                $.ajax({
                    url: `/messages/search/${activeUserId}?query=${encodeURIComponent(query)}`,
                    type: 'GET',
                    success: function(results) {
                        const dropdown = $('#search-results-dropdown');
                        dropdown.empty().removeClass('hidden').addClass('block');
                        
                        if (results && results.length > 0) {
                            results.forEach(msg => {
                                const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                                const senderName = isSelf ? _tp.youLabel : (msg.sender ? msg.sender.name : '');
                                
                                let mediaIndicator = '';
                                if (msg.image) mediaIndicator = '📷 ' + _tp.imageLabel + ' ';
                                else if (msg.video) mediaIndicator = '🎥 ' + _tp.videoLabel + ' ';
                                else if (msg.audio) mediaIndicator = '🎙️ ' + _tp.voiceMessageLabel + ' ';
                                
                                const textSnippet = msg.message ? escapeHtml(msg.message) : '';
                                
                                const row = `
                                    <div class="search-result-row p-2.5 hover:bg-primary/5 rounded-lg cursor-pointer transition-colors flex flex-col gap-0.5 border-b border-primary/5 last:border-0" data-msg-id="${msg.id}">
                                        <div class="flex justify-between items-center text-[10px] font-bold">
                                            <span class="text-primary">${escapeHtml(senderName)}</span>
                                            <span class="text-on-surface-variant font-medium">${formatTime(msg.created_at)}</span>
                                        </div>
                                        <div class="text-xs text-on-surface-variant truncate">
                                            ${mediaIndicator}${textSnippet}
                                        </div>
                                    </div>
                                `;
                                dropdown.append(row);
                            });
                        } else {
                            dropdown.html('<div class="text-center py-4 text-xs text-on-surface-variant font-medium">' + _tp.noMatchingSearchResults + '</div>');
                        }
                    },
                    error: function() {
                        toastr.error(_tp.errorSearchingMessages);
                    }
                });
            }, 300);
        });

        // Click search result row
        $(document).on('click', '.search-result-row', function(e) {
            e.preventDefault();
            const msgId = $(this).attr('data-msg-id');
            $('#search-results-dropdown').removeClass('block').addClass('hidden');
            
            const targetElement = $(`.message-item[data-id="${msgId}"]`);
            if (targetElement.length > 0) {
                const container = $('#messages-container');
                const scrollPos = container.scrollTop() + targetElement.position().top - 150;
                container.animate({ scrollTop: scrollPos }, 500);
                
                const targetBubble = targetElement.find('.bg-primary, .bg-white');
                targetBubble.addClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01] transition-all duration-300');
                setTimeout(() => {
                    targetBubble.removeClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01]');
                }, 1500);
            } else {
                // If it is not loaded in the DOM, fetch history starting from that message!
                loadConversationFromMessage(activeUserId, msgId);
            }
        });

        function loadConversationFromMessage(receiverId, messageId) {
            $('#messages-list-wrapper').empty();
            $('#messages-loading').removeClass('hidden');
            
            $.ajax({
                url: `/messages/fetch/${receiverId}?before_id=${parseInt(messageId) + 1}`,
                type: 'GET',
                success: function(messages) {
                    $('#messages-loading').addClass('hidden');
                    oldestMessageId = null;
                    hasMoreMessages = true;
                    
                    if (messages && messages.length > 0) {
                        messages.forEach(msg => {
                            const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                            appendMessage(msg, isSelf, false);
                            if (oldestMessageId === null || msg.id < oldestMessageId) {
                                oldestMessageId = msg.id;
                            }
                        });
                        
                        // Scroll to matched message
                        setTimeout(() => {
                            const targetElement = $(`.message-item[data-id="${messageId}"]`);
                            if (targetElement.length > 0) {
                                const container = $('#messages-container');
                                const scrollPos = container.scrollTop() + targetElement.position().top - 150;
                                container.animate({ scrollTop: scrollPos }, 500);
                                
                                const targetBubble = targetElement.find('.bg-primary, .bg-white');
                                targetBubble.addClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01] transition-all duration-300');
                                setTimeout(() => {
                                    targetBubble.removeClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01]');
                                }, 1500);
                            }
                        }, 300);
                        
                        if (messages.length < 20) {
                            hasMoreMessages = false;
                        }
                    }
                },
                error: function() {
                    $('#messages-loading').addClass('hidden');
                    toastr.error(_tp.errorLoadingMessagesForSearch);
                }
            });
        }

        // --- Message Reply Event Handlers (WhatsApp Style) ---
        // Trigger reply mode on message hover button click
        $(document).on('click', '.reply-msg-trigger', function(e) {
            e.preventDefault();
            const trigger = $(this);
            const msgItem = trigger.closest('.message-item');
            const messageId = msgItem.attr('data-id');
            const senderName = msgItem.attr('data-sender-name');
            const previewText = msgItem.attr('data-preview');

            activeReplyParentId = messageId;
            
            $('#reply-sender-name').text(senderName);
            $('#reply-message-text').text(previewText);
            $('#reply-preview-container').removeClass('hidden').addClass('flex');
            
            // Auto focus textarea
            $('#message-textarea').focus();
        });

        // Cancel reply mode
        $(document).on('click', '#cancel-reply-btn', function(e) {
            e.preventDefault();
            activeReplyParentId = null;
            $('#reply-preview-container').removeClass('flex').addClass('hidden');
            $('#reply-sender-name').text('');
            $('#reply-message-text').text('');
        });

        // --- Delete Message Handlers ---
        function openDeleteModal(msgId) {
            $('#delete-message-id').val(msgId);
            $('#delete-message-modal').removeClass('hidden').addClass('flex');
        }

        function closeDeleteModal() {
            $('#delete-message-modal').removeClass('flex').addClass('hidden');
            $('#delete-message-id').val('');
        }

        function removeMessageFromUI(msgId) {
            const el = $(`.message-item[data-id="${msgId}"]`);
            el.addClass('opacity-0 scale-95 transition-all duration-200');
            setTimeout(() => el.remove(), 200);
        }

        // Open delete confirmation on button click
        $(document).on('click', '.delete-msg-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const msgId = $(this).attr('data-msg-id');
            openDeleteModal(msgId);
        });

        // Close modal on cancel or backdrop
        $(document).on('click', '#cancel-delete-btn, #delete-modal-backdrop', function(e) {
            e.preventDefault();
            closeDeleteModal();
        });

        // Confirm delete
        $(document).on('click', '#confirm-delete-btn', function(e) {
            e.preventDefault();
            const msgId = $('#delete-message-id').val();
            if (!msgId) return;

            $(this).prop('disabled', true).text(_tp.deletingStatus);

            $.ajax({
                url: `/messages/${msgId}`,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.status === 'success') {
                        removeMessageFromUI(msgId);
                        closeDeleteModal();
                        toastr.success(_tp.messageDeletedSuccess);
                    } else {
                        toastr.error(_tp.messageDeleteFailed);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || _tp.messageDeleteFailed;
                    toastr.error(msg);
                },
                complete: function() {
                    $('#confirm-delete-btn').prop('disabled', false).text(_tp.yesDelete);
                    closeDeleteModal();
                }
            });
        });

        // Click quote to scroll to original message and blink it
        $(document).on('click', '.reply-quote-click', function(e) {
            e.preventDefault();
            const targetId = $(this).attr('data-target-id');
            const targetElement = $(`.message-item[data-id="${targetId}"]`);
            if (targetElement.length > 0) {
                const container = $('#messages-container');
                // Scroll container to element
                const scrollPos = container.scrollTop() + targetElement.position().top - 150;
                container.animate({ scrollTop: scrollPos }, 500);
                
                // Visual highlight blink effect
                const targetBubble = targetElement.find('.bg-primary, .bg-white');
                targetBubble.addClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01] transition-all duration-300');
                setTimeout(() => {
                    targetBubble.removeClass('ring-4 ring-primary-container ring-offset-2 scale-[1.01]');
                }, 1500);
            } else {
                toastr.info(_tp.originalMsgNotFound);
            }
        });

        // Send Message action
        function sendMessage() {
            const text = $('#message-textarea').val().trim();
            const imageFile = $('#image-upload-input')[0].files[0];
            const videoFile = $('#video-upload-input')[0].files[0];
            
            if (!text && !imageFile && !videoFile) return;
            if (!activeUserId && !activeGroupId) return;
            
            // Get trim parameters if video is selected
            let trimStart = null;
            let trimEnd = null;
            if (videoFile && $('#video-trimmer-container').is(':visible')) {
                trimStart = $('#trim-start-range').val();
                trimEnd = $('#trim-end-range').val();
            }
            
            // Disable inputs during upload
            $('#message-textarea').prop('disabled', true);
            $('#send-message-btn').prop('disabled', true).addClass('opacity-50');
            $('#image-select-btn').prop('disabled', true);
            $('#video-select-btn').prop('disabled', true);
            
            // Prepare Form Data
            const formData = new FormData();
            let postUrl = '/messages';
            if (activeGroupId) {
                postUrl = `/messages/groups/${activeGroupId}/messages`;
            } else {
                formData.append('receiver_id', activeUserId);
            }

            if (text) {
                formData.append('message', text);
            }
            if (imageFile) {
                formData.append('image', imageFile);
            }
            if (videoFile) {
                formData.append('video', videoFile);
                if (trimStart !== null) {
                    formData.append('trim_start', trimStart);
                }
                if (trimEnd !== null) {
                    formData.append('trim_end', trimEnd);
                }
            }
            if (activeReplyParentId) {
                formData.append('parent_id', activeReplyParentId);
            }
            
            // Show progress bar only for media uploads
            const isMedia = !!(imageFile || videoFile);
            if (isMedia) {
                $('#upload-progress-bar').css('width', '0%');
                $('#upload-progress-percent').text('0%');
                $('#upload-progress-container').removeClass('hidden').addClass('flex');
                
                if (videoFile) {
                    if (videoFile.size > 15 * 1024 * 1024) {
                        $('#upload-status-text').text(_tp.uploadingLargeVideoStatus);
                    } else {
                        $('#upload-status-text').text(_tp.uploadingVideoStatus);
                    }
                } else if (imageFile) {
                    $('#upload-status-text').text(_tp.uploadingImageStatus);
                }
            }
            
            $.ajax({
                url: postUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload && isMedia) {
                        myXhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                const percentComplete = Math.round((e.loaded / e.total) * 100);
                                $('#upload-progress-bar').css('width', percentComplete + '%');
                                $('#upload-progress-percent').text(percentComplete + '%');
                                
                                if (percentComplete === 100) {
                                    if (videoFile) {
                                        $('#upload-status-text').text(_tp.processingCroppingStatus);
                                    } else if (imageFile) {
                                        $('#upload-status-text').text(_tp.savingImageStatus);
                                    }
                                }
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function (response) {
                    // Hide progress bar
                    $('#upload-progress-container').removeClass('flex').addClass('hidden');
                    
                    // Clear reply parent if any
                    if (activeReplyParentId) {
                        $('#cancel-reply-btn').click();
                    }
 
                    // Reset typing indicator timeout
                    if (typingTimeout) {
                        clearTimeout(typingTimeout);
                        typingTimeout = null;
                    }
                    if (activeUserId && activeConversationChannel) {
                        activeConversationChannel.whisper('typing', {
                            sender_id: authUserId,
                            typing: false
                        });
                    }
 
                    // Enable inputs
                    $('#message-textarea').prop('disabled', false).val('');
                    $('#send-message-btn').prop('disabled', false).removeClass('opacity-50');
                    $('#image-select-btn').prop('disabled', false);
                    $('#video-select-btn').prop('disabled', false);
                    
                    // Clear file inputs & previews
                    $('#image-upload-input').val('');
                    $('#video-upload-input').val('');
                    $('#media-preview-container').removeClass('flex').addClass('hidden');
                    $('#image-preview').attr('src', '').hide();
                    $('#video-preview').attr('src', '');
                    $('#video-preview-wrapper').hide();
                    $('#video-trimmer-container').removeClass('flex').addClass('hidden');
                    
                    if (response.status === 'success') {
                        const msg = response.message;
                        
                        // Append sent message
                        appendMessage(msg, true);
                        scrollToBottom();
                        
                        // Update sidebar last message details
                        let previewText = msg.message;
                        if (msg.image) {
                            previewText = '📷 ' + _tp.imageLabel + (msg.message ? `: ${msg.message}` : '');
                        } else if (msg.video) {
                            previewText = '🎥 ' + _tp.videoLabel + (msg.message ? `: ${msg.message}` : '');
                        } else if (msg.audio) {
                            previewText = '🎙️ ' + _tp.voiceMessageLabel;
                        }
                        
                        if (activeGroupId) {
                            updateGroupSidebarLastMessage(activeGroupId, previewText, msg.created_at, _tp.youLabel);
                        } else {
                            updateSidebarLastMessage(activeUserId, previewText, msg.created_at);
                        }
                    }
                },
                error: function (xhr) {
                    // Hide progress bar
                    $('#upload-progress-container').removeClass('flex').addClass('hidden');
                    
                    // Enable inputs
                    $('#message-textarea').prop('disabled', false);
                    $('#send-message-btn').prop('disabled', false).removeClass('opacity-50');
                    $('#image-select-btn').prop('disabled', false);
                    $('#video-select-btn').prop('disabled', false);
                    
                    let errorMsg = _tp.failedSendMessage;
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg += ' (' + xhr.responseJSON.message + ')';
                        } else if (xhr.responseJSON.errors) {
                            errorMsg += '\n' + Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                    } else if (xhr.status) {
                        errorMsg += ' (كود الخطأ: ' + xhr.status + ' ' + (xhr.statusText || '') + ')';
                    }
                    toastr.error(errorMsg);
                }
            });
        }

        // Update last message preview in the sidebar
        function updateSidebarLastMessage(userId, message, timeString) {
            const sidebarItem = $(`.conversation-item[data-user-id="${userId}"]`);
            if (sidebarItem.length > 0) {
                sidebarItem.find('.last-message-text').text(message);
                sidebarItem.find('.last-message-time').text(formatTime(timeString));
                
                // Prepend to DM conversations container
                $('#dm-conversations-container').prepend(sidebarItem);
                $('#no-conversations-placeholder').remove();
            } else {
                // If it doesn't exist, we should reload sidebar or prepend a new dynamic item
                // Let's create a new conversation item dynamically
                const userName = $('#active-user-name').text();
                const userAvatar = $('#active-user-avatar').attr('src');
                
                const newItemHtml = `
                    <div class="conversation-item relative flex items-center gap-3 p-3 mx-2 rounded-xl transition-colors cursor-pointer bg-white border border-primary/10 shadow-sm active-chat" 
                         data-user-id="${userId}"
                         data-user-name="${escapeHtml(userName)}"
                         data-user-avatar="${userAvatar}">
                        <div class="relative shrink-0">
                            <div class="w-11 h-11 rounded-full overflow-hidden border border-outline-variant">
                                <img alt="${escapeHtml(userName)}" class="w-full h-full object-cover" src="${userAvatar}">
                            </div>
                            <div class="status-indicator absolute top-0 right-0 w-3 h-3 bg-secondary rounded-full border-2 border-white z-10"></div>
                        </div>
                        <div class="flex-grow min-w-0 text-right">
                            <h3 class="font-title-lg text-xs font-bold text-primary truncate mb-0.5">${escapeHtml(userName)}</h3>
                            <p class="last-message-text font-body-md text-[11px] text-on-surface-variant truncate leading-normal">${escapeHtml(message)}</p>
                        </div>
                        <div class="flex flex-col items-end justify-between shrink-0 self-stretch text-left select-none">
                            <span class="last-message-time font-label-sm text-[9px] text-on-surface-variant whitespace-nowrap">${formatTime(timeString)}</span>
                            <span class="unread-badge bg-primary text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full mt-1.5 hidden shrink-0 shadow-sm border border-white leading-none">0</span>
                        </div>
                    </div>
                `;
                
                $('.conversation-item').removeClass('bg-white border border-primary/10 shadow-sm relative active-chat').addClass('hover:bg-white/40');
                $('#dm-conversations-container').prepend(newItemHtml);
                $('#no-conversations-placeholder').remove();
            }
        }

        // Trigger send on click / enter
        $(document).on('click', '#send-message-btn', function (e) {
            e.preventDefault();
            sendMessage();
        });

        $(document).on('keydown', '#message-textarea', function (e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Initialize messages load on first page load
        if (activeUserId) {
            fetchConversation(activeUserId);
        }

        // --- Search/New Chat Modal ---
        function openSearchModal() {
            const modal = $('#search-chat-modal');
            modal.removeClass('hidden').addClass('flex');
            setTimeout(() => {
                modal.addClass('modal-show');
            }, 20);
            
            loadFriendsList();
        }

        function closeSearchModal() {
            const modal = $('#search-chat-modal');
            modal.removeClass('modal-show');
            setTimeout(() => {
                modal.addClass('hidden').removeClass('flex');
            }, 300);
        }

        $(document).on('click', '#open-search-modal-btn, #start-chat-btn', function (e) {
            e.preventDefault();
            openSearchModal();
        });

        $(document).on('click', '#close-search-chat-modal-btn, #search-chat-modal .modal-backdrop', function () {
            closeSearchModal();
        });

        // Search friends input typing event
        $(document).on('input', '#friend-search-input', function () {
            const query = $(this).val().trim();
            loadFriendsList(query);
        });

        // AJAX search all active friends
        function loadFriendsList(query = '') {
            $('#friends-search-results').html('<div class="text-center py-4 text-on-surface-variant text-xs">' + _tp.searchingFriends + '</div>');
            
            $.ajax({
                url: '/friends/search',
                type: 'GET',
                data: { q: query },
                success: function (friends) {
                    $('#friends-search-results').empty();
                    
                    if (friends && friends.length > 0) {
                        friends.forEach(friend => {
                            const friendHtml = `
                                <div class="friend-select-item flex items-center gap-3 p-3 rounded-xl hover:bg-primary/5 transition-colors cursor-pointer text-right" 
                                     data-id="${friend.id}" 
                                     data-name="${escapeHtml(friend.name)}" 
                                     data-avatar="${friend.avatar}">
                                    <div class="w-10 h-10 rounded-full overflow-hidden shrink-0 border border-outline-variant">
                                        <img alt="${escapeHtml(friend.name)}" class="w-full h-full object-cover" src="${friend.avatar}">
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-xs text-primary">${escapeHtml(friend.name)}</h4>
                                    </div>
                                    <span class="material-symbols-outlined text-[20px] text-primary">chevron_left</span>
                                </div>
                            `;
                            $('#friends-search-results').append(friendHtml);
                        });
                    } else {
                        $('#friends-search-results').html('<div class="text-center py-4 text-on-surface-variant text-xs">' + _tp.noMatchingActiveFriends + '</div>');
                    }
                },
                error: function () {
                    $('#friends-search-results').html('<div class="text-center py-4 text-red-500 text-xs">' + _tp.failedFetchFriends + '</div>');
                }
            });
        }

        // Selecting a friend from Search Results
        $(document).on('click', '.friend-select-item', function () {
            const id = $(this).attr('data-id');
            const name = $(this).attr('data-name');
            const avatar = $(this).attr('data-avatar');
            
            closeSearchModal();
            selectConversation(id, name, avatar);
        });

        // --- Sidebar Contacts Lazy Loading ---
        function buildContactItemHtml(user, isOnline) {
            const dotColor = isOnline ? 'bg-green-500' : 'bg-secondary';
            const lastMsgText = user.last_message ? escapeHtml(user.last_message) : _tp.noMessagesYet;
            const lastMsgTime = user.last_message_at ? formatTime(user.last_message_at) : '';
            const unreadCount = user.unread_count || 0;
            const unreadHidden = unreadCount > 0 ? '' : 'hidden';
            return `
                <div class="conversation-item relative flex items-center gap-3 p-3 mx-2 rounded-xl transition-colors cursor-pointer hover:bg-white/40"
                     data-user-id="${user.id}"
                     data-user-name="${escapeHtml(user.name)}"
                     data-user-avatar="${user.avatar_url}">
                    <div class="relative shrink-0">
                        <div class="w-11 h-11 rounded-full overflow-hidden border border-outline-variant">
                            <img alt="${escapeHtml(user.name)}" class="w-full h-full object-cover" src="${user.avatar_url}">
                        </div>
                        <div class="status-indicator absolute top-0 right-0 w-3 h-3 ${dotColor} rounded-full border-2 border-white z-10"></div>
                    </div>
                    <div class="flex-grow min-w-0 text-right">
                        <h3 class="font-title-lg text-xs font-bold text-primary truncate mb-0.5">${escapeHtml(user.name)}</h3>
                        <p class="last-message-text font-body-md text-[11px] text-on-surface-variant truncate leading-normal">${lastMsgText}</p>
                    </div>
                    <div class="flex flex-col items-end justify-between shrink-0 self-stretch text-left select-none">
                        <span class="last-message-time font-label-sm text-[9px] text-on-surface-variant whitespace-nowrap">${lastMsgTime}</span>
                        <span class="unread-badge bg-primary text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full mt-1.5 ${unreadHidden} shrink-0 shadow-sm border border-white leading-none">
                            ${unreadCount}
                        </span>
                    </div>
                </div>
            `;
        }

        function loadMoreContacts() {
            if (!contactsHasMore || contactsIsLoading) return;
            contactsIsLoading = true;
            const nextPage = contactsPage + 1;

            $('#contacts-loading-spinner').removeClass('hidden').addClass('flex');

            $.ajax({
                url: `/messages/contacts?page=${nextPage}`,
                type: 'GET',
                success: function(data) {
                    $('#contacts-loading-spinner').removeClass('flex').addClass('hidden');
                    contactsIsLoading = false;

                    if (data.users && data.users.length > 0) {
                        contactsPage = data.page;
                        contactsHasMore = data.has_more;

                        const sentinel = $('#contacts-sentinel');
                        data.users.forEach(user => {
                            const isOnline = onlineUsers.has(parseInt(user.id));
                            const html = buildContactItemHtml(user, isOnline);
                            sentinel.before(html);
                        });
                    } else {
                        contactsHasMore = false;
                    }

                    if (!contactsHasMore) {
                        // Disconnect observer when all loaded
                        if (window._contactsObserver) {
                            window._contactsObserver.disconnect();
                        }
                        $('#contacts-sentinel').remove();
                    }
                },
                error: function() {
                    $('#contacts-loading-spinner').removeClass('flex').addClass('hidden');
                    contactsIsLoading = false;
                }
            });
        }

        // Setup IntersectionObserver on sentinel element
        if (contactsHasMore) {
            const sentinel = document.getElementById('contacts-sentinel');
            if (sentinel && 'IntersectionObserver' in window) {
                window._contactsObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            loadMoreContacts();
                        }
                    });
                }, {
                    root: document.getElementById('conversations-list'),
                    threshold: 0.1
                });
                window._contactsObserver.observe(sentinel);
            }
        }

        // --- Helper functions for user online/offline status UI updates ---
        function updateUserStatusUI(userId, isOnline) {
            userId = parseInt(userId);
            const statusDot = $(`.conversation-item[data-user-id="${userId}"] .status-indicator`);
            if (isOnline) {
                statusDot.removeClass('bg-secondary').addClass('bg-green-500');
            } else {
                statusDot.removeClass('bg-green-500').addClass('bg-secondary');
            }

            // Also check if this user is the active chat partner
            if (activeUserId && activeUserId === userId) {
                const headerDot = $('#active-user-status-dot');
                const headerText = $('#active-user-status-text');
                
                // If they are currently typing, keep the typing status
                const isTyping = headerText.text() === _tp.typingIndicator;
                if (!isTyping) {
                    if (isOnline) {
                        headerDot.removeClass('bg-secondary bg-slate-400').addClass('bg-green-500');
                        headerText.text(_tp.onlineNow);
                    } else {
                        headerDot.removeClass('bg-green-500 bg-secondary').addClass('bg-slate-400');
                        headerText.text(_tp.offlineNow);
                    }
                }
            }
        }

        function updateAllUsersStatusUI() {
            // Loop through all conversation items in sidebar
            $('.conversation-item').each(function() {
                const userId = parseInt($(this).attr('data-user-id'));
                if (onlineUsers.has(userId)) {
                    updateUserStatusUI(userId, true);
                } else {
                    updateUserStatusUI(userId, false);
                }
            });

            // Also update active user status
            if (activeUserId) {
                if (onlineUsers.has(activeUserId)) {
                    updateUserStatusUI(activeUserId, true);
                } else {
                    updateUserStatusUI(activeUserId, false);
                }
            }
        }

        // --- Laravel Echo WebSockets Real-time Listeners ---
        if (window.Echo) {
            console.log('Echo connection established for user: ' + authUserId);

            // Join Presence Channel to track online status
            window.Echo.join('chat-presence')
                .here((users) => {
                    users.forEach(u => onlineUsers.add(parseInt(u.id)));
                    updateAllUsersStatusUI();
                })
                .joining((user) => {
                    onlineUsers.add(parseInt(user.id));
                    updateUserStatusUI(user.id, true);
                })
                .leaving((user) => {
                    onlineUsers.delete(parseInt(user.id));
                    updateUserStatusUI(user.id, false);
                });
            
            // Subscribe to active conversation channel on page load if activeUserId is defined
            if (activeUserId) {
                activeConversationChannel = window.Echo.private(`chat.${activeUserId}`);
                activeConversationChannel.subscribed(() => {
                    console.log('Successfully subscribed to whisper channel chat.' + activeUserId + ' on load');
                }).error((e) => {
                    console.error('Subscription error to chat.' + activeUserId + ' on load', e);
                });
            }

            window.Echo.private(`chat.${authUserId}`)
                .listen('.MessageSent', (e) => {
                    console.log('New message received via WebSocket:', e);
                    
                    let previewText = e.message;
                    if (e.image_url) {
                        previewText = '📷 ' + _tp.imageLabel + (e.message ? `: ${e.message}` : '');
                    } else if (e.video_url) {
                        previewText = '🎥 ' + _tp.videoLabel + (e.message ? `: ${e.message}` : '');
                    } else if (e.audio_url) {
                        previewText = '🎙️ ' + _tp.voiceMessageLabel;
                    }
                    
                    // 1. If message belongs to current active conversation
                    if (parseInt(e.sender_id) === parseInt(activeUserId)) {
                        appendMessage({
                            id: e.id,
                            message: e.message,
                            image_url: e.image_url,
                            video_url: e.video_url,
                            audio_url: e.audio_url,
                            parent: e.parent,
                            sender_id: e.sender_id,
                            created_at: e.created_at
                        }, false);
                        
                        // Update last message in sidebar (and move to top)
                        updateSidebarLastMessage(e.sender_id, previewText, e.created_at);
                    } else {
                        // 2. Message belongs to another user conversation
                        // Update last message in sidebar and increment/show unread badge
                        const sidebarItem = $(`.conversation-item[data-user-id="${e.sender_id}"]`);
                        if (sidebarItem.length > 0) {
                            sidebarItem.find('.last-message-text').text(previewText);
                            sidebarItem.find('.last-message-time').text(formatTime(e.created_at));
                            
                            // Show/Increment badge
                            const badge = sidebarItem.find('.unread-badge');
                            let count = parseInt(badge.text()) || 0;
                            count++;
                            badge.text(count).removeClass('hidden');
                            
                            // Visual shake/pulse effect
                            sidebarItem.addClass('bg-primary/5 transition-all duration-300');
                            setTimeout(() => {
                                sidebarItem.removeClass('bg-primary/5');
                            }, 1000);
                            
                            // Prepend to DM conversations container
                            $('#dm-conversations-container').prepend(sidebarItem);
                        } else {
                            // If conversation item doesn't exist, create it with unread badge!
                            const newItemHtml = `
                                <div class="conversation-item relative flex items-center gap-3 p-3 mx-2 rounded-xl transition-colors cursor-pointer hover:bg-white/40 bg-primary/5" 
                                     data-user-id="${e.sender_id}"
                                     data-user-name="${escapeHtml(e.sender_name)}"
                                     data-user-avatar="${e.sender_avatar}">
                                    <div class="relative shrink-0">
                                        <div class="w-11 h-11 rounded-full overflow-hidden border border-outline-variant">
                                            <img alt="${escapeHtml(e.sender_name)}" class="w-full h-full object-cover" src="${e.sender_avatar}">
                                        </div>
                                        <div class="status-indicator absolute top-0 right-0 w-3 h-3 bg-secondary rounded-full border-2 border-white z-10"></div>
                                    </div>
                                    <div class="flex-grow min-w-0 text-right">
                                        <h3 class="font-title-lg text-xs font-bold text-primary truncate mb-0.5">${escapeHtml(e.sender_name)}</h3>
                                        <p class="last-message-text font-body-md text-[11px] text-on-surface-variant truncate leading-normal">${escapeHtml(previewText)}</p>
                                    </div>
                                    <div class="flex flex-col items-end justify-between shrink-0 self-stretch text-left select-none">
                                        <span class="last-message-time font-label-sm text-[9px] text-on-surface-variant whitespace-nowrap">${formatTime(e.created_at)}</span>
                                        <span class="unread-badge bg-primary text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full mt-1.5 shrink-0 shadow-sm border border-white leading-none">1</span>
                                    </div>
                                </div>
                            `;
                            
                            $('#dm-conversations-container').prepend(newItemHtml);
                            $('#no-conversations-placeholder').remove();
                        }
                    }
                })
                .listenForWhisper('typing', (e) => {
                    console.log('Received typing whisper:', e);
                    if (parseInt(e.sender_id) === parseInt(activeUserId)) {
                        if (e.typing) {
                            // Show typing indicator in header status
                            $('#active-user-status-dot').removeClass('bg-secondary').addClass('bg-primary animate-pulse');
                            $('#active-user-status-text').text(_tp.typingIndicator);
                        } else {
                            // Hide typing indicator in header status
                            $('#active-user-status-dot').removeClass('bg-primary animate-pulse').addClass('bg-secondary');
                            $('#active-user-status-text').text(_tp.onlineNow);
                        }
                    }
                });

            // Listen for real-time message deletions on own private channel
            window.Echo.private(`chat.${authUserId}`)
                .listen('.MessageDeleted', (e) => {
                    if (e.message_id) {
                        removeMessageFromUI(e.message_id);
                    }
                })
                .listen('.GroupMessageSent', (e) => {
                    console.log('New group message received via WebSocket:', e);
                    
                    let previewText = e.message;
                    if (e.image_url) {
                        previewText = '📷 ' + _tp.imageLabel + (e.message ? `: ${e.message}` : '');
                    } else if (e.video_url) {
                        previewText = '🎥 ' + _tp.videoLabel + (e.message ? `: ${e.message}` : '');
                    } else if (e.audio_url) {
                        previewText = '🎙️ ' + _tp.voiceMessageLabel;
                    }
                    
                    // 1. If message belongs to current active group conversation
                    if (activeGroupId && parseInt(e.group_id) === parseInt(activeGroupId)) {
                        appendMessage({
                            id: e.id,
                            message: e.message,
                            image_url: e.image_url,
                            video_url: e.video_url,
                            audio_url: e.audio_url,
                            parent: e.parent,
                            sender_id: e.sender_id,
                            sender_name: e.sender_name,
                            sender_avatar: e.sender_avatar,
                            group_id: e.group_id,
                            created_at: e.created_at
                        }, false);
                        
                        scrollToBottom();
                        
                        // Update last message in sidebar (and move to top)
                        updateGroupSidebarLastMessage(e.group_id, previewText, e.created_at, e.sender_name);
                    } else {
                        // 2. Message belongs to another group conversation
                        // Update last message in sidebar and increment/show unread badge
                        const sidebarItem = $(`.group-item[data-group-id="${e.group_id}"]`);
                        if (sidebarItem.length > 0) {
                            const preview = `${e.sender_name}: ${previewText}`;
                            sidebarItem.find('.last-message-text').text(preview);
                            sidebarItem.find('.last-message-time').text(formatTime(e.created_at));
                            
                            // Move to top
                            $('#groups-list-wrapper').prepend(sidebarItem);
                            
                            // Show/Increment unread indicator
                            const badge = sidebarItem.find('.unread-badge');
                            let count = parseInt(badge.text()) || 0;
                            count++;
                            badge.text(count).removeClass('hidden');
                        }
                    }
                });

            // --- Groups AJAX Load & Render Helpers ---
            function fetchGroupConversation(groupId, beforeId = null) {
                if (!groupId) return;
                
                if (beforeId === null) {
                    $('#messages-list-wrapper').empty();
                    $('#messages-loading').removeClass('hidden');
                    oldestMessageId = null;
                    hasMoreMessages = true;
                    isLoadingMore = false;
                } else {
                    isLoadingMore = true;
                    $('#lazy-loading-spinner').removeClass('hidden').addClass('flex');
                }
                
                const url = beforeId 
                    ? `/messages/groups/${groupId}/messages?before_id=${beforeId}`
                    : `/messages/groups/${groupId}/messages`;

                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (messages) {
                        if (beforeId === null) {
                            $('#messages-loading').addClass('hidden');
                        } else {
                            $('#lazy-loading-spinner').removeClass('flex').addClass('hidden');
                            isLoadingMore = false;
                        }
                        
                        if (messages && messages.length > 0) {
                            const container = document.getElementById('messages-container');
                            const oldScrollHeight = container ? container.scrollHeight : 0;
                            const oldScrollTop = container ? container.scrollTop : 0;

                            if (beforeId === null) {
                                messages.forEach(msg => {
                                    const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                                    appendMessage(msg, isSelf, false);
                                    if (oldestMessageId === null || msg.id < oldestMessageId) {
                                        oldestMessageId = msg.id;
                                    }
                                });
                                scrollToBottom();
                            } else {
                                for (let i = messages.length - 1; i >= 0; i--) {
                                    const msg = messages[i];
                                    const isSelf = parseInt(msg.sender_id) === parseInt(authUserId);
                                    appendMessage(msg, isSelf, true);
                                    if (oldestMessageId === null || msg.id < oldestMessageId) {
                                        oldestMessageId = msg.id;
                                    }
                                }
                                if (container) {
                                    container.scrollTop = oldScrollTop + (container.scrollHeight - oldScrollHeight);
                                }
                            }

                            if (messages.length < 20) {
                                hasMoreMessages = false;
                            }
                        } else {
                            hasMoreMessages = false;
                            if (beforeId === null) {
                                $('#messages-list-wrapper').html(`
                                    <div class="no-messages-placeholder text-center py-12 text-on-surface-variant text-xs font-medium">
                                        ${_tp.noGroupMessagesYet || _tp.noMessagesYet}
                                    </div>
                                `);
                            }
                        }
                    },
                    error: function (xhr) {
                        if (beforeId === null) {
                            $('#messages-loading').addClass('hidden');
                        } else {
                            $('#lazy-loading-spinner').removeClass('flex').addClass('hidden');
                            isLoadingMore = false;
                        }
                        toastr.error(_tp.errorLoadingPreviousMessages);
                    }
                });
            }

            function updateGroupSidebarLastMessage(groupId, message, timeString, senderName) {
                const sidebarItem = $(`.group-item[data-group-id="${groupId}"]`);
                if (sidebarItem.length > 0) {
                    const preview = senderName ? `${senderName}: ${message}` : message;
                    sidebarItem.find('.last-message-text').text(preview);
                    sidebarItem.find('.last-message-time').text(formatTime(timeString));
                    
                    // Prepend to list
                    $('#groups-list-wrapper').prepend(sidebarItem);
                }
            }

            function loadGroupsList() {
                const wrapper = $('#groups-list-wrapper');
                wrapper.html('<div class="text-center py-4 text-on-surface-variant text-xs">' + _tp.loadingGroups + '</div>');
                
                $.ajax({
                    url: '/messages/groups/list',
                    type: 'GET',
                    success: function(response) {
                        wrapper.empty();
                        if (response.status === 'success' && response.groups && response.groups.length > 0) {
                            response.groups.forEach(group => {
                                const isGroupActive = activeGroupId && parseInt(group.id) === parseInt(activeGroupId);
                                const unreadCount = group.unread_count || 0;
                                const unreadHidden = unreadCount > 0 ? '' : 'hidden';
                                const itemHtml = `
                                    <div class="group-item relative flex items-center gap-3 p-3 mx-2 rounded-xl transition-colors cursor-pointer ${isGroupActive ? 'bg-white border border-primary/10 shadow-sm active-chat' : 'hover:bg-white/40'}" 
                                         data-group-id="${group.id}"
                                         data-group-name="${escapeHtml(group.name)}"
                                         data-group-avatar="${group.avatar_url}"
                                         data-group-members-count="${group.members.length}">
                                        <div class="relative shrink-0">
                                            <div class="w-11 h-11 rounded-full overflow-hidden border border-outline-variant bg-surface-container-high animate-fade-in">
                                                <img alt="${escapeHtml(group.name)}" class="w-full h-full object-cover" src="${group.avatar_url}">
                                            </div>
                                        </div>
                                        <div class="flex-grow min-w-0 text-right">
                                            <h3 class="font-title-lg text-xs font-bold text-primary truncate mb-0.5">${escapeHtml(group.name)}</h3>
                                            <p class="last-message-text font-body-md text-[11px] text-on-surface-variant truncate leading-normal">
                                                ${escapeHtml(group.latest_message)}
                                            </p>
                                        </div>
                                        <div class="flex flex-col items-end justify-between shrink-0 self-stretch text-left select-none">
                                            <span class="last-message-time font-label-sm text-[9px] text-on-surface-variant whitespace-nowrap">
                                                ${group.latest_message_time || ''}
                                            </span>
                                            <span class="unread-badge bg-primary text-white text-[9px] font-bold min-w-[16px] h-4 px-1 flex items-center justify-center rounded-full mt-1.5 ${unreadHidden} shrink-0 shadow-sm border border-white leading-none">
                                                ${unreadCount}
                                            </span>
                                        </div>
                                    </div>
                                `;
                                wrapper.append(itemHtml);
                            });
                        } else {
                            wrapper.html('<div class="text-center py-6 text-on-surface-variant text-xs">' + _tp.noActiveGroups + '</div>');
                        }
                    },
                    error: function() {
                        wrapper.html('<div class="text-center py-4 text-red-500 text-xs">' + _tp.failedLoadingGroups + '</div>');
                    }
                });
            }

            // --- Tabs Switching ---
            $(document).on('click', '.tab-btn', function(e) {
                e.preventDefault();
                $('.tab-btn').removeClass('bg-primary text-white shadow-sm').addClass('text-on-surface-variant hover:bg-surface-container-high');
                $(this).addClass('bg-primary text-white shadow-sm').removeClass('text-on-surface-variant hover:bg-surface-container-high');
                
                const tabId = $(this).attr('id');
                if (tabId === 'tab-messages-btn') {
                    $('#dm-conversations-container').removeClass('hidden');
                    $('#group-conversations-container').addClass('hidden');
                } else if (tabId === 'tab-groups-btn') {
                    $('#dm-conversations-container').addClass('hidden');
                    $('#group-conversations-container').removeClass('hidden');
                    loadGroupsList();
                }
            });

            // --- Create Group Modal Handlers ---
            $(document).on('click', '#open-create-group-modal-btn', function(e) {
                e.preventDefault();
                $('#create-group-form')[0].reset();
                $('#group-image-preview').attr('src', '{{ asset("upload/no_image.jpg") }}');
                $('#group-friends-list .group-friend-item').show();
                $('.group-member-checkbox').prop('checked', false);
                
                const modal = $('#create-group-modal');
                modal.removeClass('hidden').addClass('flex');
                setTimeout(() => {
                    modal.addClass('modal-show');
                }, 10);
            });

            function closeCreateGroupModal() {
                const modal = $('#create-group-modal');
                modal.removeClass('modal-show');
                setTimeout(() => {
                    modal.removeClass('flex').addClass('hidden');
                }, 300);
            }

            $(document).on('click', '#close-create-group-modal-btn, #create-group-modal .modal-backdrop', function(e) {
                e.preventDefault();
                closeCreateGroupModal();
            });

            // Image preview
            $(document).on('change', '#group-image-input', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#group-image-preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Client side filter friends list
            $(document).on('input', '#group-friend-search', function() {
                const query = $(this).val().toLowerCase().trim();
                $('#group-friends-list .group-friend-item').each(function() {
                    const name = $(this).attr('data-name').toLowerCase();
                    if (name.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Toggle checkbox when friend item clicked
            $(document).on('click', '.group-friend-item', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    const cb = $(this).find('.group-member-checkbox');
                    cb.prop('checked', !cb.prop('checked'));
                }
            });

            // Submit create group
            $(document).on('submit', '#create-group-form', function(e) {
                e.preventDefault();
                const name = $('#group-name-input').val().trim();
                const selectedMembers = $('.group-member-checkbox:checked');
                
                if (!name) {
                    toastr.warning(_tp.enterGroupNameWarn);
                    return;
                }
                if (selectedMembers.length === 0) {
                    toastr.warning(_tp.chooseAtLeastOneMemberWarn);
                    return;
                }
                
                const formData = new FormData(this);
                $('#submit-create-group-btn').prop('disabled', true).addClass('opacity-50').text(_tp.creatingGroupStatus);
                
                $.ajax({
                    url: '/messages/groups/create',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submit-create-group-btn').prop('disabled', false).removeClass('opacity-50').html('<span class="material-symbols-outlined text-sm">check</span><span>' + _tp.createGroupSubmit + '</span>');
                        if (response.status === 'success') {
                            toastr.success(_tp.groupCreatedSuccess);
                            closeCreateGroupModal();
                            loadGroupsList();
                            
                            // Select group
                            setTimeout(() => {
                                selectGroupConversation(
                                    response.group.id,
                                    response.group.name,
                                    response.group.avatar_url,
                                    response.group.members.length
                                );
                            }, 500);
                        } else {
                            toastr.error(_tp.groupCreationFailed);
                        }
                    },
                    error: function(xhr) {
                        $('#submit-create-group-btn').prop('disabled', false).removeClass('opacity-50').html('<span class="material-symbols-outlined text-sm">check</span><span>' + _tp.createGroupSubmit + '</span>');
                        const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.unexpectedErrorGroupCreation;
                        toastr.error(errorMsg);
                    }
                });
            });

            // --- Group Info Modal Actions ---
            $(document).on('click', '#group-info-btn', function(e) {
                e.preventDefault();
                if (!activeGroupId) return;

                const wrapper = $('#info-group-members-list');
                wrapper.html('<div class="text-center py-4 text-on-surface-variant text-xs">' + _tp.loadingData + '</div>');
                $('#info-group-actions-container').empty();

                $.ajax({
                    url: `/messages/groups/${activeGroupId}/details`,
                    type: 'GET',
                    success: function(response) {
                        if (response.status === 'success') {
                            const group = response.group;
                            const isCreator = response.is_creator;
                            const currentUserId = parseInt(response.auth_user_id);
                            
                            // Fill fields
                            $('#info-group-avatar').attr('src', group.avatar_url);
                            $('#info-group-name').text(group.name);
                            $('#info-group-desc').text(group.descriptions || _tp.noGroupDesc);
                            $('#info-group-members-count').text(group.members.length);

                            // Populate members list
                            wrapper.empty();
                            group.members.forEach(member => {
                                const user = member.user;
                                if (!user) return;

                                const memberName = user.first_name + ' ' + user.last_name;
                                const isMemberCreator = (parseInt(user.id) === parseInt(group.created_by_user_id));
                                
                                // Build role badge
                                let roleBadge = '';
                                if (isMemberCreator) {
                                    roleBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-200">${_tp.creatorRole}</span>';
                                } else if (member.role && member.role.name === 'Admin') {
                                    roleBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-primary/10 text-primary border border-primary/20">${_tp.adminRole}</span>';
                                } else {
                                    roleBadge = '<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">${_tp.memberRole}</span>';
                                }

                                // Build action button
                                let actionBtn = '';
                                if (isCreator && !isMemberCreator) {
                                    actionBtn = `
                                        <button class="remove-group-member-btn text-red-500 hover:bg-red-50 p-1.5 rounded-full border-none bg-transparent cursor-pointer flex items-center justify-center transition-colors animate-fade-in" data-user-id="${user.id}" title="${_tp.removeMemberTitle}">
                                            <span class="material-symbols-outlined text-[16px]">person_remove</span>
                                        </button>
                                    `;
                                }

                                const itemHtml = `
                                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-100/50 transition-colors" id="member-row-${user.id}">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full overflow-hidden border border-outline-variant shrink-0 bg-slate-100">
                                                <img alt="${escapeHtml(memberName)}" class="w-full h-full object-cover" src="${user.avatar_url}">
                                            </div>
                                            <div class="flex flex-col gap-0.5 text-right">
                                                <span class="text-xs font-bold text-on-surface">${escapeHtml(memberName)}</span>
                                                <div class="flex items-center gap-1">${roleBadge}</div>
                                            </div>
                                        </div>
                                        <div>${actionBtn}</div>
                                    </div>
                                `;
                                wrapper.append(itemHtml);
                            });

                            // Build action footer buttons
                            const actionsContainer = $('#info-group-actions-container');
                            if (isCreator) {
                                actionsContainer.html(`
                                    <button id="delete-group-btn" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-sm border-none cursor-pointer transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-sm">delete_forever</span>
                                        <span>${_tp.deleteGroupPermanently}</span>
                                    </button>
                                `);
                            } else {
                                actionsContainer.html(`
                                    <button id="leave-group-btn" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-sm border-none cursor-pointer transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-sm">logout</span>
                                        <span>${_tp.leaveGroup}</span>
                                    </button>
                                `);
                            }

                            // Show modal
                            const modal = $('#group-info-modal');
                            modal.removeClass('hidden').addClass('flex');
                            setTimeout(() => {
                                modal.addClass('modal-show');
                            }, 10);
                        } else {
                            toastr.error(_tp.failedGroupDetails);
                        }
                    },
                    error: function() {
                        toastr.error(_tp.failedConnGroupDetails);
                    }
                });
            });

            function closeGroupInfoModal() {
                const modal = $('#group-info-modal');
                modal.removeClass('modal-show');
                setTimeout(() => {
                    modal.removeClass('flex').addClass('hidden');
                }, 300);
            }

            $(document).on('click', '#close-group-info-modal-btn, #group-info-modal .modal-backdrop', function(e) {
                e.preventDefault();
                closeGroupInfoModal();
            });

            // Action: Remove Member
            $(document).on('click', '.remove-group-member-btn', function(e) {
                e.preventDefault();
                const userId = $(this).attr('data-user-id');
                const row = $(`#member-row-${userId}`);

                if (confirm(_tp.confirmRemoveMember)) {
                    $.ajax({
                        url: `/messages/groups/${activeGroupId}/members/remove`,
                        type: 'POST',
                        data: { user_id: userId },
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(_tp.memberRemovedSuccess);
                                row.fadeOut(300, function() {
                                    $(this).remove();
                                    const currentCount = parseInt($('#info-group-members-count').text());
                                    $('#info-group-members-count').text(currentCount - 1);
                                    // Update active group header members count
                                    $('#active-user-status-text').text((currentCount - 1) + ' ' + _tp.membersCountLabel);
                                    loadGroupsList();
                                });
                            } else {
                                toastr.error(response.message || _tp.memberRemovalFailed);
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.errorRemovingMember;
                            toastr.error(errorMsg);
                        }
                    });
                }
            });

            // Action: Leave Group
            $(document).on('click', '#leave-group-btn', function(e) {
                e.preventDefault();
                if (confirm(_tp.confirmLeaveGroup)) {
                    $.ajax({
                        url: `/messages/groups/${activeGroupId}/leave`,
                        type: 'POST',
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(_tp.groupLeaveSuccess);
                                closeGroupInfoModal();
                                
                                // Reset active conversation view
                                activeGroupId = null;
                                $('#chat-main-panel').addClass('hidden');
                                $('#chat-empty-state').removeClass('hidden');
                                loadGroupsList();
                            } else {
                                toastr.error(response.message || _tp.groupLeaveFailed);
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.errorLeavingGroup;
                            toastr.error(errorMsg);
                        }
                    });
                }
            });

            // Action: Delete Group
            $(document).on('click', '#delete-group-btn', function(e) {
                e.preventDefault();
                if (confirm(_tp.confirmDeleteGroup)) {
                    $.ajax({
                        url: `/messages/groups/${activeGroupId}/delete`,
                        type: 'DELETE',
                        success: function(response) {
                            if (response.status === 'success') {
                                toastr.success(_tp.groupDeletedSuccess);
                                closeGroupInfoModal();
                                
                                // Reset active conversation view
                                activeGroupId = null;
                                $('#chat-main-panel').addClass('hidden');
                                $('#chat-empty-state').removeClass('hidden');
                                loadGroupsList();
                            } else {
                                toastr.error(response.message || _tp.groupDeleteFailed);
                            }
                        },
                        error: function(xhr) {
                            const errorMsg = xhr.responseJSON ? xhr.responseJSON.message : _tp.errorDeletingGroup;
                            toastr.error(errorMsg);
                        }
                    });
                }
            });

            // Load groups directly if group_id is in URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const urlGroupId = urlParams.get('group_id');
            if (urlGroupId) {
                $('#tab-groups-btn').click();
                setTimeout(() => {
                    const groupItem = $(`.group-item[data-group-id="${urlGroupId}"]`);
                    if (groupItem.length > 0) {
                        groupItem.click();
                    } else {
                        selectGroupConversation(urlGroupId, _tp.groupLabel, '{{ asset("upload/no_image.jpg") }}', 0);
                    }
                }, 1000);
            }

            // Listen for call signaling events
            window.Echo.private(`chat.${authUserId}`)
                .listen('.CallInitiated', (e) => {
                    handleIncomingCall(e);
                })
                .listen('.GroupCallInitiated', (e) => {
                    handleIncomingGroupCall(e);
                })
                .listen('.CallAccepted', (e) => {
                    handleCallAccepted(e);
                })
                .listen('.CallDeclined', (e) => {
                    handleCallDeclined(e);
                })
                .listen('.CallEnded', (e) => {
                    handleCallEnded(e);
                });

            // Typing indicator event trigger
            $('#message-textarea').on('input', function() {
                if (activeUserId && activeConversationChannel) {
                    if (!typingTimeout) {
                        console.log('Whispering typing:true to chat.' + activeUserId);
                        activeConversationChannel.whisper('typing', {
                            sender_id: authUserId,
                            typing: true
                        });
                    }
                    
                    clearTimeout(typingTimeout);
                    
                    typingTimeout = setTimeout(function() {
                        if (activeUserId && activeConversationChannel) {
                            console.log('Whispering typing:false to chat.' + activeUserId);
                            activeConversationChannel.whisper('typing', {
                                sender_id: authUserId,
                                typing: false
                            });
                        }
                        typingTimeout = null;
                    }, 2000);
                }
            });
        } else {
            console.error('Laravel Echo is not defined on the window object.');
        }
    });
</script>
@endpush
