<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز استعادة كلمة المرور</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f3;
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #191c1a;
            direction: rtl;
            text-align: right;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f6f3;
            padding: 40px 0;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 580px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 33, 18, 0.08);
            border: 1px solid #e1e3df;
        }
        .header {
            background: linear-gradient(135deg, #002112 0%, #1a5237 100%);
            padding: 35px 30px;
            text-align: center;
            color: #ffffff;
        }
        .header-title {
            font-size: 24px;
            font-weight: 700;
            margin: 10px 0 0 0;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 13px;
            color: #b5f0cb;
            margin-top: 5px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 35px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #003a23;
            margin-bottom: 15px;
        }
        .message-text {
            font-size: 15px;
            line-height: 1.7;
            color: #404943;
            margin-bottom: 25px;
        }
        .code-container {
            background-color: #f2f4f0;
            border: 2px dashed #1a5237;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
        }
        .code-label {
            font-size: 13px;
            color: #717972;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .code-digits {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #1a5237;
            font-family: 'Courier New', Courier, monospace;
            direction: ltr;
            display: inline-block;
        }
        .action-button-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #1a5237;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(26, 82, 55, 0.25);
            transition: background-color 0.3s ease;
        }
        .security-note {
            background-color: #fff8e6;
            border-right: 4px solid #caa800;
            padding: 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #554500;
            line-height: 1.6;
            margin-top: 25px;
        }
        .footer {
            background-color: #f8faf5;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #edeeea;
            font-size: 12px;
            color: #717972;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <!-- Header -->
            <div class="header">
                <div style="font-size: 38px; line-height: 1;">🏛️</div>
                <h1 class="header-title">مجلس الحكماء - Wiselook</h1>
                <div class="header-subtitle">منصة الحوار والتواصل الاجتماعي</div>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    أهلاً {{ $user->first_name ? $user->first_name : 'عزيزنا المستخدم' }} 👋
                </div>
                
                <p class="message-text">
                    لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة <strong>مجلس الحكماء</strong>. استخدم كود التحقق التالي لإكمال العملية:
                </p>

                <!-- OTP Code Block -->
                <div class="code-container">
                    <div class="code-label">رمز التحقق الخاص بك</div>
                    <div class="code-digits">{{ $code }}</div>
                </div>

                <!-- CTA Button -->
                <div class="action-button-wrapper">
                    <a href="{{ $resetUrl }}" class="action-button" target="_blank">
                        إعادة تعيين كلمة المرور مباشرة
                    </a>
                </div>

                <!-- Security Warning -->
                <div class="security-note">
                    ⚠️ <strong>تنبيه أمني:</strong> هذا الرمز صالحة لاستخدام واحد فقط وسينتهي خلال وقت قصير. إذا لم تكن أنت من طلب إعادة تعيين كلمة المرور، فيمكنك تجاهل هذا البريد بأمان وحسابك في أمان تام.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                &copy; {{ date('Y') }} مجلس الحكماء (Wiselook). جميع الحقوق محفوظة.<br>
                هذه الرسالة مرسلة تلقائياً، يرجى عدم الرد عليها.
            </div>
        </div>
    </div>
</body>
</html>
