<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>تسجيل الدخول - حكماء العالم</title>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Toastr CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >

    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "emerald-deep": "#0a1a14",
                        "gold-theme": "#e9c34a",
                        "gold-light": "#f2e2aa"
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #0a1a14;
            color: #ffffff;
            overflow: hidden;
        }

        .glass-card {
            background: rgba(10, 26, 20, 0.65);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(233, 195, 74, 0.15);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3), 0 0 40px rgba(233, 195, 74, 0.05);
        }

        .gold-text-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #f2e2aa 50%, #e9c34a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            color: transparent;
        }

        @keyframes logoGlow {
            0%, 100% {
                box-shadow: 0 0 25px rgba(233, 195, 74, 0.35), 0 0 10px rgba(233, 195, 74, 0.15);
                transform: scale(1);
            }
            50% {
                box-shadow: 0 0 50px rgba(233, 195, 74, 0.8), 0 0 25px rgba(233, 195, 74, 0.45);
                transform: scale(1.03);
            }
        }

        .logo-frame {
            position: relative;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, rgba(233, 195, 74, 0.6) 0%, rgba(255, 255, 255, 0.1) 50%, rgba(233, 195, 74, 0.2) 100%);
            box-shadow: 0 0 30px rgba(233, 195, 74, 0.15);
            animation: logoGlow 3s ease-in-out infinite;
        }
        
        .logo-frame::before {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: 50%;
            background: #0a1a14;
            z-index: 0;
        }

        .logo-inner {
            position: relative;
            z-index: 1;
            border-radius: 50%;
            overflow: hidden;
        }

        /* Ambient glowing orbs */
        .ambient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.12;
            z-index: 0;
            pointer-events: none;
        }
        
        .orb-1 {
            width: 500px;
            height: 500px;
            background: #14422d;
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            width: 450px;
            height: 450px;
            background: rgba(233, 195, 74, 0.15);
            bottom: -100px;
            left: -50px;
        }

        /* Float-in card animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Premium Buttons */
        .btn-elegant {
            background: transparent;
            border: 1px solid rgba(233, 195, 74, 0.4);
            color: #f2e2aa;
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }
        
        .btn-elegant:hover {
            border-color: rgba(233, 195, 74, 0.8);
            box-shadow: 0 0 25px rgba(233, 195, 74, 0.25);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .btn-elegant::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(233, 195, 74, 0.15), transparent);
            transition: left 0.6s ease;
        }
        
        .btn-elegant:hover::before {
            left: 100%;
        }

        /* Constellation Background */
        .bg-constellation {
            background-image: radial-gradient(circle at center, rgba(255, 255, 255, 0.01) 0%, transparent 75%);
        }
    </style>
</head>
<body class="min-h-screen relative flex items-center justify-center bg-constellation px-6 py-12">

    <!-- Orbs -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <div class="w-full max-w-md glass-card rounded-3xl p-8 md:p-10 animate-fade-in z-10 relative">
        <div class="flex flex-col items-center mb-8">
            <!-- Logo -->
            <div class="logo-frame mb-4">
                <div class="logo-inner bg-emerald-deep p-1.5 w-24 h-24 flex items-center justify-center">
                    <img alt="حكماء العالم" class="w-full h-full object-contain rounded-full opacity-95" src="{{ asset('backend/assets/images/logo.png') }}">
                </div>
            </div>
            <!-- Title -->
            <h2 class="text-3xl font-bold tracking-wide gold-text-gradient mb-1">بوابة الحكماء</h2>
            <p class="text-xs text-white/50 tracking-wider">لوحة تحكم حكماء العالم</p>
        </div>

        <form class="space-y-6" method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Input -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-white/70">البريد الإلكتروني</label>
                <div class="relative rounded-xl shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-white/40">
                        <i class="bx bx-envelope text-xl"></i>
                    </div>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="block w-full rounded-xl border-white/10 bg-white/5 pr-10 text-white placeholder-white/20 transition-all duration-300 focus:border-gold-theme focus:bg-white/10 focus:ring-2 focus:ring-gold-theme/20 text-right dir-rtl" 
                        placeholder="أدخل بريدك الإلكتروني">
                </div>
                @error('email')
                    <p class="mt-1 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Input -->
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <label for="password" class="block text-sm font-medium text-white/70">كلمة المرور</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-gold-theme/70 hover:text-gold-theme transition duration-200">نسيت كلمة المرور؟</a>
                    @endif
                </div>
                <div class="relative rounded-xl shadow-sm" id="show_hide_password">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-white/40">
                        <i class="bx bx-lock-alt text-xl"></i>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="block w-full rounded-xl border-white/10 bg-white/5 px-10 text-white placeholder-white/20 transition-all duration-300 focus:border-gold-theme focus:bg-white/10 focus:ring-2 focus:ring-gold-theme/20 text-right dir-rtl" 
                        placeholder="أدخل كلمة المرور">
                    <button type="button" class="absolute inset-y-0 left-0 flex items-center pl-3 text-white/40 hover:text-white transition duration-200" id="toggle_password_btn">
                        <i class="bx bx-hide text-xl" id="toggle_password_icon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-red-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Submit -->
            <div class="flex items-center justify-between py-1">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-white/10 bg-white/5 text-gold-theme transition duration-300 focus:ring-offset-emerald-deep focus:ring-gold-theme focus:ring-2">
                    <label for="remember" class="mr-2 block text-sm text-white/60 select-none">تذكرني</label>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full btn-elegant py-3 px-4 rounded-xl text-sm font-semibold shadow-lg transition duration-300 focus:outline-none focus:ring-2 focus:ring-gold-theme/40 flex items-center justify-center gap-2">
                    <span>تسجيل الدخول</span>
                    <i class="bx bx-left-arrow-alt text-lg"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function () {
            // Password Show/Hide Toggle
            $("#toggle_password_btn").on('click', function (event) {
                event.preventDefault();
                const passwordInput = $('#password');
                const icon = $('#toggle_password_icon');
                
                if (passwordInput.attr("type") === "text") {
                    passwordInput.attr('type', 'password');
                    icon.addClass("bx-hide").removeClass("bx-show");
                } else {
                    passwordInput.attr('type', 'text');
                    icon.removeClass("bx-hide").addClass("bx-show");
                }
            });

            // Toastr Setup
            @if(Session::has('message'))
                var type = "{{ Session::get('alert-type','info') }}";
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-left",
                    "rtl": true
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

    <!-- Star particles effect -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.createElement('div');
            container.className = 'fixed inset-0 pointer-events-none z-[1] overflow-hidden';
            document.body.insertBefore(container, document.body.firstChild);

            const emojis = ['👑', '👍', '❤️', '💬'];
            
            const filters = [
                'sepia(1) hue-rotate(10deg) saturate(200%)', // Warm Gold
                'sepia(1) hue-rotate(120deg) saturate(100%)', // Emerald
                'grayscale(1) brightness(1.5)' // Soft White/Silver
            ];

            function createParticle() {
                const p = document.createElement('div');
                const emoji = emojis[Math.floor(Math.random() * emojis.length)];
                const filter = filters[Math.floor(Math.random() * filters.length)];
                
                const size = Math.random() * 16 + 10;
                const left = Math.random() * 100;
                const duration = Math.random() * 20 + 25;
                const opacity = Math.random() * 0.12 + 0.04;
                const blur = Math.random() * 3;
                const drift = (Math.random() - 0.5) * 120; 
                
                p.innerText = emoji;
                p.style.position = 'absolute';
                p.style.left = `${left}%`;
                p.style.bottom = '-50px';
                p.style.fontSize = `${size}px`;
                p.style.filter = `${filter} blur(${blur}px)`;
                p.style.opacity = '0';
                p.style.userSelect = 'none';
                p.style.willChange = 'transform, opacity';
                p.style.fontFamily = 'Cairo, sans-serif';
                
                container.appendChild(p);
                
                const animation = p.animate([
                    { transform: `translateY(0) translateX(0) rotate(0deg)`, opacity: 0 },
                    { opacity: opacity, offset: 0.1 },
                    { opacity: opacity, offset: 0.9 },
                    { transform: `translateY(-110vh) translateX(${drift}px) rotate(${drift/2}deg)`, opacity: 0 }
                ], {
                    duration: duration * 1000,
                    easing: 'linear',
                    fill: 'forwards'
                });
                
                animation.onfinish = () => p.remove();
            }

            for(let i=0; i<20; i++) {
                setTimeout(createParticle, Math.random() * 10000);
            }
            
            setInterval(createParticle, 1800);
        });
    </script>
</body>
</html>
