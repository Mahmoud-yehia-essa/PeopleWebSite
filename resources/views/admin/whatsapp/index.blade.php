@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #25D366; padding-left: 15px;">
            <i class="bx bxl-whatsapp me-1 font-24"></i> الربط مع الواتساب (WhatsApp Gateway)
        </div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">إدارة جلسة الواتساب والـ API</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto" id="topStatusIndicator">
            @if(!empty($status['is_connected']))
                <span class="badge bg-success px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                    <span class="pulse-dot bg-white"></span>
                    <span>متصل: +{{ $status['phone'] ?? $settings->connected_phone }}</span>
                </span>
            @elseif(($status['status'] ?? '') === 'reconnecting')
                <span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                    <i class="bx bx-loader-alt bx-spin fs-5"></i>
                    <span>جاري استعادة الاتصال...</span>
                </span>
            @elseif(($status['status'] ?? '') === 'waiting_for_scan')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                    <i class="bx bx-qr-scan fs-5"></i>
                    <span>بانتظار مسح رمز QR</span>
                </span>
            @elseif(empty($status['online']) || ($status['status'] ?? '') === 'service_offline')
                <span class="badge bg-danger px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                    <i class="bx bx-power-off fs-5"></i>
                    <span>الخادم متوقف</span>
                </span>
            @else
                <span class="badge bg-secondary px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                    <i class="bx bx-power-off fs-5"></i>
                    <span>غير متصل</span>
                </span>
            @endif
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div class="container-fluid">
        <!-- Session Alert Messages -->
        @if(session('message'))
            <div class="alert alert-{{ session('alert-type') == 'success' ? 'success' : (session('alert-type') == 'warning' ? 'warning' : 'danger') }} border-0 bg-{{ session('alert-type') == 'success' ? 'success' : (session('alert-type') == 'warning' ? 'warning' : 'danger') }} alert-dismissible fade show text-white mb-4 shadow-sm" style="border-radius: 12px;">
                <div class="d-flex align-items-center">
                    <div class="font-35 text-white">
                        @if(session('alert-type') == 'success')
                            <i class="bx bx-check-circle"></i>
                        @elseif(session('alert-type') == 'warning')
                            <i class="bx bx-error-circle"></i>
                        @else
                            <i class="bx bx-x-circle"></i>
                        @endif
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 text-white font-weight-bold">تنبيه النظام</h6>
                        <div>{{ session('message') }}</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <!-- Connection State Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #25D366 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small font-weight-bold">حالة الاتصال</span>
                                <h5 class="mb-1 font-weight-bold" id="cardStatusText" style="color: {{ !empty($status['is_connected']) ? '#059669' : '#dc2626' }};">
                                    {{ !empty($status['is_connected']) ? 'متصل بنجاح' : 'غير متصل' }}
                                </h5>
                                <small class="text-muted d-block" id="cardConnectedPhone">
                                    {{ !empty($status['phone']) ? '+' . $status['phone'] : 'لا يوجد رقم مقترن' }}
                                </small>
                            </div>
                            <div class="stat-icon-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #ecfdf5; color: #059669;">
                                <i class="bx bxl-whatsapp fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Sent Messages Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #3b82f6 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small font-weight-bold">إجمالي الرسائل المرسلة</span>
                                <h3 class="mb-0 font-weight-bold text-dark">{{ number_format($totalSent ?? 0) }}</h3>
                                <small class="text-success"><i class="bx bx-check-double me-1"></i> عبر الواتساب</small>
                            </div>
                            <div class="stat-icon-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #eff6ff; color: #2563eb;">
                                <i class="bx bx-send fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today Sent Messages Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #8b5cf6 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small font-weight-bold">رسائل اليوم</span>
                                <h3 class="mb-0 font-weight-bold text-dark">{{ number_format($todaySent ?? 0) }}</h3>
                                <small class="text-primary"><i class="bx bx-time-five me-1"></i> خلال آخر 24 ساعة</small>
                            </div>
                            <div class="stat-icon-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #f5f3ff; color: #7c3aed;">
                                <i class="bx bx-message-dots fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Engine Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm h-100 stat-card" style="border-radius: 14px; border-right: 5px solid #f59e0b !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="d-block mb-1 text-muted small font-weight-bold">المحرك المعتمد</span>
                                <h5 class="mb-1 font-weight-bold text-dark">
                                    {{ $settings->engine === 'textmebot' ? 'بوابة TextMeBot' : 'السيرفر المباشر (Baileys)' }}
                                </h5>
                                <span class="badge bg-light-warning text-warning border border-warning-subtle" style="font-size: 0.72rem;">
                                    {{ $settings->engine === 'textmebot' ? 'سحابي خارجي' : 'داخلي مستقل' }}
                                </span>
                            </div>
                            <div class="stat-icon-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; border-radius: 12px; background: #fffbeb; color: #d97706;">
                                <i class="bx bx-server fs-2"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Navigation Tabs -->
        <div class="card shadow-sm border-0" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom p-3">
                <ul class="nav nav-pills card-header-pills gap-2" id="whatsappTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab', 'connect') == 'connect' ? 'active' : '' }}" id="connect-tab" data-bs-toggle="pill" data-bs-target="#connectTabPane" type="button" role="tab">
                            <i class="bx bx-qr-scan me-1"></i> ربط الرقم (QR Code)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'send_message' ? 'active' : '' }}" id="send-tab" data-bs-toggle="pill" data-bs-target="#sendTabPane" type="button" role="tab">
                            <i class="bx bx-paper-plane me-1"></i> إرسال رسالة تجريبية
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'logs' ? 'active' : '' }}" id="logs-tab" data-bs-toggle="pill" data-bs-target="#logsTabPane" type="button" role="tab">
                            <i class="bx bx-history me-1"></i> سجل الرسائل المرسلة
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'api_docs' ? 'active' : '' }}" id="api-tab" data-bs-toggle="pill" data-bs-target="#apiTabPane" type="button" role="tab">
                            <i class="bx bx-code-curly me-1"></i> توثيق واجهة البرمجة (REST API)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link custom-tab-btn {{ session('active_tab') == 'settings' ? 'active' : '' }}" id="settings-tab" data-bs-toggle="pill" data-bs-target="#settingsTabPane" type="button" role="tab">
                            <i class="bx bx-cog me-1"></i> إعدادات المحرك
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="whatsappTabsContent">

                    <!-- ======================================================== -->
                    <!-- TAB 1: QR Code & Connection -->
                    <!-- ======================================================== -->
                    <div class="tab-pane fade {{ session('active_tab', 'connect') == 'connect' ? 'show active' : '' }}" id="connectTabPane" role="tabpanel">
                        <div class="row align-items-center justify-content-center">
                            
                            <!-- Connected View Container -->
                            <div class="col-lg-8" id="connectedContainer" style="display: {{ !empty($status['is_connected']) ? 'block' : 'none' }};">
                                <div class="card border-0 shadow-sm p-4 text-center" style="background: linear-gradient(145deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 16px; border: 2px solid #86efac !important;">
                                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: #22c55e; color: white;">
                                        <i class="bx bx-check font-35"></i>
                                    </div>
                                    <h4 class="font-weight-bold text-success mb-2">رقمك متصل وجاهز للعمل!</h4>
                                    <p class="text-secondary mb-3">تم ربط حساب الواتساب بنجاح، ويمكنك الآن إرسال الرسائل للمستخدمين عبر الـ API أو لوحة التحكم.</p>

                                    <div class="bg-white p-3 rounded-3 shadow-sm d-inline-block mx-auto mb-4" style="min-width: 320px; border: 1px solid #bbf7d0;">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-muted small">رقم الهاتف المتصل:</span>
                                            <span class="font-weight-bold text-dark fs-6" id="connectedPhoneDisplay">+{{ $status['phone'] ?? $settings->connected_phone }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="text-muted small">اسم الحساب:</span>
                                            <span class="font-weight-bold text-dark" id="connectedNameDisplay">{{ $status['name'] ?? $settings->connected_name ?? 'WiseLook WhatsApp' }}</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted small">تاريخ الاتصال:</span>
                                            <span class="text-muted small">{{ $settings->last_connected_at ? $settings->last_connected_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i') }}</span>
                                        </div>
                                    </div>

                                    <div>
                                        <form action="{{ route('admin.whatsapp.disconnect') }}" method="POST" onsubmit="return confirm('هل أنت متأكد من رغبتك في قطع الاتصال وفك الارتباط؟ ستحتاج لمسح الـ QR مجدداً.');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm">
                                                <i class="bx bx-power-off me-1"></i> قطع الاتصال وفك الارتباط
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Disconnected / Scan QR Container -->
                            <div class="col-lg-10" id="scanQrContainer" style="display: {{ empty($status['is_connected']) ? 'block' : 'none' }};">
                                <div class="row align-items-center g-4">
                                    <!-- QR Box Column -->
                                    <div class="col-md-5 text-center">
                                        <div class="p-4 bg-white rounded-4 shadow-sm border position-relative qr-wrapper d-inline-block">
                                            @php
                                                $isOffline = empty($status['online']) || (($status['status'] ?? '') === 'service_offline');
                                                $isReconnecting = (($status['status'] ?? '') === 'reconnecting');
                                                $hasQr = !empty($status['qr']);
                                            @endphp

                                            <!-- 1. Service Offline Container -->
                                            <div id="serviceOfflineContainer" style="display: {{ $isOffline ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center p-3">
                                                <div class="text-danger mb-2">
                                                    <i class="bx bx-power-off" style="font-size: 3rem;"></i>
                                                </div>
                                                <h6 class="font-weight-bold text-danger mb-1">خادم الواتساب متوقف</h6>
                                                <p class="text-muted small mb-3" style="font-size: 0.78rem;">محرك Node.js غير نشط على المنفذ 3001.</p>
                                                <button type="button" class="btn btn-sm btn-success px-3 py-2 rounded-pill shadow-sm" id="btnStartService" onclick="startWhatsAppService()">
                                                    <i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن
                                                </button>
                                            </div>

                                            <!-- 2. Reconnecting Container -->
                                            <div id="reconnectingContainer" style="display: {{ (!$isOffline && $isReconnecting) ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center p-3">
                                                <div class="spinner-border text-primary mb-3" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
                                                <h6 class="font-weight-bold text-primary mb-1">جاري استعادة الاتصال...</h6>
                                                <p class="text-muted small mb-2" style="font-size: 0.78rem;">يتم الاتصال تلقائياً بالجلسة السابقة دون الحاجة لإعادة المسح.</p>
                                                <span class="badge bg-light text-primary border" id="reconnectingPhoneBadge" style="display: {{ !empty($status['phone']) ? 'inline-block' : 'none' }};">{{ !empty($status['phone']) ? ('+' . $status['phone']) : '' }}</span>
                                            </div>

                                            <!-- 3. Loading QR Spinner -->
                                            <div id="qrLoadingSpinner" style="display: {{ (!$isOffline && !$isReconnecting && !$hasQr) ? 'flex' : 'none' }}; width: 240px; height: 240px;" class="align-items-center justify-content-center flex-column text-center">
                                                <div class="spinner-border text-success mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                                                <span class="text-muted small fw-bold">جاري تهيئة رمز QR...</span>
                                            </div>

                                            <!-- 4. QR Image -->
                                            <div id="qrImageWrapper" style="display: {{ (!$isOffline && !$isReconnecting && $hasQr) ? 'block' : 'none' }}; position: relative;">
                                                <img id="whatsappQrImage" src="{{ $status['qr'] ?? '' }}" alt="WhatsApp QR Code" class="img-fluid rounded" style="width: 240px; height: 240px; object-fit: contain;">
                                                <div class="scanner-laser-line"></div>
                                            </div>

                                            <div class="mt-3">
                                                <span class="badge bg-light text-secondary border px-3 py-2 small" id="qrRefreshNotice">
                                                    @if($isOffline)
                                                        <i class="bx bx-error text-danger me-1"></i> الخادم متوقف
                                                    @elseif($isReconnecting)
                                                        <i class="bx bx-loader bx-spin text-primary me-1"></i> جاري استعادة الاتصال...
                                                    @else
                                                        <i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instructions Column -->
                                    <div class="col-md-7">
                                        <div class="ps-md-3">
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-weight-bold mb-2">
                                                <i class="bx bx-shield-quarter me-1"></i> ربط آمن ومباشر
                                            </span>
                                            <h4 class="font-weight-bold text-dark mb-3">خطوات ربط رقم الواتساب:</h4>
                                            
                                            <ol class="list-group list-group-numbered border-0 mb-4">
                                                <li class="list-group-item border-0 px-0 d-flex align-items-start gap-2">
                                                    <div>افتح تطبيق <strong>واتساب (WhatsApp)</strong> على هاتفك الشخصي.</div>
                                                </li>
                                                <li class="list-group-item border-0 px-0 d-flex align-items-start gap-2">
                                                    <div>اضغط على <strong>القائمة (ثلاث نقاط)</strong> في أندرويد أو <strong>الإعدادات</strong> في آيفون.</div>
                                                </li>
                                                <li class="list-group-item border-0 px-0 d-flex align-items-start gap-2">
                                                    <div>اختر <strong>الأجهزة المرتبطة (Linked Devices)</strong> ثم اضغط على <strong>ربط جهاز (Link a Device)</strong>.</div>
                                                </li>
                                                <li class="list-group-item border-0 px-0 d-flex align-items-start gap-2">
                                                    <div>وجّه كاميرا هاتفك نحو رمز <strong>QR Code</strong> المقابل، وسيتم الربط تلقائياً خلال ثوانٍ!</div>
                                                </li>
                                            </ol>

                                            <!-- Alternative: Pairing Code -->
                                            <div class="card border bg-light-subtle p-3 rounded-3" style="border: 1px dashed #cbd5e1 !important;">
                                                <h6 class="font-weight-bold text-dark mb-2">
                                                    <i class="bx bx-mobile-alt text-primary me-1"></i> أو الربط بواسطة كود الاقتران (Pairing Code)
                                                </h6>
                                                <p class="text-muted small mb-3">إذا كانت كاميرا هاتفك لا تعمل، أدخل رقم هاتفك مع كود الدولة لتوليد كود مكون من 8 أحرف لربطه في الواتساب مباشرة.</p>
                                                
                                                <div class="input-group">
                                                    <input type="text" id="pairingPhoneInput" class="form-control" placeholder="مثال: 966500000000 أو 201000000000">
                                                    <button type="button" id="btnRequestPairingCode" class="btn btn-outline-success">
                                                        <i class="bx bx-key me-1"></i> طلب كود الاقتران
                                                    </button>
                                                </div>

                                                <div id="pairingCodeResult" class="mt-3 p-3 bg-white border rounded text-center" style="display: none;">
                                                    <span class="text-muted small d-block mb-1">أدخل هذا الكود في هاتفك (ربط عبر رقم الهاتف):</span>
                                                    <h3 class="font-monospace text-success font-weight-bold mb-0 letter-spacing-2" id="pairingCodeDisplay"></h3>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 2: Send Test Message -->
                    <!-- ======================================================== -->
                    <div class="tab-pane fade {{ session('active_tab') == 'send_message' ? 'show active' : '' }}" id="sendTabPane" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <form action="{{ route('admin.whatsapp.send') }}" method="POST" id="sendMessageForm">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label font-weight-bold text-dark">
                                            <i class="bx bx-phone text-success me-1"></i> رقم هاتف المستلم (مع كود الدولة) <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-white text-muted border-light-success"><i class="bx bx-globe"></i></span>
                                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" placeholder="مثال: 966501234567 أو 201012345678 (بدون علامة +)" required>
                                        </div>
                                        <small class="text-muted d-block mt-1">يجب أن يبدأ الرقم بمفتاح الدولة مباشرة (مثال: للسعودية 966، لمصر 20، للإمارات 971).</small>
                                        @error('phone')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Quick Templates -->
                                    <div class="mb-3">
                                        <label class="form-label text-muted small font-weight-bold">قوالب نصوص سريعة:</label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="مرحباً بك في منصة حكماء العالم! نتشرف بانضمامك إلينا.">ترحيب</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="رمز التحقق الخاص بك لمنصة حكماء العالم هو: 849201. لا تشارك هذا الرمز مع أي شخص.">رمز OTP</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="تنبيه: تم نشر موضوع جديد ومميز يهمك في مجتمع حكماء العالم. شاهده الآن عبر التطبيق!">تنبيه محتوى</button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill btn-quick-template" data-text="شكراً لتواصلك مع إدارة حكماء العالم. تم استلام رسالتك وسنقوم بالرد عليك في أقرب وقت.">خدمة عملاء</button>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label font-weight-bold text-dark">
                                            <i class="bx bx-message-detail text-success me-1"></i> نص الرسالة <span class="text-danger">*</span>
                                        </label>
                                        <textarea name="message" id="messageContent" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="اكتب نص الرسالة التي ستصل للمستلم عبر الواتساب..." required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" id="btnSubmitSend" class="btn btn-success btn-lg px-5 rounded-pill shadow-sm d-flex align-items-center gap-2" style="background: #25D366; border: none;">
                                            <i class="bx bxl-whatsapp font-20"></i>
                                            <span>إرسال الرسالة الآن</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 3: Message Logs -->
                    <!-- ======================================================== -->
                    <div class="tab-pane fade {{ session('active_tab') == 'logs' ? 'show active' : '' }}" id="logsTabPane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="font-weight-bold text-dark mb-0">سجل الرسائل المرسلة:</h5>
                            @if($messages->isNotEmpty())
                                <form action="{{ route('admin.whatsapp.clear_logs') }}" method="POST" onsubmit="return confirm('هل تريد بالتأكيد مسح جميع سجلات الرسائل؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bx bx-trash me-1"></i> مسح السجل بالكامل
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>رقم المستلم</th>
                                        <th>نص الرسالة</th>
                                        <th>المحرك</th>
                                        <th>المصدر</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإرسال</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($messages as $msg)
                                        <tr>
                                            <td>{{ $msg->id }}</td>
                                            <td class="font-weight-bold text-dark" dir="ltr">+{{ $msg->recipient_phone }}</td>
                                            <td style="max-width: 320px;">
                                                <div class="text-truncate">{{ $msg->message }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    {{ $msg->engine_used === 'textmebot' ? 'TextMeBot' : 'Local Baileys' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($msg->sender_type === 'api')
                                                    <span class="badge bg-info-subtle text-info border">API</span>
                                                @elseif($msg->sender_type === 'system')
                                                    <span class="badge bg-purple-subtle text-purple border">النظام</span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary border">لوحة التحكم</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($msg->status === 'sent')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                        <i class="bx bx-check me-1"></i> ناجح
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" title="{{ $msg->error_message }}">
                                                        <i class="bx bx-x me-1"></i> فاشل
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $msg->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bx bx-message-square-x fs-1 opacity-50 mb-2"></i>
                                                <p class="mb-0">لا توجد رسائل واتساب مسجلة في السجل حتى الآن.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $messages->links() }}
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 4: API Documentation (Like TextMeBot) -->
                    <!-- ======================================================== -->
                    <div class="tab-pane fade {{ session('active_tab') == 'api_docs' ? 'show active' : '' }}" id="apiTabPane" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-12">
                                <!-- API Key Box -->
                                <div class="card border shadow-none bg-light p-4 mb-4 rounded-3">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div>
                                            <span class="text-muted small d-block mb-1 font-weight-bold">مفتاح الـ API السري الخاص بك (API Key):</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <code class="fs-5 bg-white px-3 py-2 rounded border font-monospace text-primary" id="apiKeyDisplay">{{ $settings->api_key }}</code>
                                                <button class="btn btn-outline-secondary btn-sm" onclick="copyText('{{ $settings->api_key }}')">
                                                    <i class="bx bx-copy"></i> نسخ
                                                </button>
                                            </div>
                                        </div>

                                        <form action="{{ route('admin.whatsapp.regenerate_api_key') }}" method="POST" onsubmit="return confirm('تنبيه: تجديد المفتاح سيؤدي لتوقف أي كود أو تكامل خارجي يستخدم المفتاح القديم. هل تريد المتابعة؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bx bx-refresh"></i> توليد مفتاح جديد
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <h5 class="font-weight-bold text-dark mb-3"><i class="bx bx-terminal text-success me-1"></i> طرق إرسال الرسائل عبر الـ API (مثل TextMeBot تماماً):</h5>

                                <!-- METHOD 1: GET REQUEST -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-white py-3">
                                        <span class="badge bg-success me-2 px-3 py-1">GET</span>
                                        <strong class="text-dark">الإرسال البسيط عبر الرابط (URL Parameter)</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small">يمكنك إرسال رسالة مباشرة بمجرد طلب هذا الرابط من المتصفح أو من أي كود برمجي:</p>
                                        
                                        <div class="p-3 bg-dark rounded text-white font-monospace text-break mb-3 position-relative" dir="ltr">
                                            <span>{{ url('/api/whatsapp/send') }}?apikey={{ $settings->api_key }}&phone=966500000000&text=مرحباً+بك</span>
                                            <button class="btn btn-sm btn-light position-absolute top-0 end-0 m-2" onclick="copyText('{{ url('/api/whatsapp/send') }}?apikey={{ $settings->api_key }}&phone=966500000000&text=Hello')">
                                                <i class="bx bx-copy"></i>
                                            </button>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>المعامل (Parameter)</th>
                                                        <th>النوع</th>
                                                        <th>مطلوب؟</th>
                                                        <th>الوصف</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><code>apikey</code></td>
                                                        <td>String</td>
                                                        <td>نعم</td>
                                                        <td>مفتاح الـ API السري الخاص بك.</td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>phone</code> أو <code>recipient</code></td>
                                                        <td>String</td>
                                                        <td>نعم</td>
                                                        <td>رقم هاتف المستلم مسبوقاً بكود الدولة وبدون أصفار أو زائد (مثال: <code>966501234567</code>).</td>
                                                    </tr>
                                                    <tr>
                                                        <td><code>text</code> أو <code>message</code></td>
                                                        <td>String</td>
                                                        <td>نعم</td>
                                                        <td>نص الرسالة المراد إرسالها.</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- METHOD 2: POST REQUEST (JSON) -->
                                <div class="card border mb-4">
                                    <div class="card-header bg-white py-3">
                                        <span class="badge bg-primary me-2 px-3 py-1">POST</span>
                                        <strong class="text-dark">الإرسال عبر طلب POST (JSON Body)</strong>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted small">Endpoint: <code>{{ url('/api/whatsapp/send') }}</code></p>
                                        <div class="p-3 bg-dark rounded text-white font-monospace text-break mb-3" dir="ltr">
<pre class="mb-0 text-white">curl -X POST "{{ url('/api/whatsapp/send') }}" \
  -H "Content-Type: application/json" \
  -H "x-api-key: {{ $settings->api_key }}" \
  -d '{
    "phone": "966500000000",
    "message": "مرحباً بك في منصة حكماء العالم!"
  }'</pre>
                                        </div>
                                    </div>
                                </div>

                                <!-- METHOD 3: PHP CODE EXAMPLE -->
                                <div class="card border">
                                    <div class="card-header bg-white py-3">
                                        <i class="bx bxl-php fs-5 text-primary me-1"></i>
                                        <strong class="text-dark">مثال كود PHP جاهز للاستخدام في أي مشروع أو سكربت:</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="p-3 bg-dark rounded text-white font-monospace text-break" dir="ltr">
<pre class="mb-0 text-white">&lt;?php
$apikey  = "{{ $settings->api_key }}";
$phone   = "966500000000"; // رقم المستلم بكود الدولة
$message = "رمز التحقق هو: 123456";

$url = "{{ url('/api/whatsapp/send') }}?" . http_build_query([
    'apikey' => $apikey,
    'phone'  => $phone,
    'text'   => $message,
]);

$response = file_get_contents($url);
$result = json_decode($response, true);

if ($result['success'] ?? false) {
    echo "تم الإرسال بنجاح!";
} else {
    echo "فشل الإرسال: " . ($result['message'] ?? 'Unknown error');
}
?&gt;</pre>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- ======================================================== -->
                    <!-- TAB 5: Engine Settings -->
                    <!-- ======================================================== -->
                    <div class="tab-pane fade {{ session('active_tab') == 'settings' ? 'show active' : '' }}" id="settingsTabPane" role="tabpanel">
                        <div class="row justify-content-center">
                            <div class="col-lg-8">
                                <form action="{{ route('admin.whatsapp.settings.update') }}" method="POST">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label font-weight-bold text-dark">
                                            <i class="bx bx-slider-alt text-success me-1"></i> اختيار محرك تشغيل الواتساب <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex flex-column gap-3 p-3 bg-light rounded-3 border">
                                            <div class="form-check">
                                                <input class="form-check-input engine-radio" type="radio" name="engine" id="engineLocal" value="local" {{ $settings->engine === 'local' ? 'checked' : '' }}>
                                                <label class="form-check-label font-weight-bold text-dark" for="engineLocal" style="cursor: pointer;">
                                                    <i class="bx bx-server text-success me-1"></i> السيرفر الداخلي المباشر (Built-in Baileys Node Engine) - موصى به
                                                </label>
                                                <small class="text-muted d-block mt-1 ps-4">يعمل مجاناً وبشكل مستقل تماماً على السيرفر دون أي اشتراكات أو أطراف ثالثة عبر مكتبة Baileys الرسمية للواتساب.</small>
                                            </div>

                                            <hr class="my-1">

                                            <div class="form-check">
                                                <input class="form-check-input engine-radio" type="radio" name="engine" id="engineTextMeBot" value="textmebot" {{ $settings->engine === 'textmebot' ? 'checked' : '' }}>
                                                <label class="form-check-label font-weight-bold text-dark" for="engineTextMeBot" style="cursor: pointer;">
                                                    <i class="bx bx-cloud text-primary me-1"></i> بوابة TextMeBot السحابية (TextMeBot.com API)
                                                </label>
                                                <small class="text-muted d-block mt-1 ps-4">إذا كنت تمتلك حساباً ومفتاح API في موقع textmebot.com وتريد الربط والارسال من خلال بوابتهم السحابية.</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Local Engine Config -->
                                    <div id="localConfigSection" class="card border p-3 mb-4 rounded-3" style="display: {{ $settings->engine === 'local' ? 'block' : 'none' }};">
                                        <h6 class="font-weight-bold text-dark mb-2">إعدادات السيرفر الداخلي:</h6>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">رابط خادم الواتساب الداخلي (Local Server URL):</label>
                                            <input type="text" name="local_server_url" value="{{ old('local_server_url', $settings->local_server_url ?: 'http://127.0.0.1:3001') }}" class="form-control" placeholder="http://127.0.0.1:3001">
                                            <small class="text-muted d-block mt-1">المنفذ الافتراضي لمحرك Baileys هو <code>http://127.0.0.1:3001</code>.</small>
                                        </div>
                                    </div>

                                    <!-- TextMeBot Engine Config -->
                                    <div id="textmebotConfigSection" class="card border p-3 mb-4 rounded-3" style="display: {{ $settings->engine === 'textmebot' ? 'block' : 'none' }};">
                                        <h6 class="font-weight-bold text-dark mb-2">بيانات حساب TextMeBot:</h6>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">مفتاح TextMeBot API Key:</label>
                                            <input type="text" name="textmebot_api_key" value="{{ old('textmebot_api_key', $settings->textmebot_api_key) }}" class="form-control" placeholder="أدخل مفتاح TextMeBot ApiKey الخاص بك">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small text-muted">رقم الهاتف المسجل في TextMeBot:</label>
                                            <input type="text" name="textmebot_phone" value="{{ old('textmebot_phone', $settings->textmebot_phone) }}" class="form-control" placeholder="مثال: 966500000000">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm">
                                            <i class="bx bx-save me-1"></i> حفظ التغييرات
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.06) !important;
    }
    .custom-tab-btn {
        color: #475569;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 10px !important;
        transition: all 0.2s ease;
    }
    .custom-tab-btn:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    .custom-tab-btn.active {
        background-color: #25D366 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(37, 211, 102, 0.35);
    }
    .scanner-laser-line {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, #25D366, transparent);
        box-shadow: 0 0 12px #25D366;
        animation: scanAnim 2.2s infinite ease-in-out;
    }
    @keyframes scanAnim {
        0% { top: 5%; opacity: 0.8; }
        50% { top: 95%; opacity: 1; }
        100% { top: 5%; opacity: 0.8; }
    }
    .pulse-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        animation: pulseEffect 1.5s infinite;
    }
    @keyframes pulseEffect {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(255, 255, 255, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
    }
</style>

<script>
    function copyText(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('تم نسخ النص بنجاح!');
        }, function(err) {
            prompt('انسخ النص التالي:', text);
        });
    }

    $(document).ready(function() {
        // نصوص القوالب السريعة
        $('.btn-quick-template').on('click', function() {
            var text = $(this).data('text');
            $('#messageContent').val(text);
        });

        // تبديل إعدادات المحرك
        $('.engine-radio').on('change', function() {
            if ($('#engineLocal').is(':checked')) {
                $('#localConfigSection').slideDown();
                $('#textmebotConfigSection').slideUp();
            } else {
                $('#localConfigSection').slideUp();
                $('#textmebotConfigSection').slideDown();
            }
        });

        // طلب كود الاقتران عبر الهاتف (Pairing Code)
        $('#btnRequestPairingCode').on('click', function() {
            var phone = $('#pairingPhoneInput').val().trim();
            if (!phone) {
                alert('يرجى إدخال رقم الهاتف مع كود الدولة أولاً.');
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> جاري الطلب...');

            $.ajax({
                url: "{{ route('admin.whatsapp.pairing_code') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-key me-1"></i> طلب كود الاقتران');
                    if (res.success && res.pairing_code) {
                        $('#pairingCodeDisplay').text(res.pairing_code);
                        $('#pairingCodeResult').slideDown();
                    } else {
                        alert(res.message || 'تعذر توليد كود الاقتران');
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bx bx-key me-1"></i> طلب كود الاقتران');
                    var msg = xhr.responseJSON ? xhr.responseJSON.message : 'خطأ في الاتصال بالسيرفر';
                    alert(msg);
                }
            });
        });

        // تشغيل خادم الواتساب يدوياً عبر زر الواجهة
        window.startWhatsAppService = function() {
            var btn = $('#btnStartService');
            btn.prop('disabled', true).html('<i class="bx bx-loader bx-spin me-1"></i> جاري تشغيل الخادم...');

            $.ajax({
                url: "{{ route('admin.whatsapp.start_service') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن');
                    if (res && res.message) {
                        alert(res.message);
                    }
                    setTimeout(checkWhatsAppStatus, 1500);
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="bx bx-play me-1"></i> تشغيل خادم الواتساب الآن');
                    alert('تعذر إرسال أمر التشغيل تلقائياً. يمكنك تشغيله يدوياً عبر SSH.');
                }
            });
        };

        // الفحص اللحظي لحالة الاتصال ورمز QR (Live Status Polling كل 3 ثوانٍ)
        var pollTimer = null;

        function checkWhatsAppStatus() {
            $.ajax({
                url: "{{ route('admin.whatsapp.ajax_status') }}",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    if (data.is_connected) {
                        // 1. متصل بنجاح
                        $('#scanQrContainer').hide();
                        $('#connectedContainer').show();
                        $('#connectedPhoneDisplay').text('+' + data.phone);
                        if (data.name) {
                            $('#connectedNameDisplay').text(data.name);
                        }
                        $('#cardStatusText').text('متصل بنجاح').css('color', '#059669');
                        $('#cardConnectedPhone').text('+' + data.phone);
                        $('#topStatusIndicator').html(`
                            <span class="badge bg-success px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                <span class="pulse-dot bg-white"></span>
                                <span>متصل: +${data.phone}</span>
                            </span>
                        `);
                    } else {
                        // غير متصل حالياً
                        $('#connectedContainer').hide();
                        $('#scanQrContainer').show();

                        if (!data.online || data.status === 'service_offline') {
                            // 2. الخادم متوقف
                            $('#qrImageWrapper').hide();
                            $('#qrLoadingSpinner').hide();
                            $('#reconnectingContainer').hide();
                            $('#serviceOfflineContainer').show();

                            $('#cardStatusText').text('الخادم متوقف').css('color', '#dc2626');
                            $('#cardConnectedPhone').text(data.phone ? ('+' + data.phone + ' (جلسة سابقة)') : 'الخادم غير نشط');
                            $('#topStatusIndicator').html(`
                                <span class="badge bg-danger px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bx bx-power-off fs-5"></i>
                                    <span>الخادم متوقف</span>
                                </span>
                            `);
                            $('#qrRefreshNotice').html('<i class="bx bx-error text-danger me-1"></i> الخادم متوقف');
                        } else if (data.status === 'reconnecting') {
                            // 3. جاري إعادة الاتصال تلقائياً بالجلسة السابقة
                            $('#qrImageWrapper').hide();
                            $('#qrLoadingSpinner').hide();
                            $('#serviceOfflineContainer').hide();
                            $('#reconnectingContainer').show();

                            if (data.phone) {
                                $('#reconnectingPhoneBadge').text('الرقم: +' + data.phone).show();
                            } else {
                                $('#reconnectingPhoneBadge').hide();
                            }

                            $('#cardStatusText').text('جاري استعادة الاتصال...').css('color', '#2563eb');
                            $('#cardConnectedPhone').text(data.phone ? ('+' + data.phone) : 'جاري التحقق');
                            $('#topStatusIndicator').html(`
                                <span class="badge bg-info text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bx bx-loader-alt bx-spin fs-5"></i>
                                    <span>جاري استعادة الاتصال...</span>
                                </span>
                            `);
                            $('#qrRefreshNotice').html('<i class="bx bx-loader bx-spin text-primary me-1"></i> جاري استعادة الاتصال...');
                        } else if (data.qr) {
                            // 4. رمز QR جاهز للمسح
                            $('#serviceOfflineContainer').hide();
                            $('#reconnectingContainer').hide();
                            $('#qrLoadingSpinner').hide();
                            $('#whatsappQrImage').attr('src', data.qr);
                            $('#qrImageWrapper').show();

                            $('#cardStatusText').text('بانتظار مسح QR').css('color', '#d97706');
                            $('#cardConnectedPhone').text('امسح الرمز من هاتفك');
                            $('#topStatusIndicator').html(`
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bx bx-qr-scan fs-5"></i>
                                    <span>بانتظار مسح رمز QR</span>
                                </span>
                            `);
                            $('#qrRefreshNotice').html('<i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط');
                        } else {
                            // 5. جاري تهيئة رمز QR
                            $('#serviceOfflineContainer').hide();
                            $('#reconnectingContainer').hide();
                            $('#qrImageWrapper').hide();
                            $('#qrLoadingSpinner').show();

                            $('#cardStatusText').text('جاري التهيئة...').css('color', '#6b7280');
                            $('#cardConnectedPhone').text('يرجى الانتظار');
                            $('#topStatusIndicator').html(`
                                <span class="badge bg-secondary px-3 py-2 fs-6 shadow-sm d-flex align-items-center gap-2">
                                    <i class="bx bx-loader-alt bx-spin fs-5"></i>
                                    <span>جاري التهيئة...</span>
                                </span>
                            `);
                            $('#qrRefreshNotice').html('<i class="bx bx-refresh bx-spin me-1 text-success"></i> فحص الاتصال التلقائي نشط');
                        }
                    }
                },
                error: function() {
                    // في حال حدوث خطأ في طلب AJAX
                }
            });
        }

        // بدء الفحص الدوري
        pollTimer = setInterval(checkWhatsAppStatus, 3000);
    });
</script>
@endsection
