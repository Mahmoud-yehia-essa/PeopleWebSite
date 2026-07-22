@php
    $isHome = request()->is('/') || request()->routeIs('frontend.home');
    $isSage = request()->routeIs('frontend.sage_committee') || request()->is('sage-committee*');
    $isGroups = request()->routeIs('frontend.groups') || request()->routeIs('frontend.groups.details') || request()->is('groups*');
    $isProfile = request()->routeIs('profile.edit') || request()->is('profile*') || request()->routeIs('profile.edit_form');
    $isNetwork = request()->routeIs('frontend.my_network') || request()->is('my-network*');
    $isSearch = request()->routeIs('frontend.search') || request()->is('search*');
    $isAmbassadors = request()->routeIs('frontend.ambassadors') || request()->is('ambassadors*');
    $isSavedPosts = request()->routeIs('frontend.saved_posts') || request()->is('saved-posts*');
    
    $currentLang = current_language();
    $dir = $currentLang->direction ?? 'rtl';
    $dropdownAlign = $dir === 'rtl' ? 'left-0 origin-top-left text-right' : 'right-0 origin-top-right text-left';
@endphp
<!-- Sidebar Mobile Backdrop -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-primary/20 backdrop-blur-sm z-30 hidden opacity-0 transition-opacity duration-300"></div>

<!-- SideNavBar (Visible on Desktop, Drawer on Mobile) -->
<nav id="main-sidebar" class="flex flex-col h-screen fixed {{ $dir === 'rtl' ? 'right-0 border-l' : 'left-0 border-r' }} w-72 top-0 border-primary/5 bg-surface-container-low z-40 py-stack-md transition-all duration-300">
    <div class="px-6 mb-4 flex items-center gap-4">
        @auth
            @php
                $authUser = Auth::user();
                $avatar = url('upload/no_image.jpg');
                if ($authUser->profile_picture && $authUser->profile_picture !== 'non') {
                    $avatar = filter_var($authUser->profile_picture, FILTER_VALIDATE_URL)
                        ? $authUser->profile_picture
                        : asset('new_wiselook/uploads/' . $authUser->profile_picture);
                }

                $rankPhotoPath = null;
                $rankName = null;
                if ($authUser->rank) {
                    $rankName = __t($authUser->rank->rank_name);
                    $rPhoto = $authUser->rank->photo;
                    if (!empty($rPhoto) && file_exists(public_path('upload/rankings/' . $rPhoto))) {
                        $rankPhotoPath = asset('upload/rankings/' . $rPhoto);
                    }
                }
            @endphp
            <img alt="User Avatar" class="w-12 h-12 rounded-full border-2 border-secondary-container object-cover" src="{{ $avatar }}"/>
            <div>
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-primary font-bold leading-tight">{{ $authUser->first_name }} {{ $authUser->last_name }}</h2>
                <div class="mt-0.5">
                    @if($rankName)
                        <span class="inline-flex items-center gap-1.5" style="display: inline-flex; align-items: center; gap: 4px; vertical-align: middle;">
                            @if($rankPhotoPath)
                                <img src="{{ $rankPhotoPath }}" alt="{{ $rankName }}" style="width: 16px; height: 16px; object-fit: contain;">
                            @endif
                            <span class="font-bold text-xs" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ $rankName }}</span>
                        </span>
                    @else
                        <span class="font-label-sm text-xs text-on-surface-variant">{{ __t('honorary_member') }}</span>
                    @endif
                </div>
            </div>
        @else
            <img alt="Guest Avatar" class="w-12 h-12 rounded-full border-2 border-secondary-container object-cover" src="{{ url('upload/no_image.jpg') }}"/>
            <div>
                <h2 class="font-headline-lg-mobile text-headline-lg-mobile text-primary font-bold leading-tight">{{ __t('guest_user') }}</h2>
                <a href="{{ route('user.login') }}" class="font-label-sm text-label-sm text-primary hover:underline">{{ __t('guest_login') }}</a>
            </div>
        @endauth
    </div>

    <ul class="flex-1 space-y-2 overflow-y-auto px-4">
        <li>
            <a class="{{ $isHome ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.home') }}">
                <span class="material-symbols-outlined {{ $isHome ? 'fill' : '' }}">forum</span>
                <span class="font-body-md text-body-md">{{ __t('discussion_board') }}</span>
            </a>
        </li>
        <li>
            <a class="{{ $isSage ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.sage_committee') }}">
                <span class="material-symbols-outlined {{ $isSage ? 'fill' : '' }}">account_balance</span>
                <span class="font-body-md text-body-md">{{ __t('wise_committee') }}</span>
            </a>
        </li>
        <li>
            <a class="{{ $isGroups ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.groups') }}">
                <span class="material-symbols-outlined {{ $isGroups ? 'fill' : '' }}">groups</span>
                <span class="font-body-md text-body-md">{{ __t('groups') }}</span>
            </a>
        </li>
        <li>
            <a class="{{ $isNetwork ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.my_network') }}">
                <span class="material-symbols-outlined {{ $isNetwork ? 'fill' : '' }}">diversity_3</span>
                <span class="font-body-md text-body-md">{{ __t('my_network') }}</span>
            </a>
        </li>
        <li>
            <a class="{{ $isSearch ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.search') }}">
                <span class="material-symbols-outlined {{ $isSearch ? 'fill' : '' }}">search</span>
                <span class="font-body-md text-body-md">{{ __t('search') }}</span>
            </a>
        </li>
        <li>
            <a class="{{ $isAmbassadors ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.ambassadors') }}">
                <span class="material-symbols-outlined {{ $isAmbassadors ? 'fill' : '' }}">campaign</span>
                <span class="font-body-md text-body-md">{{ __t('ambassadors') }}</span>
            </a>
        </li>
        @auth
        <li>
            <a class="{{ $isSavedPosts ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('frontend.saved_posts') }}">
                <span class="material-symbols-outlined {{ $isSavedPosts ? 'fill' : '' }}">bookmark</span>
                <span class="font-body-md text-body-md">{{ __t('saved_posts') }}</span>
            </a>
        </li>
        @endauth
        @auth
        <li>
            <a class="{{ $isProfile ? 'bg-secondary-container text-on-secondary-container font-semibold' : 'text-on-surface-variant hover:bg-surface-variant/50' }} rounded-lg mx-2 px-4 py-3 flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('profile.edit_form') }}">
                <span class="material-symbols-outlined {{ $isProfile ? 'fill' : '' }}">manage_accounts</span>
                <span class="font-body-md text-body-md">{{ __t('edit_profile') }}</span>
            </a>
        </li>
        @endauth
    </ul>

    <div class="mt-auto px-4 border-t border-primary/5 pt-4">
        <ul class="space-y-2">
            @auth
                <li>
                    <a class="text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-lg mx-2 px-4 py-3 transition-all flex items-center space-x-4 space-x-reverse hover:translate-x-reverse-1 duration-200" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="material-symbols-outlined">logout</span>
                        <span class="font-body-md text-body-md">{{ __t('logout') }}</span>
                    </a>
                </li>
            @endauth
        </ul>
    </div>
</nav>

<!-- TopNavBar (Web & Mobile) -->
<header class="fixed top-0 {{ $dir === 'rtl' ? 'right-0 lg:right-72 left-0' : 'left-0 lg:left-72 right-0' }} w-auto z-50 flex justify-between items-center px-margin-mobile md:px-margin-desktop py-4 bg-surface/70 backdrop-blur-xl border-b border-primary/10 shadow-sm shadow-primary/5 transition-all duration-300">
    <div class="flex items-center gap-4">
        <!-- Hamburger Menu Button -->
        <button id="hamburger-menu-btn" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-all duration-300 flex items-center justify-center cursor-pointer">
            <span class="material-symbols-outlined text-[24px]">menu</span>
        </button>
        <a href="{{ route('frontend.home') }}" class="flex items-center shrink-0">
            <div class="logo-frame-header">
                <div class="logo-inner-header bg-emerald-deep p-0.5 w-10 h-10 flex items-center justify-center rounded-full">
                    <img alt="حكماء العالم" class="w-full h-full object-contain rounded-full opacity-95" src="{{ asset('backend/assets/images/logo.png') }}">
                </div>
            </div>
        </a>
    </div>

    <!-- Web Navigation Links -->
    <nav class="hidden md:flex items-center space-x-2 space-x-reverse h-full md:mr-8">
        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isHome ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ route('frontend.home') }}">
            <span class="material-symbols-outlined text-[18px]">forum</span>
            <span>{{ __t('discussion_board') }}</span>
            @if($isHome)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>

        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isSage ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ route('frontend.sage_committee') }}">
            <span class="material-symbols-outlined text-[18px]">account_balance</span>
            <span>{{ __t('wise_committee') }}</span>
            @if($isSage)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>

        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isGroups ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ route('frontend.groups') }}">
            <span class="material-symbols-outlined text-[18px]">groups</span>
            <span>{{ __t('groups') }}</span>
            @if($isGroups)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>

        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isNetwork ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ route('frontend.my_network') }}">
            <span class="material-symbols-outlined text-[18px]">diversity_3</span>
            <span>{{ __t('my_network') }}</span>
            @if($isNetwork)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>

        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isProfile ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ url('/profile') }}">
            <span class="material-symbols-outlined text-[18px]">person</span>
            <span>{{ __t('profile') }}</span>
            @if($isProfile)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>

        <a class="relative py-2 px-3 font-label-md text-xs font-bold transition-all duration-300 rounded-lg flex items-center gap-1.5 {{ $isAmbassadors ? 'text-primary bg-primary/5' : 'text-on-surface-variant/80 hover:text-primary hover:bg-primary/5' }}" href="{{ route('frontend.ambassadors') }}">
            <span class="material-symbols-outlined text-[18px]">campaign</span>
            <span>{{ __t('ambassadors') }}</span>
            @if($isAmbassadors)
                <span class="absolute bottom-0 inset-x-3 h-0.5 bg-primary rounded-t-full"></span>
            @endif
        </a>
    </nav>

    <div class="flex items-center space-x-4 space-x-reverse">
        <!-- Language Switcher Dropdown -->
        @php
            $activeLanguagesData = cache()->rememberForever('active_languages', function() {
                return \App\Models\Language::where('is_active', true)->get()->map(function($lang) {
                    return [
                        'id' => $lang->id,
                        'code' => $lang->code,
                        'direction' => $lang->direction,
                        'flag_path' => $lang->flag_path,
                        'name' => $lang->name
                    ];
                })->toArray();
            });
            $activeLanguages = collect($activeLanguagesData)->map(function($lang) {
                return (object) $lang;
            });
            $currentLang = current_language();
        @endphp
        @if($activeLanguages->count() > 1 && $currentLang)
            <div class="relative" id="language-switcher-container">
                <button id="language-switcher-btn" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-all duration-300 flex items-center justify-center relative cursor-pointer" title="{{ __t('switch_language') }}">
                    <span class="text-lg leading-none">{{ $currentLang->flag_path ?? '🌐' }}</span>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="language-dropdown" class="absolute {{ $dropdownAlign }} mt-2 w-40 bg-white rounded-xl border border-primary/10 shadow-lg z-50 overflow-hidden">
                    <div class="p-2 space-y-1 font-sans">
                        @foreach($activeLanguages as $lang)
                            <a href="{{ route('language.switch', $lang->code) }}" class="flex items-center justify-between px-3 py-2.5 text-xs text-on-surface hover:bg-primary/5 hover:text-primary rounded-lg transition-all duration-200 {{ $currentLang->code === $lang->code ? 'bg-primary/10 text-primary font-bold' : '' }}">
                                <span>{{ $lang->name }}</span>
                                <span class="text-base leading-none">{{ $lang->flag_path }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Search Button -->
        <a href="{{ route('frontend.search') }}" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-all duration-300 flex items-center justify-center relative" title="{{ __t('search') }}">
            <span class="material-symbols-outlined">search</span>
        </a>

        @auth
            <a href="{{ route('frontend.messages') }}" id="header-messages-btn" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-all duration-300 flex items-center justify-center relative" title="{{ __t('conversations') }}">
                <span class="material-symbols-outlined">forum</span>
                <span id="unread-messages-badge" class="absolute -top-0.5 -right-0.5 bg-error text-white text-[9px] font-bold min-w-[16px] h-4 px-0.5 flex items-center justify-center rounded-full hidden shrink-0 shadow-sm border border-white leading-none">0</span>
            </a>
            <div class="relative" id="notifications-dropdown-container">
                <button id="notification-bell-btn" class="text-primary hover:bg-primary/5 p-2 rounded-full transition-all duration-300 relative cursor-pointer flex items-center justify-center">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="unread-notification-badge" class="absolute -top-0.5 -right-0.5 bg-error text-white text-[9px] font-bold min-w-[16px] h-4 px-0.5 flex items-center justify-center rounded-full hidden shrink-0 shadow-sm border border-white leading-none">0</span>
                </button>
                
                <!-- Dropdown Menu (Facebook style) -->
                <div id="notifications-dropdown" class="absolute {{ $dropdownAlign }} mt-2 w-80 bg-white rounded-2xl border border-primary/10 shadow-lg hidden z-50 overflow-hidden transition-all duration-300 scale-95">
                    <div class="p-4 border-b border-primary/5 flex items-center justify-between">
                        <span class="text-xs font-bold text-primary">{{ __t('notifications') }}</span>
                        <button id="mark-all-read-btn" class="text-[10px] text-secondary font-bold hover:underline cursor-pointer">{{ __t('mark_all_read') }}</button>
                    </div>
                    <div id="notifications-list" class="max-h-80 overflow-y-auto divide-y divide-primary/5 {{ $dir === 'rtl' ? 'text-right' : 'text-left' }}">
                        <div class="p-4 text-center text-xs text-on-surface-variant font-medium">{{ __t('loading_notifications') }}</div>
                    </div>
                    <div class="p-3 border-t border-primary/5 bg-[#f8faf5] text-center">
                        <a href="{{ route('frontend.notifications') }}" class="text-xs font-bold text-primary hover:underline block">{{ __t('view_all') }}</a>
                    </div>
                </div>
            </div>

            <div class="relative" id="user-profile-dropdown-container">
                <button id="user-profile-menu-btn" class="w-10 h-10 rounded-full border-2 border-primary-fixed overflow-hidden shrink-0 cursor-pointer flex items-center justify-center focus:outline-none">
                    @php
                        $avatar = url('upload/no_image.jpg');
                        if (Auth::user()->profile_picture && Auth::user()->profile_picture !== 'non') {
                            $avatar = filter_var(Auth::user()->profile_picture, FILTER_VALIDATE_URL)
                                ? Auth::user()->profile_picture
                                : asset('new_wiselook/uploads/' . Auth::user()->profile_picture);
                        }
                    @endphp
                    <img alt="User Avatar" class="w-full h-full object-cover" src="{{ $avatar }}"/>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="user-profile-dropdown" class="absolute {{ $dropdownAlign }} mt-2 w-48 bg-white rounded-xl border border-primary/10 shadow-lg z-50 overflow-hidden">
                    <div class="p-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center justify-between px-4 py-2.5 text-xs text-on-surface hover:bg-primary/5 hover:text-primary rounded-lg transition-all duration-200">
                            <span>{{ __t('profile') }}</span>
                            <span class="material-symbols-outlined text-[18px]">account_circle</span>
                        </a>
                        <a href="{{ route('profile.edit_form') }}" class="flex items-center justify-between px-4 py-2.5 text-xs text-on-surface hover:bg-primary/5 hover:text-primary rounded-lg transition-all duration-200">
                            <span>{{ __t('edit_profile') }}</span>
                            <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                        </a>
                        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center justify-between px-4 py-2.5 text-xs text-error hover:bg-error/5 rounded-lg transition-all duration-200">
                            <span>{{ __t('logout') }}</span>
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <a href="{{ route('user.login') }}" class="bg-primary text-on-primary font-label-sm text-label-sm px-4 py-2 rounded-lg hover:bg-primary-container transition-all duration-300 text-center">
                {{ __t('login') }}
            </a>
        @endauth
    </div>
</header>

@auth
    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
@endauth

<style>
    /* Glowing logo animation for header */
    @keyframes logoGlowHeader {
        0%, 100% {
            box-shadow: 0 0 12px rgba(233, 195, 74, 0.45), 0 0 5px rgba(233, 195, 74, 0.2);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 24px rgba(233, 195, 74, 0.85), 0 0 12px rgba(233, 195, 74, 0.5);
            transform: scale(1.05);
        }
    }

    .logo-frame-header {
        position: relative;
        border-radius: 50%;
        padding: 2px;
        background: linear-gradient(135deg, rgba(233, 195, 74, 0.7) 0%, rgba(255, 255, 255, 0.2) 50%, rgba(233, 195, 74, 0.3) 100%);
        box-shadow: 0 0 15px rgba(233, 195, 74, 0.2);
        animation: logoGlowHeader 3s ease-in-out infinite;
        display: inline-block;
    }
    
    .logo-frame-header::before {
        content: '';
        position: absolute;
        inset: 1px;
        border-radius: 50%;
        background: #0a1a14;
        z-index: 0;
    }

    .logo-inner-header {
        position: relative;
        z-index: 1;
        border-radius: 50%;
        overflow: hidden;
    }

    /* Profile & Language dropdown animations */
    #user-profile-dropdown, #language-dropdown {
        transform: translateY(-8px) scale(0.95);
        pointer-events: none;
        opacity: 0;
        transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease-out;
    }
    #user-profile-dropdown.show-menu, #language-dropdown.show-menu {
        transform: translateY(0) scale(1);
        pointer-events: auto;
        opacity: 1;
    }

    /* Default sidebar state: CLOSED on all screens */
    nav#main-sidebar {
        transition: transform 0.3s ease-in-out;
        transform: translateX({{ $dir === 'rtl' ? '100%' : '-100%' }}) !important;
    }
    
    /* When sidebar is OPEN */
    body.sidebar-open nav#main-sidebar {
        transform: translateX(0) !important;
    }
    
    /* Desktop layout adjustments when sidebar is OPEN/CLOSED */
    @media (min-width: 1024px) {
        #pjax-container {
            padding-{{ $dir === 'rtl' ? 'right' : 'left' }}: 0 !important;
            transition: padding-{{ $dir === 'rtl' ? 'right' : 'left' }} 0.3s ease-in-out;
        }
        header {
            {{ $dir === 'rtl' ? 'right' : 'left' }}: 0 !important;
            transition: {{ $dir === 'rtl' ? 'right' : 'left' }} 0.3s ease-in-out;
        }
        
        /* Shifting layout when sidebar is OPEN on desktop */
        body.sidebar-open #pjax-container {
            padding-{{ $dir === 'rtl' ? 'right' : 'left' }}: 18rem !important; /* w-72 */
        }
        body.sidebar-open header {
            {{ $dir === 'rtl' ? 'right' : 'left' }}: 18rem !important; /* lg:right-72 / lg:left-72 */
        }
    }
</style>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
$(document).ready(function() {
    // Toggle sidebar script
    $('#hamburger-menu-btn').on('click', function(e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-open');
        
        // Mobile backdrop handling
        if ($(window).width() < 1024) {
            if ($('body').hasClass('sidebar-open')) {
                $('#sidebar-backdrop').removeClass('hidden').addClass('block');
                setTimeout(() => {
                    $('#sidebar-backdrop').addClass('opacity-100');
                }, 10);
            } else {
                $('#sidebar-backdrop').removeClass('opacity-100');
                setTimeout(() => {
                    $('#sidebar-backdrop').removeClass('block').addClass('hidden');
                }, 300);
            }
        }
    });

    // Close sidebar when clicking outside of it
    $(document).on('click', function(e) {
        if ($('body').hasClass('sidebar-open')) {
            if (!$(e.target).closest('#main-sidebar').length && !$(e.target).closest('#hamburger-menu-btn').length) {
                $('body').removeClass('sidebar-open');
                
                $('#sidebar-backdrop').removeClass('opacity-100');
                setTimeout(() => {
                    $('#sidebar-backdrop').removeClass('block').addClass('hidden');
                }, 300);
            }
        }
    });

    // Toggle language switcher dropdown
    $(document).on('click', '#language-switcher-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#language-dropdown').toggleClass('show-menu');
    });

    // Close language dropdown on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#language-switcher-container').length) {
            $('#language-dropdown').removeClass('show-menu');
        }
    });

    // Fetch notifications unread count initially to show badge
    @auth
    function checkUnreadNotifications() {
        $.ajax({
            url: "{{ route('frontend.notifications.api') }}",
            type: "GET",
            success: function(response) {
                if (response.unread_count > 0) {
                    const displayCount = response.unread_count > 99 ? '99+' : response.unread_count;
                    $('#unread-notification-badge').text(displayCount).removeClass('hidden');
                } else {
                    $('#unread-notification-badge').addClass('hidden');
                }
            }
        });
    }
    checkUnreadNotifications();

    let notificationPage = 1;
    let notificationsLoading = false;
    let hasMoreNotifications = true;
    const headerDir = '{{ $dir }}';

    // Toggle dropdown
    $('#notification-bell-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $dropdown = $('#notifications-dropdown');
        if ($dropdown.hasClass('hidden')) {
            $dropdown.removeClass('hidden').addClass('flex flex-col');
            // Reset pagination variables and fetch first page
            notificationPage = 1;
            hasMoreNotifications = true;
            loadNotificationsPage(1);
        } else {
            $dropdown.addClass('hidden').removeClass('flex flex-col');
        }
    });

    // Close dropdown on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#notifications-dropdown-container').length) {
            $('#notifications-dropdown').addClass('hidden').removeClass('flex flex-col');
        }
    });

    // Infinite scroll / lazy loading trigger on scroll down
    $('#notifications-list').on('scroll', function() {
        var container = $(this);
        // If we are close to the bottom (within 20px) and not already loading, and there are more notifications
        if (container.scrollTop() + container.innerHeight() >= container[0].scrollHeight - 20) {
            if (!notificationsLoading && hasMoreNotifications) {
                notificationPage++;
                loadNotificationsPage(notificationPage);
            }
        }
    });

    function loadNotificationsPage(page) {
        notificationsLoading = true;
        
        // Append loader
        if (page === 1) {
            $('#notifications-list').html('<div class="p-4 text-center text-xs text-on-surface-variant font-medium">{{ __t("loading_notifications") }}</div>');
        } else {
            // Append small inline spinner at the bottom
            if ($('#notifications-loader').length === 0) {
                $('#notifications-list').append('<div id="notifications-loader" class="p-3 text-center"><div class="animate-spin inline-block rounded-full h-4 w-4 border-b-2 border-primary"></div></div>');
            }
        }
        
        $.ajax({
            url: "{{ route('frontend.notifications.api') }}?page=" + page + "&per_page=5",
            type: "GET",
            success: function(response) {
                $('#notifications-loader').remove();
                notificationsLoading = false;
                hasMoreNotifications = response.has_more;
                
                var notifications = response.notifications;
                var html = '';
                
                if (notifications.length === 0 && page === 1) {
                    html = '<div class="py-8 px-4 text-center flex flex-col items-center justify-center gap-2">';
                    html += '  <span class="material-symbols-outlined text-primary/30 text-[44px] mb-1">notifications_off</span>';
                    html += '  <p class="text-xs font-bold text-on-surface-variant">{{ __t("no_notifications_available_yet") }}</p>';
                    html += '  <p class="text-[10px] text-on-surface-variant/70">{{ __t("no_notifications_desc") }}</p>';
                    html += '</div>';
                    $('#notifications-list').html(html);
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
                        var unreadDot = !item.is_seen ? '<div class="w-2 h-2 bg-primary rounded-full shrink-0 self-center"></div>' : '';

                        html += '<a href="' + item.url + '" class="p-3 flex items-start gap-3 hover:bg-primary/5 transition-colors cursor-pointer notification-link-item ' + activeClass + '" data-id="' + item.id + '" data-type="' + item.type + '">';
                        html += '  <div class="relative shrink-0">';
                        html += '    <img src="' + avatarUrl + '" class="w-10 h-10 rounded-full object-cover border border-outline-variant">';
                        html += '    <div class="absolute -bottom-1 -left-1 w-5 h-5 rounded-full flex items-center justify-center border border-white shadow-sm ' + iconColor + '">';
                        html += '      <span class="material-symbols-outlined text-[10px]" style="font-variation-settings:\'FILL\' 1;">' + icon + '</span>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '  <div class="flex-grow min-w-0 ' + (headerDir === 'rtl' ? 'text-right' : 'text-left') + '">';
                        html += '    <p class="text-[11px] font-semibold text-on-surface leading-normal">' + item.message + '</p>';
                        html += '    <span class="text-[9px] text-on-surface-variant font-medium mt-0.5 block">' + item.diff + '</span>';
                        html += '  </div>';
                        html +=    unreadDot;
                        html += '</a>';
                    });
                    
                    if (page === 1) {
                        $('#notifications-list').html(html);
                    } else {
                        $('#notifications-list').append(html);
                    }
                }
                
                // Update badge
                if (response.unread_count > 0) {
                    const displayCount = response.unread_count > 99 ? '99+' : response.unread_count;
                    $('#unread-notification-badge').text(displayCount).removeClass('hidden');
                } else {
                    $('#unread-notification-badge').addClass('hidden');
                }
            },
            error: function() {
                $('#notifications-loader').remove();
                notificationsLoading = false;
                if (page === 1) {
                    $('#notifications-list').html('<div class="p-4 text-center text-xs text-error font-medium">{{ __t("error_loading_notifications") }}</div>');
                }
            }
        });
    }

    // Keep the helper function name alias for backwards compatibility
    function fetchNotificationsDropdown() {
        notificationPage = 1;
        hasMoreNotifications = true;
        loadNotificationsPage(1);
    }

    // Mark all read button
    $('#mark-all-read-btn').on('click', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('frontend.notifications.mark_read') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('{{ __t("notifications_marked_read") }}');
                    fetchNotificationsDropdown();
                    $('#unread-notification-badge').addClass('hidden');
                }
            }
        });
    });

    // Toggle user profile dropdown
    $('#user-profile-menu-btn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        $('#user-profile-dropdown').toggleClass('show-menu');
    });

    // Close user profile dropdown on click outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#user-profile-dropdown-container').length) {
            $('#user-profile-dropdown').removeClass('show-menu');
        }
    });

    // Mark single notification as read on click
    $(document).on('click', '.notification-link-item', function(e) {
        e.preventDefault();
        var $item = $(this);
        var id = $item.attr('data-id');
        var type = $item.attr('data-type');
        var url = $item.attr('href');

        $.ajax({
            url: "{{ route('frontend.notifications.mark_single_read') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                type: type
            },
            complete: function() {
                if (url && url !== '#') {
                    window.location.href = url;
                } else {
                    $item.slideUp(200, function() {
                        $(this).remove();
                        checkUnreadNotifications();
                    });
                }
            }
        });
    });

    // --- Unread Messages Badge Logic ---
    function checkUnreadMessages() {
        if (window.location.pathname.startsWith('/messages')) {
            $('#unread-messages-badge').addClass('hidden').text('0');
            return;
        }

        $.ajax({
            url: "/messages/unread-count",
            type: "GET",
            success: function(response) {
                if (response.status === 'success' && response.unread_count > 0) {
                    $('#unread-messages-badge').removeClass('hidden').text(response.unread_count);
                } else {
                    $('#unread-messages-badge').addClass('hidden').text('0');
                }
            },
            error: function() {
                console.log('Failed to fetch unread messages count.');
            }
        });
    }

    checkUnreadMessages();

    $(document).on('click', '#header-messages-btn', function(e) {
        e.stopPropagation();
        $('#unread-messages-badge').addClass('hidden').text('0');
        
        $.ajax({
            url: "/messages/mark-all-read",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            }
        });
    });

    function initHeaderEcho() {
        if (window.Echo) {
            const authUserId = "{{ Auth::id() }}";
            window.Echo.private(`chat.${authUserId}`)
                .listen('.MessageSent', (e) => {
                    if (!window.location.pathname.startsWith('/messages')) {
                        const badge = $('#unread-messages-badge');
                        let currentCount = parseInt(badge.text()) || 0;
                        currentCount++;
                        badge.removeClass('hidden').text(currentCount);
                        
                        try {
                            const audio = new Audio('/assets/incoming.mp3');
                            audio.play().catch(err => console.log('Audio autoplay blocked'));
                        } catch(err) {}
                    }
                });
        } else {
            setTimeout(initHeaderEcho, 500);
        }
    }

    initHeaderEcho();
    @endauth
});
</script>
