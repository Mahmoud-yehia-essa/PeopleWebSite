<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>دعوة انضمام من {{ $marketer->first_name }} - حكماء العالم</title>
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
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
            overflow-x: hidden;
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

        .bg-constellation {
            background-image: radial-gradient(circle at center, rgba(255, 255, 255, 0.01) 0%, transparent 75%);
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
            display: inline-block;
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
    </style>
</head>
<body class="min-h-screen relative flex items-center justify-center bg-constellation px-6 py-12">

    <!-- Ambient Orbs -->
    <div class="ambient-orb orb-1"></div>
    <div class="ambient-orb orb-2"></div>

    <div class="w-full max-w-xl glass-card rounded-3xl p-8 md:p-12 animate-fade-in z-10 relative text-center">
        <!-- Site Logo -->
        <div class="flex justify-center mb-8">
            <div class="logo-frame">
                <div class="logo-inner bg-emerald-deep p-1.5 w-24 h-24 flex items-center justify-center">
                    <img alt="حكماء العالم" class="w-full h-full object-contain rounded-full opacity-95" src="{{ asset('backend/assets/images/logo.png') }}">
                </div>
            </div>
        </div>

        <div class="flex flex-col items-center mb-8">
            <!-- Marketer Avatar -->
            <div class="mb-4">
                <img alt="{{ $marketer->first_name }}" 
                     class="w-24 h-24 object-cover rounded-2xl border-2 border-gold-theme/40 shadow-lg" 
                     src="{{ (!empty($marketer->profile_picture)) ? 'http://localhost:8888/new_wiselook/uploads/'.$marketer->profile_picture : url('upload/no_image.jpg') }}">
            </div>
            
            <!-- Invitation Title -->
            <span class="text-xs text-gold-theme uppercase tracking-widest font-semibold bg-gold-theme/10 px-3 py-1 rounded-full border border-gold-theme/20 mb-3">دعوة خاصة</span>
            <h2 class="text-3xl font-bold gold-text-gradient mb-2">لقد تم دعوتك بواسطة {{ $marketer->first_name }}</h2>
            <p class="text-sm text-white/60">ينصحك {{ $marketer->first_name }} {{ $marketer->last_name }} بالانضمام إلى شبكة حكماء العالم</p>
        </div>

        <!-- Marketer Custom Message/Bio -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 mb-8 text-right">
            <h5 class="text-sm font-semibold text-gold-theme mb-2 flex items-center gap-2">
                <i class="bx bxs-quote-right-alt text-lg"></i>
                <span>رسالة ترحيبية:</span>
            </h5>
            <p class="text-sm text-white/80 leading-relaxed">
                {{ $marketer->bio ?? 'مرحباً بك! أدعوك للتسجيل معنا في منصة حكماء العالم لمتابعة ومشاركة أفضل المقالات والمواضيع والتواصل مع الأعضاء والقصص اليومية الشيقة.' }}
            </p>
        </div>

        <!-- Action Button -->
        <div class="space-y-4">
            @auth
                <!-- If already logged in -->
                <div class="bg-emerald-deep/40 border border-emerald-500/20 rounded-xl p-4 mb-4 text-center">
                    <p class="text-sm text-emerald-400">
                        <i class="bx bx-check-circle align-middle me-1"></i>
                        أنت مسجل دخول بالفعل كـ <strong>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</strong>
                    </p>
                </div>
                <a href="{{ route('dashboard') }}" class="block w-full btn-elegant py-3 px-4 rounded-xl text-sm font-semibold shadow-lg flex items-center justify-center gap-2">
                    <span>الانتقال إلى لوحة التحكم</span>
                    <i class="bx bx-left-arrow-alt text-lg"></i>
                </a>
            @else
                <!-- If Guest -->
                <a href="{{ url('/user-login') }}" class="block w-full btn-elegant py-3 px-4 rounded-xl text-sm font-semibold shadow-lg flex items-center justify-center gap-2">
                    <span>قبول الدعوة وإنشاء حساب جديد</span>
                    <i class="bx bx-user-plus text-lg"></i>
                </a>
                <p class="text-xs text-white/40 pt-2">
                    لديك حساب بالفعل؟ 
                    <a href="{{ url('/user-login') }}" class="text-gold-theme hover:underline">تسجيل الدخول من هنا</a>
                </p>
            @endauth
        </div>
    </div>

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

            let active = true;

            function createParticle() {
                if (!active) return;
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
            
            const intervalId = setInterval(createParticle, 1800);

            // إيقاف الحركات فوراً عند الضغط على أي رابط لتسريع الانتقال ومنع التعليق
            const cleanUpParticles = () => {
                active = false;
                clearInterval(intervalId);
                container.innerHTML = '';
                container.remove();
            };

            document.querySelectorAll('a, button').forEach(el => {
                el.addEventListener('click', cleanUpParticles);
            });
        });
    </script>
</body>
</html>
