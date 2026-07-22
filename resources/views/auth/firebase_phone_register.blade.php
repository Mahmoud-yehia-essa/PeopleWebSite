@php
    $activeLanguagesData = cache()->rememberForever('active_languages', function() {
        if (class_exists('App\Models\Language')) {
            return \App\Models\Language::where('is_active', true)->get()->map(function($lang) {
                return [
                    'id' => $lang->id,
                    'code' => $lang->code,
                    'direction' => $lang->direction,
                    'flag_path' => $lang->flag_path,
                    'name' => $lang->name
                ];
            })->toArray();
        }
        return [];
    });
    $activeLanguages = collect($activeLanguagesData)->map(function($lang) {
        return (object) $lang;
    });

    $currentLang = function_exists('current_language') ? current_language() : null;
    $dir = $currentLang->direction ?? (app()->getLocale() == 'ar' ? 'rtl' : 'ltr');
    $langCode = $currentLang->code ?? app()->getLocale();
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $iconAlign = $dir === 'rtl' ? 'right-4' : 'left-4';
    $inputPadding = $dir === 'rtl' ? 'pr-12 pl-4' : 'pl-12 pr-4';

    $councilTitle = function_exists('__t') ? __t('wisdom_council_title') : 'مجلس الحكمة';
    $councilSubtitle = function_exists('__t') ? __t('wisdom_council_subtitle') : 'منصة الحوار والتأثير المعرفي';
@endphp
<!DOCTYPE html>
<html dir="{{ $dir }}" lang="{{ $langCode }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('إنشاء حساب جديد برقم الهاتف') }} | {{ $councilTitle }}</title>

    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite: loads firebase-phone-auth.js bundle -->
    @vite(['resources/js/firebase-phone-auth.js'])

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8faf5;
            margin: 0;
            overflow-x: hidden;
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

        /* Step views transition */
        .step-view { display: none; }
        .step-view.active { display: block; animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }

        /* Spinner */
        .fb-spinner {
            display: inline-block; border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid #fff; border-radius: 50%;
            width: 18px; height: 18px; animation: spin 0.8s linear infinite;
        }
        @keyframes spin { 0%{ transform:rotate(0deg); } 100%{ transform:rotate(360deg); } }

        /* Step Indicators */
        .step-dots { display: flex; justify-content: center; gap: 8px; margin-bottom: 1.5rem; }
        .dot { width: 32px; height: 4px; border-radius: 2px; background: rgba(0, 58, 35, 0.15); transition: all 0.3s ease; }
        .dot.active { background: #003a23; width: 48px; }

        /* Method cards */
        .fb-method-card {
            flex: 1; padding: 0.75rem; background: rgba(242, 244, 240, 0.6);
            border: 1px solid rgba(0, 58, 35, 0.1); border-radius: 12px;
            text-align: center; cursor: pointer; transition: all 0.2s ease; font-size: 0.85rem;
            color: #191c1a;
        }
        .fb-method-card:hover { background: rgba(242, 244, 240, 0.9); }
        .fb-method-card.active { border-color: #003a23; background: rgba(0, 58, 35, 0.08); color: #003a23; font-weight: 700; }
        .fb-method-card i { margin-bottom: 4px; display: block; font-size: 1.1rem; }

        /* OTP Input boxes */
        .otp-boxes { display: flex; justify-content: space-between; gap: 8px; margin: 1.5rem 0; direction: ltr; }
        .fb-otp-box {
            width: 44px; height: 56px; background: rgba(242, 244, 240, 0.6);
            border: 1.5px solid rgba(0, 58, 35, 0.15); border-radius: 12px;
            text-align: center; font-size: 1.25rem; font-weight: 700;
            color: #003a23; outline: none; transition: all 0.2s ease;
        }
        .fb-otp-box:focus { border-color: #003a23; background: #ffffff; box-shadow: 0 0 0 3px rgba(0, 58, 35, 0.15); transform: translateY(-2px); }

        #recaptcha-container { min-height: 78px; }
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
              "fontFamily": {
                      "title-lg": ["Tajawal"],
                      "headline-md": ["Cairo"],
                      "label-md": ["Tajawal"],
                      "display-lg": ["Cairo"],
                      "label-sm": ["Tajawal"],
                      "body-md": ["Tajawal"],
                      "body-lg": ["Tajawal"],
                      "headline-lg": ["Cairo"]
              }
            },
          },
        }
    </script>
</head>
<body class="bg-background text-on-surface">
<main class="flex flex-col lg:flex-row min-h-screen w-full overflow-hidden">

    <!-- Left Half: Branding & Illustration (Desktop Only) -->
    <section class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-tr from-[#002112] via-[#1a5237] to-[#003a23] items-center justify-center p-12 overflow-hidden z-0">
        <!-- Background Decorative Elements -->
        <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-secondary-fixed filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-tertiary-fixed filter blur-3xl"></div>
        </div>
        
        <!-- Falling Emojis Container -->
        <div class="absolute inset-0 pointer-events-none z-0" id="emoji-container"></div>

        <!-- Branding Glassmorphic Box -->
        <div class="relative z-20 text-center max-w-md bg-white/5 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-2xl">
            <img alt="Logo" class="w-20 h-20 mx-auto mb-6 object-contain drop-shadow-md rounded-full bg-white/10 p-1" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
            <h1 class="font-headline-lg text-3xl font-extrabold text-white mb-2 tracking-wide">{{ $councilTitle }}</h1>
            <p class="font-body-md text-sm text-primary-fixed-dim/90 font-medium">{{ $councilSubtitle }}</p>
        </div>
    </section>

    <!-- Right Half: Authentication -->
    <section class="w-full lg:w-1/2 min-h-screen bg-surface flex flex-col items-center justify-center p-4 sm:p-8 overflow-y-auto custom-scroll relative">
        
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
            <div class="flex flex-col items-center mb-6 lg:hidden text-center">
                <img alt="Logo" class="w-16 h-16 mb-3 object-contain rounded-full shadow-sm" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
                <h2 class="font-headline-md text-xl font-bold text-primary">{{ $councilTitle }}</h2>
                <p class="font-body-md text-xs text-outline" id="fb-step-subtitle-mobile">{{ __('إنشاء حساب برقم الهاتف') }}</p>
            </div>
            
            <!-- Desktop Title Header -->
            <div class="hidden lg:flex flex-col mb-6" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">
                <h2 class="font-headline-md text-2xl font-bold text-primary">{{ __('إنشاء حساب برقم الهاتف') }}</h2>
                <p class="font-body-md text-xs text-outline mt-1" id="fb-step-subtitle">{{ __('أدخل الاسم ورقم الهاتف لتأكيد حسابك') }}</p>
            </div>

            <!-- Step Dots -->
            <div class="step-dots">
                <div class="dot active" id="fb-dot-1"></div>
                <div class="dot" id="fb-dot-2"></div>
            </div>

            <!-- Alerts -->
            <div class="p-3 rounded-xl text-xs font-medium mb-4 hidden border border-red-200 bg-red-50 text-red-700" id="fb-alert-error"></div>
            <div class="p-3 rounded-xl text-xs font-medium mb-4 hidden border border-green-200 bg-green-50 text-green-700" id="fb-alert-success"></div>

            <!-- STEP 1: Registration Form -->
            <div class="step-view active" id="fb-step-1-view">
                <form id="fb-form-send" onsubmit="fbHandleSendOtp(event)" class="space-y-4">
                    <input type="hidden" id="fb-is-register" value="1">
                    
                    <!-- First Name & Last Name Fields -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ __('الاسم الأول') }}</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-[20px]">person</span>
                                <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="fb-fname" placeholder="{{ __('الاسم الأول') }}" type="text" required>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ __('اسم العائلة') }}</label>
                            <div class="relative group">
                                <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-[20px]">person</span>
                                <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="fb-lname" placeholder="{{ __('اسم العائلة') }}" type="text" required>
                            </div>
                        </div>
                    </div>

                    <!-- Phone Number Field -->
                    <div class="space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ __('رقم الهاتف') }}</label>
                        <div class="flex gap-2">
                            <select class="bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs text-on-surface focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all px-3 py-3" id="fb-country-code" required dir="ltr">
                                <option value="+965" selected>🇰🇼 +965 (الكويت)</option>
                                <option value="+966">🇸🇦 +966 (السعودية)</option>
                                <option value="+971">🇦🇪 +971 (الإمارات)</option>
                                <option value="+974">🇶🇦 +974 (قطر)</option>
                                <option value="+20">🇪🇬 +20 (مصر)</option>
                                <option value="+968">🇴🇲 +968 (عمان)</option>
                                <option value="+973">🇧🇭 +973 (البحرين)</option>
                                <option value="+962">🇯🇴 +962 (الأردن)</option>
                                <option value="+1">🇺🇸 +1 (أمريكا)</option>
                            </select>
                            <div class="relative flex-1 group">
                                <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors text-[20px]">phone_iphone</span>
                                <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="fb-phone" placeholder="5xxxxxxxx" type="tel" required autocomplete="tel">
                            </div>
                        </div>
                    </div>

                    <!-- Flow selector -->
                    <div class="space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ __('وسيلة الاستلام') }}</label>
                        <div class="flex gap-2">
                            <div class="fb-method-card active" onclick="fbSelectFlow('SMS', this)">
                                <i class="fa-solid fa-comment-sms"></i>
                                <span>SMS</span>
                            </div>
                            <div class="fb-method-card" onclick="fbSelectFlow('WHATSAPP', this)">
                                <i class="fa-brands fa-whatsapp"></i>
                                <span>واتساب</span>
                            </div>
                        </div>
                        <input type="hidden" id="fb-flow-type" value="SMS">
                    </div>

                    <!-- reCAPTCHA widget -->
                    <div id="recaptcha-container" style="margin-bottom:1rem; display:flex; justify-content:center;"></div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary-container text-white py-3 rounded-xl font-title-lg text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer" id="fb-btn-send">
                        <span id="fb-btn-send-text">{{ __('إرسال رمز التحقق') }}</span>
                        <span class="material-symbols-outlined text-[18px] {{ $dir === 'rtl' ? 'rotate-180' : '' }}" id="fb-btn-send-icon">arrow_forward</span>
                    </button>
                </form>
            </div>

            <!-- STEP 2: OTP Verification Form -->
            <div class="step-view" id="fb-step-2-view">
                <p class="text-center font-body-md text-xs text-outline mb-4">
                    {{ __('تم إرسال رمز التحقق إلى:') }}<br>
                    <strong id="fb-display-phone" class="text-on-surface font-bold text-sm inline-block mt-1" dir="ltr"></strong>
                </p>

                <form id="fb-form-verify" onsubmit="fbHandleVerifyOtp(event)" class="space-y-4">
                    <div class="otp-boxes">
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                        <input type="text" class="fb-otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary-container text-white py-3 rounded-xl font-title-lg text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer" id="fb-btn-verify">
                        <span id="fb-btn-verify-text">{{ __('تأكيد وإنشاء الحساب') }}</span>
                        <span class="material-symbols-outlined text-[18px]">check</span>
                    </button>
                </form>

                <div class="text-center mt-6 text-xs text-outline space-y-2">
                    <div>
                        <span id="fb-timer-text">
                            {{ __('إعادة الإرسال بعد:') }} <strong id="fb-countdown" class="text-primary">60</strong> {{ __('ثانية') }}
                        </span>
                        <button type="button" class="text-primary font-bold hover:underline cursor-pointer" id="fb-btn-resend" onclick="fbResendOtp()" disabled style="display:none;">
                            {{ __('إعادة إرسال الرمز الآن') }}
                        </button>
                    </div>
                    <div class="pt-2">
                        <button type="button" onclick="fbSwitchToWhatsappAndSend()" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 font-bold transition-all text-xs cursor-pointer">
                            <i class="fa-brands fa-whatsapp text-sm text-emerald-600"></i>
                            <span>{{ __('لم تصلك رسالة الـ SMS؟ استلم الرمز عبر الواتساب') }}</span>
                        </button>
                    </div>
                    <div>
                        <a class="text-outline hover:text-on-surface font-medium cursor-pointer inline-flex items-center gap-1 mt-2" onclick="fbBackToStep1()">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                            <span>{{ __('تعديل البيانات') }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Back / Toggle links -->
            <div class="mt-6 pt-4 border-t border-outline-variant/60 text-center space-y-2">
                <div>
                    <a href="{{ route('firebase.phone.login') }}" class="text-xs font-bold text-primary hover:underline">
                        {{ __('لديك حساب بالفعل؟ تسجيل الدخول عبر رقم الهاتف') }}
                    </a>
                </div>
                <div>
                    <a href="{{ route('user.login') }}" class="inline-flex items-center justify-center gap-2 text-xs font-bold text-outline hover:text-on-surface transition-all">
                        <span class="material-symbols-outlined text-[18px] {{ $dir === 'rtl' ? 'rotate-180' : '' }}">arrow_back</span>
                        <span>{{ __('العودة إلى صفحة تسجيل الدخول الرئيسية') }}</span>
                    </a>
                </div>
            </div>

        </div>

    </section>

</main>

<script>
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
</script>
</body>
</html>
