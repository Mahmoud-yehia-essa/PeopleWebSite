<!DOCTYPE html>
<html dir="{{ current_language()->direction ?? 'rtl' }}" lang="{{ current_language()->code ?? 'ar' }}">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="facebook-domain-verification" content="0v84hov7fx1htv4mzy3ta9qstb54z4" />
    @auth
        @vite(['resources/js/app.js'])
    @endauth
    <title>حكماء العالم | @yield('title', 'الرئيسية')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&amp;family=Tajawal:wght@300;400;500;700;800;900&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=block" rel="stylesheet"/>
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css" >
    
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
                      "on-primary-fixed-variant": "#274e3d",
                      "surface-bright": "#f4fafd",
                      "surface-container-lowest": "#ffffff",
                      "on-background": "#0f172a",
                      "on-secondary-fixed-variant": "#574500",
                      "primary": "#003a23", // deep forest green matching login.blade
                      "surface-container-highest": "#dde4e6",
                      "surface-dim": "#d4dbdd",
                      "surface-container-high": "#e2e9ec",
                      "surface-variant": "#dde4e6",
                      "on-primary-fixed": "#002114",
                      "outline": "#717973",
                      "on-tertiary-fixed": "#1c1c11",
                      "secondary-fixed": "#ffe088",
                      "error-container": "#ffdad6",
                      "on-tertiary": "#ffffff",
                      "secondary-fixed-dim": "#e9c349",
                      "inverse-surface": "#2b3234",
                      "on-surface-variant": "#334155",
                      "surface-container-low": "#eef5f7",
                      "surface": "#f4fafd",
                      "primary-fixed-dim": "#a5d0b9",
                      "on-error-container": "#93000a",
                      "secondary-container": "#fed65b",
                      "on-tertiary-container": "#a8a695",
                      "on-secondary": "#ffffff",
                      "on-secondary-container": "#745c00",
                      "error": "#ba1a1a",
                      "tertiary-container": "#3d3c2f",
                      "surface-tint": "#3f6653",
                      "outline-variant": "#c1c8c2",
                      "background": "#f4fafd",
                      "on-error": "#ffffff",
                      "inverse-primary": "#a5d0b9",
                      "inverse-on-surface": "#ebf2f4",
                      "primary-container": "#1a5237",
                      "tertiary": "#27261a",
                      "on-secondary-fixed": "#241a00",
                      "on-primary": "#ffffff",
                      "on-surface": "#0f172a",
                      "on-primary-container": "#86af99",
                      "primary-fixed": "#c1ecd4",
                      "tertiary-fixed": "#e6e3d0",
                      "tertiary-fixed-dim": "#c9c7b5",
                      "on-tertiary-fixed-variant": "#48473a",
                      "secondary": "#735c00",
                      "surface-container": "#e8eff1"
              },
              "borderRadius": {
                      "DEFAULT": "0.25rem",
                      "lg": "0.5rem",
                      "xl": "0.75rem",
                      "full": "9999px"
              },
              "spacing": {
                      "unit": "8px",
                      "stack-sm": "12px",
                      "margin-desktop": "40px",
                      "margin-mobile": "16px",
                      "gutter": "24px",
                      "container-max-width": "1140px",
                      "stack-lg": "48px",
                      "stack-md": "24px"
              },
              "fontFamily": {
                      "headline-display": ["Cairo", "sans-serif"],
                      "headline-lg-mobile": ["Cairo", "sans-serif"],
                      "headline-lg": ["Cairo", "sans-serif"],
                      "body-lg": ["Tajawal", "sans-serif"],
                      "body-md": ["Tajawal", "sans-serif"],
                      "label-sm": ["Tajawal", "sans-serif"]
              },
              "fontSize": {
                      "headline-display": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                      "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "600"}],
                      "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "600"}],
                      "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                      "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                      "label-sm": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "600"}]
              }
            }
          }
        }
    </script>
    
    <style>
        body {
            font-family: 'Tajawal', 'Cairo', sans-serif;
            font-weight: 500; /* Set default body font weight to Medium (500) for clearer typography */
            background-color: #f4fafd;
            color: #0f172a; /* Slate 900 for premium high-contrast readability */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Heading styles for stronger and clearer headers */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            font-weight: 700 !important;
            color: #003a23;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1;
        }
        
        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #dde4e6;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #717973;
        }

        /* Share, Delete, Guest Auth & Welcome Modal Transitions */
        #guest-auth-modal, #rank-details-modal, #points-details-modal, #share-post-modal, #delete-post-modal, #delete-action-modal, #welcome-modal {
            transition: visibility 0.4s;
        }
        #guest-auth-modal.modal-show, #rank-details-modal.modal-show, #points-details-modal.modal-show, #share-post-modal.modal-show, #delete-post-modal.modal-show, #delete-action-modal.modal-show, #welcome-modal.modal-show {
            display: flex !important;
        }
        #guest-auth-modal.modal-show .modal-backdrop, #rank-details-modal.modal-show .modal-backdrop, #points-details-modal.modal-show .modal-backdrop, #share-post-modal.modal-show .modal-backdrop, #delete-post-modal.modal-show .modal-backdrop, #delete-action-modal.modal-show .modal-backdrop, #welcome-modal.modal-show .modal-backdrop {
            opacity: 1 !important;
        }
        #guest-auth-modal.modal-show .modal-container, #rank-details-modal.modal-show .modal-container, #points-details-modal.modal-show .modal-container, #share-post-modal.modal-show .modal-container, #delete-post-modal.modal-show .modal-container, #delete-action-modal.modal-show .modal-container, #welcome-modal.modal-show .modal-container {
            transform: translateY(0) scale(1) !important;
            opacity: 1 !important;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-background antialiased flex flex-col min-h-screen selection:bg-primary-container selection:text-on-primary-container">
    <!-- Global Preloader -->
    @include('frontend.wiselook.body.preloader')
    
    <!-- Header -->
    @include('frontend.wiselook.body.header')

    <!-- Main Content Area -->
    <div id="pjax-container" class="flex-1 w-full {{ (current_language()->direction ?? 'rtl') === 'rtl' ? 'lg:pr-72' : 'lg:pl-72' }} pb-6 md:pb-0">
        @yield('main')
    </div>

    <!-- Footer -->
    @include('frontend.wiselook.body.footer')

    <!-- Global Comments & Supporters Modal -->
    @include('frontend.wiselook.body.global_comments_modal')

    <!-- Global Share Post Modal -->
    <div id="share-post-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop with high-end glassmorphism -->
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Content Container (Premium Glassmorphism Design) -->
        <div class="modal-container relative max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 text-right overflow-hidden">
            <!-- Ambient Glow Effects inside Modal -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Close Button -->
            <button id="close-share-modal-btn" class="absolute top-5 left-5 text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            
            <!-- Header -->
            <div class="mb-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/5 flex items-center justify-center text-primary shrink-0 border border-primary/10">
                    <span class="material-symbols-outlined text-[24px]">share</span>
                </div>
                <div>
                    <h3 class="font-headline-lg text-base font-extrabold text-primary leading-tight">مشاركة الحكمة والموضوع</h3>
                    <p class="font-body-md text-[11px] text-on-surface-variant/80 mt-0.5">انشر المعرفة عبر شبكات التواصل الاجتماعي بنقرة واحدة</p>
                </div>
            </div>
            
            <!-- Shared Post Mini Preview Widget -->
            <div class="mb-6 p-4 rounded-2xl bg-surface-container-low/70 border border-primary/5 text-right relative">
                <span class="absolute top-3 left-3 text-[9px] font-bold px-2 py-0.5 rounded-full bg-primary/5 text-primary border border-primary/10">معاينة المشاركة</span>
                <p id="share-modal-preview-text" class="font-body-md text-xs text-on-surface-variant leading-relaxed line-clamp-3 pl-16 pr-1"></p>
            </div>
            
            <!-- Social Networks list (Brand cards with gradients, custom icons and hover transitions) -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <!-- WhatsApp -->
                <a id="share-whatsapp" href="#" target="_blank" class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-emerald-500/5 to-emerald-500/10 border border-emerald-500/20 hover:border-emerald-500 text-emerald-600 group cursor-pointer transition-all duration-300 hover:shadow-[0_8px_20px_-6px_rgba(16,185,129,0.3)] hover:-translate-y-0.5">
                    <span class="font-label-sm text-xs font-extrabold pr-1">واتساب (WhatsApp)</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.5-5.739-1.453L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.413 9.863-9.83.001-2.624-1.02-5.09-2.875-6.948C16.607 1.98 14.14 1.955 11.514 1.955c-5.437 0-9.862 4.414-9.865 9.831-.001 1.713.453 3.388 1.317 4.894L2.005 21.93l5.094-1.336z"/></svg>
                    </div>
                </a>
                
                <!-- X / Twitter -->
                <a id="share-twitter" href="#" target="_blank" class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-slate-900/5 to-slate-900/10 border border-slate-900/20 hover:border-slate-900 text-slate-800 group cursor-pointer transition-all duration-300 hover:shadow-[0_8px_20px_-6px_rgba(15,23,42,0.3)] hover:-translate-y-0.5">
                    <span class="font-label-sm text-xs font-extrabold pr-1">منصة إكس (X)</span>
                    <div class="w-9 h-9 rounded-xl bg-slate-950 text-white flex items-center justify-center shadow-lg shadow-slate-950/20 group-hover:scale-110 transition-transform">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </div>
                </a>

                <!-- Facebook -->
                <a id="share-facebook" href="#" target="_blank" class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-blue-600/5 to-blue-600/10 border border-blue-600/20 hover:border-blue-600 text-blue-600 group cursor-pointer transition-all duration-300 hover:shadow-[0_8px_20px_-6px_rgba(37,99,235,0.3)] hover:-translate-y-0.5">
                    <span class="font-label-sm text-xs font-extrabold pr-1">فيسبوك (Facebook)</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-600/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </div>
                </a>

                <!-- LinkedIn -->
                <a id="share-linkedin" href="#" target="_blank" class="flex items-center justify-between p-3.5 rounded-2xl bg-gradient-to-r from-sky-700/5 to-sky-700/10 border border-sky-700/20 hover:border-sky-700 text-sky-700 group cursor-pointer transition-all duration-300 hover:shadow-[0_8px_20px_-6px_rgba(3,105,161,0.3)] hover:-translate-y-0.5">
                    <span class="font-label-sm text-xs font-extrabold pr-1">لينكدإن (LinkedIn)</span>
                    <div class="w-9 h-9 rounded-xl bg-sky-700 text-white flex items-center justify-center shadow-lg shadow-sky-700/20 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M22.23 0H1.77C.8 0 0 .77 0 1.72v20.56C0 23.23.8 24 1.77 24h20.46c.98 0 1.77-.77 1.77-1.72V1.72C24 .77 23.2 0 22.23 0zM7.12 20.45H3.56V9H7.12v11.45zM5.34 7.43c-1.14 0-2.06-.92-2.06-2.06 0-1.14.92-2.06 2.06-2.06 1.14 0 2.06.92 2.06 2.06 0 1.14-.92 2.06-2.06 2.06zm15.11 13.02h-3.56v-5.6c0-1.34-.03-3.05-1.86-3.05-1.86 0-2.14 1.45-2.14 2.95v5.7H9.33V9h3.42v1.56h.05c.48-.9 1.64-1.86 3.39-1.86 3.63 0 4.3 2.39 4.3 5.5v6.25z"/></svg>
                    </div>
                </a>
            </div>
            
            <!-- Copy Link Field -->
            <div class="relative w-full rounded-2xl bg-surface-container-low border border-primary/10 p-1.5 flex items-center justify-between gap-2">
                <input id="share-link-input" type="text" readonly class="flex-1 bg-transparent py-2.5 px-3 font-body-md text-xs text-on-surface-variant/90 focus:outline-none select-all text-left border-none" dir="ltr">
                <button id="copy-share-link-btn" class="px-5 py-2.5 bg-primary text-white hover:bg-primary-dark rounded-xl font-label-md text-xs transition-all duration-200 cursor-pointer border-none shadow-sm flex items-center gap-1.5 shrink-0">
                    <span class="material-symbols-outlined text-[16px]">content_copy</span>
                    <span>نسخ الرابط</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Guest Auth Prompt Modal (Popup) -->
    <div id="guest-auth-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop with high-end glassmorphism -->
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Content Container (Premium Glassmorphism Design) -->
        <div class="modal-container relative max-w-sm w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 text-center overflow-hidden">
            <!-- Ambient Glow Effects inside Modal -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-secondary/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Close Button -->
            <button id="close-guest-modal-btn" class="absolute top-5 left-5 text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            
            <!-- Icon -->
            <div class="w-16 h-16 rounded-3xl bg-primary/5 flex items-center justify-center text-primary border border-primary/10 mx-auto mb-4 mt-2">
                <span class="material-symbols-outlined text-[36px]" style="font-variation-settings: 'FILL' 1;">account_circle</span>
            </div>
            
            <h3 class="font-headline-lg text-lg font-extrabold text-primary mb-2 leading-tight">انضم لحكماء العالم الان</h3>
            <p class="font-body-md text-xs text-on-surface-variant/80 mb-6 leading-relaxed">يرجى تسجيل الدخول أو إنشاء حساب جديد لتتمكن من كتابة تعليقات، إرسال ردود، وتأييد حكم المبدعين والمفكرين في مجتمعنا.</p>
            
            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <a href="{{ route('user.login') }}" class="w-full py-3.5 bg-primary hover:bg-primary-dark text-white rounded-xl font-title-lg text-xs font-bold text-center transition-all shadow-md cursor-pointer block">
                    تسجيل الدخول
                </a>
                <a href="{{ route('user.login') }}?tab=signup" class="w-full py-3.5 border border-primary text-primary hover:bg-primary/5 rounded-xl font-title-lg text-xs font-bold text-center transition-all cursor-pointer block">
                    إنشاء حساب جديد
                </a>
            </div>
        </div>
    </div>

    <!-- Rank Details Modal (Popup) -->
    <div id="rank-details-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop with high-end glassmorphism -->
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Content Container -->
        <div class="modal-container relative max-w-md w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 overflow-hidden text-right">
            <!-- Ambient Glow Effects inside Modal -->
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Close Button -->
            <button id="close-rank-modal-btn" class="absolute top-5 left-5 text-on-surface-variant/75 hover:text-primary hover:bg-surface-container-high p-2 rounded-full transition-all duration-200 cursor-pointer bg-transparent border-none flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            
            <!-- Title -->
            <div class="text-center mb-5">
                <h3 class="font-headline-lg text-base font-extrabold text-primary flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[22px] text-amber-500">military_tech</span>
                    <span>تفاصيل رتبة المستخدم</span>
                </h3>
                <p class="font-body-md text-[10px] text-on-surface-variant/80 mt-1">مسيرة عطائك وتقدمك بالمنصة</p>
            </div>

            <!-- User Info & Current Rank -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/5 to-amber-500/10 border border-amber-500/20 mb-4 relative text-center">
                <div class="mb-2">
                    <span class="text-[10px] text-on-surface-variant/90 block mb-0.5">الرتبة الحالية للمستخدم:</span>
                    <strong id="rank-modal-user-name" class="text-primary text-xs block"></strong>
                </div>
                
                <!-- Rank Badge and Name -->
                <div class="flex flex-col items-center justify-center gap-1 mb-2">
                    <img id="rank-modal-img" src="" alt="Badge" class="w-14 h-14 object-contain filter drop-shadow-[0_4px_6px_rgba(201,162,37,0.3)] animate-pulse">
                    <span id="rank-modal-name" class="font-extrabold text-sm text-amber-600"></span>
                </div>

                <!-- Rank Description -->
                <p id="rank-modal-desc" class="text-[10px] text-on-surface-variant/90 leading-relaxed max-w-sm mx-auto mb-2.5"></p>

                <!-- Points info -->
                <div class="flex items-center justify-between text-[10px] font-bold text-primary px-2 border-t border-amber-500/10 pt-2.5">
                    <div>
                        <span>نقاط المستخدم:</span>
                        <span id="rank-modal-user-points" class="badge bg-primary text-white rounded px-2 py-0.5 ms-1"></span>
                    </div>
                    <div>
                        <span>نطاق الرتبة:</span>
                        <span id="rank-modal-range" class="text-on-surface-variant font-medium ms-1"></span>
                    </div>
                </div>
            </div>

            <!-- Next Rank Section (Motivational) -->
            <div id="rank-modal-next-section" class="border border-primary/10 rounded-2xl p-3.5 bg-surface-container-low/50">
                <h4 class="font-bold text-[11px] text-primary mb-2.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-amber-500">trending_up</span>
                    <span>الرتبة القادمة والطريق إليها</span>
                </h4>
                
                <!-- Progress bar -->
                <div class="mb-3">
                    <div class="flex justify-between text-[10px] font-bold mb-1 text-on-surface-variant">
                        <span id="rank-modal-progress-text"></span>
                        <span id="rank-modal-progress-percent"></span>
                    </div>
                    <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                        <div id="rank-modal-progress-bar" class="bg-gradient-to-l from-primary to-amber-500 h-full transition-all duration-1000" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Next Rank Details Card -->
                <div class="flex items-center gap-3 p-2.5 rounded-xl bg-surface border border-outline-variant">
                    <img id="rank-modal-next-img" src="" alt="Next Badge" class="w-8 h-8 object-contain shrink-0">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <strong id="rank-modal-next-name" class="text-[11px] text-primary block"></strong>
                            <span id="rank-modal-next-range" class="text-[9px] text-on-surface-variant font-semibold"></span>
                        </div>
                        <p id="rank-modal-next-desc" class="text-[9px] text-on-surface-variant/80 mt-0.5 leading-normal line-clamp-2"></p>
                    </div>
                </div>
                
                <!-- Motivational quote -->
                <p class="text-[9px] text-primary/80 font-bold text-center mt-2.5 mb-0">
                    💡 شارك بنشاط واطرح مواضيع مميزة وتفاعل مع الأعضاء لتكسب نقاط وتصل للرتبة التالية قريباً!
                </p>
            </div>
            
            <!-- Maximum Rank achieved screen fallback -->
            <div id="rank-modal-max-section" class="hidden text-center p-4 border border-amber-500/20 rounded-2xl bg-amber-500/5">
                <span class="material-symbols-outlined text-[30px] text-amber-500 mb-1">emoji_events</span>
                <h4 class="font-extrabold text-xs text-primary mb-1">لقد وصلت للرتبة القصوى! 🏆</h4>
                <p class="text-[10px] text-on-surface-variant/90 leading-relaxed mb-0">تهانينا الحارة! لقد حققت أعلى الرتب الممكنة في النظام بفضل تفاعلك وحكمتك العظيمة المستمرة بالمنصة.</p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        $(document).ready(function () {
            // Global translations for master dashboard
            const _tpDashboard = {
                savedStatus: {!! json_encode(__t('saved_status')) !!},
                saveAction: {!! json_encode(__t('save_action')) !!}
            };

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

            // Toggle Save/Unsave Post via AJAX
            $(document).on('click', '.toggle-save-post-btn', function(e) {
                e.preventDefault();
                @guest
                    if (typeof window.openGuestModal === 'function') {
                        window.openGuestModal();
                    }
                    return false;
                @endguest
                const btn = $(this);
                const postId = btn.attr('data-post-id');
                const isSaved = btn.attr('data-saved') === 'true';

                if (btn.hasClass('pointer-events-none')) return;
                btn.addClass('pointer-events-none');

                $.ajax({
                    url: `/posts/${postId}/save`,
                    type: "POST",
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            if (typeof toastr !== "undefined") {
                                toastr.success(response.message);
                            } else {
                                alert(response.message);
                            }

                            const nowSaved = response.action === 'saved';
                            btn.attr('data-saved', nowSaved ? 'true' : 'false');
                            
                            // Target all buttons with this post ID to sync UI across the page
                            $(`.toggle-save-post-btn[data-post-id="${postId}"]`).each(function() {
                                const self = $(this);
                                self.attr('data-saved', nowSaved ? 'true' : 'false');
                                if (nowSaved) {
                                    self.addClass('text-primary');
                                    self.find('.material-symbols-outlined').addClass('fill-1').text('bookmark');
                                    self.find('.save-text').text(_tpDashboard.savedStatus);
                                } else {
                                    self.removeClass('text-primary');
                                    self.find('.material-symbols-outlined').removeClass('fill-1').text('bookmark_border');
                                    self.find('.save-text').text(_tpDashboard.saveAction);
                                }
                            });

                            // If we are on the saved posts page and unsaved, we might want to slide/fade out the post card.
                            if (window.location.pathname.includes('/saved-posts') && !nowSaved) {
                                btn.closest('article').slideUp(300, function() {
                                    $(this).remove();
                                    if ($('article').length === 0) {
                                        location.reload(); // show empty state
                                    }
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        let msg = 'حدث خطأ أثناء حفظ الموضوع.';
                        if (xhr.status === 401) {
                            msg = 'يجب تسجيل الدخول لحفظ المواضيع.';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        if (typeof toastr !== "undefined") {
                            toastr.error(msg);
                        } else {
                            alert(msg);
                        }
                    },
                    complete: function() {
                        btn.removeClass('pointer-events-none');
                    }
                });
            });

            // Open Share Post Modal or trigger Native Share
            $(document).on('click', '.share-post-btn', function(e) {
                e.preventDefault();
                const btn = $(this);
                const postId = btn.attr('data-post-id');
                const rawContentAttr = btn.attr('data-post-content') || '';
                const postContent = decodeURIComponent(rawContentAttr.replace(/\+/g, '%20'));
                const shareUrl = window.location.origin + '/post/' + postId;
                const snippet = postContent ? (postContent.substring(0, 100) + (postContent.length > 100 ? '...' : '')) : '';
                
                const textHeader = snippet ? `اقرأ هذا الموضوع الشيق على حكماء العالم:\n"${snippet}"` : 'اقرأ هذا الموضوع الشيق على حكماء العالم';
                const fullShareText = `${textHeader}\n${shareUrl}`;

                // Open custom premium share modal (bypassing native navigator.share to prevent browser permission popups)
                $('#share-link-input').val(shareUrl);
                $('#share-modal-preview-text').text(postContent ? postContent.substring(0, 150) + (postContent.length > 150 ? '...' : '') : 'موضوع مميز على حكماء العالم...');
                
                // Set href links for social share buttons
                $('#share-whatsapp').attr('href', `https://api.whatsapp.com/send?text=${encodeURIComponent(fullShareText)}`);
                $('#share-facebook').attr('href', `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`);
                $('#share-twitter').attr('href', `https://twitter.com/intent/tweet?text=${encodeURIComponent(fullShareText)}`);
                $('#share-linkedin').attr('href', `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`);

                // Show Modal
                const modal = $('#share-post-modal');
                modal.removeClass('hidden').addClass('flex');
                $('body').addClass('modal-active');
                setTimeout(() => {
                    modal.addClass('modal-show');
                }, 20);
            });

            // Close Share Modal Events
            function closeShareModal() {
                const modal = $('#share-post-modal');
                modal.removeClass('modal-show');
                $('body').removeClass('modal-active');
                setTimeout(() => {
                    modal.addClass('hidden').removeClass('flex');
                }, 300);
            }

            $(document).on('click', '#close-share-modal-btn, #share-post-modal .modal-backdrop', function() {
                closeShareModal();
            });

            // Copy Link Button handler
            $(document).on('click', '#copy-share-link-btn', function() {
                const linkInput = $('#share-link-input');
                linkInput.select();
                document.execCommand('copy');

                // Visual feedback on button
                const btn = $(this);
                const originalHtml = btn.html();
                btn.text('تم النسخ!').addClass('bg-secondary').removeClass('bg-primary');
                
                if (typeof toastr !== "undefined") {
                    toastr.success('تم نسخ رابط الموضوع إلى الحافظة بنجاح.');
                }

                setTimeout(() => {
                    btn.html(originalHtml).removeClass('bg-secondary').addClass('bg-primary');
                }, 2000);
            });

            // Guest Modal Helper Functions
            window.openGuestModal = function() {
                const modal = $('#guest-auth-modal');
                if (modal.length > 0) {
                    modal.removeClass('hidden').addClass('flex');
                    $('body').addClass('modal-active');
                    setTimeout(() => {
                        modal.addClass('modal-show');
                    }, 20);
                } else {
                    alert('يرجى تسجيل الدخول أو إنشاء حساب جديد لتتمكن من المشاركة في الموقع.');
                }
            };

            window.closeGuestModal = function() {
                const modal = $('#guest-auth-modal');
                if (modal.length > 0) {
                    modal.removeClass('modal-show');
                    $('body').removeClass('modal-active');
                    setTimeout(() => {
                        modal.addClass('hidden').removeClass('flex');
                    }, 300);
                }
            };

            $(document).on('click', '#close-guest-modal-btn, #guest-auth-modal .modal-backdrop', function() {
                window.closeGuestModal();
            });

            // Automatically trigger Comments Modal if post_id query parameter is present (except on single post details pages)
            const urlParams = new URLSearchParams(window.location.search);
            const postIdParam = urlParams.get('post_id');
            if (postIdParam && !window.location.pathname.includes('/post/')) {
                setTimeout(() => {
                    const btn = $(`.open-discussion-btn[data-post-id="${postIdParam}"]`);
                    if (btn.length > 0) {
                        // Scroll to the post smoothly
                        $('html, body').animate({
                            scrollTop: btn.closest('article').offset().top - 100
                        }, 500);
                        btn.click();
                    } else {
                        // Fallback trigger comments modal directly
                        if (typeof renderComments === "function") {
                            activePostId = postIdParam;
                            $('#modal-post-author').text('موضوع حكماء العالم');
                            $('#modal-post-title').text('نقاشات وحكم');
                            $('#modal-post-snippet').text('يرجى مراجعة التعليقات والنقاشات المطروحة أدناه لهذا الموضوع.');

                            renderComments(activePostId);

                            $('#comments-modal').removeClass('hidden').addClass('flex');
                            $('body').addClass('modal-active');
                            setTimeout(() => {
                                $('#comments-modal').addClass('modal-show');
                            }, 20);
                        }
                    }
                }, 800);
            }

            // ميزة عرض المزيد / عرض أقل الاحترافية للمنشورات والمواضيع
            function initShowMoreButtons() {
                $('.post-text-content').each(function() {
                    const $content = $(this);
                    const $wrapper = $content.closest('.post-text-container');
                    
                    if ($wrapper.data('show-more-initialized')) return;
                    
                    // التحقق مما إذا كان النص يتجاوز الـ 4 أسطر المسموحة
                    if ($content[0].scrollHeight > $content[0].clientHeight) {
                        $wrapper.find('.show-more-btn').removeClass('hidden');
                        $wrapper.data('show-more-initialized', true);
                    }
                });
            }

            // معالجة النقر على زر التبديل
            $(document).on('click', '.show-more-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const $content = $btn.siblings('.post-text-content');
                
                if ($content.hasClass('line-clamp-4')) {
                    $content.removeClass('line-clamp-4');
                    $btn.text('عرض أقل');
                } else {
                    $content.addClass('line-clamp-4');
                    $btn.text('عرض المزيد');
                    
                    // تمرير الشاشة بلطف لأعلى المنشور لعدم فقدان السياق
                    $('html, body').animate({
                        scrollTop: $content.offset().top - 100
                    }, 200);
                }
            });

            // تشغيل الفحص
            initShowMoreButtons();
            $(window).on('resize', initShowMoreButtons);
            $(document).ajaxComplete(function() {
                // تأخير بسيط للتأكد من رندرة العناصر بعد طلب الـ AJAX
                setTimeout(initShowMoreButtons, 50);
            });

            // ميزة الصعود لأعلى الصفحة (Scroll to Top)
            $(window).on('scroll', function() {
                const btn = $('#scrollToTopBtn');
                if ($(window).scrollTop() > 300) {
                    btn.removeClass('opacity-0 translate-y-4 pointer-events-none')
                       .addClass('opacity-100 translate-y-0 pointer-events-auto');
                } else {
                    btn.addClass('opacity-0 translate-y-4 pointer-events-none')
                       .removeClass('opacity-100 translate-y-0 pointer-events-auto');
                }
            });

            $('#scrollToTopBtn').on('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // ميزة عرض سجل تفاصيل نقاط العضو في نافذة منبثقة (Popup)
            function openPointsModal(userId) {
                const modal = $('#points-details-modal');
                const logsList = $('#points-modal-logs-list');
                const userName = $('#points-modal-user-name');
                const totalBadge = $('#points-modal-total-badge');

                // تهيئة الشاشة الافتراضية للتحميل
                userName.text('جاري التحميل...');
                totalBadge.text('...');
                logsList.html(`
                    <div class="flex flex-col items-center justify-center py-12 space-y-3">
                        <div class="animate-spin rounded-full h-9 w-9 border-b-2 border-primary"></div>
                        <p class="text-xs text-on-surface-variant font-bold">جاري جلب تفاصيل النقاط...</p>
                    </div>
                `);

                // إظهار المودال بالانيميشن
                modal.removeClass('hidden').addClass('flex');
                setTimeout(() => {
                    modal.addClass('opacity-100');
                    modal.find('.modal-content-card').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
                }, 10);

                // استدعاء البيانات من السيرفر
                $.ajax({
                    url: "/profile/points-details/" + userId,
                    type: "GET",
                    success: function(response) {
                        if (response.success) {
                            userName.text('سجل تقييمات العضو: ' + response.user_name);
                            totalBadge.text(response.total_points + ' نقطة');
                            logsList.empty();

                            if (response.logs.length === 0) {
                                logsList.append(`
                                    <div class="flex flex-col items-center justify-center py-12 text-center">
                                        <span class="material-symbols-outlined text-[54px] text-on-surface-variant opacity-30 mb-3">military_tech</span>
                                        <h4 class="font-headline-lg text-sm font-bold text-primary">لا توجد نقاط بعد</h4>
                                        <p class="text-xs text-on-surface-variant mt-1.5 max-w-xs leading-relaxed">هذا العضو لم يتم منحه أي نقاط تقييم من لجنة الحكماء حتى الآن.</p>
                                    </div>
                                `);
                                return;
                            }

                            response.logs.forEach(function(log) {
                                let postHtml = '';
                                if (log.post_snippet) {
                                    postHtml = `
                                        <div class="bg-surface-container-low/50 p-3 rounded-lg border border-primary/5 hover:border-primary/20 transition-all text-right mt-2">
                                            <span class="text-[10px] text-secondary font-bold block mb-1">الموضوع المستحق للنقاش:</span>
                                            <a href="${log.post_url}" class="text-xs font-bold text-primary hover:underline line-clamp-2 leading-relaxed block">${log.post_snippet}</a>
                                        </div>
                                    `;
                                }

                                logsList.append(`
                                    <div class="bg-surface-container-lowest p-4 rounded-xl border border-primary/10 space-y-3 relative hover:shadow-sm transition-all duration-300 text-right">
                                        <!-- Top bar: Points & Wise Info -->
                                        <div class="flex justify-between items-center">
                                            <div class="flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-secondary text-[16px]">gavel</span>
                                                <span class="text-xs font-bold text-on-surface">الحكيم: ${log.wise_name}</span>
                                            </div>
                                            <span class="bg-primary/10 text-primary border border-primary/20 text-[11px] font-extrabold px-2.5 py-0.5 rounded-full">
                                                +${log.points_given} نقطة
                                            </span>
                                        </div>
                                        
                                        <!-- Note Section -->
                                        <div class="bg-surface p-3 rounded-lg border border-outline-variant/30 text-xs text-on-surface-variant leading-relaxed">
                                            <span class="text-[10px] text-outline font-bold block mb-1">ملاحظة الحكيم:</span>
                                            <p class="italic">"${log.note}"</p>
                                        </div>
                                        
                                        <!-- Post snippet section -->
                                        ${postHtml}
                                        
                                        <!-- Date -->
                                        <div class="text-[10px] text-outline text-left">
                                            <span>${log.diff}</span>
                                        </div>
                                    </div>
                                `);
                            });
                        } else {
                            logsList.html('<p class="text-xs text-error text-center py-4">فشل في تحميل البيانات</p>');
                        }
                    },
                    error: function() {
                        logsList.html('<p class="text-xs text-error text-center py-4">حدث خطأ أثناء تحميل البيانات</p>');
                    }
                });
            }

            function closePointsModal() {
                const modal = $('#points-details-modal');
                modal.removeClass('opacity-100');
                modal.find('.modal-content-card').addClass('scale-95 opacity-0').removeClass('scale-100 opacity-100');
                setTimeout(() => {
                    modal.addClass('hidden').removeClass('flex');
                }, 300);
            }

            // مستمعات الأحداث للنقاط
            $(document).on('click', '.user-points-trigger', function(e) {
                e.preventDefault();
                const userId = $(this).attr('data-user-id');
                openPointsModal(userId);
            });

            $(document).on('click', '#close-points-modal-btn, #points-details-modal .modal-backdrop', function() {
                closePointsModal();
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && !$('#points-details-modal').hasClass('hidden')) {
                    closePointsModal();
                }
                if (e.key === 'Escape' && !$('#delete-post-modal').hasClass('hidden')) {
                    closeDeletePostModal();
                }
            });

            // --- Delete Post Handler (Custom Alert Modal) ---
            const deletePostModal = $('#delete-post-modal');
            let postIdToDelete = null;
            
            $(document).on('click', '.delete-post-btn', function() {
                postIdToDelete = $(this).attr('data-post-id');
                
                deletePostModal.removeClass('hidden').addClass('flex');
                setTimeout(() => {
                    deletePostModal.addClass('modal-show');
                }, 10);
            });
            
            function closeDeletePostModal() {
                deletePostModal.removeClass('modal-show');
                setTimeout(() => {
                    deletePostModal.removeClass('flex').addClass('hidden');
                    postIdToDelete = null;
                    $('#confirm-delete-post-btn').prop('disabled', false);
                }, 300);
            }
            
            $(document).on('click', '#cancel-delete-post-btn, #delete-post-modal .modal-backdrop', function() {
                closeDeletePostModal();
            });
            
            $(document).on('click', '#confirm-delete-post-btn', function() {
                if (!postIdToDelete) return;
                const btn = $(this);
                btn.prop('disabled', true);
                
                $.ajax({
                    url: `/posts/${postIdToDelete}/delete`,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            closeDeletePostModal();
                            // Fade out the post element in UI on deletion
                            $(`article:has(.delete-post-btn[data-post-id="${postIdToDelete}"])`).fadeOut(400, function() {
                                $(this).remove();
                            });
                            // Or in post details page, redirect to home page
                            if (window.location.pathname.includes('/posts/')) {
                                setTimeout(() => {
                                    window.location.href = '/';
                                }, 1000);
                            }
                        } else {
                            toastr.error(res.message);
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        toastr.error('حدث خطأ أثناء محاولة حذف المنشور.');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
    
    <!-- Scroll to Top Button -->
    <button id="scrollToTopBtn" class="fixed bottom-6 left-6 z-50 flex items-center justify-center w-11 h-11 bg-primary text-white rounded-full shadow-lg border border-white/10 opacity-0 translate-y-4 pointer-events-none transition-all duration-300 hover:bg-primary-container hover:-translate-y-1 hover:shadow-xl active:scale-95">
        <span class="material-symbols-outlined text-[24px]">arrow_upward</span>
    </button>

    <!-- Points Details Modal -->
    <div id="points-details-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <div class="modal-backdrop absolute inset-0"></div>
        <div class="modal-content-card bg-surface-container-low rounded-2xl max-w-lg w-full mx-4 overflow-hidden shadow-2xl border border-primary/10 transform scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[80vh] relative z-10">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary to-primary/90 p-5 text-white flex justify-between items-center shrink-0 text-right">
                <div>
                    <h3 class="font-headline-lg text-base font-bold text-white" style="color: #ffffff !important;" id="points-modal-user-name">{{ __t('points_logs_title') }}</h3>
                    <p class="text-[10px] text-white/80 mt-0.5">{{ __t('points_logs_sub') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-bold" id="points-modal-total-badge">0 {{ __t('points_label') }}</span>
                    <button type="button" id="close-points-modal-btn" class="text-white/80 hover:text-white hover:bg-white/10 p-1.5 rounded-full transition-all">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            </div>
            
            <!-- Scrollable content -->
            <div class="p-6 overflow-y-auto space-y-4 flex-grow text-right bg-surface-container-low" id="points-modal-logs-list">
                <!-- Dynamic Content Load -->
            </div>
        </div>
    </div>

    <!-- Delete Post Confirmation Modal -->
    <div id="delete-post-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <!-- Backdrop with high-end glassmorphism -->
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Content Container -->
        <div class="modal-container relative max-w-sm w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 text-center overflow-hidden" style="direction: rtl;">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4 mx-auto">
                <span class="material-symbols-outlined text-[26px]">warning</span>
            </div>
            
            <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('delete_discussion_post') }}</h3>
            <p class="text-xs text-on-surface-variant leading-relaxed mb-6">{{ __t('confirm_delete_post_msg') }}</p>
            
            <div class="flex gap-3 w-full">
                <button type="button" id="confirm-delete-post-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">{{ __t('confirm_delete') }}</button>
                <button type="button" id="cancel-delete-post-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
            </div>
        </div>
    </div>

    <!-- Reusable Action Delete Confirmation Modal (Comments, Replies, etc.) -->
    <div id="delete-action-modal" class="fixed inset-0 z-[110] hidden items-center justify-center p-4">
        <!-- Backdrop with high-end glassmorphism -->
        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 backdrop-blur-md opacity-0 transition-opacity duration-300"></div>
        
        <!-- Modal Content Container -->
        <div class="modal-container relative max-w-sm w-full bg-white/95 backdrop-blur-xl rounded-3xl border border-primary/10 shadow-[0_25px_50px_-12px_rgba(0,58,35,0.25)] p-6 z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 text-center overflow-hidden" style="direction: rtl;">
            <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4 mx-auto">
                <span class="material-symbols-outlined text-[26px]">warning</span>
            </div>
            
            <h3 class="font-headline-md text-base font-bold text-primary mb-2" id="delete-action-modal-title">حذف التعليق</h3>
            <p class="text-xs text-on-surface-variant leading-relaxed mb-6" id="delete-action-modal-msg">هل أنت متأكد من رغبتك في حذف هذا التعليق نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.</p>
            
            <div class="flex gap-3 w-full">
                <button type="button" id="confirm-delete-action-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">تأكيد الحذف</button>
                <button type="button" id="cancel-delete-action-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">إلغاء</button>
            </div>
        </div>
    </div>

    <script>
        // Global Reusable Delete Action Modal Handlers
        let onConfirmDeleteActionCallback = null;

        window.openDeleteActionModal = function(title, message, callback) {
            onConfirmDeleteActionCallback = callback;
            if (title) $('#delete-action-modal-title').text(title);
            if (message) $('#delete-action-modal-msg').text(message);
            $('#confirm-delete-action-btn').prop('disabled', false).text('تأكيد الحذف');

            const modal = $('#delete-action-modal');
            modal.removeClass('hidden').addClass('flex');
            $('body').addClass('modal-active');
            setTimeout(() => {
                modal.addClass('modal-show');
            }, 10);
        };

        window.closeDeleteActionModal = function() {
            const modal = $('#delete-action-modal');
            modal.removeClass('modal-show');
            setTimeout(() => {
                modal.removeClass('flex').addClass('hidden');
                if ($('#comments-modal.modal-show').length === 0 && $('#supporters-modal.modal-show').length === 0 && $('#delete-post-modal.modal-show').length === 0) {
                    $('body').removeClass('modal-active');
                }
                onConfirmDeleteActionCallback = null;
            }, 300);
        };

        $(document).on('click', '#cancel-delete-action-btn, #delete-action-modal .modal-backdrop', function() {
            window.closeDeleteActionModal();
        });

        $(document).on('click', '#confirm-delete-action-btn', function() {
            if (typeof onConfirmDeleteActionCallback === 'function') {
                const btn = $(this);
                btn.prop('disabled', true).text('جاري الحذف...');
                onConfirmDeleteActionCallback(function() {
                    window.closeDeleteActionModal();
                }, function() {
                    btn.prop('disabled', false).text('تأكيد الحذف');
                });
            }
        });

        // Global Handle Sidebar / Mobile Friend Request Actions (Accept / Reject) via AJAX
        $(document).on('click', '.accept-friendship-sidebar-btn, .reject-friendship-sidebar-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const url = btn.attr('href');
            const row = btn.closest('.friend-request-sidebar-row');
            const container = row.length ? row.parent() : null;

            if (btn.hasClass('pointer-events-none')) return;
            btn.addClass('pointer-events-none');

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (typeof toastr !== "undefined") {
                            toastr.success(response.message);
                        }
                        
                        if (row.length) {
                            row.addClass('scale-95 opacity-0 transition-all duration-300');
                            setTimeout(() => {
                                row.remove();
                                if (container && container.find('.friend-request-sidebar-row').length === 0) {
                                    $('#explore-more-sidebar-container').remove();
                                    container.html('<p class="text-xs text-on-surface-variant text-center py-2">{{ __t("no_pending_friend_requests") }}</p>');
                                }
                            }, 300);
                        }
                    } else {
                        if (typeof toastr !== "undefined") {
                            toastr.error(response.message || '{{ __t("action_failed") }}');
                        }
                        btn.removeClass('pointer-events-none');
                    }
                },
                error: function(xhr) {
                    let msg = '{{ __t("action_error") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (typeof toastr !== "undefined") {
                        toastr.error(msg);
                    }
                    btn.removeClass('pointer-events-none');
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
