// ============================================================
// Firebase Phone Authentication - Standalone Module
// Used exclusively by: /login/phone/new  (firebase_phone_login.blade.php)
// DO NOT import in app.js to avoid loading Firebase globally.
// ============================================================

import { initializeApp } from 'firebase/app';
import {
    getAuth,
    RecaptchaVerifier,
    signInWithPhoneNumber,
} from 'firebase/auth';

// ---------------------------------------------------------
// Firebase Project Config
// ---------------------------------------------------------
const firebaseConfig = {
    apiKey: "AIzaSyDSYXLxgEb36tc61kA9yawFZg2FCtJu-D8",
    authDomain: "worldwisepeople-6badf.firebaseapp.com",
    projectId: "worldwisepeople-6badf",
    storageBucket: "worldwisepeople-6badf.firebasestorage.app",
    messagingSenderId: "222432540103",
    appId: "1:222432540103:web:919367580f4a033492d5e2",
    measurementId: "G-2GHBMG358K",
};

const app  = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Keep track of the confirmation result after sending OTP
let confirmationResult = null;
let timerInterval      = null;
let recaptchaVerifier  = null;

// ---------------------------------------------------------
// Helper – show / hide alert boxes
// ---------------------------------------------------------
function showAlert(type, message) {
    const errorEl   = document.getElementById('fb-alert-error');
    const successEl = document.getElementById('fb-alert-success');
    errorEl.style.display   = 'none';
    successEl.style.display = 'none';

    if (type === 'error' && message) {
        errorEl.textContent     = message;
        errorEl.style.display   = 'block';
    } else if (type === 'success' && message) {
        successEl.textContent   = message;
        successEl.style.display = 'block';
    }
}

// ---------------------------------------------------------
// Helper – countdown timer
// ---------------------------------------------------------
function startCountdown(seconds) {
    clearInterval(timerInterval);
    const timerText   = document.getElementById('fb-timer-text');
    const countdownEl = document.getElementById('fb-countdown');
    const resendBtn   = document.getElementById('fb-btn-resend');

    timerText.style.display   = 'inline';
    resendBtn.style.display   = 'none';
    resendBtn.disabled        = true;
    countdownEl.textContent   = seconds;

    let timeLeft = seconds;
    timerInterval = setInterval(() => {
        timeLeft--;
        countdownEl.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            timerText.style.display  = 'none';
            resendBtn.style.display  = 'inline-block';
            resendBtn.disabled       = false;
        }
    }, 1000);
}

// ---------------------------------------------------------
// Initialise reCAPTCHA
// NOTE: When using Firebase Test Phone Numbers, reCAPTCHA is bypassed entirely.
// The verifier is still required by the API signature but won't actually execute.
// ---------------------------------------------------------
function initRecaptcha() {
    if (recaptchaVerifier) {
        try { recaptchaVerifier.clear(); } catch (e) { /* ignore */ }
        recaptchaVerifier = null;
    }

    recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
        size: 'invisible',
        callback: () => {
            console.log('reCAPTCHA solved ✅');
        },
        'expired-callback': () => {
            console.warn('reCAPTCHA expired');
            if (recaptchaVerifier) {
                try { recaptchaVerifier.clear(); } catch (e) { /* ignore */ }
                recaptchaVerifier = null;
            }
        },
    });

    recaptchaVerifier.render().catch((e) => {
        console.warn('reCAPTCHA render skipped (test mode):', e.message);
    });
}

// ---------------------------------------------------------
// Flow selector (SMS / WhatsApp)
// ---------------------------------------------------------
window.fbSelectFlow = function (flow, element) {
    document.querySelectorAll('.fb-method-card').forEach(c => c.classList.remove('active'));
    element.classList.add('active');
    document.getElementById('fb-flow-type').value = flow;

    const recaptchaContainer = document.getElementById('recaptcha-container');
    if (recaptchaContainer) {
        recaptchaContainer.style.display = flow === 'WHATSAPP' ? 'none' : 'flex';
    }
};

// ---------------------------------------------------------
// STEP 1 – Send OTP (Firebase SMS or TextMeBot WhatsApp)
// ---------------------------------------------------------
window.fbHandleSendOtp = async function (event) {
    event.preventDefault();

    const countryCode = document.getElementById('fb-country-code').value;
    const phone       = document.getElementById('fb-phone').value.trim();
    const flowType    = document.getElementById('fb-flow-type').value;

    if (!phone) {
        showAlert('error', 'يرجى إدخال رقم الهاتف.');
        return;
    }

    const fullPhone = countryCode + phone.replace(/^0+/, '');
    const sendBtn   = document.getElementById('fb-btn-send');
    const sendText  = document.getElementById('fb-btn-send-text');

    sendBtn.disabled   = true;
    sendText.innerHTML = '<div class="fb-spinner"></div> جاري الإرسال...';
    showAlert('', '');

    const isRegisterEl = document.getElementById('fb-is-register');
    const isRegister   = isRegisterEl ? isRegisterEl.value === '1' : false;
    window._fbIsRegister = isRegister;

    const fnameInput = document.getElementById('fb-fname');
    const lnameInput = document.getElementById('fb-lname');
    if (fnameInput) window._fbFname = fnameInput.value.trim();
    if (lnameInput) window._fbLname = lnameInput.value.trim();

    window._fbFullPhone = fullPhone;
    window._fbFlowType  = flowType;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Check phone availability before sending OTP for registration
    if (isRegister) {
        try {
            const checkRes = await fetch('/check-phone-register-new', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone: fullPhone }),
            });

            const checkData = await checkRes.json();
            if (!checkRes.ok || !checkData.success) {
                showAlert('error', checkData.message || 'رقم الهاتف هذا مسجل بالفعل. يرجى تسجيل الدخول.');
                sendBtn.disabled     = false;
                sendText.textContent = 'إرسال رمز التحقق';
                return;
            }
        } catch (err) {
            console.error('Check phone error:', err);
        }
    }

    // WhatsApp Flow (TextMeBot API)
    if (flowType === 'WHATSAPP') {
        try {
            const response  = await fetch('/send-whatsapp-otp-new', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone: fullPhone, is_register: isRegister }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert('success', data.message || `تم إرسال رمز التحقق عبر واتساب إلى ${fullPhone}`);
                setTimeout(() => fbGoToStep2(fullPhone), 600);
            } else {
                showAlert('error', data.message || 'تعذر إرسال رمز التحقق عبر واتساب.');
            }
        } catch (err) {
            console.error('WhatsApp sendOtp error:', err);
            showAlert('error', 'حدث خطأ أثناء الاتصال بالخادم لإرسال واتساب.');
        } finally {
            sendBtn.disabled     = false;
            sendText.textContent = 'إرسال رمز التحقق';
        }
        return;
    }

    // Default SMS Flow (Firebase)
    try {
        if (!recaptchaVerifier) initRecaptcha();

        confirmationResult = await signInWithPhoneNumber(auth, fullPhone, recaptchaVerifier);

        showAlert('success', `تم إرسال طلب رمز التحقق إلى ${fullPhone}`);
        setTimeout(() => fbGoToStep2(fullPhone), 600);

    } catch (err) {
        console.error("Firebase Error Details:", err);
        const errMsg = firebaseErrorArabic(err.code, err.message);
        showAlert('error', errMsg);

        // Reset recaptcha on error
        if (recaptchaVerifier) {
            try { recaptchaVerifier.clear(); } catch (e) {}
            recaptchaVerifier = null;
        }
    } finally {
        sendBtn.disabled     = false;
        sendText.textContent = 'إرسال رمز التحقق';
    }
};

// ---------------------------------------------------------
// STEP 2 – Transition view
// ---------------------------------------------------------
function fbGoToStep2(displayPhone) {
    document.getElementById('fb-step-1-view').classList.remove('active');
    document.getElementById('fb-step-2-view').classList.add('active');
    document.getElementById('fb-dot-1').classList.remove('active');
    document.getElementById('fb-dot-2').classList.add('active');
    
    const subtitleEl = document.getElementById('fb-step-subtitle');
    if (subtitleEl) {
        subtitleEl.textContent = window._fbFlowType === 'WHATSAPP' 
            ? 'أدخل رمز التحقق المرسل عبر الواتساب' 
            : 'أدخل رمز التحقق المرسل عبر الرسالة';
    }

    document.getElementById('fb-display-phone').textContent = displayPhone;

    const otpInputs = document.querySelectorAll('.fb-otp-box');
    otpInputs.forEach(i => i.value = '');
    otpInputs[0].focus();
    startCountdown(60);
}

// ---------------------------------------------------------
// STEP 2 – Back to Step 1
// ---------------------------------------------------------
window.fbBackToStep1 = function () {
    clearInterval(timerInterval);
    document.getElementById('fb-step-2-view').classList.remove('active');
    document.getElementById('fb-step-1-view').classList.add('active');
    document.getElementById('fb-dot-2').classList.remove('active');
    document.getElementById('fb-dot-1').classList.add('active');
    
    const subtitleEl = document.getElementById('fb-step-subtitle');
    if (subtitleEl) {
        subtitleEl.textContent = 'أدخل رقم الهاتف لتلقي رمز التحقق';
    }
    
    document.getElementById('fb-alert-error').style.display   = 'none';
    document.getElementById('fb-alert-success').style.display = 'none';
};

// ---------------------------------------------------------
// STEP 2 – Verify OTP (Firebase SMS or TextMeBot WhatsApp)
// ---------------------------------------------------------
window.fbHandleVerifyOtp = async function (event) {
    event.preventDefault();

    const otpInputs = document.querySelectorAll('.fb-otp-box');
    const code      = Array.from(otpInputs).map(i => i.value).join('');

    if (code.length < 6) {
        showAlert('error', 'يرجى إدخال رمز التحقق المكون من 6 أرقام.');
        return;
    }

    const verifyBtn  = document.getElementById('fb-btn-verify');
    const verifyText = document.getElementById('fb-btn-verify-text');
    verifyBtn.disabled   = true;
    verifyText.innerHTML = '<div class="fb-spinner"></div> جاري التحقق...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // WhatsApp Flow
    if (window._fbFlowType === 'WHATSAPP') {
        try {
            const response = await fetch('/verify-whatsapp-otp-new', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    phone: window._fbFullPhone,
                    code,
                    first_name: window._fbFname,
                    last_name: window._fbLname,
                    is_register: window._fbIsRegister,
                }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert('success', 'تم التحقق بنجاح! جاري تحويلك...');
                setTimeout(() => { window.location.href = data.redirect || '/'; }, 1000);
            } else {
                showAlert('error', data.message || 'رمز التحقق غير صحيح.');
            }
        } catch (err) {
            console.error('WhatsApp verifyOtp error:', err);
            showAlert('error', 'حدث خطأ في الاتصال أثناء التحقق من الرمز.');
        } finally {
            verifyBtn.disabled     = false;
            verifyText.textContent = 'التحقق وتسجيل الدخول';
        }
        return;
    }

    // Default SMS Flow (Firebase)
    if (!confirmationResult) {
        showAlert('error', 'انتهت الجلسة. يرجى إعادة إرسال الرمز.');
        verifyBtn.disabled    = false;
        verifyText.textContent = 'التحقق وتسجيل الدخول';
        return;
    }

    try {
        const result  = await confirmationResult.confirm(code);
        const user    = result.user;
        const idToken = await user.getIdToken();
        const phone   = user.phoneNumber;

        // Send idToken to Laravel backend
        const response  = await fetch('/verify-firebase-token-new', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                token: idToken,
                phone,
                first_name: window._fbFname,
                last_name: window._fbLname,
                is_register: window._fbIsRegister,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            showAlert('success', 'تم التحقق بنجاح! جاري تحويلك...');
            setTimeout(() => { window.location.href = data.redirect || '/'; }, 1000);
        } else {
            showAlert('error', data.message || 'فشل التحقق من الخادم.');
        }

    } catch (err) {
        console.error('Firebase verifyOtp error:', err);
        showAlert('error', firebaseErrorArabic(err.code));
    } finally {
        verifyBtn.disabled    = false;
        verifyText.textContent = 'التحقق وتسجيل الدخول';
    }
};

// ---------------------------------------------------------
// Resend OTP
// ---------------------------------------------------------
window.fbResendOtp = async function () {
    const fullPhone = window._fbFullPhone;
    if (!fullPhone) return;

    const resendBtn = document.getElementById('fb-btn-resend');
    resendBtn.disabled    = true;
    resendBtn.textContent = 'جاري إرسال رمز جديد...';

    if (window._fbFlowType === 'WHATSAPP') {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const response  = await fetch('/send-whatsapp-otp-new', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ phone: fullPhone }),
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert('success', 'تم إعادة إرسال رمز التحقق عبر واتساب بنجاح.');
                startCountdown(60);
            } else {
                showAlert('error', data.message || 'فشلت إعادة الإرسال.');
            }
        } catch (err) {
            showAlert('error', 'خطأ في الاتصال أثناء إعادة إرسال الرمز.');
        } finally {
            resendBtn.textContent = 'إعادة إرسال الرمز الآن';
        }
        return;
    }

    try {
        if (!recaptchaVerifier) initRecaptcha();
        confirmationResult = await signInWithPhoneNumber(auth, fullPhone, recaptchaVerifier);
        showAlert('success', 'تم إعادة إرسال رمز التحقق بنجاح.');
        startCountdown(60);
    } catch (err) {
        console.error('Firebase resendOtp error:', err);
        showAlert('error', firebaseErrorArabic(err.code));
        if (recaptchaVerifier) { try { recaptchaVerifier.clear(); } catch(e){} recaptchaVerifier = null; }
    } finally {
        resendBtn.textContent = 'إعادة إرسال الرمز الآن';
    }
};


// ---------------------------------------------------------
// OTP input auto-advance behaviour (exposed globally)
// ---------------------------------------------------------
window.fbInitOtpInputs = function () {
    const inputs = document.querySelectorAll('.fb-otp-box');
    inputs.forEach((input, idx) => {
        input.addEventListener('input', e => {
            if (e.target.value.length === 1 && idx < inputs.length - 1) {
                inputs[idx + 1].focus();
            }
        });
        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                inputs[idx - 1].focus();
            }
        });
        input.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').trim();
            if (/^\d{4,6}$/.test(pasted)) {
                pasted.split('').forEach((d, i) => { if (inputs[i]) inputs[i].value = d; });
                inputs[Math.min(pasted.length, inputs.length) - 1].focus();
            }
        });
    });
};

// ---------------------------------------------------------
// Map Firebase error codes to Arabic messages
// ---------------------------------------------------------
function firebaseErrorArabic(code, customMessage = '') {
    const map = {
        'auth/invalid-phone-number'      : 'رقم الهاتف المدخل غير صحيح أو الصيغة غير مدعومة.',
        'auth/too-many-requests'         : 'تم حظر هذا الرقم مؤقتاً لكثرة المحاولات. يرجى المحاولة لاحقاً.',
        'auth/invalid-verification-code' : 'رمز التحقق غير صحيح.',
        'auth/code-expired'              : 'انتهت صلاحية رمز التحقق. يرجى إعادة الإرسال.',
        'auth/quota-exceeded'            : 'تم تجاوز حصة الرسائل المسموحة في مشروع Firebase (Quota Exceeded).',
        'auth/captcha-check-failed'      : 'فشل التحقق من reCAPTCHA. تأكد من إضافة الدومين إلى Authorized Domains في Firebase.',
        'auth/app-not-authorized'        : 'هذا الدومين غير مصرح له بتسجيل الدخول في Firebase (Authorized Domains).',
        'auth/network-request-failed'    : 'خطأ في الشبكة. تحقق من اتصال الإنترنت أو إعدادات خوادم Firebase.',
        'auth/internal-error'            : 'حدث خطأ داخلي في نظام Firebase.',
    };
    const translated = map[code];
    if (translated) {
        return `${translated} [${code}]`;
    }
    return `خطأ في الفيربيز (${code || 'Error'}): ${customMessage || 'يرجى مراجعة إعدادات Firebase Console.'}`;
}

// ---------------------------------------------------------
// Initialise reCAPTCHA when the DOM is ready
// ---------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
    initRecaptcha();
    window.fbInitOtpInputs();
});
