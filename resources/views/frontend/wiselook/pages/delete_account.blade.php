<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حذف الحساب والبيانات | Account & Data Deletion - WiseLook</title>
    <meta name="description" content="Official account and data deletion instructions and web request form for WiseLook mobile app and website.">
    <style>
        :root {
            --primary: #1b4332;
            --primary-accent: #2ea85a;
            --danger: #dc2626;
            --danger-dark: #b91c1c;
            --text-main: #111827;
            --text-muted: #4b5563;
            --bg-page: #f4f7f5;
            --bg-card: #ffffff;
            --border: #e5e7eb;
            --soft-green: #ecfdf5;
            --soft-red: #fef2f2;
            --footer-bg: #0f172a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Tahoma, Arial, sans-serif;
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
            padding: 0.35rem 1rem;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.85rem;
            letter-spacing: 0.02em;
        }

        header h1 {
            font-size: 1.85rem;
            font-weight: 800;
            margin-bottom: 0.4rem;
        }

        header .subtitle-en {
            font-size: 1.05rem;
            opacity: 0.92;
            direction: ltr;
        }

        header .meta {
            margin-top: 0.65rem;
            font-size: 0.85rem;
            opacity: 0.85;
        }

        main {
            max-width: 900px;
            width: 100%;
            margin: 2rem auto;
            padding: 0 1.25rem;
            flex: 1;
        }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        }

        .section-title {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: baseline;
            gap: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .section-title h2 {
            font-size: 1.2rem;
            color: var(--primary);
            font-weight: 700;
        }

        .section-title .en-label {
            font-size: 0.92rem;
            color: var(--text-muted);
            font-weight: 600;
            direction: ltr;
        }

        .dual-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .col-ar {
            direction: rtl;
            text-align: right;
        }

        .col-en {
            direction: ltr;
            text-align: left;
            border-right: 1px solid #eef2f7;
            padding-right: 1.25rem;
        }

        ol, ul {
            padding-inline-start: 1.35rem;
            color: var(--text-muted);
        }

        li {
            margin-bottom: 0.55rem;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 0.85rem;
        }

        .pill-step {
            display: inline-block;
            background: var(--soft-green);
            color: var(--primary);
            border: 1px solid #a7f3d0;
            border-radius: 6px;
            padding: 0.1rem 0.5rem;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .alert {
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background: var(--soft-green);
            border: 1px solid #86efac;
            color: #14532d;
        }

        .alert-danger {
            background: var(--soft-red);
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .form-group {
            margin-bottom: 1.15rem;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        input[type="text"],
        textarea {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 0.75rem 0.9rem;
            font-size: 0.95rem;
            font-family: inherit;
            background: #f8fafc;
            transition: border-color 0.2s, background-color 0.2s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            background: #ffffff;
        }

        .checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            margin-bottom: 1.25rem;
            font-size: 0.92rem;
            color: var(--text-muted);
        }

        .checkbox-row input[type="checkbox"] {
            margin-top: 0.3rem;
            width: 18px;
            height: 18px;
            accent-color: var(--danger);
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s, transform 0.1s;
        }

        .btn:hover {
            opacity: 0.94;
        }

        .btn:active {
            transform: scale(0.99);
        }

        .btn-danger {
            background-color: var(--danger);
            color: #ffffff;
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .instant-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
            font-size: 0.93rem;
        }

        .data-table th,
        .data-table td {
            border: 1px solid var(--border);
            padding: 0.75rem;
            text-align: right;
            vertical-align: top;
        }

        .data-table th {
            background: #f8fafc;
            color: var(--primary);
            font-weight: 700;
        }

        .top-links {
            display: flex;
            justify-content: center;
            gap: 1.25rem;
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .top-links a {
            color: #d1fae5;
            text-decoration: underline;
        }

        footer {
            background-color: var(--footer-bg);
            color: #e2e8f0;
            padding: 1.75rem 1.25rem;
            text-align: center;
            border-top: 4px solid var(--primary-accent);
            margin-top: auto;
            font-size: 0.92rem;
        }

        footer a {
            color: #6ee7b7;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .dual-grid {
                grid-template-columns: 1fr;
            }
            .col-en {
                border-right: none;
                border-top: 1px solid #eef2f7;
                padding-right: 0;
                padding-top: 1rem;
            }
            .card {
                padding: 1.35rem;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="brand-badge">WiseLook</div>
        <h1>طلب حذف الحساب والبيانات</h1>
        <div class="subtitle-en">WiseLook Account &amp; Data Deletion</div>
        <div class="meta">https://wiselook.org | WiseLook Platform</div>
        <div class="top-links">
            <a href="{{ url('/') }}">الصفحة الرئيسية / Home</a>
            <a href="{{ route('terms') }}">الشروط والأحكام / Terms of Service</a>
            <a href="{{ route('privacy') }}">سياسة الخصوصية / Privacy Policy</a>
        </div>
    </header>

    <main>
        @if(session('deleted_immediately'))
            <div class="alert alert-success">
                <strong>تم حذف حسابك بنجاح / Your account has been permanently deleted.</strong><br>
                تم تسجيل خروجك وإزالة حسابك وبياناتك الشخصية من منصة WiseLook.
            </div>
        @endif

        @if(session('deletion_request_submitted'))
            <div class="alert alert-success">
                <strong>تم استلام طلب حذف الحساب بنجاح / Deletion Request Received</strong><br>
                رقم المرجع (Reference ID): <strong>{{ session('reference_code') }}</strong><br>
                سيتم التحقق من الهوية وإتمام حذف حسابك وبياناتك خلال مدة أقصاها 7 أيام عمل (ضمن سياسة WiseLook).
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @auth
            <div class="card instant-box">
                <div class="section-title">
                    <h2>حذف الحساب الفوري (أنت مسجل الدخول حالياً)</h2>
                    <span class="en-label">Instant Account Deletion (Signed In)</span>
                </div>
                <p>
                    الحساب الحالي / Signed in as:
                    <strong>{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}</strong>
                    ({{ auth()->user()->email ?? auth()->user()->phone_number ?? 'ID #' . auth()->id() }})
                </p>
                <form method="POST" action="{{ route('account.deletion.execute') }}" onsubmit="return confirm('هل أنت متأكد من حذف حسابك نهائياً؟ لا يمكن التراجع عن هذا الإجراء.');">
                    @csrf
                    <label class="checkbox-row">
                        <input type="checkbox" name="confirm_instant" value="1" required>
                        <span>أؤكد رغبتي في حذف حسابي وبياناتي نهائياً من WiseLook الآن. / I confirm that I want to permanently delete my WiseLook account and data immediately.</span>
                    </label>
                    <button type="submit" class="btn btn-danger">حذف حسابي الآن نهائياً / Delete My Account Now</button>
                </form>
            </div>
        @endauth

        <!-- Section 1: In-App Deletion Steps -->
        <div class="card">
            <div class="section-title">
                <h2>الطريقة الأولى: حذف الحساب من داخل تطبيق WiseLook</h2>
                <span class="en-label">Option 1: Delete Account In-App</span>
            </div>
            <div class="dual-grid">
                <div class="col-ar">
                    <p>يمكنك حذف حسابك مباشرة وفوراً من داخل تطبيق <strong>WiseLook</strong> عبر الخطوات التالية:</p>
                    <ol>
                        <li>افتح تطبيق <strong>WiseLook</strong> وسجّل الدخول إلى حسابك.</li>
                        <li>انتقل إلى صفحة <strong>الملف الشخصي</strong> ثم افتح <span class="pill-step">الإعدادات (Settings)</span>.</li>
                        <li>اختر <span class="pill-step">حذف الحساب</span> (حذف حسابك وبياناتك نهائياً من المنصة).</li>
                        <li>اضغط تأكيد الحذف في نافذة التأكيد ليتم حذف الحساب فوراً.</li>
                    </ol>
                </div>
                <div class="col-en">
                    <p>You can permanently and immediately delete your account directly from inside the <strong>WiseLook</strong> mobile app:</p>
                    <ol>
                        <li>Open the <strong>WiseLook</strong> app and sign in to your account.</li>
                        <li>Go to your <strong>Profile</strong> and open <span class="pill-step">Settings</span>.</li>
                        <li>Tap <span class="pill-step">Delete Account</span> (حذف الحساب).</li>
                        <li>Confirm deletion in the confirmation dialog. Your account is deleted immediately.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Section 2: Web Request Form -->
        <div class="card">
            <div class="section-title">
                <h2>الطريقة الثانية: طلب حذف الحساب عبر الويب (بدون التطبيق)</h2>
                <span class="en-label">Option 2: Request Deletion via Web Form</span>
            </div>
            <div class="dual-grid" style="margin-bottom: 1.25rem;">
                <div class="col-ar">
                    <p>إذا قمت بإزالة التطبيق من هاتفك ولا تستطيع الدخول، يمكنك إرسال طلب حذف حسابك وبياناتك من خلال النموذج التالي وسيتولى فريق الدعم تنفيذه بعد التحقق من الملكية:</p>
                </div>
                <div class="col-en">
                    <p>If you uninstalled the WiseLook app or cannot access it, submit this web request form to permanently delete your WiseLook account and associated data:</p>
                </div>
            </div>

            <form method="POST" action="{{ route('account.deletion.request') }}">
                @csrf
                <div class="form-group">
                    <label for="identifier">البريد الإلكتروني أو رقم الهاتف المسجل في WiseLook / Registered Email or Phone Number *</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        value="{{ old('identifier') }}"
                        placeholder="example@email.com or +9665XXXXXXXX"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="reason">ملاحظات إضافية أو سبب الطلب (اختياري) / Additional Details or Reason (Optional)</label>
                    <textarea
                        id="reason"
                        name="reason"
                        rows="3"
                        placeholder="اختياري / Optional"
                    >{{ old('reason') }}</textarea>
                </div>

                <label class="checkbox-row">
                    <input type="checkbox" name="confirm_deletion" value="1" required>
                    <span>
                        أقرّ أن حذف الحساب إجراء نهائي يؤدي إلى إزالة حسابي وبياناتي في WiseLook ولا يمكن التراجع عنه. /
                        I understand that deleting my WiseLook account is permanent and my account and personal data will be erased.
                    </span>
                </label>

                <button type="submit" class="btn btn-primary">إرسال طلب حذف الحساب / Submit Deletion Request</button>
            </form>
        </div>

        <!-- Section 3: Data Deleted & Retention Policy -->
        <div class="card">
            <div class="section-title">
                <h2>البيانات التي يتم حذفها وسياسة الاحتفاظ</h2>
                <span class="en-label">Data Deleted &amp; Retention Policy</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>الفئة / Category</th>
                        <th>التفاصيل (العربية)</th>
                        <th style="direction:ltr; text-align:left;">Details (English)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>بيانات الحساب المحذوفة<br><span style="direction:ltr;display:inline-block;">Deleted Account Data</span></strong></td>
                        <td>الاسم، البريد الإلكتروني، رقم الهاتف، الصورة الشخصية، النبذة، وبيانات تسجيل الدخول.</td>
                        <td style="direction:ltr; text-align:left;">Full name, email address, phone number, profile photo, bio, and authentication credentials.</td>
                    </tr>
                    <tr>
                        <td><strong>المحتوى والتفاعلات<br><span style="direction:ltr;display:inline-block;">User Content &amp; Activity</span></strong></td>
                        <td>المواضيع (المنشورات)، الاستطلاعات، التعليقات، الإعجابات، المحفوظات، القصص، عضوية المجموعات، ورسائل الدردشة ورموز الإشعارات (FCM).</td>
                        <td style="direction:ltr; text-align:left;">Posts, polls, comments, reactions, saved posts, stories, group memberships, chat messages, and push notification tokens (FCM).</td>
                    </tr>
                    <tr>
                        <td><strong>مدة التنفيذ والاحتفاظ<br><span style="direction:ltr;display:inline-block;">Timeline &amp; Retention</span></strong></td>
                        <td>الحذف من داخل التطبيق فوري. طلبات الويب تُنفذ خلال <strong>7 أيام عمل</strong> بحد أقصى. لا نحتفظ بأي بيانات شخصية بعد التنفيذ باستثناء سجلات الأمان التقنية الضرورية والتي تُحذف تلقائياً خلال <strong>30 يوماً</strong>.</td>
                        <td style="direction:ltr; text-align:left;">In-app deletion takes effect immediately. Web requests are processed within <strong>7 business days</strong>. No personal profile data is retained after deletion; minimal security/audit server logs (if any) are automatically purged within <strong>30 days</strong>.</td>
                    </tr>
                    <tr>
                        <td><strong>التواصل للدعم<br><span style="direction:ltr;display:inline-block;">Support Contact</span></strong></td>
                        <td>البريد الرسمي للدعم: <a href="mailto:support@wiselook.com">support@wiselook.com</a></td>
                        <td style="direction:ltr; text-align:left;">Official support email: <a href="mailto:support@wiselook.com">support@wiselook.com</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>© 2026 WiseLook (<a href="https://wiselook.org">https://wiselook.org</a>). All rights reserved.</p>
    </footer>

</body>
</html>
