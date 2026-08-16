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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #191c1a;
            direction: rtl;
            text-align: right;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f6f3;
            padding: 30px 0;
        }
        .main-card {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 560px;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0F7A4D;
            padding: 30px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            color: #ffffff;
        }
        .header-subtitle {
            font-size: 13px;
            color: #d1fae5;
            margin-top: 6px;
        }
        .content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 700;
            color: #064e3b;
            margin-bottom: 12px;
        }
        .message-text {
            font-size: 14px;
            line-height: 1.7;
            color: #334155;
            margin-bottom: 20px;
        }
        .code-container {
            background-color: #f0fdf4;
            border: 2px dashed #0F7A4D;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }
        .code-label {
            font-size: 12px;
            color: #047857;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .code-digits {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0F7A4D;
            font-family: Consolas, 'Courier New', monospace;
            direction: ltr;
            display: inline-block;
        }
        .action-button-wrapper {
            text-align: center;
            margin: 25px 0;
        }
        .action-button {
            display: inline-block;
            background-color: #0F7A4D;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
        }
        .security-note {
            background-color: #fffbeb;
            border-right: 4px solid #f59e0b;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 12px;
            color: #92400e;
            line-height: 1.6;
            margin-top: 20px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 24px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-card">
            <div class="header">
                <h1 class="header-title">مجلس الحكماء - Wiselook</h1>
                <div class="header-subtitle">منصة الحوار والتواصل الاجتماعي</div>
            </div>

            <div class="content">
                <div class="greeting">
                    أهلاً {{ $user->first_name ? $user->first_name : 'عزيزنا المستخدم' }}
                </div>
                
                <p class="message-text">
                    لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة <strong>مجلس الحكماء</strong>. استخدم رمز التحقق التالي لإكمال العملية:
                </p>

                <div class="code-container">
                    <div class="code-label">رمز التحقق الخاص بك</div>
                    <div class="code-digits">{{ $code }}</div>
                </div>

                <div class="action-button-wrapper">
                    <a href="{{ $resetUrl }}" class="action-button" target="_blank">
                        إعادة تعيين كلمة المرور مباشرة
                    </a>
                </div>

                <div class="security-note">
                    <strong>تنبيه أمني:</strong> هذا الرمز صالح للاستخدام لمرة واحدة فقط وسينتهي خلال وقت قصير. إذا لم تكن أنت من طلب إعادة تعيين كلمة المرور، فيمكنك تجاهل هذا البريد بأمان.
                </div>
            </div>

            <div class="footer">
                &copy; {{ date('Y') }} مجلس الحكماء (Wiselook). جميع الحقوق محفوظة.<br>
                هذه الرسالة مرسلة تلقائياً من نظام التوثيق.
            </div>
        </div>
    </div>
</body>
</html>
