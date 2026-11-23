<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سياسة الخصوصية | Privacy Policy - WiseLook</title>
    <meta name="description" content="Official Privacy Policy for WiseLook (حكماء العالم) mobile application and website. Learn how we collect, protect, and handle your data in full compliance with Google Play Store policies.">
    <style>
        :root {
            --primary: #1b4332;
            --primary-accent: #2ea85a;
            --primary-light: #ecfdf5;
            --text-main: #111827;
            --text-muted: #4b5563;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --footer-bg: #0f172a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
            color: #ffffff;
            padding: 2.5rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(27, 67, 50, 0.15);
        }

        .brand-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 999px;
            padding: 0.35rem 1.2rem;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.85rem;
            letter-spacing: 0.02em;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
        }

        header .subtitle-en {
            font-size: 1.15rem;
            opacity: 0.95;
            direction: ltr;
        }

        header .meta {
            margin-top: 0.85rem;
            font-size: 0.88rem;
            opacity: 0.85;
            direction: ltr;
        }

        .top-links {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .top-links a {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.45rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .top-links a:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        /* Language Toggle Bar */
        .lang-bar {
            max-width: 960px;
            width: 100%;
            margin: 1.5rem auto 0;
            padding: 0 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .lang-switch {
            display: inline-flex;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 10px;
        }

        .lang-btn {
            border: none;
            background: transparent;
            padding: 6px 18px;
            font-size: 0.9rem;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            color: #475569;
            transition: all 0.2s;
        }

        .lang-btn.active {
            background: var(--primary);
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .effective-date {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        main {
            max-width: 960px;
            width: 100%;
            margin: 1.5rem auto 3rem;
            padding: 0 1.25rem;
            flex: 1;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 2.25rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .section-number {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .section-title h2 {
            font-size: 1.25rem;
            color: var(--primary);
            font-weight: 700;
        }

        .dual-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .lang-panel {
            font-size: 0.95rem;
        }

        .panel-ar {
            direction: rtl;
            text-align: right;
        }

        .panel-en {
            direction: ltr;
            text-align: left;
            border-right: 1px solid #edf2f7;
            padding-right: 1.5rem;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 0.85rem;
        }

        ul, ol {
            padding-inline-start: 1.4rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        strong {
            color: var(--text-main);
        }

        .highlight-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 1.25rem;
            margin: 1rem 0;
        }

        .highlight-box h4 {
            color: #166534;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .highlight-box p {
            color: #15803d;
            margin-bottom: 0;
            font-size: 0.9rem;
        }

        .action-link {
            color: var(--primary-accent);
            text-decoration: underline;
            font-weight: 700;
        }

        footer {
            background-color: var(--footer-bg);
            color: #94a3b8;
            padding: 2.5rem 1.5rem;
            text-align: center;
            font-size: 0.9rem;
            margin-top: auto;
        }

        footer a {
            color: #38bdf8;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .dual-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .panel-en {
                border-right: none;
                border-top: 1px dashed #cbd5e1;
                padding-right: 0;
                padding-top: 1.25rem;
            }

            header h1 {
                font-size: 1.6rem;
            }

            .card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="brand-badge">WiseLook | حكماء العالم</div>
        <h1>سياسة الخصوصية</h1>
        <div class="subtitle-en">Privacy Policy for WiseLook App &amp; Website</div>
        <div class="meta">Official Web Documentation &bull; https://wiselook.org</div>
        <div class="top-links">
            <a href="{{ url('/') }}">الصفحة الرئيسية / Home</a>
            <a href="{{ route('terms') }}">شروط الاستخدام / Terms of Service</a>
            <a href="{{ route('account.deletion') }}">حذف الحساب والبيانات / Account Deletion</a>
        </div>
    </header>

    <div class="lang-bar">
        <div class="effective-date">
            <span>تاريخ السريان: 2026-11-23 | Last Updated: November 23, 2026</span>
        </div>
        <div class="lang-switch">
            <button type="button" class="lang-btn active" id="btnShowAll" onclick="setMode('all')">ثنائي اللغة / Bilingual</button>
            <button type="button" class="lang-btn" id="btnShowEn" onclick="setMode('en')">English Only</button>
            <button type="button" class="lang-btn" id="btnShowAr" onclick="setMode('ar')">العربية فقط</button>
        </div>
    </div>

    <main>

        <!-- 1. Introduction -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">1</div>
                <div class="section-title">
                    <h2>مقدمة ونطاق التطبيق &bull; Introduction &amp; Scope</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>مرحباً بك في منصة وتطبيق <strong>حكماء العالم (WiseLook)</strong> المتاحة عبر الموقع الإلكتروني الرسمي <strong>https://wiselook.org</strong> وتطبيق الهواتف الذكية. تلتزم منصة WiseLook بحماية خصوصية وأمان بياناتك الشخصية بأعلى معايير الشفافية والأمان، ووفقاً لسياسات متجر Google Play وقوانين حماية البيانات الدولية.</p>
                    <p>توضح هذه السياسة كيفية جمع بياناتك واستخدامها وحمايتها، وحقوقك الكاملة في إدارة بياناتك أو طلب حذفها في أي وقت.</p>
                </div>
                <div class="lang-panel panel-en">
                    <p>Welcome to <strong>WiseLook</strong> ("we", "our", or "us"), accessible via our website <strong>https://wiselook.org</strong> and our official mobile application. We are deeply committed to protecting your personal privacy and safeguarding your data in full compliance with the <strong>Google Play Developer Policies</strong> and global data protection standards.</p>
                    <p>This Privacy Policy explains what information we collect, how it is processed and protected, and your comprehensive rights to access, manage, or permanently delete your account and personal data.</p>
                </div>
            </div>
        </div>

        <!-- 2. Information We Collect -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">2</div>
                <div class="section-title">
                    <h2>المعلومات التي نجمعها &bull; Information We Collect</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>نقوم بجمع الأنواع التالية من المعلومات لتقديم خدمات التطبيق الأساسية وتحسين تجربتك:</p>
                    <ul>
                        <li><strong>بيانات الحساب الشخصية:</strong> مثل الاسم، عنوان البريد الإلكتروني، ورقم الهاتف (المستخدم للتحقق وتسجيل الدخول عبر إرسال كود OTP الآمن عبر الواتساب أو الرسائل).</li>
                        <li><strong>بيانات الملف الشخصي والمحتوى:</strong> الصورة الشخصية، النبذة التعريفية، المنشورات، التعليقات، الإعجابات، والصور أو المرفقات التي تختار مشاركتها داخل المنصة.</li>
                        <li><strong>بيانات الجهاز والمعلومات الفنية:</strong> نوع وطراز الجهاز، إصدار نظام التشغيل، عنوان الـ IP، ومعرفات الإشعارات (Firebase Cloud Messaging Tokens) لإرسال الإشعارات اللحظية.</li>
                        <li><strong>أذونات الهاتف الاختيارية:</strong>
                            <ul>
                                <li><strong>الكاميرا ومكتبة الصور:</strong> تطلب فقط عندما تختار بنفسك التقاط صورة، تغيير الصورة الشخصية، أو بدء مكالمة فيديو.</li>
                                <li><strong>الميكروفون:</strong> يطلب فقط عند إجراء المكالمات الصوتية أو مكالمات الفيديو أو إرسال رسائل صوتية.</li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="lang-panel panel-en">
                    <p>We collect only the information necessary to provide, maintain, and improve our services:</p>
                    <ul>
                        <li><strong>Personal Identification Data:</strong> Your name, email address, and phone number (utilized solely for secure authentication and login verification via OTP codes delivered through WhatsApp or SMS).</li>
                        <li><strong>Profile &amp; User-Generated Content:</strong> Profile avatar, bio, published posts, comments, likes, images, and attachments that you voluntarily create or share on the platform.</li>
                        <li><strong>Device &amp; Technical Identifiers:</strong> Device model, operating system version, IP address, and Firebase Cloud Messaging (FCM) registration tokens used exclusively to deliver push notifications.</li>
                        <li><strong>Runtime Device Permissions:</strong>
                            <ul>
                                <li><strong>Camera &amp; Gallery:</strong> Requested solely when you explicitly choose to take a photo, update your avatar, attach images to posts, or join a video call.</li>
                                <li><strong>Microphone:</strong> Requested solely for participating in voice messages, real-time audio calls, or video calls.</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 3. How We Use Information -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">3</div>
                <div class="section-title">
                    <h2>كيف نستخدم معلوماتك &bull; How We Use Your Information</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>نستخدم البيانات التي نجمعها للأغراض التالية فقط:</p>
                    <ul>
                        <li>إنشاء وإدارة حساب المستخدم والمصادقة الآمنة على تسجيل الدخول.</li>
                        <li>تمكين التواصل الاجتماعي، النشر، التعليق، والمراسلة والمكالمات الصوتية والمرئية المباشرة بين الأعضاء.</li>
                        <li>إرسال إشعارات وتنبيهات الأنشطة الهامة والرسائل الجديدة عبر خدمة Firebase Cloud Messaging.</li>
                        <li>حماية المنصة من محاولات الاحتيال، الإساءة، والأنشطة المشبوهة، وضمان بيئة آمنة للمجتمع.</li>
                        <li>تقديم الدعم الفني وحل أي مشكلات تقنية يواجهها المستخدم.</li>
                    </ul>
                    <div class="highlight-box">
                        <h4>تأكيد حاسم:</h4>
                        <p>نحن <strong>لا نبيع</strong> ولا نؤجر ولا نتاجر ببياناتك الشخصية لأي طرف ثالث أو لأغراض إعلانية على الإطلاق.</p>
                    </div>
                </div>
                <div class="lang-panel panel-en">
                    <p>We use your collected information strictly for legitimate operational purposes:</p>
                    <ul>
                        <li>Facilitating account creation, profile management, and secure multi-factor authentication (OTP verification).</li>
                        <li>Enabling social networking capabilities, including publishing discussions, commenting, real-time messaging, and interactive voice/video calling.</li>
                        <li>Delivering timely push notifications regarding messages, interactions, or system updates via Firebase Cloud Messaging.</li>
                        <li>Maintaining system security, preventing unauthorized access, abuse, and fraudulent activity.</li>
                        <li>Providing customer assistance and technical support.</li>
                    </ul>
                    <div class="highlight-box">
                        <h4>Strict Commitment:</h4>
                        <p>We <strong>do not sell, rent, trade, or monetize</strong> your personal data or user content to any third parties or advertisers.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Third-Party Services -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">4</div>
                <div class="section-title">
                    <h2>خدمات الأطراف الثالثة &bull; Third-Party Services &amp; SDKs</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>قد يستخدم التطبيق خدمات تابعة لأطراف ثالثة موثوقة لتسهيل العمليات التقنية الأساسية:</p>
                    <ul>
                        <li><strong>خدمات Google Play و Google Firebase:</strong> تُستخدم في المصادقة وإرسال الإشعارات اللحظية (FCM) وتتبع أخطاء التطبيق لضمان استقراره.</li>
                        <li><strong>بوابة الواتساب (WhatsApp Gateway):</strong> لإرسال رموز التحقق لمرة واحدة (OTP) لتسجيل الدخول السريع والآمن.</li>
                        <li><strong>خدمات الاتصال في الوقت الفعلي (WebRTC / Agora):</strong> لتوفير مكالمات صوت وفيديو مشفرة وعالية الجودة بين المستخدمين.</li>
                    </ul>
                </div>
                <div class="lang-panel panel-en">
                    <p>Our app incorporates reliable, industry-standard third-party SDKs to deliver core functionality:</p>
                    <ul>
                        <li><strong>Google Play Services &amp; Google Firebase:</strong> Employed for secure backend authentication, crash analytics, and push notification delivery (Firebase Cloud Messaging).</li>
                        <li><strong>WhatsApp Gateway / SMS Services:</strong> Used strictly for transmitting automated One-Time Password (OTP) verification codes.</li>
                        <li><strong>Real-Time Communication Services (WebRTC / Agora):</strong> Utilized to power encrypted, peer-to-peer audio and video calls between platform members.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 5. Data Security & Storage -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">5</div>
                <div class="section-title">
                    <h2>أمان وحماية البيانات &bull; Data Security &amp; Protection</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>نطبق معايير أمان تقنية وإدارية متقدمة لحماية معلوماتك:</p>
                    <ul>
                        <li>تشفير جميع الاتصالات والبيانات المنقولة عبر بروتوكول HTTPS / TLS المشفر.</li>
                        <li>تشفير كلمات المرور والرموز السرية باستخدام أقوى خوارزميات التشفير (Bcrypt).</li>
                        <li>عزل الخوادم وتطبيق جدران حماية وفحص دوري لمنع أي وصول غير مصرح به.</li>
                    </ul>
                </div>
                <div class="lang-panel panel-en">
                    <p>We enforce rigorous administrative and technical security safeguards:</p>
                    <ul>
                        <li>All data transmissions between your device and our servers are encrypted using modern <strong>HTTPS / TLS</strong> encryption protocols.</li>
                        <li>User passwords and sensitive credentials are cryptographic hashed using <strong>Bcrypt</strong>.</li>
                        <li>Regular security audits, access controls, and firewall defenses are implemented across our infrastructure.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 6. Data Retention & Account Deletion (Google Play Compliance) -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">6</div>
                <div class="section-title">
                    <h2>الاحتفاظ بالبيانات وحذف الحساب &bull; Data Retention &amp; Account Deletion</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>نحتفظ ببياناتك طالما كان حسابك نشطاً. ونلتزم بتمكينك من حذف حسابك وبياناتك بالكامل في أي وقت بكل سهولة وشفافية:</p>
                    <ul>
                        <li><strong>من داخل التطبيق:</strong> يمكنك الذهاب إلى الملف الشخصي &larr; الإعدادات &larr; حذف الحساب.</li>
                        <li><strong>عبر صفحة الويب المخصصة (حتى لو لم يكن التطبيق مثبتاً على هاتفك):</strong> يمكنك زيارة صفحة حذف الحساب والبيانات الرسمية مباشرة عبر الرابط التالي:
                            <br><a href="{{ route('account.deletion') }}" class="action-link" target="_blank">https://wiselook.org/delete-account</a>
                        </li>
                    </ul>
                    <p>عند تأكيد طلب الحذف، يتم مسح بياناتك الشخصية (الاسم، البريد، الهاتف، كلمة المرور، الصورة الشخصية) وإلغاء ارتباط منشوراتك بشكل دائم لا يمكن التراجع عنه.</p>
                </div>
                <div class="lang-panel panel-en">
                    <p>In full adherence to Google Play's <strong>Account Deletion and Data Retention Policy</strong>, we empower you to delete your account and all associated personal data at any time:</p>
                    <ul>
                        <li><strong>In-App Deletion:</strong> Navigate to Profile &rarr; Settings &rarr; Delete Account to initiate instant account deletion.</li>
                        <li><strong>Dedicated Web Deletion Portal:</strong> You can submit an account and data deletion request at any time without having the app installed by visiting our official portal:
                            <br><a href="{{ route('account.deletion') }}" class="action-link" target="_blank">https://wiselook.org/delete-account</a>
                        </li>
                    </ul>
                    <p>Upon processing the deletion request, your personal credentials (name, email, phone number, password, profile photo, and active sessions) are permanently wiped from our active databases.</p>
                </div>
            </div>
        </div>

        <!-- 7. Children's Privacy -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">7</div>
                <div class="section-title">
                    <h2>خصوصية الأطفال &bull; Children's Privacy</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>تطبيق ومنصة WiseLook غير موجهة للأطفال دون سن 13 عاماً. نحن لا نقوم بجمع معلومات شخصية عن قصد من أي طفل دون هذا السن. إذا علمنا بأننا قد جمعنا معلومات شخصية من طفل دون سن 13 عاماً، فإننا نتخذ خطوات فورية لحذف هذه المعلومات من خوادمنا.</p>
                </div>
                <div class="lang-panel panel-en">
                    <p>WiseLook is not directed to children under the age of 13. We do not knowingly collect or solicit personal information from children under 13. If we discover that a user under 13 has provided personal information, we promptly delete such data from our servers.</p>
                </div>
            </div>
        </div>

        <!-- 8. Updates to this Policy -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">8</div>
                <div class="section-title">
                    <h2>التحديثات على هذه السياسة &bull; Changes to this Policy</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر لتعكس أي تغييرات في خدماتنا أو المتطلبات القانونية والتنظيمية. سيتم نشر أي تعديلات على هذه الصفحة مع تحديث "تاريخ السريان". نشجعك على مراجعة هذه السياسة دورياً.</p>
                </div>
                <div class="lang-panel panel-en">
                    <p>We may update this Privacy Policy periodically to reflect changes in our legal obligations, features, or operational practices. Any revisions will be published immediately on this page with an updated "Last Updated" date. Continued use of WiseLook constitutes acceptance of the revised policy.</p>
                </div>
            </div>
        </div>

        <!-- 9. Contact Us -->
        <div class="card">
            <div class="section-header">
                <div class="section-number">9</div>
                <div class="section-title">
                    <h2>معلومات التواصل &bull; Contact Information</h2>
                </div>
            </div>
            <div class="dual-content">
                <div class="lang-panel panel-ar">
                    <p>إذا كان لديك أي استفسارات أو أسئلة بخصوص سياسة الخصوصية أو ممارسات إدارة البيانات، يسعدنا تواصلك معنا عبر:</p>
                    <ul>
                        <li><strong>البريد الإلكتروني للدعم الفني:</strong> <a href="mailto:support@wiselook.org" class="action-link">support@wiselook.org</a></li>
                        <li><strong>الموقع الرسمي:</strong> <a href="https://wiselook.org" class="action-link">https://wiselook.org</a></li>
                    </ul>
                </div>
                <div class="lang-panel panel-en">
                    <p>If you have questions, inquiries, or feedback regarding this Privacy Policy or your data rights, please contact our support team:</p>
                    <ul>
                        <li><strong>Support Email:</strong> <a href="mailto:support@wiselook.org" class="action-link">support@wiselook.org</a></li>
                        <li><strong>Official Website:</strong> <a href="https://wiselook.org" class="action-link">https://wiselook.org</a></li>
                    </ul>
                </div>
            </div>
        </div>

    </main>

    <footer>
        <p>&copy; 2026 WiseLook. All rights reserved.</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem;">
            <a href="{{ route('terms') }}">الشروط والأحكام / Terms of Service</a> &bull; 
            <a href="{{ route('privacy') }}">سياسة الخصوصية / Privacy Policy</a> &bull; 
            <a href="{{ route('account.deletion') }}">حذف الحساب / Account Deletion</a>
        </p>
    </footer>

    <script>
        function setMode(mode) {
            document.querySelectorAll('.lang-btn').forEach(btn => btn.classList.remove('active'));
            const arPanels = document.querySelectorAll('.panel-ar');
            const enPanels = document.querySelectorAll('.panel-en');
            const containers = document.querySelectorAll('.dual-content');

            if (mode === 'ar') {
                document.getElementById('btnShowAr').classList.add('active');
                arPanels.forEach(el => el.style.display = 'block');
                enPanels.forEach(el => el.style.display = 'none');
                containers.forEach(el => el.style.gridTemplateColumns = '1fr');
                document.documentElement.setAttribute('dir', 'rtl');
            } else if (mode === 'en') {
                document.getElementById('btnShowEn').classList.add('active');
                arPanels.forEach(el => el.style.display = 'none');
                enPanels.forEach(el => {
                    el.style.display = 'block';
                    el.style.borderRight = 'none';
                    el.style.paddingRight = '0';
                });
                containers.forEach(el => el.style.gridTemplateColumns = '1fr');
                document.documentElement.setAttribute('dir', 'ltr');
            } else {
                document.getElementById('btnShowAll').classList.add('active');
                arPanels.forEach(el => el.style.display = 'block');
                enPanels.forEach(el => {
                    el.style.display = 'block';
                    el.style.borderRight = window.innerWidth > 768 ? '1px solid #edf2f7' : 'none';
                    el.style.paddingRight = window.innerWidth > 768 ? '1.5rem' : '0';
                });
                containers.forEach(el => el.style.gridTemplateColumns = window.innerWidth > 768 ? '1fr 1fr' : '1fr');
                document.documentElement.setAttribute('dir', 'rtl');
            }
        }
    </script>

</body>
</html>
