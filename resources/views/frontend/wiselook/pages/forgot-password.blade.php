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
    $dir = $currentLang->direction ?? 'rtl';
    $langCode = $currentLang->code ?? 'ar';
    $iconAlign = $dir === 'rtl' ? 'right-4' : 'left-4';
    $inputPadding = $dir === 'rtl' ? 'pr-12 pl-4' : 'pl-12 pr-4';

    $codeSent   = session('code_sent', false);
    $resetEmail = session('reset_email', old('email', ''));
    $resetCode  = session('reset_code', '');
@endphp
<!DOCTYPE html>
<html dir="{{ $dir }}" lang="{{ $langCode }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>استعادة كلمة المرور | {{ __t('wisdom_council_title') }}</title>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f8faf5;
            margin: 0;
            overflow: hidden;
        }
        .font-headline-md { font-family: 'Cairo', sans-serif; }
        .font-headline-lg { font-family: 'Cairo', sans-serif; }
        
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

        @keyframes pulse-success {
            0%, 100% { box-shadow: 0 0 0 0 rgba(26, 82, 55, 0.3); }
            50% { box-shadow: 0 0 0 8px rgba(26, 82, 55, 0); }
        }
        .btn-proceed {
            animation: pulse-success 2s infinite;
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "background": "#f8faf5",
                      "outline": "#717972",
                      "surface": "#f8faf5",
                      "primary-container": "#1a5237",
                      "primary": "#003a23",
                      "on-surface": "#191c1a",
                      "on-surface-variant": "#404943"
              },
              "fontFamily": {
                      "headline-md": ["Cairo"],
                      "body-md": ["Tajawal"],
                      "title-lg": ["Tajawal"]
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
        <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-400 filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-yellow-400 filter blur-3xl"></div>
        </div>

        <div class="relative z-20 text-center max-w-md bg-white/5 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-2xl">
            <img alt="Logo" class="w-20 h-20 mx-auto mb-6 object-contain drop-shadow-md rounded-full bg-white/10 p-1" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
            <h1 class="font-headline-lg text-3xl font-extrabold text-white mb-2 tracking-wide">{{ __t('wisdom_council_title') }}</h1>
            <p class="font-body-md text-sm text-emerald-200/90 font-medium">استعادة الوصول إلى حسابك بكل أمان ويسر</p>
        </div>
    </section>

    <!-- Right Half: Forgot Password Card -->
    <section class="w-full lg:w-1/2 min-h-screen bg-surface flex flex-col items-center justify-center p-4 sm:p-8 overflow-y-auto custom-scroll relative">
        <!-- Language Switcher Top Corner -->
        <div class="absolute top-4 {{ $dir === 'rtl' ? 'left-4' : 'right-4' }} z-50">
            @if($activeLanguages->count() > 1 && $currentLang)
                <div class="relative" id="language-switcher-container">
                    <button type="button" id="language-switcher-btn" class="flex items-center gap-1.5 px-3 py-1.5 bg-[#f2f4f0]/60 hover:bg-primary/5 border border-primary/10 rounded-xl transition-all duration-300 text-xs font-semibold text-primary cursor-pointer">
                        <span class="text-sm leading-none">{{ $currentLang->flag_path ?? '🌐' }}</span>
                        <span>{{ $currentLang->name }}</span>
                    </button>
                </div>
            @endif
        </div>

        <div class="w-full max-w-md bg-white border border-primary/5 rounded-3xl p-6 sm:p-10 shadow-[0_20px_50px_rgba(26,82,55,0.05)] transition-all duration-300">
            <!-- Header Icon & Title -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-800 rounded-2xl flex items-center justify-center mb-4 shadow-sm border border-emerald-100">
                    <span class="material-symbols-outlined text-[32px]">lock_reset</span>
                </div>
                <h2 class="font-headline-md text-2xl font-bold text-primary mb-2">استعادة كلمة المرور</h2>
                <p class="font-body-md text-xs text-outline leading-relaxed px-4">
                    أدخل بريدك الإلكتروني المسجل لدينا وسنرسل لك كود التحقق لإعادة تعيين كلمة المرور.
                </p>
            </div>

            @if($codeSent)
                {{-- ===== SUCCESS STATE: كود تم إرساله ===== --}}
                <div class="mb-5 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 text-sm rounded-2xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="material-symbols-outlined text-[22px] text-emerald-600">mark_email_read</span>
                        <span class="font-bold text-base">تم إرسال كود التحقق!</span>
                    </div>
                    <p class="text-xs leading-relaxed text-emerald-700">
                        تم إرسال كود التحقق المكوّن من 6 أرقام إلى بريدك الإلكتروني:
                        <strong class="block mt-1 font-mono text-emerald-800">{{ $resetEmail }}</strong>
                    </p>
                    <p class="text-xs text-emerald-600 mt-2">
                        يرجى مراجعة صندوق الوارد (وقد يكون في Spam/Junk).
                    </p>
                </div>

                <!-- زر الانتقال لإدخال الكود -->
                <a href="{{ route('password.reset', ['email' => $resetEmail, 'token' => $resetCode]) }}"
                   class="btn-proceed w-full bg-[#1a5237] hover:bg-[#003a23] text-white py-3.5 rounded-xl font-title-lg text-sm font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer text-center">
                    <span class="material-symbols-outlined text-[18px]">verified</span>
                    <span>إدخال كود التحقق الآن</span>
                </a>

                <div class="mt-4 text-center">
                    <span class="text-xs text-outline">لم يصلك الكود؟ </span>
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-primary hover:underline">
                        إعادة إرسال الكود
                    </a>
                </div>
            @else
                {{-- ===== NORMAL STATE: نموذج الإدخال ===== --}}

                <!-- Session Status Alert -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-5" id="forgotForm">
                    @csrf

                    <!-- Email Input Field -->
                    <div class="space-y-1">
                        <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="email">البريد الإلكتروني</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">mail</span>
                            <input class="w-full {{ $inputPadding }} py-3.5 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all placeholder:text-outline/40" id="email" name="email" placeholder="example@domain.com" type="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <p class="mt-1 text-[11px] text-red-500 font-medium px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="w-full bg-[#1a5237] hover:bg-[#003a23] text-white py-3.5 rounded-xl font-title-lg text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span id="btnText">إرسال كود التحقق</span>
                        <span class="material-symbols-outlined text-[18px] transform {{ $dir === 'rtl' ? '' : 'rotate-180' }}" id="btnIcon">send</span>
                        <span id="btnLoader" class="hidden">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </button>
                </form>
            @endif

            <!-- Back to Login Link -->
            <div class="mt-8 text-center border-t border-gray-100 pt-6">
                <a href="{{ route('user.login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-primary hover:underline transition-all">
                    <span class="material-symbols-outlined text-[16px] transform {{ $dir === 'rtl' ? 'rotate-180' : '' }}">arrow_back</span>
                    <span>العودة إلى صفحة تسجيل الدخول</span>
                </a>
            </div>
        </div>
    </section>
</main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    $(document).ready(function () {

        // إعدادات Toastr الموحّدة
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-left",
            "rtl": {{ $dir === 'rtl' ? 'true' : 'false' }},
            "timeOut": "6000",
            "extendedTimeOut": "2000"
        };

        @if(Session::has('message'))
            var msgType = "{{ Session::get('alert-type', 'info') }}";
            var msgText = "{{ addslashes(Session::get('message')) }}";
            switch(msgType) {
                case 'success': toastr.success(msgText); break;
                case 'error':   toastr.error(msgText);   break;
                case 'warning': toastr.warning(msgText); break;
                default:        toastr.info(msgText);    break;
            }
        @endif

        // إظهار loading عند إرسال النموذج
        $('#forgotForm').on('submit', function() {
            const btn = $('#submitBtn');
            btn.prop('disabled', true);
            $('#btnText').text('جارٍ الإرسال...');
            $('#btnIcon').addClass('hidden');
            $('#btnLoader').removeClass('hidden');
        });
    });
</script>
</body>
</html>

