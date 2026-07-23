@php
    $postsList = isset($posts) ? $posts : App\Models\Post::with('user')->where('is_active', 1)->latest()->take(20)->get();
    
    $jsPosts = [];
    foreach ($postsList as $post) {
        $authorName = $post->user ? ($post->user->first_name . ' ' . $post->user->last_name) : __t('unknown_user');
        
        $avatar = url('upload/no_image.jpg');
        if ($post->user && $post->user->profile_picture && $post->user->profile_picture !== 'non') {
            $picture = $post->user->profile_picture;
            if (filter_var($picture, FILTER_VALIDATE_URL)) {
                $avatar = $picture;
            } else {
                $avatar = 'http://localhost:8888/new_wiselook/uploads/' . basename($picture);
            }
        }
        
        $diffTime = $post->created_at ? $post->created_at->locale(current_language()->code ?? 'ar')->diffForHumans() : '';
        
        $content = strip_tags($post->content);
        if (mb_strlen($content) > 100) {
            $content = mb_substr($content, 0, 100) . '...';
        }

        $postImage = null;
        if ($post->image) {
            if (filter_var($post->image, FILTER_VALIDATE_URL)) {
                $postImage = $post->image;
            } else {
                $postImage = asset('new_wiselook/uploads/' . $post->image);
            }
        }
        
        $jsPosts[] = [
            'author' => $authorName,
            'time' => $diffTime,
            'snippet' => $content,
            'avatar' => $avatar,
            'image' => $postImage
        ];
    }
    
    if (empty($jsPosts)) {
        $jsPosts = [
            [
                'author' => 'أحمد محمد',
                'time' => 'منذ ساعة',
                'snippet' => 'كيف يمكننا تحقيق التنمية الحضرية المستدامة؟',
                'avatar' => 'https://i.pravatar.cc/150?img=11'
            ],
            [
                'author' => 'سارة خالد',
                'time' => 'منذ 3 ساعات',
                'snippet' => 'مستقبل الذكاء الاصطناعي في الأخلاقيات والقرارات البشرية',
                'avatar' => 'https://i.pravatar.cc/150?img=5'
            ],
            [
                'author' => 'عمر عبدالله',
                'time' => 'منذ يوم',
                'snippet' => 'أهمية التوازن بين الصحة النفسية والجسدية في بيئة العمل الرقمية',
                'avatar' => 'https://i.pravatar.cc/150?img=8'
            ]
        ];
    }

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
    $dir = $currentLang->direction ?? 'rtl';
    $langCode = $currentLang->code ?? 'ar';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
    $iconAlign = $dir === 'rtl' ? 'right-4' : 'left-4';
    $inputPadding = $dir === 'rtl' ? 'pr-12 pl-4' : 'pl-12 pr-4';
    $toggleButtonAlign = $dir === 'rtl' ? 'left-4' : 'right-4';
    
    $isSignup = $errors->has('fname') || $errors->has('lname') || $errors->has('password_confirmation') || old('fname') || request()->get('tab') === 'signup';

    $activeIndicatorStyle = '';
    if ($dir === 'rtl') {
        $activeIndicatorStyle = $isSignup ? 'transform: translateX(-100%); right: 0;' : 'transform: translateX(0); right: 0;';
    } else {
        $activeIndicatorStyle = $isSignup ? 'transform: translateX(100%); left: 0;' : 'transform: translateX(0); left: 0;';
    }
@endphp
<!DOCTYPE html>
<html dir="{{ $dir }}" lang="{{ $langCode }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ __t('sign_in') }} | {{ __t('wisdom_council_title') }}</title>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >
    <style>
        html, body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8faf5;
            margin: 0;
            padding: 0;
            min-height: 100%;
        }
        @media (min-width: 1024px) {
            body {
                overflow: hidden;
            }
        }
        .font-headline-md { font-family: 'Cairo', sans-serif; }
        .font-headline-lg { font-family: 'Cairo', sans-serif; }
        
        /* Custom Scrollbar */
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(0,0,0,0.02);
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #1a5237;
            border-radius: 10px;
        }

        /* Animation for falling emojis */
        @keyframes fall {
            0% {
                transform: translateY(-100px) rotate(var(--start-rot));
                opacity: var(--start-opacity);
            }
            100% {
                transform: translateY(110vh) rotate(var(--end-rot));
                opacity: var(--end-opacity);
            }
        }
        .falling-emoji {
            position: absolute;
            top: -50px;
            pointer-events: none;
            animation: fall linear infinite;
        }

        /* Animation for floating cards */
        @keyframes floatCard {
            0% {
                transform: translateY(100vh) translateX(calc(-50% + var(--x-offset)));
                opacity: 0;
            }
            5% {
                opacity: var(--max-opacity);
            }
            95% {
                opacity: var(--max-opacity);
            }
            100% {
                transform: translateY(-50vh) translateX(calc(-50% + var(--x-offset)));
                opacity: 0;
            }
        }
        .floating-card {
            position: absolute;
            top: 0;
            left: 50%;
            animation: floatCard linear infinite;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            will-change: transform, opacity;
        }

        /* Premium Tab and Form Transitions */
        .tab-content {
            transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1), 
                        opacity 0.4s ease-in-out, 
                        transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                        margin 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                        padding 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .tab-collapsed {
            max-height: 0 !important;
            opacity: 0 !important;
            transform: translateY(15px) !important;
            pointer-events: none;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .tab-expanded {
            opacity: 1 !important;
            transform: translateY(0) !important;
            pointer-events: auto;
        }
        #signupOnlyFields.tab-expanded {
            max-height: 480px !important;
        }
        #loginOnlyFields.tab-expanded {
            max-height: 180px !important;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "on-tertiary": "#ffffff",
                      "outline": "#717972",
                      "inverse-on-surface": "#f0f1ed",
                      "tertiary": "#705d00",
                      "surface-tint": "#32694c",
                      "on-secondary": "#ffffff",
                      "background": "#f8faf5",
                      "outline-variant": "#c0c9c0",
                      "on-primary-fixed": "#002112",
                      "surface-container-lowest": "#ffffff",
                      "on-primary-fixed-variant": "#185035",
                      "error-container": "#ffdad6",
                      "surface": "#f8faf5",
                      "primary-fixed": "#b5f0cb",
                      "surface-container-high": "#e7e9e4",
                      "on-primary-container": "#8bc4a1",
                      "on-secondary-fixed-variant": "#005301",
                      "inverse-surface": "#2e312e",
                      "primary-container": "#1a5237",
                      "surface-variant": "#e1e3df",
                      "on-error-container": "#93000a",
                      "on-surface": "#191c1a",
                      "surface-container": "#edeeea",
                      "surface-container-low": "#f2f4f0",
                      "on-secondary-fixed": "#002200",
                      "secondary-fixed": "#76ff63",
                      "on-tertiary-fixed": "#221b00",
                      "on-secondary-container": "#007303",
                      "surface-dim": "#d9dbd6",
                      "on-primary": "#ffffff",
                      "tertiary-container": "#caa800",
                      "primary": "#003a23",
                      "tertiary-fixed": "#ffe174",
                      "inverse-primary": "#9ad3b0",
                      "secondary": "#006e03",
                      "primary-fixed-dim": "#9ad3b0",
                      "tertiary-fixed-dim": "#eac300",
                      "surface-bright": "#f8faf5",
                      "on-tertiary-fixed-variant": "#554500",
                      "surface-container-highest": "#e1e3df",
                      "on-tertiary-container": "#4c3e00",
                      "on-error": "#ffffff",
                      "secondary-container": "#64fe52",
                      "error": "#ba1a1a",
                      "secondary-fixed-dim": "#46e33a",
                      "on-surface-variant": "#404943",
                      "on-background": "#191c1a"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "gutter": "24px",
                      "stack-lg": "32px",
                      "stack-sm": "8px",
                      "margin-desktop": "32px",
                      "container-max": "1200px",
                      "unit": "8px",
                      "margin-mobile": "16px",
                      "stack-md": "16px"
              },
              "fontFamily": {
                      "title-lg": ["Tajawal"],
                      "headline-md": ["Cairo"],
                      "headline-lg-mobile": ["Cairo"],
                      "label-md": ["Tajawal"],
                      "display-lg": ["Cairo"],
                      "label-sm": ["Tajawal"],
                      "body-md": ["Tajawal"],
                      "body-lg": ["Tajawal"],
                      "headline-lg": ["Cairo"]
              },
              "fontSize": {
                      "title-lg": ["20px", {"lineHeight": "28px", "fontWeight": "700"}],
                      "headline-md": ["24px", {"lineHeight": "34px", "fontWeight": "600"}],
                      "headline-lg-mobile": ["24px", {"lineHeight": "34px", "fontWeight": "700"}],
                      "label-md": ["14px", {"lineHeight": "20px", "fontWeight": "500"}],
                      "display-lg": ["40px", {"lineHeight": "52px", "fontWeight": "700"}],
                      "label-sm": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                      "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                      "headline-lg": ["32px", {"lineHeight": "44px", "fontWeight": "700"}]
              }
            },
          },
        }
    </script>
</head>
<body class="bg-background text-on-surface">
<!-- Global Preloader -->
@include('frontend.wiselook.body.preloader')
<main class="flex flex-col lg:flex-row min-h-screen w-full overflow-x-hidden lg:overflow-hidden">
    <!-- Left Half: Branding & Illustration (Desktop Only) -->
    <section class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-tr from-[#002112] via-[#1a5237] to-[#003a23] items-center justify-center p-12 overflow-hidden z-0">
        <!-- Background Decorative Elements -->
        <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-secondary-fixed filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-tertiary-fixed filter blur-3xl"></div>
        </div>
        
        <!-- Falling Emojis Container -->
        <div class="absolute inset-0 pointer-events-none z-0" id="emoji-container"></div>
        <!-- Floating Cards Container -->
        <div class="absolute inset-0 pointer-events-none z-10" id="cards-container"></div>

        <!-- Branding Glassmorphic Box -->
        <div class="relative z-20 text-center max-w-md bg-white/5 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-2xl">
            <img alt="Logo" class="w-20 h-20 mx-auto mb-6 object-contain drop-shadow-md rounded-full bg-white/10 p-1" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
            <h1 class="font-headline-lg text-3xl font-extrabold text-white mb-2 tracking-wide">{{ __t('wisdom_council_title') }}</h1>
            <p class="font-body-md text-sm text-primary-fixed-dim/90 font-medium">{{ __t('wisdom_council_subtitle') }}</p>
        </div>
    </section>

    <!-- Right Half: Authentication -->
    <section class="w-full lg:w-1/2 min-h-screen bg-surface flex flex-col items-center justify-start lg:justify-center p-4 sm:p-8 py-8 sm:py-12 overflow-y-auto custom-scroll relative my-auto lg:my-0">
        <!-- Language Switcher in Top Corner -->
        <div class="absolute top-4 {{ $dir === 'rtl' ? 'left-4' : 'right-4' }} z-50">
            @if($activeLanguages->count() > 1 && $currentLang)
                <div class="relative" id="language-switcher-container">
                    <button type="button" id="language-switcher-btn" class="flex items-center gap-1.5 px-3 py-1.5 bg-[#f2f4f0]/60 hover:bg-primary/5 border border-primary/10 rounded-xl transition-all duration-300 text-xs font-semibold text-primary cursor-pointer">
                        <span class="text-sm leading-none">{{ $currentLang->flag_path ?? '🌐' }}</span>
                        <span>{{ $currentLang->name }}</span>
                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div id="language-dropdown" class="absolute {{ $dir === 'rtl' ? 'left-0 origin-top-left' : 'right-0 origin-top-right' }} mt-2 w-36 bg-white rounded-xl border border-primary/10 shadow-lg z-50 overflow-hidden hidden">
                        <div class="p-1.5 space-y-0.5">
                            @foreach($activeLanguages as $lang)
                                <a href="{{ route('language.switch', $lang->code) }}" class="flex items-center justify-between px-3 py-2 text-xs text-on-surface hover:bg-primary/5 hover:text-primary rounded-lg transition-all duration-200 {{ $currentLang->code === $lang->code ? 'bg-primary/10 text-primary font-bold' : '' }}">
                                    <span>{{ $lang->name }}</span>
                                    <span class="text-sm leading-none">{{ $lang->flag_path }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="w-full max-w-md bg-white border border-primary/5 rounded-3xl p-6 sm:p-10 shadow-[0_20px_50px_rgba(26,82,55,0.05)] transition-all duration-300">
            <!-- Header Logo Area (Visible only on mobile/tablet) -->
            <div class="flex flex-col items-center mb-8 lg:hidden text-center">
                <img alt="Logo" class="w-16 h-16 mb-3 object-contain rounded-full shadow-sm" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
                <h2 class="font-headline-md text-xl font-bold text-primary">{{ __t('wisdom_council_title') }}</h2>
                <p class="font-body-md text-xs text-outline">{{ __t('wisdom_council_subtitle') }}</p>
            </div>
            
            <!-- Desktop Title Header -->
            <div class="hidden lg:flex flex-col mb-8" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">
                <h2 class="font-headline-md text-2xl font-bold text-primary">{{ __t('wisdom_council_title') }}</h2>
                <p class="font-body-md text-xs text-outline mt-1">{{ __t('wisdom_council_subtitle') }}</p>
            </div>
            
            <!-- Auth Tabs -->
            <div class="relative flex border-b border-outline-variant/60 mb-8 w-full">
                <!-- Underline active indicator bar -->
                <div id="activeIndicator" class="absolute bottom-0 h-0.5 bg-primary transition-all duration-300 rounded-full" style="width: 50%; {{ $activeIndicatorStyle }}"></div>

                <button class="flex-1 pb-3 font-title-lg text-sm font-bold {{ $isSignup ? 'text-outline hover:text-on-surface' : 'text-primary' }} transition-colors z-10" id="loginTab" onclick="switchTab('login')">
                    {{ __t('login_tab') }}
                </button>
                <button class="flex-1 pb-3 font-title-lg text-sm font-bold {{ $isSignup ? 'text-primary' : 'text-outline hover:text-on-surface' }} transition-colors z-10" id="signupTab" onclick="switchTab('signup')">
                    {{ __t('signup_tab') }}
                </button>
            </div>

            <!-- Form Element Wrapper -->
            <form id="authFormElement" method="POST" action="{{ $isSignup ? route('register') : route('login') }}" class="space-y-4">
                @csrf

                <!-- Signup-Only Fields Group -->
                <div id="signupOnlyFields" class="tab-content {{ $isSignup ? 'tab-expanded' : 'tab-collapsed' }} space-y-4">
                    <!-- Name Fields (fname & lname) -->
                    <div class="grid grid-cols-2 gap-4">
                        <!-- First Name Field -->
                        <div class="space-y-1">
                            <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="fname">{{ __t('first_name') }}</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">person</span>
                                <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="fname" name="fname" placeholder="{{ __t('first_name') }}" type="text" value="{{ old('fname') }}" {{ $isSignup ? 'required' : '' }}>
                            </div>
                            @error('fname')
                                <p class="mt-1 text-[10px] text-red-500 font-medium" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Last Name Field -->
                        <div class="space-y-1">
                            <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="lname">{{ __t('last_name') }}</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">person</span>
                                <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="lname" name="lname" placeholder="{{ __t('last_name') }}" type="text" value="{{ old('lname') }}" {{ $isSignup ? 'required' : '' }}>
                            </div>
                            @error('lname')
                                <p class="mt-1 text-[10px] text-red-500 font-medium" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>

                <!-- Email/Password Fields Container -->
                <div id="emailPasswordFieldsContainer" class="space-y-4">
                    <!-- Email Field -->
                    <div class="space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1 relative h-5 w-full overflow-hidden">
                            <span class="absolute transition-all duration-300 {{ $isSignup ? 'opacity-0 -translate-y-4' : 'opacity-100 translate-y-0' }}" style="{{ $dir === 'rtl' ? 'right: 4px;' : 'left: 4px;' }}" id="emailLabelLogin">{{ __t('email_or_username') }}</span>
                            <span class="absolute transition-all duration-300 {{ $isSignup ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4' }}" style="{{ $dir === 'rtl' ? 'right: 4px;' : 'left: 4px;' }}" id="emailLabelSignup">{{ __t('email') }}</span>
                        </label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">mail</span>
                            <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="email" name="email" placeholder="example@hikma.sa" type="text" value="{{ old('email') }}" required>
                        </div>
                        @error('email')
                            <p class="mt-1 text-[10px] text-red-500 font-medium" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1">
                        <div class="flex justify-between items-center px-1">
                            <label class="block font-label-md text-xs font-bold text-on-surface-variant" for="password">{{ __t('password') }}</label>
                        </div>
                        <div class="relative group" id="show_hide_password">
                            <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock</span>
                            <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="password" name="password" placeholder="••••••••" type="password" required>
                            <button type="button" class="absolute {{ $toggleButtonAlign }} top-1/2 -translate-y-1/2 text-outline hover:text-primary" id="toggle_password_btn">
                                <span class="material-symbols-outlined text-[20px]" id="toggle_password_icon">visibility</span>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-[10px] text-red-500 font-medium" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password Field (Signup Only) -->
                    <div id="confirmPasswordWrapper" class="tab-content {{ $isSignup ? 'tab-expanded' : 'tab-collapsed' }} space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="password_confirmation">{{ __t('confirm_password') }}</label>
                        <div class="relative group" id="show_hide_confirm_password">
                            <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock</span>
                            <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" {{ $isSignup ? 'required' : '' }}>
                            <button type="button" class="absolute {{ $toggleButtonAlign }} top-1/2 -translate-y-1/2 text-outline hover:text-primary" id="toggle_confirm_password_btn">
                                <span class="material-symbols-outlined text-[20px]" id="toggle_confirm_password_icon">visibility</span>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-[10px] text-red-500 font-medium" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Phone-Only Fields Group (Hidden by default) -->
                <div id="phoneFieldsContainer" class="hidden space-y-4">
                    <!-- Country Code & Phone Input -->
                    <div class="space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ __t('phone_number_label') }}</label>
                        <div class="flex gap-2">
                            <!-- Mobile Number Input -->
                            <div class="relative group flex-1 {{ $dir === 'rtl' ? 'order-1' : 'order-2' }}">
                                <span class="material-symbols-outlined absolute {{ $dir === 'rtl' ? 'right-4' : 'left-4' }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">phone_iphone</span>
                                <input class="w-full {{ $dir === 'rtl' ? 'pr-12 pl-4 text-right' : 'pl-12 pr-4 text-left' }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="phone_input" placeholder="5xxxxxxxx" type="tel">
                            </div>
                            <!-- Country Code Input -->
                            <div class="w-28 relative group {{ $dir === 'rtl' ? 'order-2' : 'order-1' }}">
                                <input class="w-full px-4 py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all text-center" id="phone_code" value="+966" placeholder="+966" type="text" dir="ltr">
                            </div>
                        </div>
                    </div>

                    <!-- Action Button to Send Code -->
                    <button type="button" id="sendOtpBtn" class="w-full bg-primary hover:bg-primary-container text-white py-3 rounded-xl font-title-lg text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>{{ __t('send_otp_btn') }}</span>
                        <span class="material-symbols-outlined text-[18px]">send</span>
                    </button>

                    <!-- OTP Code Input -->
                    <div id="otpInputContainer" class="hidden space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="otp_input">{{ __t('enter_otp') }}</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">pin</span>
                            <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40 text-center tracking-widest" id="otp_input" placeholder="******" type="text" maxlength="6">
                        </div>
                        <div class="flex justify-between items-center text-xs text-outline px-1 mt-1">
                            <span id="otpTimer" class="font-medium text-primary"></span>
                            <button type="button" id="resendOtpBtn" class="text-primary hover:underline hidden">{{ __t('resend_otp_btn') }}</button>
                        </div>
                    </div>

                    <!-- Back to Email login option -->
                    <div style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">
                        <button type="button" id="backToEmailBtn" class="text-xs font-bold text-primary hover:underline transition-all cursor-pointer">
                            {{ __t('login_with_email') }}
                        </button>
                    </div>
                </div>

                <!-- Login-Only Fields Group -->
                <div id="loginOnlyFields" class="tab-content {{ $isSignup ? 'tab-collapsed' : 'tab-expanded' }} space-y-4">
                    <!-- Remember Me and Forget Password -->
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 rounded border-primary/20 bg-[#f2f4f0]/40 text-primary focus:ring-primary focus:ring-2">
                            <label for="remember" class="mr-2 ml-2 block text-xs text-outline select-none">{{ __t('remember_me') }}</label>
                        </div>
                        
                        <div id="forgetPassword">
                            <a class="text-xs font-bold text-primary hover:underline transition-all cursor-pointer" href="{{ route('password.request') }}">{{ __t('forgot_password') }}</a>
                        </div>
                    </div>
                </div>

                <!-- CTA Submit Button -->
                <button type="submit" class="w-full bg-[#1a5237] hover:bg-[#003a23] text-white py-3.5 rounded-xl font-title-lg text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 mt-6 cursor-pointer" id="submitBtn">
                    <div class="relative h-6 w-44 overflow-hidden flex items-center justify-center">
                        <span class="absolute inset-0 flex items-center justify-center transition-all duration-300 {{ $isSignup ? 'opacity-0 -translate-y-4' : 'opacity-100 translate-y-0' }}" id="btnTextLogin">{{ __t('login_submit_btn') }}</span>
                        <span class="absolute inset-0 flex items-center justify-center transition-all duration-300 {{ $isSignup ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4' }}" id="btnTextSignup">{{ __t('signup_submit_btn') }}</span>
                    </div>
                    <span class="material-symbols-outlined text-[18px] transform {{ $dir === 'rtl' ? '' : 'rotate-180' }}">arrow_back</span>
                </button>
            </form>

            <!-- Social Auth Divider & Buttons -->
            <div class="relative py-6 flex items-center justify-center">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-outline-variant/40"></div></div>
                <span class="relative px-4 bg-white text-xs font-medium text-outline">{{ __t('or_via') }}</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('auth.google') }}" class="flex items-center justify-center gap-1.5 py-3 border border-outline-variant/60 rounded-xl font-label-md text-xs text-on-surface hover:bg-surface-container-low transition-all">
                    <img alt="Google" class="w-4 h-4" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAs4w4inxc0CZZpTSAxsj1_UcmeKjiU6-CXbN8Tpz6hRA6ENcTX2SCWVWLFkf6GtywYFFcLdnvvhfIa7m-9aLsxm5Fe1wOrBc1_P1jY2SHlT3TwyEOHwFGsD4cOTBDRpkCMGCviSSfjEUSlR5tC67TxxazAcbWbAJKtVw9NW32SDU05997cjOLtxRc-7h0B50weyrhC3UN4NLqV6Te9do61Ot8eOKUWH0yBLoR4fjijwRHkxe4An2UHzmqDnwP9oEfzg0vQH9sIDNf7">
                    {{ __t('google_login') }}
                </a>
                <a href="{{ route('auth.facebook') }}" class="flex items-center justify-center gap-1.5 py-3 border border-outline-variant/60 rounded-xl font-label-md text-xs text-on-surface hover:bg-surface-container-low transition-all">
                    <img alt="Facebook" class="w-4 h-4" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDsnVL8artnWKkhGWutFl3Df9e45OOUy6VZu19Hn-1kg8SwdMvEmGo4_jFp2WILwxcMdSZ2l6AId1iHykhW5hKMixebwD4QCzM5RhwdF14lk_Si0Gm1sP_KTe6P_TCjdE1EMQcFLZeOgCgk5wvf3cYUG7AntjpmOIGercHVdpjn5UEH10Jcb-xH-Jl8u0NDCq6_f9HUP72LPqDGVN4V1HvuUITlHKXMlwmdIFaod179KUnit6zYjJ3MkrFYVBWCNCgusF4wBKfj1ZPx">
                    {{ __t('facebook_login') }}
                </a>
                <a href="{{ $isSignup ? route('firebase.phone.register') : route('firebase.phone.login') }}" id="phoneAuthLink" class="flex items-center justify-center gap-1.5 py-3 border border-outline-variant/60 rounded-xl font-label-md text-xs text-on-surface hover:bg-surface-container-low transition-all">
                    <span class="material-symbols-outlined text-[18px] text-primary">verified</span>
                    <span id="phoneAuthBtnText">{{ $isSignup ? (__t('signup_with_phone') ?: 'إنشاء حساب برقم الهاتف') : (__t('login_with_phone') ?: 'تسجيل الدخول برقم الهاتف') }}</span>
                </a>
            </div>
            
            <p class="mt-8 text-center font-body-md text-xs text-outline leading-relaxed">
                {!! str_replace(
                    [':terms', ':privacy'],
                    ['<a class="text-primary font-bold hover:underline" href="#">' . __t('terms_of_use') . '</a>', '<a class="text-primary font-bold hover:underline" href="#">' . __t('privacy_policy') . '</a>'],
                    __t('terms_privacy_agreement')
                ) !!}
            </p>
        </div>
    </section>
</main>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function () {
        // Toastr Setup
        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-left",
                "rtl": "{{ $dir === 'rtl' ? 'true' : 'false' }}"
            };
            switch(type){
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    });
</script>

<script>
    let authMode = 'email';
    let currentTab = '{{ $isSignup ? "signup" : "login" }}';
    let phoneVerificationId = null;
    let timerInterval = null;

    function switchTab(type) {
        currentTab = type;
        const loginTab = document.getElementById('loginTab');
        const signupTab = document.getElementById('signupTab');
        const activeIndicator = document.getElementById('activeIndicator');
        
        const signupOnlyFields = document.getElementById('signupOnlyFields');
        const loginOnlyFields = document.getElementById('loginOnlyFields');
        
        const btnTextLogin = document.getElementById('btnTextLogin');
        const btnTextSignup = document.getElementById('btnTextSignup');
        
        const emailLabelLogin = document.getElementById('emailLabelLogin');
        const emailLabelSignup = document.getElementById('emailLabelSignup');
        
        const authFormElement = document.getElementById('authFormElement');
        const dir = '{{ $dir }}';

        if (type === 'signup') {
            loginTab.classList.remove('text-primary');
            loginTab.classList.add('text-outline');
            signupTab.classList.add('text-primary');
            signupTab.classList.remove('text-outline');
            
            // Slide indicator
            activeIndicator.style.transform = dir === 'rtl' ? 'translateX(-100%)' : 'translateX(100%)';
            
            authFormElement.action = "{{ route('register') }}";
            
            emailLabelLogin.classList.add('opacity-0', '-translate-y-4');
            emailLabelLogin.classList.remove('opacity-100', 'translate-y-0');
            emailLabelSignup.classList.add('opacity-100', 'translate-y-0');
            emailLabelSignup.classList.remove('opacity-0', 'translate-y-4');
            
            signupOnlyFields.classList.remove('tab-collapsed');
            signupOnlyFields.classList.add('tab-expanded');
            document.getElementById('confirmPasswordWrapper').classList.remove('tab-collapsed');
            document.getElementById('confirmPasswordWrapper').classList.add('tab-expanded');
            
            loginOnlyFields.classList.remove('tab-expanded');
            loginOnlyFields.classList.add('tab-collapsed');
            
            btnTextLogin.classList.add('opacity-0', '-translate-y-4');
            btnTextLogin.classList.remove('opacity-100', 'translate-y-0');
            btnTextSignup.classList.add('opacity-100', 'translate-y-0');
            btnTextSignup.classList.remove('opacity-0', 'translate-y-4');
            
            document.getElementById('fname').required = true;
            document.getElementById('lname').required = true;
            document.getElementById('password_confirmation').required = true;

            const phoneAuthLink = document.getElementById('phoneAuthLink');
            const phoneAuthBtnText = document.getElementById('phoneAuthBtnText');
            if (phoneAuthLink) {
                phoneAuthLink.href = "{{ route('firebase.phone.register') }}";
                if (phoneAuthBtnText) phoneAuthBtnText.textContent = "إنشاء حساب برقم الهاتف";
            }
        } else {
            signupTab.classList.remove('text-primary');
            signupTab.classList.add('text-outline');
            loginTab.classList.add('text-primary');
            loginTab.classList.remove('text-outline');
            
            activeIndicator.style.transform = 'translateX(0)';
            
            authFormElement.action = "{{ route('login') }}";

            emailLabelSignup.classList.add('opacity-0', 'translate-y-4');
            emailLabelSignup.classList.remove('opacity-100', 'translate-y-0');
            emailLabelLogin.classList.add('opacity-100', 'translate-y-0');
            emailLabelLogin.classList.remove('opacity-0', '-translate-y-4');

            signupOnlyFields.classList.remove('tab-expanded');
            signupOnlyFields.classList.add('tab-collapsed');
            document.getElementById('confirmPasswordWrapper').classList.remove('tab-expanded');
            document.getElementById('confirmPasswordWrapper').classList.add('tab-collapsed');
            
            loginOnlyFields.classList.remove('tab-collapsed');
            loginOnlyFields.classList.add('tab-expanded');

            btnTextSignup.classList.add('opacity-0', 'translate-y-4');
            btnTextSignup.classList.remove('opacity-100', 'translate-y-0');
            btnTextLogin.classList.add('opacity-100', 'translate-y-0');
            btnTextLogin.classList.remove('opacity-0', '-translate-y-4');
            
            document.getElementById('fname').required = false;
            document.getElementById('lname').required = false;
            document.getElementById('password_confirmation').required = false;

            const phoneAuthLink = document.getElementById('phoneAuthLink');
            const phoneAuthBtnText = document.getElementById('phoneAuthBtnText');
            if (phoneAuthLink) {
                phoneAuthLink.href = "{{ route('firebase.phone.login') }}";
                if (phoneAuthBtnText) phoneAuthBtnText.textContent = "{{ __t('login_with_phone') ?: 'تسجيل الدخول برقم الهاتف' }}";
            }
        }

        if (authMode === 'phone') {
            adjustPhoneInputsForTab();
        }
    }

    function toggleAuthMode(mode) {
        authMode = mode;
        const emailPasswordContainer = document.getElementById('emailPasswordFieldsContainer');
        const phoneContainer = document.getElementById('phoneFieldsContainer');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const fnameInput = document.getElementById('fname');
        const lnameInput = document.getElementById('lname');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const loginOnlyFields = document.getElementById('loginOnlyFields');
        const togglePhoneAuthBtn = document.getElementById('togglePhoneAuthBtn');

        if (mode === 'phone') {
            emailPasswordContainer.classList.add('hidden');
            loginOnlyFields.classList.add('hidden');
            phoneContainer.classList.remove('hidden');

            emailInput.required = false;
            passwordInput.required = false;
            confirmPasswordInput.required = false;

            if (togglePhoneAuthBtn) {
                togglePhoneAuthBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[18px] text-blue-600">mail</span>
                    {{ __t('email') }}
                `;
            }

            adjustPhoneInputsForTab();
        } else {
            emailPasswordContainer.classList.remove('hidden');
            phoneContainer.classList.add('hidden');

            emailInput.required = true;
            passwordInput.required = true;

            if (togglePhoneAuthBtn) {
                togglePhoneAuthBtn.innerHTML = `
                    <span class="material-symbols-outlined text-[18px] text-green-600">phone</span>
                    {{ __t('phone_login') }}
                `;
            }

            if (currentTab === 'login') {
                loginOnlyFields.classList.remove('tab-collapsed');
                loginOnlyFields.classList.add('tab-expanded');
                document.getElementById('signupOnlyFields').classList.remove('tab-expanded');
                document.getElementById('signupOnlyFields').classList.add('tab-collapsed');
                fnameInput.required = false;
                lnameInput.required = false;
                confirmPasswordInput.required = false;
            } else {
                loginOnlyFields.classList.remove('tab-expanded');
                loginOnlyFields.classList.add('tab-collapsed');
                document.getElementById('signupOnlyFields').classList.remove('tab-collapsed');
                document.getElementById('signupOnlyFields').classList.add('tab-expanded');
                document.getElementById('confirmPasswordWrapper').classList.remove('tab-collapsed');
                document.getElementById('confirmPasswordWrapper').classList.add('tab-expanded');
                fnameInput.required = true;
                lnameInput.required = true;
                confirmPasswordInput.required = true;
            }
        }
    }

    function adjustPhoneInputsForTab() {
        const fnameInput = document.getElementById('fname');
        const lnameInput = document.getElementById('lname');
        const confirmPasswordInput = document.getElementById('password_confirmation');

        confirmPasswordInput.required = false;

        if (currentTab === 'login') {
            document.getElementById('signupOnlyFields').classList.remove('tab-expanded');
            document.getElementById('signupOnlyFields').classList.add('tab-collapsed');
            fnameInput.required = false;
            lnameInput.required = false;
        } else {
            document.getElementById('signupOnlyFields').classList.remove('tab-collapsed');
            document.getElementById('signupOnlyFields').classList.add('tab-expanded');
            fnameInput.required = true;
            lnameInput.required = true;
        }
    }

    $(document).ready(function () {
        $('#togglePhoneAuthBtn').on('click', function(e) {
            e.preventDefault();
            if (authMode === 'email') {
                toggleAuthMode('phone');
            } else {
                toggleAuthMode('email');
            }
        });

        $('#backToEmailBtn').on('click', function(e) {
            e.preventDefault();
            toggleAuthMode('email');
        });

        // Send OTP
        $('#sendOtpBtn').on('click', function(e) {
            e.preventDefault();
            const code = $('#phone_code').val();
            const phone = $('#phone_input').val();

            if (!phone) {
                toastr.error("{{ __t('please_enter_phone') }}");
                return;
            }

            const sendBtn = $(this);
            const originalText = sendBtn.html();
            sendBtn.prop('disabled', true).html("{{ __t('sending_otp_status') }}");

            $.ajax({
                url: "{{ route('phone.send_otp') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    code: code,
                    phone_number: phone
                },
                success: function(response) {
                    if (response.success) {
                        phoneVerificationId = response.verification_id;
                        toastr.success(response.message);
                        $('#otpInputContainer').removeClass('hidden');
                        startOtpTimer(60);
                    } else {
                        toastr.error(response.message || "{{ __t('failed_send_otp') }}");
                    }
                    sendBtn.prop('disabled', false).html(originalText);
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toastr.error(response && response.message ? response.message : "{{ __t('error_connecting_server') }}");
                    sendBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        function startOtpTimer(duration) {
            clearInterval(timerInterval);
            $('#resendOtpBtn').addClass('hidden');
            $('#otpTimer').removeClass('hidden');
            
            let timer = duration;
            updateTimerDisplay(timer);

            timerInterval = setInterval(function () {
                timer--;
                updateTimerDisplay(timer);

                if (timer <= 0) {
                    clearInterval(timerInterval);
                    $('#otpTimer').addClass('hidden');
                    $('#resendOtpBtn').removeClass('hidden');
                }
            }, 1000);
        }

        function updateTimerDisplay(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remSeconds = seconds % 60;
            const formattedSeconds = remSeconds < 10 ? '0' + remSeconds : remSeconds;
            $('#otpTimer').text("{{ __t('resend_in') }} " + minutes + ":" + formattedSeconds);
        }

        $('#resendOtpBtn').on('click', function(e) {
            e.preventDefault();
            $('#sendOtpBtn').trigger('click');
        });

        // Form submission phone auth
        $('#authFormElement').on('submit', function(e) {
            if (authMode === 'phone') {
                e.preventDefault();
                
                const otpCode = $('#otp_input').val();
                if (!phoneVerificationId) {
                    toastr.error("{{ __t('please_send_otp_first') }}");
                    return;
                }
                if (!otpCode || otpCode.length !== 6) {
                    toastr.error("{{ __t('please_enter_6digit_otp') }}");
                    return;
                }

                const submitBtn = $('#submitBtn');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html("{{ __t('verifying_otp_status') }}");

                let url = '';
                let data = {
                    _token: "{{ csrf_token() }}",
                    verification_id: phoneVerificationId,
                    otp_code: otpCode
                };

                if (currentTab === 'login') {
                    url = "{{ route('phone.login_verify') }}";
                    data.remember = $('#remember').is(':checked') ? 1 : 0;
                } else {
                    url = "{{ route('phone.register_verify') }}";
                    data.fname = $('#fname').val();
                    data.lname = $('#lname').val();
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            toastr.success("{{ __t('verification_success') }}");
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else {
                            toastr.error(response.message || "{{ __t('failed_verification') }}");
                            submitBtn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        toastr.error(response && response.message ? response.message : "{{ __t('invalid_otp_error') }}");
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });

    // Toggle language switcher dropdown
    const langBtn = document.getElementById('language-switcher-btn');
    const langDropdown = document.getElementById('language-dropdown');
    if (langBtn && langDropdown) {
        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            langDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!langBtn.contains(e.target) && !langDropdown.contains(e.target)) {
                langDropdown.classList.add('hidden');
            }
        });
    }

    // Toggle password visibility
    const togglePasswordBtn = document.getElementById('toggle_password_btn');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('toggle_password_icon');

    if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
        togglePasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                togglePasswordIcon.innerText = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                togglePasswordIcon.innerText = 'visibility';
            }
        });
    }

    // Toggle confirm password visibility
    const toggleConfirmPasswordBtn = document.getElementById('toggle_confirm_password_btn');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const toggleConfirmPasswordIcon = document.getElementById('toggle_confirm_password_icon');

    if (toggleConfirmPasswordBtn && confirmPasswordInput && toggleConfirmPasswordIcon) {
        toggleConfirmPasswordBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                toggleConfirmPasswordIcon.innerText = 'visibility_off';
            } else {
                confirmPasswordInput.type = 'password';
                toggleConfirmPasswordIcon.innerText = 'visibility';
            }
        });
    }

    // Falling Emojis Initialization
    document.addEventListener('DOMContentLoaded', () => {
        const emojis = ['💡', '🌍', '🤝', '🧠', '✨', '💬', '❤️'];
        const container = document.getElementById('emoji-container');
        if (container) {
            const emojiCount = 35;
            for (let i = 0; i < emojiCount; i++) {
                const el = document.createElement('div');
                el.innerText = emojis[Math.floor(Math.random() * emojis.length)];
                
                const sizes = ['text-3xl', 'text-4xl'];
                el.className = `falling-emoji ${sizes[Math.floor(Math.random() * sizes.length)]}`;
                
                const leftPos = Math.random() * 100;
                const duration = Math.random() * 8 + 6;
                const delay = Math.random() * -14;
                const startRot = Math.random() * 360 - 180;
                const endRot = startRot + (Math.random() * 360 - 180);
                const opacity = Math.random() * 0.2 + 0.4;
                
                el.style.left = `${leftPos}%`;
                el.style.animationDuration = `${duration}s`;
                el.style.animationDelay = `${delay}s`;
                el.style.setProperty('--start-rot', `${startRot}deg`);
                el.style.setProperty('--end-rot', `${endRot}deg`);
                el.style.setProperty('--start-opacity', opacity);
                el.style.setProperty('--end-opacity', opacity * 0.5);
                
                container.appendChild(el);
            }
        }

        // Post Cards Initialization (تم إيقافه مؤقتاً بناءً على طلب المستخدم)
        /*
        const posts = {!! json_encode($jsPosts) !!};
        const cardsContainer = document.getElementById('cards-container');
        if (cardsContainer && posts.length > 0) {
            const cardCount = Math.min(4, posts.length);
            let postIndex = cardCount;

            const dir = '{{ $dir }}';
            const isRtl = dir === 'rtl';
            const cardFlex = isRtl ? 'flex-row' : 'flex-row-reverse';
            const cardText = isRtl ? 'text-right' : 'text-left';

            function createCard(index) {
                const post = posts[index % posts.length];
                const el = document.createElement('div');
                
                const duration = 25; 
                const delay = -(index * (duration / cardCount)); 
                const scale = 1; 
                const maxOpacity = 1; 
                const xOffset = (Math.random() * 32 - 16) + 'vw';

                el.className = 'floating-card bg-white/20 border border-white/30 p-4 rounded-xl shadow-lg w-64 pointer-events-none backdrop-blur-md';
                el.style.animationDuration = `${duration}s`;
                el.style.animationDelay = `${delay}s`;
                el.style.transform = `scale(${scale})`;
                el.style.setProperty('--x-offset', xOffset);
                el.style.setProperty('--max-opacity', maxOpacity);

                function setCardContent(cardEl, postData) {
                    let imageHtml = '';
                    if (postData.image) {
                        imageHtml = `<img src="${postData.image}" alt="Post image" class="mt-2 w-full h-28 object-cover rounded-lg border border-white/20" onerror="this.style.display='none';">`;
                    }

                    cardEl.innerHTML = `
                        <div class="flex items-center gap-3 mb-2 ${cardFlex}">
                            <img src="${postData.avatar}" alt="${postData.author}" class="w-10 h-10 rounded-full border border-white/30 object-cover" onerror="this.onerror=null; this.src='{{ url('upload/no_image.jpg') }}';">
                            <div class="flex flex-col ${cardText} w-full">
                                <span class="font-label-md text-white font-bold leading-tight">${postData.author}</span>
                                <span class="font-label-sm text-primary-fixed-dim/80 text-[11px]">${postData.time}</span>
                            </div>
                        </div>
                        <p class="${cardText} font-body-md text-white/90 text-xs leading-snug">${postData.snippet}</p>
                        ${imageHtml}
                    `;
                }

                setCardContent(el, post);

                el.addEventListener('animationiteration', () => {
                    const nextPost = posts[postIndex % posts.length];
                    setCardContent(el, nextPost);
                    postIndex++;
                    
                    const newXOffset = (Math.random() * 32 - 16) + 'vw';
                    el.style.setProperty('--x-offset', newXOffset);
                });

                cardsContainer.appendChild(el);
            }

            cardsContainer.innerHTML = '';
            for (let i = 0; i < cardCount; i++) {
                createCard(i);
            }
        }
        */
    });
</script>
</body>
</html>
