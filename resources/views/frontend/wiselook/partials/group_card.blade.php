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

    $isUserMember = false;
    if (auth()->check()) {
        $isUserMember = \App\Models\GroupSiteUser::where('group_site_id', $group->id)
            ->where('user_id', auth()->id())
            ->exists();
    }

    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp

<div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 overflow-hidden shadow-sm flex flex-col group hover:shadow-md hover:border-primary/20 transition-all duration-300">
    
    <!-- Cover Card Image -->
    <div class="h-40 w-full relative bg-surface-container-high shrink-0">
        <a href="{{ route('frontend.groups.details', $group->id) }}" class="w-full h-full block overflow-hidden">
            <img alt="{{ $group->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $imageUrl }}">
        </a>
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent pointer-events-none"></div>

        <!-- Status Badge -->
        <span class="absolute top-3 {{ $dir === 'rtl' ? 'right-3' : 'left-3' }} flex items-center space-x-1.5 space-x-reverse px-2.5 py-1 rounded-full text-[10px] font-bold shadow-sm backdrop-blur-md {{ $isOpen ? 'bg-primary/80 text-white' : 'bg-secondary/80 text-on-secondary' }}">
            <span class="material-symbols-outlined text-[12px]">{{ $isOpen ? 'public' : 'lock' }}</span>
            <span>{{ $isOpen ? __t('public_group') : __t('private_group') }}</span>
        </span>

        <!-- Group Logo/Avatar Overlapping -->
        <div class="absolute -bottom-5 {{ $dir === 'rtl' ? 'left-5' : 'right-5' }} w-12 h-12 rounded-xl border-2 border-white shadow-md overflow-hidden bg-white z-20">
            <img class="w-full h-full object-cover" src="{{ $logoUrl }}" alt="{{ $group->title }}">
        </div>
    </div>

    <!-- Card Body -->
    <div class="p-5 pt-8 flex-1 flex flex-col justify-between {{ $textAlign }}">
        <div>
            <a href="{{ route('frontend.groups.details', $group->id) }}">
                <h4 class="font-headline-md text-base font-bold text-primary group-hover:text-secondary transition-colors mb-2">{{ $group->title }}</h4>
            </a>
            <p class="font-body-md text-xs text-on-surface-variant leading-relaxed line-clamp-3 mb-4">
                {{ $group->description ?: __t('no_group_desc_fallback') }}
            </p>
        </div>

        <!-- Stats row -->
        <div class="flex items-center gap-3 text-[10px] text-on-surface-variant mb-3">
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">group</span>
                {{ $group->members_count }} {{ __t('member_label') }}
            </span>
            <span class="flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">forum</span>
                {{ $group->subjects_count }} {{ __t('topic_label') }}
            </span>
        </div>

        <!-- Metadata (Admin & Action) -->
        <div class="border-t border-primary/5 pt-4 mt-auto">
            <!-- Admin Info -->
            @if($group->admin)
                <div class="flex items-center space-x-3 space-x-reverse mb-4">
                    @php
                        $adminAvatar = url('upload/no_image.jpg');
                        if ($group->admin->profile_picture && $group->admin->profile_picture !== 'non') {
                            $adminAvatar = filter_var($group->admin->profile_picture, FILTER_VALIDATE_URL)
                                ? $group->admin->profile_picture
                                : asset('new_wiselook/uploads/' . $group->admin->profile_picture);
                        }
                    @endphp
                    <img alt="{{ $group->admin->first_name }}" class="w-8 h-8 rounded-full object-cover border border-outline-variant shrink-0" src="{{ $adminAvatar }}">
                    <div class="truncate {{ $textAlign }}">
                        <p class="text-[11px] font-bold text-on-surface truncate">{{ __t('admin_prefix') }}: {{ $group->admin->first_name }} {{ $group->admin->last_name }}</p>
                        <p class="text-[9px] text-on-surface-variant truncate">{{ $group->admin->email ?? $group->admin->phone_number }}</p>
                    </div>
                </div>
            @endif

            <!-- Action button -->
            @if($isUserMember)
                <a href="{{ route('frontend.groups.details', $group->id) }}" class="w-full py-2 bg-primary text-white hover:bg-primary/90 text-xs font-bold rounded-lg flex items-center justify-center space-x-2 space-x-reverse transition-all">
                    <span class="material-symbols-outlined text-[16px]">forum</span>
                    <span>{{ __t('enter_discussion_yard') }}</span>
                </a>
            @else
                @if(auth()->check())
                    @if($isOpen)
                        <button class="join-open-group-btn w-full py-2 bg-secondary text-on-secondary hover:bg-secondary/90 text-xs font-bold rounded-lg flex items-center justify-center space-x-2 space-x-reverse transition-all cursor-pointer" data-group-id="{{ $group->id }}">
                            <span class="material-symbols-outlined text-[16px]">group_add</span>
                            <span>{{ __t('join_group_btn') }}</span>
                        </button>
                    @else
                        <button class="trigger-join-closed-modal w-full py-2 bg-secondary text-on-secondary hover:bg-secondary/90 text-xs font-bold rounded-lg flex items-center justify-center space-x-2 space-x-reverse transition-all cursor-pointer" data-group-id="{{ $group->id }}">
                            <span class="material-symbols-outlined text-[16px]">vpn_key</span>
                            <span>{{ __t('request_join_private') }}</span>
                        </button>
                    @endif
                @else
                    <a href="{{ route('user.login') }}" class="w-full py-2 bg-secondary text-on-secondary hover:bg-secondary/90 text-xs font-bold rounded-lg flex items-center justify-center space-x-2 space-x-reverse transition-all">
                        <span class="material-symbols-outlined text-[16px]">login</span>
                        <span>{{ __t('login_to_participate') }}</span>
                    </a>
                @endif
            @endif
        </div>
    </div>
</div>
