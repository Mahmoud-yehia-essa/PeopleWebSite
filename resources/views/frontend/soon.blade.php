<!DOCTYPE html>
<html lang="{{ current_language()->code ?? 'ar' }}" dir="{{ current_language()->direction ?? 'rtl' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>قريباً | {{ __t('wisdom_council_title') ?? 'حكماء العالم' }}</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#003a23',
                        'primary-light': '#1a5237',
                        accent: '#fed65b',
                        'accent-dark': '#e9c349',
                        darkBg: '#00190e',
                    },
                    fontFamily: {
                        headline: ["Cairo", "sans-serif"],
                        body: ["Tajawal", "sans-serif"],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            background: radial-gradient(circle at center, #004d2f 0%, #002818 65%, #00150b 100%);
            color: #ffffff;
            min-h-screen: 100vh;
            overflow-x: hidden;
        }

        /* Glassmorphic Container */
        .glass-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.5), inset 0 1px 1px rgba(255, 255, 255, 0.2);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(254, 214, 91, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 12px 30px -8px rgba(0, 58, 35, 0.4);
        }

        /* Animated Glowing Orbs */
        .glow-orb-1 {
            position: absolute;
            top: -10%;
            right: -10%;
            width: 45vw;
            height: 45vw;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(0, 0, 0, 0) 70%);
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            animation: orbFloat 8s ease-in-out infinite alternate;
        }

        .glow-orb-2 {
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 45vw;
            height: 45vw;
            background: radial-gradient(circle, rgba(234, 179, 8, 0.15) 0%, rgba(0, 0, 0, 0) 70%);
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            animation: orbFloat 10s ease-in-out infinite alternate-reverse;
        }

        @keyframes orbFloat {
            0% { transform: scale(0.9) translate(0, 0); opacity: 0.6; }
            100% { transform: scale(1.15) translate(30px, 20px); opacity: 0.9; }
        }

        /* Logo Spin Outer Ring */
        .logo-ring-spin {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0%, #10b981 40%, #fed65b 70%, transparent 100%);
            animation: ringSpin 3s linear infinite;
            mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2px));
            -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2px));
        }

        @keyframes ringSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Pulsing Badge */
        .pulse-badge {
            animation: badgePulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes badgePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.03); }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-10 relative overflow-x-hidden selection:bg-accent selection:text-primary">

    <!-- Ambient Glowing Orbs -->
    <div class="glow-orb-1"></div>
    <div class="glow-orb-2"></div>

    <main class="w-full max-w-4xl mx-auto z-10 my-auto">
        <div class="glass-container rounded-3xl p-6 sm:p-10 md:p-14 text-center relative overflow-hidden">
            
            <!-- Top Status Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-accent/15 border border-accent/30 text-accent font-headline font-bold text-xs sm:text-sm mb-8 pulse-badge shadow-lg shadow-accent/5">
                <span class="w-2 h-2 rounded-full bg-accent animate-ping"></span>
                <span>جاري الإعداد والتحضير .. قريباً في حلتها الجديدة</span>
            </div>

            <!-- Logo Section -->
            <div class="relative w-28 h-28 sm:w-32 sm:h-32 mx-auto mb-8 flex items-center justify-center">
                <div class="logo-ring-spin"></div>
                <div class="w-full h-full bg-white/10 backdrop-blur-xl border border-white/20 rounded-full flex items-center justify-center shadow-2xl p-2">
                    <img src="{{ asset('backend/assets/images/logo.png') }}" alt="حكماء العالم" class="w-full h-full object-contain rounded-full drop-shadow-lg">
                </div>
            </div>

            <!-- Main Heading -->
            <h1 class="font-headline text-3xl sm:text-4xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-accent mb-4 tracking-tight leading-tight">
                منصة حكماء العالم
            </h1>

            <p class="font-body text-base sm:text-lg md:text-xl text-emerald-100/90 max-w-2xl mx-auto mb-10 leading-relaxed font-medium">
                نعمل بكل شغف ودقة على تطوير منصة معرفية وثقافية رائدة تجمع القادة والمفكرين والمبدعين. انتظرونا قريباً لإطلاق التجربة المتكاملة!
            </p>

            <!-- Feature Cards Preview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10 text-right">
                <div class="glass-card p-5 rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/20 text-accent flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-[24px]">forum</span>
                    </div>
                    <h3 class="font-headline font-bold text-base text-white mb-1">ساحة النقاشات</h3>
                    <p class="font-body text-xs text-slate-300/80 leading-normal">حوارات فكرية وإثراء محتوى رقمي متجدد باستمرار</p>
                </div>

                <div class="glass-card p-5 rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/20 text-accent flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-[24px]">account_balance</span>
                    </div>
                    <h3 class="font-headline font-bold text-base text-white mb-1">مجلس الحكماء</h3>
                    <p class="font-body text-xs text-slate-300/80 leading-normal">لجان متخصصة وتقييمات عالية المستوى للمواضيع</p>
                </div>

                <div class="glass-card p-5 rounded-2xl">
                    <div class="w-10 h-10 rounded-xl bg-accent/10 border border-accent/20 text-accent flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-[24px]">groups</span>
                    </div>
                    <h3 class="font-headline font-bold text-base text-white mb-1">المجموعات التخصصية</h3>
                    <p class="font-body text-xs text-slate-300/80 leading-normal">مجموعات نقاشية في مختلف المواضيع</p>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-8 text-xs text-slate-400 font-body">
                جميع الحقوق محفوظة &copy; {{ date('Y') }} لمنصة حكماء العالم
            </div>

        </div>
    </main>

</body>
</html>
