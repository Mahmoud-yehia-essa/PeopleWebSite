<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('تسجيل الدخول عبر رمز التحقق (OTP) - Wiselook') }}</title>
    
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-glow: rgba(79, 70, 229, 0.35);
            --bg-dark: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.85);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --error-bg: rgba(239, 68, 68, 0.15);
            --error-text: #fca5a5;
            --success-bg: rgba(16, 185, 129, 0.15);
            --success-text: #6ee7b7;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Tajawal', 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(147, 51, 234, 0.2) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #8b5cf6, #ec4899);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo h1 {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #a5b4fc, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        .brand-logo p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Step Indicators */
        .step-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 2rem;
        }

        .dot {
            width: 32px;
            height: 4px;
            border-radius: 2px;
            background: rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
        }

        .dot.active {
            background: var(--primary);
            box-shadow: 0 0 10px var(--primary-glow);
            width: 48px;
        }

        /* Form Controls */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-main);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .country-select {
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            padding: 0.85rem 0.75rem;
            border-radius: 12px 0 0 12px;
            outline: none;
            font-size: 0.95rem;
            cursor: pointer;
            border-right: none;
        }

        html[dir="rtl"] .country-select {
            border-radius: 0 12px 12px 0;
            border-right: 1px solid var(--card-border);
            border-left: none;
        }

        .phone-input {
            width: 100%;
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            padding: 0.85rem 1rem;
            border-radius: 0 12px 12px 0;
            outline: none;
            font-size: 1.05rem;
            letter-spacing: 1px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        html[dir="rtl"] .phone-input {
            border-radius: 12px 0 0 12px;
        }

        .phone-input:focus, .country-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        /* OTP Code Inputs */
        .otp-boxes {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin: 1.5rem 0;
            direction: ltr;
        }

        .otp-box {
            width: 52px;
            height: 60px;
            background: rgba(31, 41, 55, 0.9);
            border: 1.5px solid var(--card-border);
            border-radius: 12px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            outline: none;
            transition: all 0.2s ease;
        }

        .otp-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            transform: translateY(-2px);
        }

        /* Method Selector Radio */
        .method-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .method-card {
            flex: 1;
            padding: 0.75rem;
            background: rgba(31, 41, 55, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .method-card:hover {
            background: rgba(31, 41, 55, 0.9);
        }

        .method-card.active {
            border-color: var(--primary);
            background: rgba(79, 70, 229, 0.15);
            color: #a5b4fc;
        }

        .method-card i {
            margin-bottom: 4px;
            display: block;
            font-size: 1.1rem;
        }

        /* Primary Button */
        .btn-submit {
            width: 100%;
            padding: 0.95rem;
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px var(--primary-glow);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, var(--primary-hover), #4f46e5);
            transform: translateY(-1px);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Alert Boxes */
        .alert {
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            display: none;
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Timer & Secondary Links */
        .resend-wrapper {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .resend-btn {
            background: none;
            border: none;
            color: #818cf8;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            padding: 0;
            font-size: 0.9rem;
        }

        .resend-btn:disabled {
            color: var(--text-muted);
            text-decoration: none;
            cursor: not-allowed;
        }

        .change-phone-btn {
            display: inline-block;
            margin-top: 1rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-decoration: none;
            cursor: pointer;
        }

        .change-phone-btn:hover {
            color: var(--text-main);
        }

        /* Hidden step container */
        .step-view {
            display: none;
        }

        .step-view.active {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .spinner {
            border: 2px solid rgba(255,255,255,0.3);
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Header -->
        <div class="brand-logo">
            <h1>Wiselook</h1>
            <p id="step-subtitle">{{ __('تسجيل الدخول عبر رمز الهاتف') }}</p>
        </div>

        <!-- Step Indicator Dots -->
        <div class="step-dots">
            <div class="dot active" id="dot-step-1"></div>
            <div class="dot" id="dot-step-2"></div>
        </div>

        <!-- Alerts -->
        <div class="alert alert-error" id="alert-error"></div>
        <div class="alert alert-success" id="alert-success"></div>

        <!-- STATE 1: Phone Number Input Form -->
        <div class="step-view active" id="step-1-view">
            <form id="form-request-otp" onsubmit="handleRequestOtp(event)">
                <div class="form-group">
                    <label class="form-label">{{ __('رقم الهاتف') }}</label>
                    <div class="input-wrapper">
                        <select class="country-select" id="country_code" required>
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
                        <input type="tel" class="phone-input" id="phone" placeholder="55123456" required autocomplete="tel">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('وسيلة الاستلام') }}</label>
                    <div class="method-selector">
                        <div class="method-card active" onclick="selectFlow('SMS', this)">
                            <i class="fa-solid fa-comment-sms"></i>
                            <span>SMS</span>
                        </div>
                        <div class="method-card" onclick="selectFlow('WHATSAPP', this)">
                            <i class="fa-brands fa-whatsapp"></i>
                            <span>واتساب</span>
                        </div>
                    </div>
                    <input type="hidden" id="flow_type" value="SMS">
                </div>

                <button type="submit" class="btn-submit" id="btn-send-otp">
                    <span id="btn-send-text">{{ __('إرسال رمز التحقق') }}</span>
                    <i class="fa-solid fa-arrow-left" id="btn-send-icon"></i>
                </button>
            </form>
        </div>

        <!-- STATE 2: OTP Verification Form -->
        <div class="step-view" id="step-2-view">
            <p style="text-align: center; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                {{ __('تم إرسال رمز التحقق مكون من 6 أرقام إلى الرقم:') }}
                <br>
                <strong id="display-target-phone" style="color: var(--text-main); font-size: 1rem; direction: ltr; display: inline-block; margin-top: 4px;"></strong>
            </p>

            <form id="form-verify-otp" onsubmit="handleVerifyOtp(event)">
                <div class="otp-boxes">
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required autofocus>
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                    <input type="text" class="otp-box" maxlength="1" pattern="[0-9]" inputmode="numeric" required>
                </div>

                <button type="submit" class="btn-submit" id="btn-verify-otp">
                    <span id="btn-verify-text">{{ __('التحقق وتسجيل الدخول') }}</span>
                    <i class="fa-solid fa-check" id="btn-verify-icon"></i>
                </button>
            </form>

            <div class="resend-wrapper">
                <span id="timer-text">{{ __('إعادة الإرسال بعد:') }} <strong id="countdown">60</strong> {{ __('ثانية') }}</span>
                <button type="button" class="resend-btn" id="btn-resend" onclick="resendOtpCode()" disabled style="display: none;">
                    {{ __('إعادة إرسال الرمز الآن') }}
                </button>
                <br>
                <a class="change-phone-btn" onclick="backToStep1()">
                    <i class="fa-solid fa-pen-to-square"></i> {{ __('تعديل رقم الهاتف') }}
                </a>
            </div>
        </div>

    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        let currentVerificationId = null;
        let currentPhone = '';
        let currentCountryCode = '';
        let timerInterval = null;

        // Flow Selection (SMS vs WHATSAPP)
        function selectFlow(flow, element) {
            document.querySelectorAll('.method-card').forEach(card => card.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('flow_type').value = flow;
        }

        // Show Alert Message
        function showAlert(type, message) {
            const errorAlert = document.getElementById('alert-error');
            const successAlert = document.getElementById('alert-success');
            
            errorAlert.style.display = 'none';
            successAlert.style.display = 'none';

            if (type === 'error') {
                errorAlert.textContent = message;
                errorAlert.style.display = 'block';
            } else if (type === 'success') {
                successAlert.textContent = message;
                successAlert.style.display = 'block';
            }
        }

        // Handle OTP Request Submission (Step 1)
        async function handleRequestOtp(event) {
            event.preventDefault();
            
            const countryCode = document.getElementById('country_code').value;
            const phone = document.getElementById('phone').value.trim();
            const flowType = document.getElementById('flow_type').value;

            if (!phone) {
                showAlert('error', 'يرجى إدخال رقم الهاتف بشكل صحيح.');
                return;
            }

            const sendBtn = document.getElementById('btn-send-otp');
            const sendText = document.getElementById('btn-send-text');
            sendBtn.disabled = true;
            sendText.innerHTML = '<div class="spinner"></div> جاري الإرسال...';

            try {
                const response = await fetch('/otp/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        country_code: countryCode,
                        phone: phone,
                        flow_type: flowType
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    currentVerificationId = data.verification_id;
                    currentPhone = phone;
                    currentCountryCode = countryCode;

                    showAlert('success', data.message || 'تم إرسال رمز التحقق بنجاح!');
                    
                    // Switch to Step 2
                    setTimeout(() => {
                        goToStep2(countryCode + ' ' + phone, flowType);
                    }, 600);
                } else {
                    showAlert('error', data.message || 'تعذر إرسال رمز التحقق. يرجى المحاولة لاحقاً.');
                }
            } catch (error) {
                console.error('Error sending OTP:', error);
                showAlert('error', 'حدث خطأ في الاتصال بالخادم. يرجى التحقق من اتصال الإنترنت.');
            } finally {
                sendBtn.disabled = false;
                sendText.textContent = 'إرسال رمز التحقق';
            }
        }

        // Transition to Step 2 (OTP Entry)
        function goToStep2(displayPhone, flowType = 'SMS') {
            document.getElementById('step-1-view').classList.remove('active');
            document.getElementById('step-2-view').classList.add('active');
            document.getElementById('dot-step-1').classList.remove('active');
            document.getElementById('dot-step-2').classList.add('active');
            
            const channelName = flowType === 'WHATSAPP' ? 'الواتس اب' : 'رسالة SMS';
            document.getElementById('step-subtitle').textContent = `أدخل رمز التحقق المرسل عبر ${channelName}`;
            document.getElementById('display-target-phone').textContent = displayPhone;

            // Reset OTP Inputs
            const otpInputs = document.querySelectorAll('.otp-box');
            otpInputs.forEach(input => input.value = '');
            otpInputs[0].focus();

            startCountdown(60);
        }

        // Back to Step 1
        function backToStep1() {
            clearInterval(timerInterval);
            document.getElementById('step-2-view').classList.remove('active');
            document.getElementById('step-1-view').classList.add('active');
            document.getElementById('dot-step-2').classList.remove('active');
            document.getElementById('dot-step-1').classList.add('active');
            document.getElementById('step-subtitle').textContent = 'تسجيل الدخول عبر رمز الهاتف';
            showAlert('success', '');
            document.getElementById('alert-success').style.display = 'none';
            document.getElementById('alert-error').style.display = 'none';
        }

        // OTP Box Auto-Focus Behavior
        const otpInputs = document.querySelectorAll('.otp-box');
        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text').trim();
                if (/^\d{4,6}$/.test(pastedData)) {
                    const digits = pastedData.split('');
                    otpInputs.forEach((inp, idx) => {
                        inp.value = digits[idx] || '';
                    });
                    if (digits.length >= otpInputs.length) {
                        otpInputs[otpInputs.length - 1].focus();
                    }
                }
            });
        });

        // Handle OTP Verification Submission (Step 2)
        async function handleVerifyOtp(event) {
            event.preventDefault();

            const otpCode = Array.from(otpInputs).map(i => i.value).join('');

            if (otpCode.length < 4) {
                showAlert('error', 'يرجى إدخال رمز التحقق المكون من الأرقام بشكل كامل.');
                return;
            }

            const verifyBtn = document.getElementById('btn-verify-otp');
            const verifyText = document.getElementById('btn-verify-text');
            verifyBtn.disabled = true;
            verifyText.innerHTML = '<div class="spinner"></div> جاري التحقق...';

            try {
                const response = await fetch('/otp/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        country_code: currentCountryCode,
                        phone: currentPhone,
                        code: otpCode,
                        verification_id: currentVerificationId
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    showAlert('success', 'تم التحقق بنجاح! جاري تحويلك...');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/';
                    }, 1000);
                } else {
                    showAlert('error', data.message || 'رمز التحقق غير صحيح. يرجى المحاولة مرة أخرى.');
                }
            } catch (error) {
                console.error('Error verifying OTP:', error);
                showAlert('error', 'حدث خطأ غير متوقع أثناء التحقق.');
            } finally {
                verifyBtn.disabled = false;
                verifyText.textContent = 'التحقق وتسجيل الدخول';
            }
        }

        // Countdown Timer Logic
        function startCountdown(seconds) {
            clearInterval(timerInterval);
            let timeLeft = seconds;
            
            const timerText = document.getElementById('timer-text');
            const countdownEl = document.getElementById('countdown');
            const resendBtn = document.getElementById('btn-resend');

            timerText.style.display = 'inline';
            resendBtn.style.display = 'none';
            resendBtn.disabled = true;

            countdownEl.textContent = timeLeft;

            timerInterval = setInterval(() => {
                timeLeft--;
                countdownEl.textContent = timeLeft;

                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    timerText.style.display = 'none';
                    resendBtn.style.display = 'inline-block';
                    resendBtn.disabled = false;
                }
            }, 1000);
        }

        // Resend OTP Code Action
        async function resendOtpCode() {
            if (!currentPhone || !currentCountryCode) return;

            const flowType = document.getElementById('flow_type').value;
            const resendBtn = document.getElementById('btn-resend');
            resendBtn.disabled = true;
            resendBtn.textContent = 'جاري إرسال رمز جديد...';

            try {
                const response = await fetch('/otp/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        country_code: currentCountryCode,
                        phone: currentPhone,
                        flow_type: flowType
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    currentVerificationId = data.verification_id;
                    showAlert('success', 'تم إعادة إرسال رمز التحقق بنجاح.');
                    startCountdown(60);
                } else {
                    showAlert('error', data.message || 'فشلت إعادة الإرسال.');
                }
            } catch (error) {
                showAlert('error', 'خطأ في الاتصال أثناء إعادة إرسال الرمز.');
            } finally {
                resendBtn.textContent = 'إعادة إرسال الرمز الآن';
            }
        }
    </script>
</body>
</html>
