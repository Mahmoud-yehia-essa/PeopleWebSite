@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #0d6efd; padding-left: 10px; font-weight: bold;">حالة الموقع والتطبيق</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">الإعدادات</a></li>
                <li class="breadcrumb-item active" aria-current="page">إدارة تشغيل وإيقاف المنصة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-12 mb-3">
        <div class="alert alert-info border-0 bg-light-info alert-dismissible fade show py-3">
            <div class="d-flex align-items-center">
                <div class="font-35 text-info"><i class='bx bx-info-circle'></i></div>
                <div class="ms-3">
                    <h6 class="mb-1 fw-bold text-dark">معلومات هامة حول وضع الصيانة:</h6>
                    <ul class="mb-0 small text-secondary ps-3">
                        <li><strong>إيقاف الموقع:</strong> يمنع زوار وأعضاء الموقع من التصفح ويعرض لهم صفحة الصيانة الأنيقة، مع بقاء لوحة التحكم هذه متاحة للمديرين في أي وقت.</li>
                        <li><strong>إيقاف التطبيق:</strong> يوقف استجابات الـ API لتطبيق الهاتف الذكي مع إرسال رسالة الصيانة المحددة ليعرضها التطبيق للمستخدمين.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.system_settings.update') }}" method="POST">
    @csrf
    <div class="row">
        <!-- 1. بطاقة التحكم في الموقع الإلكتروني -->
        <div class="col-lg-6 mb-4">
            <div class="card border-top border-0 border-4 {{ $settings->is_website_active ? 'border-success' : 'border-danger' }} shadow-sm h-100" id="websiteCard">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2 bg-light-primary text-primary rounded-circle p-2 text-center" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                <i class="bx bx-globe font-24"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">حالة الموقع الإلكتروني (Website)</h5>
                                <small class="text-muted">التحكم في تشغيل أو إيقاف الموقع للزوار</small>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span id="websiteStatusBadge" class="badge {{ $settings->is_website_active ? 'bg-light-success text-success border border-success' : 'bg-light-danger text-danger border border-danger' }} px-3 py-2 rounded-pill font-13 fw-bold">
                            <i class='bx {{ $settings->is_website_active ? 'bx-check-circle' : 'bx-x-circle' }} me-1'></i>
                            <span id="websiteStatusText">{{ $settings->is_website_active ? 'الموقع يعمل الآن' : 'الموقع متوقف مؤقتاً' }}</span>
                        </span>
                    </div>
                    <hr>

                    <!-- Toggle Switch -->
                    <div class="p-3 bg-light rounded-3 mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" for="is_website_active">
                                تفعيل وتشغيل الموقع
                            </label>
                            <p class="text-muted mb-0 small">عند التعطيل يدخل الموقع في وضع الصيانة</p>
                        </div>
                        <div class="form-check form-switch form-switch-lg" style="font-size: 1.5rem;">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="is_website_active" name="is_website_active" 
                                   {{ $settings->is_website_active ? 'checked' : '' }} onchange="ajaxToggle('website', this.checked)">
                        </div>
                    </div>

                    <!-- Maintenance Message -->
                    <div class="mb-3">
                        <label for="website_maintenance_message" class="form-label fw-bold text-dark">
                            <i class="bx bx-message-square-detail text-primary me-1"></i> رسالة صيانة الموقع للزوار:
                        </label>
                        <textarea name="website_maintenance_message" id="website_maintenance_message" class="form-control" rows="4" 
                                  placeholder="اكتب الرسالة التي ستظهر للمستخدمين عند إيقاف الموقع...">{{ old('website_maintenance_message', $settings->website_maintenance_message) }}</textarea>
                        <div class="form-text text-muted">
                            هذه الرسالة ستظهر بشكل بارز على صفحة الصيانة الرئيسية للموقع.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. بطاقة التحكم في تطبيق الهاتف -->
        <div class="col-lg-6 mb-4">
            <div class="card border-top border-0 border-4 {{ $settings->is_app_active ? 'border-success' : 'border-danger' }} shadow-sm h-100" id="appCard">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm me-2 bg-light-success text-success rounded-circle p-2 text-center" style="width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;">
                                <i class="bx bx-mobile-alt font-24"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">حالة تطبيق الهاتف (Mobile App / API)</h5>
                                <small class="text-muted">التحكم في تشغيل أو إيقاف الـ API للتطبيق</small>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span id="appStatusBadge" class="badge {{ $settings->is_app_active ? 'bg-light-success text-success border border-success' : 'bg-light-danger text-danger border border-danger' }} px-3 py-2 rounded-pill font-13 fw-bold">
                            <i class='bx {{ $settings->is_app_active ? 'bx-check-circle' : 'bx-x-circle' }} me-1'></i>
                            <span id="appStatusText">{{ $settings->is_app_active ? 'التطبيق يعمل الآن' : 'التطبيق متوقف مؤقتاً' }}</span>
                        </span>
                    </div>
                    <hr>

                    <!-- Toggle Switch -->
                    <div class="p-3 bg-light rounded-3 mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <label class="form-check-label fw-bold text-dark mb-0 cursor-pointer" for="is_app_active">
                                تفعيل وتشغيل التطبيق
                            </label>
                            <p class="text-muted mb-0 small">عند التعطيل تتوقف عمليات التطبيق وتظهر رسالة الصيانة</p>
                        </div>
                        <div class="form-check form-switch form-switch-lg" style="font-size: 1.5rem;">
                            <input class="form-check-input cursor-pointer" type="checkbox" id="is_app_active" name="is_app_active" 
                                   {{ $settings->is_app_active ? 'checked' : '' }} onchange="ajaxToggle('app', this.checked)">
                        </div>
                    </div>

                    <!-- Maintenance Message -->
                    <div class="mb-3">
                        <label for="app_maintenance_message" class="form-label fw-bold text-dark">
                            <i class="bx bx-message-square-detail text-success me-1"></i> رسالة صيانة التطبيق:
                        </label>
                        <textarea name="app_maintenance_message" id="app_maintenance_message" class="form-control" rows="4" 
                                  placeholder="اكتب الرسالة التي ستظهر للمستخدمين داخل التطبيق عند إيقافه...">{{ old('app_maintenance_message', $settings->app_maintenance_message) }}</textarea>
                        <div class="form-text text-muted">
                            هذه الرسالة سيتم إرجاعها مباشرة عبر الـ API لكافة مستخدمي تطبيق الهاتف.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- زر الحفظ النهائي -->
    <div class="row">
        <div class="col-12 text-end">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="bx bx-check-shield text-success me-1"></i>
                        يمكنك تعديل النصوص وحفظ كل الإعدادات دفعة واحدة بالضغط على الزر.
                    </span>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold">
                        <i class="bx bx-save me-1"></i> حفظ وتحديث الإعدادات
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function ajaxToggle(type, isChecked) {
    var url = type === 'website' 
        ? "{{ route('admin.system_settings.toggle_website') }}" 
        : "{{ route('admin.system_settings.toggle_app') }}";

    var cardId = type === 'website' ? '#websiteCard' : '#appCard';
    var badgeId = type === 'website' ? '#websiteStatusBadge' : '#appStatusBadge';
    var textId = type === 'website' ? '#websiteStatusText' : '#appStatusText';

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            status: isChecked ? 1 : 0
        },
        success: function(response) {
            if (response.success) {
                if (isChecked) {
                    toastr.success(response.message);
                    $(cardId).removeClass('border-danger').addClass('border-success');
                    $(badgeId).removeClass('bg-light-danger text-danger border-danger')
                              .addClass('bg-light-success text-success border-success');
                    $(badgeId).find('i').removeClass('bx-x-circle').addClass('bx-check-circle');
                    $(textId).text(type === 'website' ? 'الموقع يعمل الآن' : 'التطبيق يعمل الآن');
                } else {
                    toastr.warning(response.message);
                    $(cardId).removeClass('border-success').addClass('border-danger');
                    $(badgeId).removeClass('bg-light-success text-success border-success')
                              .addClass('bg-light-danger text-danger border-danger');
                    $(badgeId).find('i').removeClass('bx-check-circle').addClass('bx-x-circle');
                    $(textId).text(type === 'website' ? 'الموقع متوقف مؤقتاً' : 'التطبيق متوقف مؤقتاً');
                }
            }
        },
        error: function(xhr) {
            toastr.error('حدث خطأ أثناء تحديث الحالة. يرجى المحاولة مرة أخرى.');
            // Revert checkbox state
            $('#is_' + type + '_active').prop('checked', !isChecked);
        }
    });
}
</script>

@endsection
