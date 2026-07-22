<!-- Global Site Preloader (شاشة التحميل الاحترافية لجميع الصفحات) -->
<style>
    /* Prevent icon raw text flash while font loads */
    .material-symbols-outlined {
        font-display: block;
    }
    
    /* Preloader Fullscreen Overlay Container */
    #global-site-preloader {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: radial-gradient(circle at center, #004d2f 0%, #002818 70%, #00190e 100%);
        color: #ffffff;
        font-family: 'Cairo', 'Tajawal', -apple-system, BlinkMacSystemFont, sans-serif;
        transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s cubic-bezier(0.4, 0, 0.2, 1), transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        user-select: none;
        -webkit-user-select: none;
    }

    #global-site-preloader.preloader-hidden {
        opacity: 0;
        visibility: hidden;
        transform: scale(1.04);
        pointer-events: none;
    }

    /* Ambient Background Glows */
    .preloader-glow-1 {
        position: absolute;
        top: 20%;
        left: 25%;
        width: 380px;
        height: 380px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, rgba(0, 0, 0, 0) 70%);
        border-radius: 50%;
        filter: blur(60px);
        animation: preloaderPulseGlow 4s ease-in-out infinite alternate;
        pointer-events: none;
    }

    .preloader-glow-2 {
        position: absolute;
        bottom: 20%;
        right: 25%;
        width: 340px;
        height: 340px;
        background: radial-gradient(circle, rgba(234, 179, 8, 0.14) 0%, rgba(0, 0, 0, 0) 70%);
        border-radius: 50%;
        filter: blur(50px);
        animation: preloaderPulseGlow 5s ease-in-out infinite alternate-reverse;
        pointer-events: none;
    }

    @keyframes preloaderPulseGlow {
        0% { transform: scale(0.8); opacity: 0.4; }
        100% { transform: scale(1.2); opacity: 0.85; }
    }

    /* Logo Outer Ring / Orbit Animation */
    .preloader-logo-wrapper {
        position: relative;
        width: 110px;
        height: 110px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
    }

    .preloader-ring-spin {
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        background: conic-gradient(from 0deg, transparent 0%, #10b981 35%, #eab308 70%, transparent 100%);
        animation: preloaderSpin 1.8s linear infinite;
        mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2px));
        -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 3px), #fff calc(100% - 2px));
    }

    @keyframes preloaderSpin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Central Glassmorphism Logo Card */
    .preloader-logo-card {
        position: relative;
        width: 98px;
        height: 98px;
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.25);
        animation: preloaderFloat 3s ease-in-out infinite;
    }

    @keyframes preloaderFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-7px); }
    }

    .preloader-logo-img {
        width: 68px;
        height: 68px;
        object-fit: contain;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
    }

    /* Brand Title & Status Text */
    .preloader-brand-title {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.01em;
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #fed65b 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 6px 0;
        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        direction: rtl;
    }

    .preloader-status-text {
        font-size: 13px;
        color: #94a3b8;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        margin-bottom: 22px;
        direction: rtl;
    }

    .preloader-dots span {
        display: inline-block;
        animation: preloaderDotBlink 1.4s infinite fill-mode-both;
        font-weight: bold;
    }
    .preloader-dots span:nth-child(2) { animation-delay: 0.2s; }
    .preloader-dots span:nth-child(3) { animation-delay: 0.4s; }

    @keyframes preloaderDotBlink {
        0%, 80%, 100% { opacity: 0.2; transform: scale(0.8); }
        40% { opacity: 1; transform: scale(1.2); }
    }

    /* Modern Progress Track & Bar */
    .preloader-progress-track {
        width: 200px;
        height: 5px;
        background: rgba(255, 255, 255, 0.12);
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
    }

    .preloader-progress-bar {
        height: 100%;
        width: 15%;
        background: linear-gradient(90deg, #10b981 0%, #eab308 100%);
        border-radius: 10px;
        transition: width 0.25s ease-out;
        box-shadow: 0 0 12px rgba(234, 179, 8, 0.6);
        position: relative;
    }

    .preloader-progress-bar::after {
        content: '';
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.45), transparent);
        animation: preloaderShimmer 1.5s infinite;
    }

    @keyframes preloaderShimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
</style>

<div id="global-site-preloader">
    <div class="preloader-glow-1"></div>
    <div class="preloader-glow-2"></div>

    <div class="preloader-logo-wrapper">
        <div class="preloader-ring-spin"></div>
        <div class="preloader-logo-card">
            <img src="{{ asset('backend/assets/images/logo.png') }}" alt="{{ __t('wisdom_council_title') ?? 'حكماء العالم' }}" class="preloader-logo-img">
        </div>
    </div>

    <h2 class="preloader-brand-title">{{ __t('wisdom_council_title') ?? 'حكماء العالم' }}</h2>
    
    <div class="preloader-status-text">
        <span>{{ __t('loading_platform') ?? 'جاري تحميل المنصة' }}</span>
        <span class="preloader-dots">
            <span>.</span><span>.</span><span>.</span>
        </span>
    </div>

    <div class="preloader-progress-track">
        <div id="preloader-progress-bar" class="preloader-progress-bar"></div>
    </div>
</div>

<script>
(function() {
    var preloader = document.getElementById('global-site-preloader');
    var progressBar = document.getElementById('preloader-progress-bar');
    if (!preloader) return;

    var progress = 15;
    var progressInterval = setInterval(function() {
        if (progress < 88) {
            progress += Math.floor(Math.random() * 9) + 3;
            if (progress > 88) progress = 88;
            if (progressBar) progressBar.style.width = progress + '%';
        }
    }, 100);

    function hidePreloader() {
        if (!preloader || preloader.classList.contains('preloader-hidden')) return;
        clearInterval(progressInterval);
        if (progressBar) progressBar.style.width = '100%';

        setTimeout(function() {
            preloader.classList.add('preloader-hidden');
            setTimeout(function() {
                if (preloader && preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }, 550);
        }, 180);
    }

    if (document.readyState === 'complete') {
        hidePreloader();
    } else {
        window.addEventListener('load', function() {
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(hidePreloader).catch(hidePreloader);
            } else {
                hidePreloader();
            }
        });
    }

    // Safety fallback: 3.5 seconds maximum timeout
    setTimeout(hidePreloader, 3500);
})();
</script>
