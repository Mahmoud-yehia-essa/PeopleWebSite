<!doctype html>
<html lang="ar" dir="rtl" class="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>ترقبوا صحيفة حماك الإلكترونية بحلتها الجديدة</title>
  
  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
  
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: '#0ea5e9',     /* Sky Blue */
            secondary: '#1e3a8a',   /* Royal Blue */
            accent: '#fbbf24',      /* Amber / Gold */
            darkBg: '#090d16',      /* Deep Midnight Blue */
            darkCard: '#111827',    /* Dark Card */
          },
          fontFamily: {
            display: ["'Noto Kufi Arabic'", "sans-serif"],
            sans: ["'Noto Kufi Arabic'", "sans-serif"],
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Noto Kufi Arabic', sans-serif;
    }
    .glow-bg {
      background: radial-gradient(circle at 50% 50%, rgba(14, 165, 233, 0.15) 0%, rgba(30, 58, 138, 0.05) 50%, transparent 100%);
    }
    .glass-panel {
      background: rgba(17, 24, 39, 0.7);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .glass-card {
      background: rgba(255, 255, 255, 0.02);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.05);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-card:hover {
      background: rgba(255, 255, 255, 0.05);
      border-color: rgba(14, 165, 233, 0.3);
      transform: translateY(-4px);
      box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.1), 0 8px 10px -6px rgba(14, 165, 233, 0.1);
    }
    .animate-pulse-slow {
      animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    .text-glow {
      text-shadow: 0 0 20px rgba(14, 165, 233, 0.5);
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: .85; transform: scale(1.02); }
    }
    
    /* Scrollbar style */
    ::-webkit-scrollbar {
      width: 6px;
    }
    ::-webkit-scrollbar-track {
      background: #090d16;
    }
    ::-webkit-scrollbar-thumb {
      background: #1e293b;
      border-radius: 3px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #0ea5e9;
    }
  </style>
</head>
<body class="bg-darkBg text-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 relative overflow-x-hidden selection:bg-primary selection:text-white">
  
  <!-- Glowing Background Elements -->
  <div class="absolute top-[-10%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
  <div class="absolute bottom-[-10%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-secondary/25 blur-[150px] pointer-events-none"></div>
  
  <div class="w-full max-w-5xl mx-auto z-10 my-8">
    <!-- Main Premium Glass Container -->
    <div class="glass-panel rounded-3xl p-6 sm:p-10 md:p-12 shadow-2xl relative overflow-hidden">
      <!-- Glow effect inside card -->
      <div class="absolute inset-0 glow-bg pointer-events-none"></div>

      <!-- Top Brand Header -->
      <div class="flex flex-col items-center justify-center mb-8 relative z-10">
        <a href="{{ url('/') }}" class="relative group mb-4 block">
          <!-- Logo Outer Glow -->
          <div class="absolute -inset-1 bg-gradient-to-r from-primary to-secondary rounded-full blur opacity-40 group-hover:opacity-75 transition duration-1000 group-hover:duration-200 animate-pulse-slow"></div>
          <div class="relative bg-darkBg border border-slate-800 p-4 rounded-full flex items-center justify-center w-24 h-24 sm:w-28 sm:h-28 shadow-xl">
            <img src="{{ asset('backend/assets/images/logo-hmak.png') }}" alt="شعار صحيفة حماك" class="w-full h-full object-contain">
          </div>
        </a>
        <div class="text-center">
          <span class="px-3 py-1 rounded-full bg-primary/10 text-primary border border-primary/20 text-xs font-bold tracking-wider mb-2 inline-block">
            إطلاق النسخة المطورة
          </span>
          <h2 class="text-xl font-bold text-slate-350 tracking-wide">صحيفة حماك الإلكترونية</h2>
        </div>
      </div>

      <!-- Main Headline Announcement -->
      <div class="text-center max-w-3xl mx-auto mb-10 relative z-10">
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight mb-6">
          ترقبوا 
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary via-sky-400 to-amber-400 text-glow">
            صحيفة حماك الإلكترونية
          </span>
          <br class="hidden sm:inline"> في شكلها الجديد
        </h1>
      </div>



      <!-- Feature Teasers / Badges -->
      <div class="mb-14 relative z-10">
        <h3 class="text-center font-bold text-lg text-slate-300 mb-6 border-b border-slate-800 pb-3 max-w-xs mx-auto">ماذا ينتظركم في الحلّة الجديدة؟</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Card 1 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">newspaper</span>
            </div>
            <h4 class="font-bold text-white mb-2">تغطية إخبارية شاملة</h4>
            <p class="text-xs text-slate-450 leading-relaxed">تغطية مستمرة ومحايدة للأخبار المحلية، العربية والدولية أولاً بأول.</p>
          </div>
          <!-- Card 2 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-450 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">campaign</span>
            </div>
            <h4 class="font-bold text-white mb-2">أنت عين الخبر</h4>
            <p class="text-xs text-slate-450 leading-relaxed">كن جزءاً من صناعة الخبر وشاركنا الحدث بالصور والفيديوهات من موقعك مباشرة.</p>
          </div>
          <!-- Card 3 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">volunteer_activism</span>
            </div>
            <h4 class="font-bold text-white mb-2">حماك الخير</h4>
            <p class="text-xs text-slate-450 leading-relaxed">مبادرات إنسانية واجتماعية تفاعلية لمد يد العون وتقديم المساعدات للمستحقين.</p>
          </div>
          <!-- Card 4 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">mic</span>
            </div>
            <h4 class="font-bold text-white mb-2">مكتبة حماك الصوتية</h4>
            <p class="text-xs text-slate-450 leading-relaxed">استمع إلى التقارير الإخبارية، البث المباشر، البرامج الإذاعية المختلفة، البودكاست الحصري، وتصفح أعمال المؤلفين بجودة صوت ممتازة.</p>
          </div>
          <!-- Card 5 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-fuchsia-500/10 text-fuchsia-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">quiz</span>
            </div>
            <h4 class="font-bold text-white mb-2">الأسئلة والمسابقات التفاعلية</h4>
            <p class="text-xs text-slate-450 leading-relaxed">اختبر معلوماتك وشارك في أسئلة متنوعة في مختلف المجالات الثقافية والعلمية مع تحديات تفاعلية متجددة تزيد من حصيلتك المعرفية.</p>
          </div>
          <!-- Card 6 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-amber-400/10 text-amber-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">storefront</span>
            </div>
            <h4 class="font-bold text-white mb-2">سوق حماك المطور</h4>
            <p class="text-xs text-slate-450 leading-relaxed">سوق إلكتروني متكامل لبيع منتجاتك مجاناً؛ ارفع منتجك الآن وانشره بين أصدقائك وتواصل مباشرة مع عملائك بسهولة وأمان.</p>
          </div>
          <!-- Card 7 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-indigo-400/10 text-indigo-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">forum</span>
            </div>
            <h4 class="font-bold text-white mb-2">مجموعات نقاشية</h4>
            <p class="text-xs text-slate-450 leading-relaxed">مساحات نقاش تفاعلية تجمعكم لتبادل الآراء ومناقشة الموضوعات الهامة.</p>
          </div>
          <!-- Card 8 -->
          <div class="glass-card rounded-2xl p-5 text-right flex flex-col justify-start">
            <div class="w-10 h-10 rounded-xl bg-teal-400/10 text-teal-400 flex items-center justify-center mb-3">
              <span class="material-symbols-outlined">devices</span>
            </div>
            <h4 class="font-bold text-white mb-2">تجاوب مع كل الأجهزة وكل الأحجام</h4>
            <p class="text-xs text-slate-450 leading-relaxed">تصفح متجاوب وسريع بالكامل يدعم مختلف الأنظمة والأجهزة الذكية.</p>
          </div>
        </div>
      </div>

      <!-- Contact / Subscription Form -->
      <div class="max-w-2xl mx-auto mb-12 relative z-10 glass-card rounded-2xl p-6 sm:p-8 border-slate-800">
        <div class="text-center mb-6">
          <h3 class="text-lg font-bold text-white mb-1">كن أول من يعلم عند الإطلاق</h3>
          <p class="text-xs text-slate-450">سجل بياناتك للحصول على دعوة حصرية فور تدشين الموقع وتفعيل الخدمات.</p>
        </div>

        <form id="notify-form" action="{{ route('add.contactus.store') }}" method="POST" class="space-y-4">
          @csrf
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label for="name" class="block text-xs font-bold text-slate-350 mb-1.5">الاسم الكريم <span class="text-red-500">*</span></label>
              <input type="text" name="name" id="name" required placeholder="أدخل اسمك الكامل" 
                     class="w-full bg-slate-950/80 border border-slate-850 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-300">
            </div>
            <div>
              <label for="email" class="block text-xs font-bold text-slate-350 mb-1.5">البريد الإلكتروني <span class="text-red-500">*</span></label>
              <input type="email" name="email" id="email" required placeholder="example@domain.com" 
                     class="w-full bg-slate-950/80 border border-slate-850 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-300 text-left" dir="ltr">
            </div>
          </div>
          
          <div>
            <label for="phone" class="block text-xs font-bold text-slate-350 mb-1.5">رقم الهاتف للتواصل <span class="text-red-500">*</span></label>
            <input type="number" name="phone" id="phone" required placeholder="مثال: 96512345678" 
                   class="w-full bg-slate-950/80 border border-slate-850 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-300 text-left" dir="ltr">
          </div>

          <div>
            <label for="message" class="block text-xs font-bold text-slate-350 mb-1.5">رسالة أو مقترح (اختياري)</label>
            <textarea name="message" id="message" rows="3" required
                      class="w-full bg-slate-950/80 border border-slate-850 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-300">أود التسجيل وتلقي إشعارات التحديث فور إطلاق النسخة الجديدة والمطورة لصحيفة حماك الإلكترونية.</textarea>
          </div>

          {{-- Honeypot field --}}
          <div style="position: absolute; left: -9999px; top: -9999px; height: 0; width: 0; overflow: hidden;">
            <label for="website_url">يرجى ترك هذا الحقل فارغاً</label>
            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off" value="" />
          </div>

          {{-- Math Captcha --}}
          <div>
            <label for="captcha_answer" class="block text-xs font-bold text-slate-350 mb-1.5">التحقق البشري: كم حاصل جمع {{ $num1 }} + {{ $num2 }}؟ <span class="text-red-500">*</span></label>
            <input type="number" name="captcha_answer" id="captcha_answer" required placeholder="أدخل ناتج الجمع" 
                   class="w-full bg-slate-950/80 border border-slate-850 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all duration-300 text-left" dir="ltr">
          </div>

          <button type="submit" id="submit-btn" class="w-full py-3 px-6 rounded-xl bg-gradient-to-l from-primary to-sky-600 hover:from-sky-600 hover:to-primary text-white font-bold text-sm transition-all duration-300 shadow-lg shadow-primary/20 hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-lg">send</span>
            <span>سجلني الآن لتلقي الإشعارات</span>
          </button>
        </form>

        <!-- Success/Error Feedback Messages (AJAX-driven) -->
        <div id="form-feedback" class="hidden mt-4 p-4 rounded-xl text-center text-sm font-bold animate-pulse-slow">
        </div>
      </div>

      <!-- App Store Badges -->
      <div class="flex flex-col items-center justify-center gap-4 relative z-10 mb-8">
        <p class="text-xs text-slate-450 font-bold">تطبيقات صحيفة حماك قريباً على المتاجر</p>
        <div class="flex gap-4 flex-wrap justify-center">
          <a href="#" class="inline-flex items-center gap-3 bg-slate-950 hover:bg-slate-900 border border-slate-850 px-4 py-2 rounded-xl transition-all duration-300 group shadow-md shadow-black/30">
            <span class="material-symbols-outlined text-2xl text-slate-400 group-hover:text-primary transition-colors">phone_iphone</span>
            <div class="text-right">
              <p class="text-[9px] text-slate-500">تحميل من متجر</p>
              <p class="text-xs font-extrabold text-white">App Store</p>
            </div>
          </a>
          <a href="#" class="inline-flex items-center gap-3 bg-slate-950 hover:bg-slate-900 border border-slate-850 px-4 py-2 rounded-xl transition-all duration-300 group shadow-md shadow-black/30">
            <span class="material-symbols-outlined text-2xl text-slate-400 group-hover:text-amber-400 transition-colors">play_arrow</span>
            <div class="text-right">
              <p class="text-[9px] text-slate-500">تحميل من متجر</p>
              <p class="text-xs font-extrabold text-white">Google Play</p>
            </div>
          </a>
        </div>
      </div>

      <!-- Dynamic Social Media Links -->
      @php
          $socialMedia = \App\Models\SocialMedia::latest()->get();
      @endphp
      @if($socialMedia->count() > 0)
        <div class="flex items-center justify-center gap-4 relative z-10 pt-6 border-t border-slate-850/60 max-w-sm mx-auto">
          @foreach($socialMedia as $item)
            <a href="{{ $item->link }}" target="_blank" title="{{ $item->title }}" class="w-10 h-10 rounded-full bg-slate-950 hover:bg-primary border border-slate-850 hover:border-primary flex items-center justify-center transition-all duration-300 hover:scale-110 shadow-md group">
              <img src="{{ asset($item->photo) }}" alt="{{ $item->title }}" class="w-5 h-5 object-contain brightness-0 invert group-hover:scale-105" style="filter: brightness(0) invert(1);">
            </a>
          @endforeach
        </div>
      @endif

      <!-- Footer Copy -->
      <div class="text-center mt-8 pt-6 border-t border-slate-850/30 relative z-10 text-[11px] text-slate-500">
        <p>&copy; {{ date('Y') }} صحيفة حماك الإلكترونية. جميع الحقوق محفوظة.</p>
        <p class="mt-1 text-[10px] text-slate-600">تصميم وتطوير المقام ميديا للدعاية والتسويق</p>
      </div>

    </div>
  </div>

  <!-- Javascript for Timer & AJAX Contact Submission -->
  <script>
    // 2. AJAX Submission for Contact / Subscription Form
    const notifyForm = document.getElementById("notify-form");
    const submitBtn = document.getElementById("submit-btn");
    const formFeedback = document.getElementById("form-feedback");

    if (notifyForm) {
      notifyForm.addEventListener("submit", function(e) {
        e.preventDefault();

        // UI Feedback: disable button, loading state
        submitBtn.disabled = true;
        const originalBtnText = submitBtn.innerHTML;
        submitBtn.innerHTML = `
          <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
          <span>جاري إرسال طلبك...</span>
        `;

        // Gather form data
        const formData = new FormData(notifyForm);
        const actionUrl = notifyForm.getAttribute("action");

        // Submit via fetch (as AJAX request)
        fetch(actionUrl, {
          method: "POST",
          headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Accept": "application/json"
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) {
            return response.json().then(err => { throw err; });
          }
          return response.json();
        })
        .then(data => {
          showFeedback(true, data.message || "تم تسجيل بياناتك بنجاح! سنقوم بإشعارك فور إطلاق النسخة الجديدة.");
          notifyForm.reset();
        })
        .catch(error => {
          console.error("Submission error:", error);
          let errMsg = "حدث خطأ أثناء إرسال طلبك. يرجى المحاولة مرة أخرى.";
          if (error && error.errors) {
            const firstKey = Object.keys(error.errors)[0];
            errMsg = error.errors[firstKey][0];
          } else if (error && error.message) {
            errMsg = error.message;
          }
          showFeedback(false, errMsg);
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
        });
      });
    }

    function showFeedback(isSuccess, message) {
      if (formFeedback) {
        formFeedback.classList.remove("hidden", "bg-green-500/10", "text-green-400", "bg-red-500/10", "text-red-400 border", "border-green-500/20", "border-red-500/20");
        
        if (isSuccess) {
          formFeedback.classList.add("bg-green-500/10", "text-green-400", "border", "border-green-500/20");
        } else {
          formFeedback.classList.add("bg-red-500/10", "text-red-400", "border", "border-red-500/20");
        }
        
        formFeedback.innerText = message;
        formFeedback.classList.remove("hidden");

        // Auto-scroll feedback into view smoothly
        formFeedback.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        // Hide feedback after 8 seconds
        setTimeout(() => {
          formFeedback.classList.add("hidden");
        }, 8000);
      }
    }
  </script>

</body>
</html>
