@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $imageUrl = url('upload/no_image.jpg');
    if ($group->image_path) {
        $imageUrl = filter_var($group->image_path, FILTER_VALIDATE_URL) 
            ? $group->image_path 
            : asset('upload/group_site_images/' . basename($group->image_path));
    }
    
    $logoUrl = url('upload/no_image.jpg');
    if ($group->logo_path) {
        $logoUrl = filter_var($group->logo_path, FILTER_VALIDATE_URL) 
            ? $group->logo_path 
            : asset('upload/group_site_logos/' . basename($group->logo_path));
    }
    
    $isOpen = $group->status === 'open';

    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp

<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24" style="direction: {{ $dir }};">
    <!-- Group Hero Section -->
    <section class="wisdom-card overflow-hidden bg-white rounded-2xl border border-[#E1E8E1] shadow-sm mb-8 relative">
        <div class="h-64 lg:h-80 relative">
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-transparent z-10"></div>
            <!-- Cover Image: clickable -->
            <div id="group-cover-trigger" title="{{ __t('view_cover_image') }}" class="w-full h-full bg-cover bg-center cursor-pointer group/cover" style="background-image: url('{{ $imageUrl }}')" data-img-url="{{ $imageUrl }}">
                <div class="absolute inset-0 bg-black/0 group-hover/cover:bg-black/20 transition-all duration-300 z-[5] flex items-center justify-center">
                    <span class="material-symbols-outlined text-white/0 group-hover/cover:text-white/80 text-5xl transition-all duration-300">zoom_in</span>
                </div>
            </div>
            @if(auth()->check() && $group->admin_user_id === auth()->id())
                <div class="absolute top-4 {{ $dir === 'rtl' ? 'left-4' : 'right-4' }} z-30">
                    <button type="button" id="edit-cover-btn" class="bg-black/60 hover:bg-black text-white p-2 rounded-full transition-all cursor-pointer flex items-center justify-center border border-white/20 shadow-md" title="{{ __t('edit_cover_image') }}">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                    </button>
                    <input type="file" id="edit-cover-input" class="hidden" accept="image/*">
                </div>
            @endif
            
            <div class="absolute bottom-6 {{ $dir === 'rtl' ? 'right-6 left-6 lg:right-10' : 'left-6 right-6 lg:left-10' }} z-20 flex flex-col md:flex-row items-end md:items-center justify-between gap-6">
                <div class="flex items-center gap-6 {{ $textAlign }}">
                    <!-- Logo / Avatar: clickable -->
                    <div id="group-logo-trigger" title="{{ __t('view_group_logo') }}" class="w-24 h-24 lg:w-32 lg:h-32 rounded-2xl border-4 border-white shadow-xl overflow-hidden bg-white shrink-0 cursor-pointer relative group/logo" data-img-url="{{ $logoUrl }}">
                        <img id="group-logo-img" class="w-full h-full object-cover" src="{{ $logoUrl }}" alt="{{ $group->title }}">
                        <div class="absolute inset-0 bg-black/0 group-hover/logo:bg-black/30 transition-all duration-300 flex items-center justify-center rounded-xl">
                            <span class="material-symbols-outlined text-white/0 group-hover/logo:text-white/90 text-3xl transition-all duration-300">zoom_in</span>
                        </div>
                        @if(auth()->check() && $group->admin_user_id === auth()->id())
                            <div class="absolute bottom-2 {{ $dir === 'rtl' ? 'left-2' : 'right-2' }} z-30">
                                <button type="button" id="edit-logo-btn" class="bg-primary hover:bg-primary-container text-white p-1.5 rounded-full transition-all cursor-pointer flex items-center justify-center border border-white shadow-md" title="{{ __t('edit_logo') }}">
                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                </button>
                                <input type="file" id="edit-logo-input" class="hidden" accept="image/*">
                            </div>
                        @endif
                    </div>
                    <div class="pb-2">
                        @if(auth()->check() && $group->admin_user_id === auth()->id())
                            <div id="group-title-display-container" class="flex items-center gap-2">
                                <h2 id="group-title-display" class="font-headline-lg text-xl md:text-2xl font-bold" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);">{{ $group->title }}</h2>
                                <button type="button" id="edit-title-btn" class="text-white/70 hover:text-white transition-colors cursor-pointer bg-transparent border-0 p-1 flex items-center justify-center" title="{{ __t('edit_group_name') }}" style="text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                            </div>
                            <div id="group-title-edit-container" class="hidden flex items-center gap-2">
                                <input type="text" id="group-title-input" class="bg-black/60 text-white border border-white/30 rounded-xl px-3 py-1.5 text-sm font-bold focus:outline-none focus:ring-1 focus:ring-white w-64" value="{{ $group->title }}">
                                <button type="button" id="save-title-btn" class="bg-white text-primary hover:bg-white/95 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">{{ __t('save') }}</button>
                                <button type="button" id="cancel-title-btn" class="bg-black/50 hover:bg-black/70 text-white/95 px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">{{ __t('cancel') }}</button>
                            </div>
                        @else
                            <h2 class="font-headline-lg text-xl md:text-2xl font-bold mb-1" style="color: #ffffff !important; text-shadow: 0 2px 8px rgba(0, 0, 0, 0.85);">{{ $group->title }}</h2>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3 mb-2 shrink-0">
                    @if(auth()->check())
                        @if($group->admin_user_id === auth()->id())
                            <div class="flex gap-2">
                                <span class="bg-secondary text-on-secondary px-4 py-2.5 rounded-lg border border-secondary text-xs font-bold transition-all flex items-center gap-1.5 cursor-default">
                                    <span class="material-symbols-outlined text-[16px]">admin_panel_settings</span>
                                    <span>{{ __t('you_are_group_admin') }}</span>
                                </span>
                                <button id="delete-group-btn" data-group-id="{{ $group->id }}" class="bg-error hover:bg-error/90 text-white px-4 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                    <span>{{ __t('delete_group') }}</span>
                                </button>
                            </div>
                        @elseif($isMember)
                            <button id="leave-group-btn" data-group-id="{{ $group->id }}" class="bg-error/15 text-error px-4 py-2.5 rounded-lg border border-error/30 hover:bg-error/25 text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">logout</span>
                                <span>{{ __t('leave_group') }}</span>
                            </button>
                        @else
                            @if($isOpen)
                                <button class="join-open-group-btn primary-btn flex items-center gap-2 cursor-pointer" data-group-id="{{ $group->id }}">
                                    <span class="material-symbols-outlined text-[16px]">group_add</span>
                                    <span>{{ __t('join_group') }}</span>
                                </button>
                            @else
                                <button class="trigger-join-closed-modal primary-btn flex items-center gap-2 cursor-pointer" data-group-id="{{ $group->id }}">
                                    <span class="material-symbols-outlined text-[16px]">vpn_key</span>
                                    <span>{{ __t('request_join_private') }}</span>
                                </button>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('user.login') }}" class="primary-btn flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">login</span>
                            <span>{{ __t('login_to_participate') }}</span>
                        </a>
                    @endif
                    
                    <button id="share-group-btn" data-group-id="{{ $group->id }}" data-group-title="{{ $group->title }}" data-group-desc="{{ strip_tags($group->description) }}" class="bg-white/20 backdrop-blur-md text-white px-4 py-2.5 rounded-lg border border-white/30 hover:bg-white/30 transition-all cursor-pointer flex items-center justify-center" title="{{ __t('share_group') }}">
                        <span class="material-symbols-outlined">share</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Group Stats/Tabs -->
        <div class="px-6 lg:px-10 py-4 border-t border-[#E1E8E1] flex justify-between items-center bg-surface-container-lowest">
            <div class="flex gap-1 overflow-x-auto scrollbar-hide">
                <button data-group-tab="posts" class="group-detail-tab active-group-tab flex items-center gap-1.5 py-2 px-4 text-xs md:text-sm rounded-full font-bold transition-all">
                    <span class="material-symbols-outlined text-[16px]">forum</span>
                    <span>{{ __t('posts') }}</span>
                    <span class="group-subjects-count-tab bg-current/10 px-2 py-0.5 rounded-full text-[10px]">{{ $group->subjects_count }}</span>
                </button>
                <button data-group-tab="members" class="group-detail-tab flex items-center gap-1.5 py-2 px-4 text-xs md:text-sm rounded-full font-bold transition-all">
                    <span class="material-symbols-outlined text-[16px]">group</span>
                    <span>{{ __t('members') }}</span>
                    <span class="bg-current/10 px-2 py-0.5 rounded-full text-[10px]">{{ $group->members_count }}</span>
                </button>
            </div>
            <div class="hidden md:flex gap-6 text-on-surface-variant font-label-md text-xs">
                <span><strong>{{ $group->members_count }}</strong> {{ __t('member') }}</span>
                <span><strong class="group-subjects-count-stats">{{ $group->subjects_count }}</strong> {{ __t('topic') }}</span>
            </div>
        </div>
    </section>

    <!-- ========= PANEL: Members ========= -->
    <div id="group-panel-members" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Members Full List -->
            <div class="lg:col-span-12">
                <div class="bg-white rounded-2xl border border-[#E1E8E1] shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-[#E1E8E1] bg-surface-container-lowest">
                        <h3 class="font-headline-md text-primary text-sm font-bold flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">group</span>
                            {{ __t('group_members') }}
                            <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-[11px] font-bold">{{ $group->members_count }}</span>
                        </h3>
                        @if(auth()->check() && $isMember && $group->admin_user_id !== auth()->id())
                        <button id="leave-group-members-tab-btn" data-group-id="{{ $group->id }}"
                            class="flex items-center gap-1.5 bg-error/10 hover:bg-error/20 text-error px-3 py-1.5 rounded-full text-[11px] font-bold transition-all cursor-pointer border border-error/20">
                            <span class="material-symbols-outlined text-[15px]">logout</span>
                            {{ __t('leave_group') }}
                        </button>
                        @endif
                    </div>

                    <!-- Members List -->
                    <div class="divide-y divide-[#E1E8E1]">
                        @php $isAdmin = auth()->check() && $group->admin_user_id === auth()->id(); @endphp

                        {{-- Admin row first --}}
                        @if($group->admin)
                        @php
                            $adminAvatar = url('upload/no_image.jpg');
                            if ($group->admin->profile_picture && $group->admin->profile_picture !== 'non') {
                                $adminAvatar = filter_var($group->admin->profile_picture, FILTER_VALIDATE_URL)
                                    ? $group->admin->profile_picture
                                    : asset('new_wiselook/uploads/' . $group->admin->profile_picture);
                            }
                        @endphp
                        <div class="flex items-center justify-between px-6 py-4 bg-secondary/3 hover:bg-secondary/5 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="relative shrink-0">
                                    <img src="{{ $adminAvatar }}" alt="{{ $group->admin->first_name }}"
                                        class="w-12 h-12 rounded-full object-cover border-2 border-secondary shadow-sm">
                                    <span class="absolute -bottom-1 {{ $dir === 'rtl' ? '-right-1' : '-left-1' }} w-5 h-5 bg-secondary rounded-full flex items-center justify-center shadow-sm" title="{{ __t('group_admin_title') }}">
                                        <span class="material-symbols-outlined text-white text-[12px]">shield</span>
                                    </span>
                                </div>
                                <div class="{{ $textAlign }}">
                                    <p class="font-bold text-sm text-primary">{{ $group->admin->first_name }} {{ $group->admin->last_name }}</p>
                                    <p class="text-[10px] text-on-surface-variant mt-0.5">{{ $group->admin->email ?? $group->admin->phone_number }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex items-center gap-1 bg-secondary/15 text-secondary px-3 py-1 rounded-full text-[11px] font-bold">
                                    <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                    {{ __t('founder_and_admin') }}
                                </span>
                            </div>
                        </div>
                        @endif

                        {{-- Regular members --}}
                        @foreach($group->members as $member)
                            @if($member->id === $group->admin_user_id) @continue @endif
                            @php
                                $memberAvatar = url('upload/no_image.jpg');
                                if ($member->profile_picture && $member->profile_picture !== 'non') {
                                    $memberAvatar = filter_var($member->profile_picture, FILTER_VALIDATE_URL)
                                        ? $member->profile_picture
                                        : asset('new_wiselook/uploads/' . $member->profile_picture);
                                }
                                $isCurrentUser = auth()->check() && auth()->id() === $member->id;
                            @endphp
                            <div class="member-row flex items-center justify-between px-6 py-4 hover:bg-surface-container-lowest transition-all" data-member-id="{{ $member->id }}" data-member-name="{{ $member->first_name }} {{ $member->last_name }}">
                                <div class="flex items-center gap-4">
                                    <div class="relative shrink-0">
                                        <img src="{{ $memberAvatar }}" alt="{{ $member->first_name }}"
                                            class="w-11 h-11 rounded-full object-cover border border-outline-variant shadow-sm">
                                        @if($isCurrentUser)
                                        <span class="absolute -bottom-1 {{ $dir === 'rtl' ? '-right-1' : '-left-1' }} w-4 h-4 bg-primary rounded-full border-2 border-white" title="{{ __t('you') }}"></span>
                                        @endif
                                    </div>
                                    <div class="{{ $textAlign }}">
                                        <p class="font-bold text-sm text-on-surface">
                                            {{ $member->first_name }} {{ $member->last_name }}
                                            @if($isCurrentUser)
                                            <span class="text-[10px] text-primary font-bold bg-primary/10 px-1.5 py-0.5 rounded-full ml-1">{{ __t('you') }}</span>
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-on-surface-variant mt-0.5">{{ $member->email ?? $member->phone_number }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="flex items-center gap-1 bg-primary/8 text-on-surface-variant px-2.5 py-1 rounded-full text-[10px] font-medium">
                                        <span class="material-symbols-outlined text-[13px]">person</span>
                                        {{ __t('member') }}
                                    </span>
                                    @if($isAdmin)
                                    {{-- Admin: can kick any regular member --}}
                                    <button class="kick-member-btn flex items-center gap-1 bg-error/10 hover:bg-error/20 text-error px-2.5 py-1 rounded-full text-[10px] font-bold transition-all cursor-pointer border border-error/15"
                                        data-group-id="{{ $group->id }}" data-user-id="{{ $member->id }}" data-member-name="{{ $member->first_name }} {{ $member->last_name }}">
                                        <span class="material-symbols-outlined text-[13px]">person_remove</span>
                                        {{ __t('delete') }}
                                    </button>
                                    @elseif($isCurrentUser)
                                    {{-- Current user: can leave --}}
                                    <button class="leave-from-list-btn flex items-center gap-1 bg-error/10 hover:bg-error/20 text-error px-2.5 py-1 rounded-full text-[10px] font-bold transition-all cursor-pointer border border-error/15"
                                        data-group-id="{{ $group->id }}">
                                        <span class="material-symbols-outlined text-[13px]">logout</span>
                                        {{ __t('leave') }}
                                    </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($group->members->isEmpty())
                        <div class="px-6 py-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-on-surface-variant mb-3 block">group_off</span>
                            <p class="text-sm text-on-surface-variant">{{ __t('no_group_members_yet') }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ========= PANEL: Posts ========= -->
    <div id="group-panel-posts">
    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Right Sidebar (RTL: Appears on the right on Desktop) -->
        <aside class="lg:col-span-4 order-2 lg:order-1 flex flex-col gap-6 xl:sticky xl:top-24 self-start">
            <!-- About Section -->
            <div class="wisdom-card p-6 bg-white rounded-2xl border border-[#E1E8E1]">
                <h3 class="font-headline-md text-primary text-sm font-bold mb-4 flex items-center justify-between gap-2 border-b border-primary/5 pb-2">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined">info</span>
                        {{ __t('about_group') }}
                    </span>
                    @if(auth()->check() && $group->admin_user_id === auth()->id())
                        <button type="button" id="edit-desc-btn" class="text-secondary hover:text-primary transition-colors cursor-pointer bg-transparent border-0 p-1 flex items-center justify-center" title="{{ __t('edit_group_desc') }}">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                        </button>
                    @endif
                </h3>
                <div id="group-desc-display-container">
                    <p id="group-desc-display" class="text-on-surface-variant font-body-md text-xs leading-relaxed mb-4 {{ $textAlign }} whitespace-pre-line">
                        {{ $group->description ?: __t('group_desc_default') }}
                    </p>
                </div>
                @if(auth()->check() && $group->admin_user_id === auth()->id())
                    <div id="group-desc-edit-container" class="hidden space-y-3 mb-4">
                        <textarea id="group-desc-input" rows="4" class="w-full bg-surface border border-primary/10 rounded-xl py-2 px-3 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary {{ $textAlign }}" placeholder="{{ __t('write_group_desc_placeholder') }}">{{ $group->description }}</textarea>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="save-desc-btn" class="bg-primary hover:bg-primary-container text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">{{ __t('save') }}</button>
                            <button type="button" id="cancel-desc-btn" class="bg-surface-container-high hover:bg-surface-container-highest text-on-surface-variant px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer">{{ __t('cancel') }}</button>
                        </div>
                    </div>
                @endif
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-on-surface-variant text-xs">
                        <span class="material-symbols-outlined text-primary text-[18px]">{{ $isOpen ? 'public' : 'lock' }}</span>
                        <span>{{ $isOpen ? __t('public_group_visibility') : __t('private_group_visibility') }}</span>
                    </div>
                    @if(!$isOpen && auth()->check() && ($isMember || $group->admin_user_id === auth()->id()))
                        <div id="copy-invite-code-box" data-code="{{ $group->invite_code }}" class="flex items-center justify-between gap-2 text-on-surface-variant text-xs bg-secondary/10 p-2 py-1.5 rounded-lg border border-secondary/20 font-bold cursor-pointer transition-all hover:bg-secondary/15" title="{{ __t('click_to_copy_code') }}">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-secondary text-[18px]">vpn_key</span>
                                <span>{{ __t('join_code_label') }}: <span class="text-secondary font-mono">{{ $group->invite_code }}</span></span>
                            </div>
                            <span class="material-symbols-outlined text-secondary text-[16px] hover:text-primary transition-colors shrink-0">content_copy</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 text-on-surface-variant text-xs">
                        <span class="material-symbols-outlined text-primary text-[18px]">history</span>
                        @php
                            $arabicMonths = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
                            $englishMonths = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                            $createdAt    = \Carbon\Carbon::parse($group->created_at);
                            $currentLocale = current_language()->code ?? 'ar';
                            $monthName    = $currentLocale === 'ar' ? $arabicMonths[$createdAt->month] : $englishMonths[$createdAt->month];
                        @endphp
                        <span>{{ __t('founded_in') }} {{ $monthName }} {{ $createdAt->year }}</span>
                    </div>
                </div>
            </div>

            <!-- Admin Section -->
            @if($group->admin)
                @php
                    $adminAvatar = url('upload/no_image.jpg');
                    if ($group->admin->profile_picture && $group->admin->profile_picture !== 'non') {
                        $adminAvatar = filter_var($group->admin->profile_picture, FILTER_VALIDATE_URL)
                            ? $group->admin->profile_picture
                            : asset('new_wiselook/uploads/' . $group->admin->profile_picture);
                    }
                @endphp
                <div class="wisdom-card p-6 bg-white rounded-2xl border border-[#E1E8E1] border-r-4 border-secondary">
                    <h3 class="font-headline-md text-primary text-sm font-bold mb-4 flex items-center gap-2 border-b border-primary/5 pb-2">
                        <span class="material-symbols-outlined text-secondary">admin_panel_settings</span>
                        {{ __t('group_administration') }}
                    </h3>
                    <div class="flex items-center gap-4 {{ $textAlign }}">
                        <div class="w-14 h-14 rounded-full overflow-hidden border-2 border-secondary shrink-0">
                            <img class="w-full h-full object-cover" src="{{ $adminAvatar }}" alt="{{ $group->admin->first_name }}">
                        </div>
                        <div class="truncate">
                            <p class="font-title-lg text-primary text-sm font-bold truncate">{{ $group->admin->first_name }} {{ $group->admin->last_name }}</p>
                            <p class="font-label-md text-on-surface-variant text-[10px] mt-0.5 truncate">{{ $group->admin->email ?? $group->admin->phone_number }}</p>
                            <p class="font-label-md text-[10px] text-secondary font-bold mt-1">{{ __t('group_admin_title') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Members Section -->
            <div class="wisdom-card p-6 bg-white rounded-2xl border border-[#E1E8E1]">
                <div class="flex justify-between items-center mb-4 border-b border-primary/5 pb-2">
                    <h3 class="font-headline-md text-primary text-sm font-bold">{{ __t('members') }} ({{ $group->members_count }})</h3>
                </div>
                <div class="grid grid-cols-5 gap-2 mb-4">
                    @foreach($group->members as $member)
                        @php
                            $memberAvatar = url('upload/no_image.jpg');
                            if ($member->profile_picture && $member->profile_picture !== 'non') {
                                $memberAvatar = filter_var($member->profile_picture, FILTER_VALIDATE_URL)
                                    ? $member->profile_picture
                                    : asset('new_wiselook/uploads/' . $member->profile_picture);
                            }
                        @endphp
                        <div class="aspect-square rounded-lg overflow-hidden bg-surface-container" title="{{ $member->first_name }} {{ $member->last_name }}">
                            <img class="w-full h-full object-cover" src="{{ $memberAvatar }}" alt="{{ $member->first_name }}">
                        </div>
                    @endforeach
                    @if($group->members_count > 5)
                        <div class="aspect-square rounded-lg bg-primary-container text-white flex items-center justify-center text-xs font-bold shrink-0">
                            +{{ $group->members_count - 5 }}
                        </div>
                    @endif
                </div>
                @if($group->members->isNotEmpty())
                    <p class="text-[10px] text-on-surface-variant flex items-center gap-1.5 justify-start">
                        <span class="material-symbols-outlined text-secondary text-[16px]">check_circle</span>
                        <span>{{ __t('last_joined_prefix') }}: {{ $group->members->first()->first_name }} {{ $group->members->first()->last_name }}</span>
                    </p>
                @endif
            </div>
        </aside>

        <!-- Main Feed Area (RTL: Appears on the left on Desktop) -->
        <div class="lg:col-span-8 order-1 lg:order-2 space-y-6">
            @if(auth()->check() && $isMember)
                <!-- Create Post Box -->
                <div class="wisdom-card p-6 bg-white rounded-2xl border border-[#E1E8E1] shadow-sm mb-6">
                    <form id="create-subject-form" action="{{ route('frontend.groups.subjects.store', $group->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div class="flex items-start gap-4">
                            @php
                                $userAvatar = url('upload/no_image.jpg');
                                if (auth()->user()->profile_picture && auth()->user()->profile_picture !== 'non') {
                                    $userAvatar = filter_var(auth()->user()->profile_picture, FILTER_VALIDATE_URL)
                                        ? auth()->user()->profile_picture
                                        : asset('new_wiselook/uploads/' . auth()->user()->profile_picture);
                                }
                            @endphp
                            <div class="w-12 h-12 rounded-full overflow-hidden shrink-0 border border-outline-variant">
                                <img class="w-full h-full object-cover" src="{{ $userAvatar }}" alt="User">
                            </div>
                            <div class="flex-1 space-y-3">
                                <input type="text" name="title" required class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary {{ $textAlign }}" placeholder="{{ __t('topic_title_placeholder') }}">
                                <textarea name="description" required rows="3" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary {{ $textAlign }}" placeholder="{{ __t('topic_body_placeholder') }}"></textarea>
                            </div>
                        </div>
                        
                        <!-- Media Preview Container -->
                        <div id="media-preview-container" class="hidden relative border border-primary/10 rounded-xl overflow-hidden max-h-60 bg-surface flex items-center justify-center">
                            <button type="button" id="remove-media-btn" class="absolute top-2 right-2 bg-black/60 hover:bg-black text-white p-1 rounded-full transition-all cursor-pointer z-10">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                            <img id="image-preview-el" class="hidden max-h-60 w-full object-contain">
                            <video id="video-preview-el" class="hidden max-h-60 w-full" controls></video>
                            <audio id="audio-preview-el" class="hidden w-full p-2" controls></audio>
                        </div>
                        
                        <div class="flex justify-between items-center border-t border-[#E1E8E1] pt-4 px-2">
                            <div class="flex gap-6">
                                <button type="button" id="trigger-media-upload" class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors cursor-pointer bg-transparent border-0">
                                    <span class="material-symbols-outlined text-secondary">image</span>
                                    <span class="text-xs font-bold">{{ __t('attach_media') }}</span>
                                </button>
                                <input type="file" id="media-file-input" name="media" accept="image/*,video/*,audio/*" class="hidden">
                            </div>
                            <button type="submit" class="bg-primary hover:bg-primary-container text-white px-6 py-2 rounded-lg text-xs font-bold transition-all cursor-pointer">{{ __t('post_topic_btn') }}</button>
                        </div>
                    </form>
                </div>
            @else
                <!-- Join Group Prompt Box -->
                <div class="wisdom-card p-8 bg-surface-container-low border border-primary/10 rounded-2xl shadow-sm text-center mb-6">
                    <span class="material-symbols-outlined text-secondary text-5xl mb-3">lock</span>
                    <h4 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('discussion_closed_to_members') }}</h4>
                    <p class="text-xs text-on-surface-variant max-w-md mx-auto mb-4 leading-relaxed">{{ __t('join_to_view_discussions') }}</p>
                    
                    @if(auth()->check())
                        @if($isOpen)
                            <button class="join-open-group-btn bg-secondary text-on-secondary hover:bg-secondary/90 px-6 py-2.5 rounded-full text-xs font-bold transition-all cursor-pointer shadow-sm" data-group-id="{{ $group->id }}">
                                {{ __t('join_group_now') }}
                            </button>
                        @else
                            <button class="trigger-join-closed-modal bg-secondary text-on-secondary hover:bg-secondary/90 px-6 py-2.5 rounded-full text-xs font-bold transition-all cursor-pointer shadow-sm" data-group-id="{{ $group->id }}">
                                {{ __t('enter_join_code_btn') }}
                            </button>
                        @endif
                    @else
                        <a href="{{ route('user.login') }}" class="bg-secondary text-on-secondary hover:bg-secondary/90 px-6 py-2.5 rounded-full text-xs font-bold transition-all inline-block shadow-sm">
                            {{ __t('login_to_join') }}
                        </a>
                    @endif
                </div>
            @endif

            <!-- Posts Feed -->
            @if($group->status === 'closed' && !$isMember)
                <!-- Private Group with no member: Feed hidden -->
                <div class="p-8 text-center text-on-surface-variant text-xs">
                    <span class="material-symbols-outlined text-4xl mb-2">visibility_off</span>
                    <p>{{ __t('discussions_hidden_private_group') }}</p>
                </div>
            @else
                @if($subjects->isEmpty())
                    <div id="empty-feed-placeholder" class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-12 text-center shadow-sm">
                        <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4">forum</span>
                        <p class="font-body-lg text-base font-bold text-on-surface">{{ __t('no_topics_in_group_yet') }}</p>
                        <p class="text-xs text-on-surface-variant mt-2">{{ __t('be_first_to_post_topic') }}</p>
                    </div>
                @else
                    <div id="group-subjects-container" class="space-y-4">
                        @foreach($subjects as $subject)
                            @include('frontend.wiselook.partials.subject_card', ['subject' => $subject, 'group' => $group, 'isMember' => $isMember])
                        @endforeach
                    </div>

                    <!-- Scroll Trigger & Loading Indicator -->
                    <div id="group-subjects-scroll-trigger" class="h-2 w-full"></div>
                    <div id="group-subjects-loader" class="hidden flex justify-center py-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <!-- Mobile Load More Button (Only visible on mobile screens < 1024px) -->
                    <div id="mobile-group-load-more-container" class="py-4 text-center block lg:hidden">
                        <button type="button" id="mobile-group-load-more-btn" onclick="loadMoreGroupSubjects()" class="w-full bg-surface-container-lowest hover:bg-primary/5 text-primary font-bold py-3.5 px-6 rounded-xl border border-primary/20 shadow-sm transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer text-xs sm:text-sm">
                            <span class="material-symbols-outlined text-[18px]">add_circle</span>
                            <span id="mobile-group-load-more-text">تحميل المزيد من المواضيع</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
</div>{{-- END: group-panel-posts --}}

<!-- Join Closed Group Modal -->
<div id="join-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="join-group-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: {{ $dir }};">
        <div class="w-12 h-12 rounded-full bg-secondary-container text-secondary flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">vpn_key</span>
        </div>
        
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('join_private_group_title') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-4">{{ __t('join_private_group_desc') }}</p>
        
        <form id="join-group-form" class="w-full space-y-4">
            <input type="hidden" id="join-group-id" name="group_id">
            <div>
                <input type="text" id="join-invite-code" required class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-center text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary font-bold" placeholder="{{ __t('enter_join_code_placeholder') }}">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-grow bg-primary text-white py-2.5 rounded-full text-xs font-bold hover:bg-primary-dark transition-all shadow-sm flex items-center justify-center gap-1.5">
                    <span>{{ __t('confirm_join_btn') }}</span>
                    <div id="join-group-spinner" class="hidden w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
                <button type="button" id="cancel-join-group-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Subject Confirmation Modal -->
<div id="delete-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="delete-subject-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: {{ $dir }};">
        <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">warning</span>
        </div>
        
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('delete_topic_title') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-6">{{ __t('delete_topic_confirm_desc') }}</p>
        
        <div class="flex gap-3 w-full">
            <button type="button" id="confirm-delete-subject-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">{{ __t('confirm_delete_btn') }}</button>
            <button type="button" id="cancel-delete-subject-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
        </div>
    </div>
</div>

<!-- Leave Group Confirmation Modal -->
<div id="leave-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="leave-group-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: {{ $dir }};">
        <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">logout</span>
        </div>
        
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('leave_group') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-6">{{ __t('leave_group_confirm_desc') }}</p>
        
        <div class="flex gap-3 w-full">
            <button type="button" id="confirm-leave-group-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">{{ __t('leave') }}</button>
            <button type="button" id="cancel-leave-group-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
        </div>
    </div>
</div>

<!-- Delete Group Confirmation Modal -->
<div id="delete-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="delete-group-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: {{ $dir }};">
        <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">delete_forever</span>
        </div>
        
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('delete_group_forever_title') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-6">{{ __t('delete_group_forever_confirm_desc') }}</p>
        
        <div class="flex gap-3 w-full">
            <button type="button" id="confirm-delete-group-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">{{ __t('confirm_delete_btn') }}</button>
            <button type="button" id="cancel-delete-group-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
        </div>
    </div>
</div>

<!-- ===== Group Image Viewer Lightbox ===== -->
<div id="group-image-viewer-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/85 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-3xl w-full max-h-[90vh] flex flex-col items-center justify-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300">
        <!-- Close Button -->
        <button id="close-group-image-viewer-btn" class="absolute -top-12 right-2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
            <span class="material-symbols-outlined text-[28px]">close</span>
        </button>
        
        <!-- Label -->
        <p id="group-image-viewer-label" class="text-white/70 text-xs font-bold mb-3 tracking-wide uppercase"></p>

        <!-- Image Card -->
        <div class="bg-white/5 p-2 rounded-2xl border border-white/10 shadow-2xl overflow-hidden backdrop-blur-sm">
            <img id="group-image-viewer-img" alt="" class="max-w-full max-h-[72vh] rounded-xl object-contain shadow-inner transition-transform duration-300 hover:scale-[1.02]" src="">
        </div>

        <!-- Download Action -->
        <div class="mt-4 flex gap-4">
            <a id="group-image-viewer-download" href="" download class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg font-label-md text-xs backdrop-blur-sm transition-all duration-200 flex items-center gap-2 border border-white/10">
                <span class="material-symbols-outlined text-[18px]">download</span>
                {{ __t('download_image_btn') }}
            </a>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .gold-chip {
        background-color: rgba(202, 168, 0, 0.08);
        color: #735c00;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid rgba(202, 168, 0, 0.15);
    }
    .primary-btn {
        background-color: #003a23;
        color: #ffffff;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 700;
        font-size: 12px;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .primary-btn:hover {
        background-color: #1a5237;
        transform: translateY(-1px);
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    #join-group-modal, #delete-subject-modal, #leave-group-modal, #delete-group-modal {
        transition: visibility 0.3s ease, opacity 0.3s ease;
    }
    #join-group-modal.modal-show, #delete-subject-modal.modal-show, #leave-group-modal.modal-show, #delete-group-modal.modal-show {
        display: flex !important;
    }
    #join-group-modal.modal-show .modal-backdrop, #delete-subject-modal.modal-show .modal-backdrop, #leave-group-modal.modal-show .modal-backdrop, #delete-group-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #join-group-modal.modal-show .modal-container, #delete-subject-modal.modal-show .modal-container, #leave-group-modal.modal-show .modal-container, #delete-group-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Group Image Viewer Lightbox */
    #group-image-viewer-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #group-image-viewer-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    
    .bg-error-container {
        background-color: rgba(186, 26, 26, 0.1);
    }
    
    @keyframes bulb-bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.25); }
    }
    .animate-bulb {
        animation: bulb-bounce 0.4s ease-out;
    }

    /* Group Detail Tabs */
    .group-detail-tab {
        color: #5f6368;
        background: transparent;
    }
    .group-detail-tab.active-group-tab {
        background: #003a23;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(0, 58, 35, 0.25);
    }
    .group-detail-tab:hover:not(.active-group-tab) {
        background: rgba(0,58,35,0.08);
        color: #003a23;
    }

    /* Member row kick/leave fade out animation */
    .member-row.removing {
        opacity: 0;
        transform: translateX(20px);
        transition: opacity 0.35s ease, transform 0.35s ease;
    }
</style>
@endpush

@push('scripts')
<script>
const _gt = {
    delete_topic_error: {!! json_encode(__t('topic_deletion_error')) !!},
    fill_required_fields: {!! json_encode(__t('fill_required_fields')) !!},
    publishing_in_progress: {!! json_encode(__t('publishing_in_progress')) !!},
    topic_published_successfully: {!! json_encode(__t('topic_published_successfully')) !!},
    topic_publishing_error: {!! json_encode(__t('topic_publishing_error')) !!},
    server_connection_error: {!! json_encode(__t('server_connection_error')) !!},
    post_topic_btn: {!! json_encode(__t('post_topic_btn')) !!},
    saving_in_progress: {!! json_encode(__t('saving_in_progress')) !!},
    save: {!! json_encode(__t('save')) !!},
    cover_image_label: {!! json_encode(__t('cover_image_label')) !!},
    group_logo_label: {!! json_encode(__t('group_logo_label')) !!},
    kick_member_confirm: {!! json_encode(__t('kick_member_confirm')) !!},
    leave_group_confirm_simple: {!! json_encode(__t('leave_group_confirm_simple')) !!},
    group_title_required: {!! json_encode(__t('group_title_required')) !!},
    title_update_error: {!! json_encode(__t('title_update_error')) !!},
    desc_update_error: {!! json_encode(__t('desc_update_error')) !!},
    no_group_desc: {!! json_encode(__t('no_group_desc')) !!},
    invite_code_copied: {!! json_encode(__t('invite_code_copied')) !!},
    group_deleted_successfully: {!! json_encode(__t('group_deleted_successfully')) !!},
    group_deletion_error: {!! json_encode(__t('group_deletion_error')) !!},
    group_left_successfully: {!! json_encode(__t('group_left_successfully')) !!},
    group_leave_error: {!! json_encode(__t('group_leave_error')) !!},
    support_registration_error: {!! json_encode(__t('support_registration_error')) !!},
    cover_update_error: {!! json_encode(__t('cover_update_error')) !!},
    logo_update_error: {!! json_encode(__t('logo_update_error')) !!},
    special_group: {!! json_encode(__t('special_group')) !!},
    join_us_in_group: {!! json_encode(__t('join_us_in_group')) !!},
    group_share_preview: {!! json_encode(__t('group_share_preview')) !!},
};

$(document).ready(function() {
    // --- File Attachment Preview Handlers ---
    const fileInput = $('#media-file-input');
    const triggerBtn = $('#trigger-media-upload');
    const previewContainer = $('#media-preview-container');
    const imgEl = $('#image-preview-el');
    const videoEl = $('#video-preview-el');
    const audioEl = $('#audio-preview-el');
    const removeBtn = $('#remove-media-btn');
    
    triggerBtn.on('click', function() {
        fileInput.trigger('click');
    });
    
    fileInput.on('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const reader = new FileReader();
            
            // Hide all previews first
            imgEl.addClass('hidden').attr('src', '');
            videoEl.addClass('hidden').find('source').remove();
            audioEl.addClass('hidden').find('source').remove();
            previewContainer.addClass('hidden');
            
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    imgEl.attr('src', e.target.result).removeClass('hidden');
                    previewContainer.removeClass('hidden');
                } else if (file.type.startsWith('video/')) {
                    videoEl.html(`<source src="${e.target.result}" type="${file.type}">`).removeClass('hidden');
                    videoEl[0].load();
                    previewContainer.removeClass('hidden');
                } else if (file.type.startsWith('audio/')) {
                    audioEl.html(`<source src="${e.target.result}" type="${file.type}">`).removeClass('hidden');
                    audioEl[0].load();
                    previewContainer.removeClass('hidden');
                }
            };
            
            reader.readAsDataURL(file);
        }
    });
    
    removeBtn.on('click', function() {
        fileInput.val('');
        imgEl.addClass('hidden').attr('src', '');
        videoEl.addClass('hidden').html('');
        audioEl.addClass('hidden').html('');
        previewContainer.addClass('hidden');
    });

    // --- Share Group Handler ---
    $(document).on('click', '#share-group-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const groupTitle = btn.attr('data-group-title') || _gt.special_group;
        const groupDesc = btn.attr('data-group-desc') || '';
        const shareUrl = window.location.origin + '/groups/' + btn.attr('data-group-id');
        
        const textHeader = _gt.join_us_in_group.replace(':group', groupTitle).replace(':desc', groupDesc.substring(0, 100)).replace('\n:url', '').replace(':url', '').trim();
        const fullShareText = `${textHeader}\n${shareUrl}`;

        // Open custom share modal directly (bypassing native navigator.share to prevent browser permission popups)
        $('#share-link-input').val(shareUrl);
        $('#share-modal-preview-text').text(groupDesc ? groupDesc.substring(0, 150) + (groupDesc.length > 150 ? '...' : '') : _gt.group_share_preview.replace(':group', groupTitle));
        
        // Set social share hrefs
        $('#share-whatsapp').attr('href', `https://api.whatsapp.com/send?text=${encodeURIComponent(fullShareText)}`);
        $('#share-facebook').attr('href', `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`);
        $('#share-twitter').attr('href', `https://twitter.com/intent/tweet?text=${encodeURIComponent(fullShareText)}`);
        $('#share-linkedin').attr('href', `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`);

        // Open Modal
        const modal = $('#share-post-modal');
        modal.removeClass('hidden').addClass('flex');
        $('body').addClass('modal-active');
        setTimeout(() => {
            modal.addClass('modal-show');
        }, 20);
    });

    // --- Copy Invite Code Handler ---
    $(document).on('click', '#copy-invite-code-box', function(e) {
        e.preventDefault();
        const code = $(this).attr('data-code');
        if (!code) return;

        // Copy using temporary input element
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(code).select();
        document.execCommand('copy');
        tempInput.remove();

        // Show professional Toastr message
        toastr.success(_gt.invite_code_copied + code);
    });

    // --- Delete Group Handler ---
    const deleteGroupModal = $('#delete-group-modal');
    let groupIdToDelete = null;
    
    $(document).on('click', '#delete-group-btn', function() {
        groupIdToDelete = $(this).attr('data-group-id');
        deleteGroupModal.removeClass('hidden').addClass('flex');
        setTimeout(() => deleteGroupModal.addClass('modal-show'), 10);
    });
    
    function closeDeleteGroupModal() {
        deleteGroupModal.removeClass('modal-show');
        setTimeout(() => {
            deleteGroupModal.removeClass('flex').addClass('hidden');
            groupIdToDelete = null;
        }, 300);
    }
    
    $('#cancel-delete-group-btn, #delete-group-backdrop').on('click', closeDeleteGroupModal);
    
    $('#confirm-delete-group-btn').on('click', function() {
        if (!groupIdToDelete) return;
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/${groupIdToDelete}/delete`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeDeleteGroupModal();
                    setTimeout(function() {
                        window.location.href = res.redirect_url;
                    }, 1000);
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : _gt.group_deletion_error;
                toastr.error(msg);
                btn.prop('disabled', false);
            }
        });
    });

    // --- Leave Group Handler ---
    const leaveModal = $('#leave-group-modal');
    let groupIdToLeave = null;
    
    $('#leave-group-btn').on('click', function() {
        groupIdToLeave = $(this).attr('data-group-id');
        leaveModal.removeClass('hidden').addClass('flex');
        setTimeout(() => leaveModal.addClass('modal-show'), 10);
    });
    
    function closeLeaveModal() {
        leaveModal.removeClass('modal-show');
        setTimeout(() => {
            leaveModal.removeClass('flex').addClass('hidden');
            groupIdToLeave = null;
        }, 300);
    }
    
    $('#cancel-leave-group-btn, #leave-group-backdrop').on('click', closeLeaveModal);
    
    $('#confirm-leave-group-btn').on('click', function() {
        if (!groupIdToLeave) return;
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/${groupIdToLeave}/leave`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeLeaveModal();
                    location.reload();
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error(_gt.group_leave_error);
                btn.prop('disabled', false);
            }
        });
    });

    // --- Join Closed Group Modals ---
    const joinModal = $('#join-group-modal');
    
    $(document).on('click', '.trigger-join-closed-modal', function() {
        const groupId = $(this).attr('data-group-id');
        $('#join-group-id').val(groupId);
        
        joinModal.removeClass('hidden').addClass('flex');
        setTimeout(() => joinModal.addClass('modal-show'), 10);
    });
    
    function closeJoinModal() {
        joinModal.removeClass('modal-show');
        setTimeout(() => {
            joinModal.removeClass('flex').addClass('hidden');
            $('#join-group-form')[0].reset();
            $('#join-group-id').val('');
        }, 300);
    }
    
    $('#cancel-join-group-btn, #join-group-backdrop').on('click', closeJoinModal);
    
    $('#join-group-form').on('submit', function(e) {
        e.preventDefault();
        const groupId = $('#join-group-id').val();
        const inviteCode = $('#join-invite-code').val().trim();
        if (!groupId || !inviteCode) return;
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const spinner = $('#join-group-spinner');
        
        btn.prop('disabled', true);
        spinner.removeClass('hidden');
        
        $.ajax({
            url: `/groups/${groupId}/join`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                invite_code: inviteCode
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeJoinModal();
                    location.reload();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error(_gt.server_connection_error);
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('hidden');
            }
        });
    });

    // --- Join Open Group Handler ---
    $(document).on('click', '.join-open-group-btn', function() {
        const btn = $(this);
        const groupId = btn.attr('data-group-id');
        if (!groupId) return;
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/${groupId}/join`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error(_gt.server_connection_error);
                btn.prop('disabled', false);
            }
        });
    });

    // --- Subject Support (Like) Handler ---
    $(document).on('click', '.subject-support-btn', function() {
        const btn = $(this);
        const subjectId = btn.attr('data-subject-id');
        if (!subjectId) return;
        
        const isActive = btn.attr('data-active') === 'true';
        const nextAction = isActive ? 'remove' : 'like';
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/subjects/${subjectId}/react`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                reaction_type: nextAction
            },
            success: function(res) {
                if (res.success) {
                    btn.attr('data-active', (!isActive).toString());
                    const parentCard = btn.closest('article');
                    parentCard.find('.like-counter').text(res.like_count);
                    parentCard.find('.open-supporters-btn').attr('data-total-supports', res.like_count);
                    
                    if (!isActive) {
                        btn.find('.material-symbols-outlined').addClass('fill-1 text-primary animate-bulb');
                        btn.addClass('text-primary');
                    } else {
                        btn.find('.material-symbols-outlined').removeClass('fill-1 text-primary animate-bulb');
                        btn.removeClass('text-primary');
                    }
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error(_gt.support_registration_error);
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // --- Delete Subject Handler (Custom Alert Modal) ---
    const deleteModal = $('#delete-subject-modal');
    let subjectIdToDelete = null;
    
    $(document).on('click', '.delete-subject-btn', function() {
        subjectIdToDelete = $(this).attr('data-subject-id');
        
        deleteModal.removeClass('hidden').addClass('flex');
        setTimeout(() => deleteModal.addClass('modal-show'), 10);
    });
    
    function closeDeleteModal() {
        deleteModal.removeClass('modal-show');
        setTimeout(() => {
            deleteModal.removeClass('flex').addClass('hidden');
            subjectIdToDelete = null;
        }, 300);
    }
    
    $('#cancel-delete-subject-btn, #delete-subject-backdrop').on('click', closeDeleteModal);
    
    $('#confirm-delete-subject-btn').on('click', function() {
        if (!subjectIdToDelete) return;
        const btn = $(this);
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/subjects/${subjectIdToDelete}/delete`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeDeleteModal();
                    $(`article[data-subject-id="${subjectIdToDelete}"]`).fadeOut(400, function() {
                        $(this).remove();
                        // Update subjects count in UI on deletion
                        const tabCountEl = $('.group-subjects-count-tab');
                        const statsCountEl = $('.group-subjects-count-stats');
                        if (tabCountEl.length) {
                            let currentCount = parseInt(tabCountEl.first().text()) || 0;
                            tabCountEl.text(Math.max(0, currentCount - 1));
                        }
                        if (statsCountEl.length) {
                            let currentCount = parseInt(statsCountEl.first().text()) || 0;
                            statsCountEl.text(Math.max(0, currentCount - 1));
                        }
                    });
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error(_gt.delete_topic_error);
                btn.prop('disabled', false);
            }
        });
    });

    // --- Create Subject AJAX Handler ---
    $('#create-subject-form').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const formData = new FormData(this);
        
        // Validation
        const title = form.find('input[name="title"]').val().trim();
        const description = form.find('textarea[name="description"]').val().trim();
        if (!title || !description) {
            toastr.error(_gt.fill_required_fields);
            return;
        }
        
        submitBtn.prop('disabled', true).text(_gt.publishing_in_progress);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    
                    // Reset form and media preview
                    form[0].reset();
                    $('#remove-media-btn').trigger('click');
                    
                    // Prepend new subject HTML
                    // If the empty state message exists, remove it
                    const emptyState = $('#empty-feed-placeholder');
                    if (emptyState.length) {
                        emptyState.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                    
                    const newEl = $(res.html).hide();
                    $('#create-subject-form').closest('.wisdom-card').after(newEl);
                    newEl.fadeIn(500);

                    // Update subjects count in UI
                    const tabCountEl = $('.group-subjects-count-tab');
                    const statsCountEl = $('.group-subjects-count-stats');
                    if (tabCountEl.length) {
                        let currentCount = parseInt(tabCountEl.first().text()) || 0;
                        tabCountEl.text(currentCount + 1);
                    }
                    if (statsCountEl.length) {
                        let currentCount = parseInt(statsCountEl.first().text()) || 0;
                        statsCountEl.text(currentCount + 1);
                    }
                } else {
                    toastr.error(res.message || _gt.topic_publishing_error);
                }
            },
            error: function(xhr) {
                let errorMsg = _gt.server_connection_error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).map(err => err.join('<br>')).join('<br>');
                }
                toastr.error(errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(_gt.post_topic_btn);
            }
        });
    });
});

// ===== Group Image Viewer Lightbox =====
(function() {
    const viewerModal = $('#group-image-viewer-modal');
    const viewerImg   = $('#group-image-viewer-img');
    const downloadBtn = $('#group-image-viewer-download');
    const labelEl     = $('#group-image-viewer-label');

    function openViewer(imgUrl, label) {
        viewerImg.attr('src', imgUrl).attr('alt', label);
        downloadBtn.attr('href', imgUrl);
        labelEl.text(label);
        viewerModal.removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        setTimeout(function() {
            viewerModal.addClass('modal-show');
        }, 20);
    }

    function closeViewer() {
        viewerModal.removeClass('modal-show');
        $('body').removeClass('overflow-hidden');
        setTimeout(function() {
            viewerModal.removeClass('flex').addClass('hidden');
            viewerImg.attr('src', '');
        }, 300);
    }

    // Cover image click
    $(document).on('click', '#group-cover-trigger', function(e) {
        if ($(e.target).closest('#edit-cover-btn').length) {
            return;
        }
        const url = $(this).data('img-url');
        if (url) openViewer(url, _gt.cover_image_label);
    });

    // Logo click
    $(document).on('click', '#group-logo-trigger', function(e) {
        if ($(e.target).closest('#edit-logo-btn').length) {
            return;
        }
        const url = $(this).data('img-url');
        if (url) openViewer(url, _gt.group_logo_label);
    });

    // Close: button + backdrop
    $(document).on('click', '#close-group-image-viewer-btn, #group-image-viewer-modal .modal-backdrop', function() {
        closeViewer();
    });

    // Close: Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && viewerModal.hasClass('modal-show')) {
            closeViewer();
        }
    });
})();

// ===== Group Detail Tabs =====
(function() {
    $('.group-detail-tab').on('click', function() {
        const tab = $(this).data('group-tab');

        // Update buttons
        $('.group-detail-tab').removeClass('active-group-tab');
        $(this).addClass('active-group-tab');

        // Show/hide panels
        if (tab === 'members') {
            $('#group-panel-posts').addClass('hidden');
            $('#group-panel-members').removeClass('hidden');
        } else {
            $('#group-panel-members').addClass('hidden');
            $('#group-panel-posts').removeClass('hidden');
        }
    });
})();

// ===== Kick Member (Admin Only) =====
$(document).on('click', '.kick-member-btn', function() {
    const groupId  = $(this).data('group-id');
    const userId   = $(this).data('user-id');
    const name     = $(this).data('member-name');
    const row      = $(this).closest('.member-row');

    if (!confirm(_gt.kick_member_confirm.replace(':name', name))) return;

    $.ajax({
        url: `/groups/${groupId}/members/${userId}/kick`,
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                row.addClass('removing');
                setTimeout(function() { row.remove(); }, 360);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error(_gt.server_connection_error);
        }
    });
});


    // ===== Infinite Scroll / Lazy Loading for Group Subjects =====
    $(document).ready(function() {
        let subjectPage = 2;
        let subjectsLoading = false;
        let hasMoreSubjects = true;

        $(window).on('scroll', function() {
            if ($('#group-subjects-container').length === 0) return;
            
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
                if (!subjectsLoading && hasMoreSubjects) {
                    if (window.innerWidth < 1024) return;
                    loadMoreGroupSubjects();
                }
            }
        });

        window.loadMoreGroupSubjects = function() {
            if (subjectsLoading || !hasMoreSubjects) return;

            subjectsLoading = true;
            $('#group-subjects-loader').removeClass('hidden');
            $('#mobile-group-load-more-btn').prop('disabled', true).addClass('opacity-75');
            $('#mobile-group-load-more-text').text('جاري التحميل...');

            const $container = $('#group-subjects-container');
            const $lastExistingSubject = $container.children('article').last();

            $.ajax({
                url: "{{ route('frontend.groups.subjects.api', $group->id) }}?page=" + subjectPage + "&per_page=5",
                type: "GET",
                success: function(response) {
                    $('#group-subjects-loader').addClass('hidden');
                    subjectsLoading = false;
                    hasMoreSubjects = response.has_more;
                    
                    if (response.html && response.html.trim() !== '') {
                        $container.append(response.html);
                        subjectPage++;

                        // Identify first newly added subject & smooth scroll to it
                        let $firstNewSubject;
                        if ($lastExistingSubject.length > 0) {
                            $firstNewSubject = $lastExistingSubject.next('article');
                        } else {
                            $firstNewSubject = $container.children('article').first();
                        }

                        if ($firstNewSubject && $firstNewSubject.length > 0) {
                            const targetScrollTop = $firstNewSubject.offset().top - 90;
                            $('html, body').animate({
                                scrollTop: Math.max(0, targetScrollTop)
                            }, 400);
                        }

                        if (!hasMoreSubjects) {
                            $('#mobile-group-load-more-container').html(`
                                <div class="py-3 px-4 text-center text-xs font-bold text-on-surface-variant/60 bg-surface-container-lowest/50 rounded-xl border border-primary/5">
                                    لا توجد مواضيع أخرى
                                </div>
                            `);
                        } else {
                            $('#mobile-group-load-more-btn').prop('disabled', false).removeClass('opacity-75');
                            $('#mobile-group-load-more-text').text('تحميل المزيد من المواضيع');
                        }
                    } else {
                        hasMoreSubjects = false;
                        $('#mobile-group-load-more-container').html(`
                            <div class="py-3 px-4 text-center text-xs font-bold text-on-surface-variant/60 bg-surface-container-lowest/50 rounded-xl border border-primary/5">
                                لا توجد مواضيع أخرى
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#group-subjects-loader').addClass('hidden');
                    subjectsLoading = false;
                    $('#mobile-group-load-more-btn').prop('disabled', false).removeClass('opacity-75');
                    $('#mobile-group-load-more-text').text('تحميل المزيد من المواضيع');
                }
            });
        };
    });


    // ===== Group Site Info Editing (Admin/Creator Only) =====
    $(document).ready(function() {
        // --- 1. Edit Cover photo ---
        $('#edit-cover-btn').on('click', function(e) {
            e.stopPropagation();
            $('#edit-cover-input').trigger('click');
        });

        $('#edit-cover-input').on('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const formData = new FormData();
                formData.append('cover', file);
                formData.append('_token', "{{ csrf_token() }}");

                // Show visual loading indicator on cover
                const coverBtn = $('#edit-cover-btn');
                const origHtml = coverBtn.html();
                coverBtn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[20px]">sync</span>');

                $.ajax({
                    url: "{{ route('frontend.groups.update_api', $group->id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            if (res.data && res.data.cover_url) {
                                $('#group-cover-trigger').css('background-image', 'url(' + res.data.cover_url + ')').attr('data-img-url', res.data.cover_url);
                            }
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : _gt.cover_update_error;
                        toastr.error(msg);
                    },
                    complete: function() {
                        coverBtn.prop('disabled', false).html(origHtml);
                    }
                });
            }
        });

        // --- 2. Edit Logo ---
        $('#edit-logo-btn').on('click', function(e) {
            e.stopPropagation();
            $('#edit-logo-input').trigger('click');
        });

        $('#edit-logo-input').on('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const formData = new FormData();
                formData.append('logo', file);
                formData.append('_token', "{{ csrf_token() }}");

                // Show visual loading indicator on logo
                const logoBtn = $('#edit-logo-btn');
                const origHtml = logoBtn.html();
                logoBtn.prop('disabled', true).html('<span class="material-symbols-outlined animate-spin text-[14px]">sync</span>');

                $.ajax({
                    url: "{{ route('frontend.groups.update_api', $group->id) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            if (res.data && res.data.logo_url) {
                                $('#group-logo-img').attr('src', res.data.logo_url);
                                $('#group-logo-trigger').attr('data-img-url', res.data.logo_url);
                            }
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON ? xhr.responseJSON.message : _gt.logo_update_error;
                        toastr.error(msg);
                    },
                    complete: function() {
                        logoBtn.prop('disabled', false).html(origHtml);
                    }
                });
            }
        });

        // --- 3. Edit Title (Inline) ---
        $('#edit-title-btn').on('click', function() {
            $('#group-title-display-container').addClass('hidden');
            $('#group-title-edit-container').removeClass('hidden');
            $('#group-title-input').focus().select();
        });

        $('#cancel-title-btn').on('click', function() {
            $('#group-title-edit-container').addClass('hidden');
            $('#group-title-display-container').removeClass('hidden');
            // reset value
            $('#group-title-input').val($('#group-title-display').text());
        });

        $('#save-title-btn').on('click', function() {
            const newTitle = $('#group-title-input').val().trim();
            if (!newTitle) {
                toastr.error(_gt.group_title_required);
                return;
            }

            const saveBtn = $(this);
            saveBtn.prop('disabled', true).text(_gt.saving_in_progress);

            $.ajax({
                url: "{{ route('frontend.groups.update_api', $group->id) }}",
                type: "POST",
                data: {
                    title: newTitle,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#group-title-display').text(newTitle);
                        $('#cancel-title-btn').trigger('click');
                    } else {
                        toastr.error(res.message);
                        saveBtn.prop('disabled', false).text(_gt.save);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : _gt.title_update_error;
                    toastr.error(msg);
                    saveBtn.prop('disabled', false).text(_gt.save);
                }
            });
        });

        // Add Enter key support for Title Edit
        $('#group-title-input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $('#save-title-btn').trigger('click');
            } else if (e.key === 'Escape') {
                $('#cancel-title-btn').trigger('click');
            }
        });

        // --- 4. Edit Description (Inline) ---
        $('#edit-desc-btn').on('click', function() {
            $('#group-desc-display-container').addClass('hidden');
            $('#group-desc-edit-container').removeClass('hidden');
            $('#group-desc-input').focus();
        });

        $('#cancel-desc-btn').on('click', function() {
            $('#group-desc-edit-container').addClass('hidden');
            $('#group-desc-display-container').removeClass('hidden');
            // reset value
            $('#group-desc-input').val($('#group-desc-display').text().trim());
        });

        $('#save-desc-btn').on('click', function() {
            const newDesc = $('#group-desc-input').val().trim();
            const saveBtn = $(this);
            saveBtn.prop('disabled', true).text(_gt.saving_in_progress);

            $.ajax({
                url: "{{ route('frontend.groups.update_api', $group->id) }}",
                type: "POST",
                data: {
                    description: newDesc,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        $('#group-desc-display').text(newDesc || _gt.no_group_desc);
                        $('#cancel-desc-btn').trigger('click');
                    } else {
                        toastr.error(res.message);
                        saveBtn.prop('disabled', false).text(_gt.save);
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : _gt.desc_update_error;
                    toastr.error(msg);
                    saveBtn.prop('disabled', false).text(_gt.save);
                }
            });
        });
    });

// ===== Leave from Members Tab =====
function leaveFromMembersTab(groupId) {
    if (!confirm(_gt.leave_group_confirm_simple)) return;

    $.ajax({
        url: `/groups/${groupId}/leave`,
        type: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(res) {
            if (res.success) {
                toastr.success(res.message);
                setTimeout(function() { location.reload(); }, 900);
            } else {
                toastr.error(res.message);
            }
        },
        error: function() {
            toastr.error(_gt.server_connection_error);
        }
    });
}

$('#leave-group-members-tab-btn').on('click', function() {
    leaveFromMembersTab($(this).data('group-id'));
});

$(document).on('click', '.leave-from-list-btn', function() {
    leaveFromMembersTab($(this).data('group-id'));
});
</script>
@endpush
