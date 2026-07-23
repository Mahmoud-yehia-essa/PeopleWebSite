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
    $toggleButtonAlign = $dir === 'rtl' ? 'left-4' : 'right-4';
    
    $emailVal = old('email', request('email', session('reset_email')));
    $tokenVal = old('token', request('token', session('reset_code')));
@endphp
<!DOCTYPE html>
<html dir="{{ $dir }}" lang="{{ $langCode }}">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>تعيين كلمة المرور الجديدة | {{ __t('wisdom_council_title') }}</title>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
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
<!-- Global Preloader -->
@include('frontend.wiselook.body.preloader')
<main class="flex flex-col lg:flex-row min-h-screen w-full overflow-x-hidden lg:overflow-hidden">
    <!-- Left Half: Branding & Illustration (Desktop Only) -->
    <section class="hidden lg:flex lg:w-1/2 relative bg-gradient-to-tr from-[#002112] via-[#1a5237] to-[#003a23] items-center justify-center p-12 overflow-hidden z-0">
        <div class="absolute inset-0 opacity-10 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-400 filter blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 rounded-full bg-yellow-400 filter blur-3xl"></div>
        </div>

        <div class="relative z-20 text-center max-w-md bg-white/5 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-2xl">
            <img alt="Logo" class="w-20 h-20 mx-auto mb-6 object-contain drop-shadow-md rounded-full bg-white/10 p-1" src="{{ asset('backend/assets/images/logo.png') }}" onerror="this.onerror=null; this.src='https://lh3.googleusercontent.com/aida-public/AB6AXuAzh9RWh1LQGFq1Vs-RgwAYj9G1wegv3GnWJ_ZHyUkyPMzPnKxx5ZzJOXeS7R2YxTKa_8sYQLKuhZ3_nVvZvGPnIndsbTHS1GnuI8aSwwmMTZUkfAmEd4cAbxbt6OYk4TsNSEjfwV7xSGihVk_KdZCEDKGvrYVE4TGLoqOo5Ka2PwhaW5G8FsP1cvruhAleOJsETuwBn34n7ePaSzSFTXbDJoNfytyxS1mpaY_-Y1IloGxYxzfAfB8f87iftPF1gxxRVEeKnBMOACLb';">
            <h1 class="font-headline-lg text-3xl font-extrabold text-white mb-2 tracking-wide">{{ __t('wisdom_council_title') }}</h1>
            <p class="font-body-md text-sm text-emerald-200/90 font-medium">إنشاء كلمة مرور جديدة قوية وآمنة لحسابك</p>
        </div>
    </section>

    <!-- Right Half: Reset Password Form -->
    <section class="w-full lg:w-1/2 min-h-screen bg-surface flex flex-col items-center justify-start lg:justify-center p-4 sm:p-8 py-8 sm:py-12 overflow-y-auto custom-scroll relative my-auto lg:my-0">
        <div class="w-full max-w-md bg-white border border-primary/5 rounded-3xl p-6 sm:p-10 shadow-[0_20px_50px_rgba(26,82,55,0.05)] transition-all duration-300">
            <!-- Header Icon & Title -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="w-16 h-16 bg-emerald-50 text-emerald-800 rounded-2xl flex items-center justify-center mb-4 shadow-sm border border-emerald-100">
                    <span class="material-symbols-outlined text-[32px]">key</span>
                </div>
                <h2 class="font-headline-md text-2xl font-bold text-primary mb-2">تأكيد كلمة المرور الجديدة</h2>
                <p class="font-body-md text-xs text-outline leading-relaxed px-4">
                    يرجى أدخل كود التحقق المرسل لبريدك مع كلمة المرور الجديدة.
                </p>
            </div>

            <!-- Global Errors -->
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">error</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div class="space-y-1">
                    <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="email">البريد الإلكتروني</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">mail</span>
                        <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all" id="email" name="email" placeholder="example@domain.com" type="email" value="{{ $emailVal }}" required readonly>
                    </div>
                </div>

                <!-- 6-digit Verification Code -->
                <div class="space-y-1">
                    <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="token">كود التحقق (6 أرقام)</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">pin</span>
                        <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all text-center tracking-widest font-mono text-base" id="token" name="token" placeholder="123456" type="text" maxlength="6" value="{{ $tokenVal }}" required autofocus>
                    </div>
                </div>

                <!-- New Password -->
                <div class="space-y-1">
                    <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="password">كلمة المرور الجديدة</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock</span>
                        <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all" id="password" name="password" placeholder="••••••••" type="password" required>
                        <button type="button" class="absolute {{ $toggleButtonAlign }} top-1/2 -translate-y-1/2 text-outline hover:text-primary" onclick="togglePass('password', 'pass_icon')">
                            <span class="material-symbols-outlined text-[20px]" id="pass_icon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1">
                    <label class="block font-label-md text-xs font-bold text-on-surface-variant px-1" style="text-align: {{ $dir === 'rtl' ? 'right' : 'left' }};" for="password_confirmation">تأكيد كلمة المرور الجديدة</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute {{ $iconAlign }} top-1/2 -translate-y-1/2 text-outline group-focus-within:text-primary transition-colors">lock_reset</span>
                        <input class="w-full {{ $inputPadding }} py-3 bg-[#f2f4f0]/40 border border-primary/10 rounded-xl font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white outline-none transition-all" id="password_confirmation" name="password_confirmation" placeholder="••••••••" type="password" required>
                        <button type="button" class="absolute {{ $toggleButtonAlign }} top-1/2 -translate-y-1/2 text-outline hover:text-primary" onclick="togglePass('password_confirmation', 'conf_icon')">
                            <span class="material-symbols-outlined text-[20px]" id="conf_icon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-[#1a5237] hover:bg-[#003a23] text-white py-3.5 rounded-xl font-title-lg text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 mt-6 cursor-pointer">
                    <span>حفظ كلمة المرور الجديدة</span>
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </button>
            </form>

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
    function togglePass(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (field.type === "password") {
            field.type = "text";
            icon.textContent = "visibility_off";
        } else {
            field.type = "password";
            icon.textContent = "visibility";
        }
    }
    $(document).ready(function () {
        @if(Session::has('message'))
            var type = "{{ Session::get('alert-type','info') }}";
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-left",
                "rtl": "{{ $dir === 'rtl' ? 'true' : 'false' }}"
            };
            switch(type){
                case 'info': toastr.info("{{ Session::get('message') }}"); break;
                case 'success': toastr.success("{{ Session::get('message') }}"); break;
                case 'warning': toastr.warning("{{ Session::get('message') }}"); break;
                case 'error': toastr.error("{{ Session::get('message') }}"); break;
            }
        @endif
    });
</script>
</body>
</html>
