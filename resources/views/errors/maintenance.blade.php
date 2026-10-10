<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الموقع متوقف مؤقتاً للصيانة | حكماء العالم - WiseLook</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0F7A4D;
            --primary-dark: #0A5334;
            --primary-light: #E8F5EE;
            --accent: #E5A93C;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --bg-gradient: linear-gradient(135deg, #F8FAFC 0%, #EFF6F2 50%, #E2EFE7 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-gradient);
            color: var(--text-main);
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle background animated shapes */
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.5;
            animation: pulse 8s ease-in-out infinite alternate;
        }

        .circle-1 {
            width: 350px;
            height: 350px;
            background: rgba(15, 122, 77, 0.15);
            top: -50px;
            right: -50px;
        }

        .circle-2 {
            width: 300px;
            height: 300px;
            background: rgba(229, 169, 60, 0.15);
            bottom: -50px;
            left: -50px;
            animation-delay: 2s;
        }

        @keyframes pulse {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.15) translate(20px, 20px); }
        }

        .card-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 28px;
            padding: 48px 36px;
            max-width: 620px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(15, 122, 77, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.03);
            animation: fadeInDown 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-wrap {
            margin-bottom: 24px;
        }

        .logo-wrap img {
            max-height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));
        }

        .logo-fallback {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #FEF3C7;
            color: #92400E;
            padding: 8px 18px;
            border-radius: 9999px;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 24px;
            border: 1px solid #FDE68A;
        }

        .pulse-dot {
            width: 10px;
            height: 10px;
            background: #F59E0B;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            animation: pulse-dot 1.8s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        @keyframes pulse-dot {
            to {
                box-shadow: 0 0 0 12px rgba(245, 158, 11, 0);
            }
        }

        .icon-gear-wrap {
            width: 100px;
            height: 100px;
            margin: 0 auto 24px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 52px;
            position: relative;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
        }

        .icon-gear-wrap i {
            animation: rotateGear 12s linear infinite;
        }

        @keyframes rotateGear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        h1 {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .desc-text {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 32px;
            padding: 0 10px;
            white-space: pre-line;
        }

        .action-btns {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .btn-refresh {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 13px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(15, 122, 77, 0.25);
            text-decoration: none;
        }

        .btn-refresh:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 122, 77, 0.35);
        }

        .btn-login {
            background: #fff;
            color: var(--text-main);
            border: 1px solid #CBD5E1;
            padding: 13px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            text-decoration: none;
        }

        .btn-login:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
            color: var(--primary);
        }

        .footer-note {
            border-top: 1px solid #E2E8F0;
            padding-top: 20px;
            font-size: 13px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        @media (max-width: 480px) {
            .card-container {
                padding: 32px 20px;
                border-radius: 20px;
            }

            h1 {
                font-size: 22px;
            }

            .desc-text {
                font-size: 14.5px;
            }

            .action-btns {
                flex-direction: column;
            }

            .btn-refresh, .btn-login {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <div class="bg-circle circle-1"></div>
    <div class="bg-circle circle-2"></div>

    <div class="card-container">
        <!-- Logo -->
        <div class="logo-wrap">
            @if(file_exists(public_path('backend/assets/images/logo.png')))
                <img src="{{ asset('backend/assets/images/logo.png') }}" alt="مجلس الحكماء" onerror="this.style.display='none'; document.getElementById('logo-text').style.display='inline-flex';">
                <span id="logo-text" class="logo-fallback" style="display:none;"><i class='bx bxs-shield-alt-2'></i> مجلس الحكماء - WiseLook</span>
            @else
                <span class="logo-fallback"><i class='bx bxs-shield-alt-2'></i> مجلس الحكماء - WiseLook</span>
            @endif
        </div>

        <!-- Status Badge -->
        <div class="status-badge">
            <span class="pulse-dot"></span>
            أعمال الصيانة والتطوير جارية
        </div>

        <!-- Animated Icon -->
        <div class="icon-gear-wrap">
            <i class='bx bx-cog'></i>
        </div>

        <!-- Title -->
        <h1>الموقع متوقف مؤقتاً لأعمال التحديث</h1>

        <!-- Maintenance Message -->
        <p class="desc-text">
            {{ $maintenanceMessage ?? 'نقوم حالياً بإجراء بعض التحسينات والصيانة الدورية لتوفير تجربة أفضل وأكثر أماناً. سنعود للعمل بكامل طاقتنا في أقرب وقت ممكن.' }}
        </p>



        <!-- Footer -->
        <div class="footer-note">
            <i class='bx bx-heart text-danger'></i>
            نشكركم على تفهمكم وصبركم &bull; &copy; {{ date('Y') }} جميع الحقوق محفوظة
        </div>
    </div>

</body>
</html>
