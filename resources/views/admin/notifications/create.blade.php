@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #d4af37; padding-left: 15px;">الإشعارات</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">إرسال إشعارات التطبيق (FCM)</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Session Alert Messages -->
                @if(session('message'))
                    <div class="alert alert-{{ session('alert-type') == 'success' ? 'success' : (session('alert-type') == 'warning' ? 'warning' : 'danger') }} border-0 bg-{{ session('alert-type') == 'success' ? 'success' : (session('alert-type') == 'warning' ? 'warning' : 'danger') }} alert-dismissible fade show text-white mb-4 shadow-sm" style="border-radius: 10px;">
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

                <!-- Stats Summary Row -->
                <div class="row mb-4 g-3">
                    <!-- Total Registered Users Card -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm custom-stat-card" style="background: #ffffff; border-radius: 14px; border-right: 5px solid #3b82f6 !important;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-block mb-1 text-secondary" style="font-size: 0.85rem; font-weight: 700; color: #64748b !important;">
                                            إجمالي المستخدمين المسجلين
                                        </span>
                                        <h2 class="mb-0 font-weight-bold" style="color: #0f172a !important; font-size: 2.2rem; line-height: 1.2;">
                                            {{ number_format($totalUsersCount ?? 0) }}
                                        </h2>
                                        <span class="badge bg-primary-subtle text-primary mt-2" style="font-size: 0.75rem; font-weight: 600; padding: 4px 8px;">
                                            <i class="bx bx-user-check me-1"></i> حساب في قاعدة البيانات
                                        </span>
                                    </div>
                                    <div class="stat-icon-circle bg-blue-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 14px; background: #eff6ff; color: #2563eb;">
                                        <i class="bx bx-group" style="font-size: 32px; color: #2563eb;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FCM Eligible Users Card -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm custom-stat-card" style="background: #ffffff; border-radius: 14px; border-right: 5px solid #10b981 !important;">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <span class="d-block mb-1 font-weight-bold" style="font-size: 0.85rem; font-weight: 700; color: #047857 !important;">
                                            مستخدمون مؤهلون للإشعارات (لديهم FCM Token)
                                        </span>
                                        <h2 class="mb-0 font-weight-bold" style="color: #059669 !important; font-size: 2.2rem; line-height: 1.2;">
                                            {{ number_format($usersWithTokenCount ?? 0) }}
                                        </h2>
                                        <span class="badge bg-success-subtle text-success mt-2" style="font-size: 0.75rem; font-weight: 600; padding: 4px 8px; background-color: #d1fae5 !important; color: #065f46 !important;">
                                            <i class="bx bx-check-double me-1"></i> جاهزون لاستقبال الإشعارات الفورية
                                        </span>
                                    </div>
                                    <div class="stat-icon-circle bg-green-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 14px; background: #ecfdf5; color: #059669;">
                                        <i class="bx bx-bell-ring" style="font-size: 32px; color: #059669;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <form id="notificationSendForm" action="{{ route('admin.notifications.store') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm border-0" style="border-radius: 14px; border-top: 5px solid #198754 !important;">
                        <div class="card-body p-4 p-md-5">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-2 p-2 rounded bg-light-success text-success">
                                        <i class="bx bx-send font-24"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 text-dark font-weight-bold">إرسال إشعار فوري لمستخدمي التطبيق</h5>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">

                            <!-- Notification Title -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark">
                                    <i class="bx bx-heading text-success me-1"></i> عنوان الإشعار (Title) <span class="text-danger">*</span>
                                </label>
                                <input name="title" type="text" class="form-control form-control-lg border-light-success @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="مثال: خصم مميز اليوم فقط / تحديث جديد متاح..." required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notification Description (des) -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark">
                                    <i class="bx bx-message-detail text-success me-1"></i> نص وموضوع الإشعار (Description) <span class="text-danger">*</span>
                                </label>
                                <textarea name="des" class="form-control border-light-success @error('des') is-invalid @enderror" rows="5" placeholder="اكتب نص الإشعار الذي سيظهر على شاشة هاتف المستخدم بالتفصيل..." required>{{ old('des') }}</textarea>
                                @error('des')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            
                            <!-- Notification Action Link (YouTube / Web URL) -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark">
                                    <i class="bx bx-link-external text-danger me-1"></i> رابط مرفق (اختياري - يوتيوب / موقع ويب)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-danger border-light-success"><i class="bx bxl-youtube font-20"></i></span>
                                    <input name="link" type="url" class="form-control border-light-success @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="https://www.youtube.com/watch?v=... أو https://example.com">
                                </div>
                                <small class="text-muted d-block mt-1">عند إدخال رابط يوتيوب أو أي رابط خارجي، سيظهر في التطبيق زر مخصص للانتقال والمشاهدة فوراً.</small>
                                @error('link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Target Group Selection -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-dark d-block mb-3">
                                    <i class="bx bx-target-lock text-success me-1"></i> تحديد الجمهور المستهدف <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex flex-wrap gap-4 p-3 bg-light rounded" style="border: 1px solid #e9ecef;">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="targetAll" value="all" {{ old('target_type', 'all') == 'all' ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold text-dark" for="targetAll" style="cursor: pointer;">
                                            <i class="bx bx-globe text-primary me-1"></i> إرسال لجميع مستخدمي التطبيق (من يمتلكون Token)
                                            <span class="badge bg-primary ms-1">{{ $usersWithTokenCount ?? 0 }} مستخدم</span>
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="targetSpecific" value="specific" {{ old('target_type') == 'specific' ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold text-dark" for="targetSpecific" style="cursor: pointer;">
                                            <i class="bx bx-check-square text-warning me-1"></i> اختيار مستخدمين محددين
                                        </label>
                                    </div>
                                </div>
                                @error('target_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Collapsible Specific Users Selection Box -->
                            <div id="usersSelectorSection" class="card shadow-none border mb-4" style="display: none; background-color: #fbfbfb; border-radius: 10px;">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                                        <div>
                                            <h6 class="mb-1 font-weight-bold text-dark">اختر المستخدمين المستهدفين:</h6>
                                            <small class="text-muted">يتم عرض المستخدمين الذين يمتلكون رمز إشعارات (FCM Token) صالح فقط.</small>
                                        </div>
                                        <!-- Select All / Deselect All Toggle -->
                                        <div class="form-check form-switch bg-white px-3 py-2 rounded shadow-sm border">
                                            <input class="form-check-input ms-0 me-2" type="checkbox" id="selectAllUsers">
                                            <label class="form-check-label font-weight-bold text-secondary small" for="selectAllUsers" style="cursor: pointer;">تحديد الكل</label>
                                        </div>
                                    </div>

                                    <!-- Quick Search Input -->
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                                        <input type="text" id="searchUserField" class="form-control border-start-0" placeholder="ابحث بالاسم أو البريد الإلكتروني أو الهاتف...">
                                    </div>

                                    <!-- Users Checkboxes Container -->
                                    <div class="user-list-scroll-box border p-3 bg-white" style="max-height: 320px; overflow-y: auto; border-radius: 8px;">
                                        @if($usersWithToken->isEmpty())
                                            <div class="text-center py-5">
                                                <i class="bx bx-bell-off fs-1 text-muted opacity-50 mb-2"></i>
                                                <h6 class="text-secondary font-weight-bold">لا يوجد مستخدمون لديهم FCM Token حالياً</h6>
                                                <p class="text-muted small mb-0">عندما يقوم المستخدمون بتسجيل الدخول في التطبيق، ستظهر حساباتهم هنا تلقائياً.</p>
                                            </div>
                                        @else
                                            <div class="row row-cols-1 row-cols-md-2 g-3" id="usersCheckboxGrid">
                                                @foreach($usersWithToken as $user)
                                                    @php
                                                        $userPhoto = (!empty($user->profile_picture) && $user->profile_picture != 'non') 
                                                            ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$user->profile_picture) 
                                                            : url('upload/no_image.jpg');
                                                    @endphp
                                                    <div class="col user-item-card" data-searchable="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . ($user->email ?? '') . ' ' . ($user->phone_number ?? '')) }}">
                                                        <div class="d-flex align-items-center p-2 rounded hover-light-bg" style="border: 1px solid #edf2f7;">
                                                            <div class="form-check me-2">
                                                                <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="userCheckbox_{{ $user->id }}" {{ is_array(old('user_ids')) && in_array($user->id, old('user_ids')) ? 'checked' : '' }}>
                                                            </div>
                                                            <label class="form-check-label d-flex align-items-center w-100 mb-0" for="userCheckbox_{{ $user->id }}" style="cursor: pointer;">
                                                                <img src="{{ $userPhoto }}" alt="{{ $user->first_name }}" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover; border: 1px solid #ddd;">
                                                                <div class="text-truncate flex-grow-1">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <h6 class="mb-0 text-dark small font-weight-bold">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;">Token نشط</span>
                                                                    </div>
                                                                    <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $user->email ?? $user->phone_number }}</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div id="noUsersFound" class="text-center py-4" style="display: none;">
                                                <i class="bx bx-user-x fs-2 text-secondary opacity-50 mb-2"></i>
                                                <p class="text-muted mb-0 small">لم يتم العثور على أي مستخدم يطابق كلمة البحث.</p>
                                            </div>
                                        @endif
                                    </div>
                                    @error('user_ids')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons Section -->
                            <div class="row mt-4">
                                <div class="col-sm-12">
                                    <button type="submit" id="submitBtn" class="btn btn-success btn-lg px-5 d-flex align-items-center gap-2 shadow" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%); border: none; border-radius: 8px;">
                                        <i class="bx bx-paper-plane"></i>
                                        <span id="btnText">إرسال الإشعار الآن</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // تبديل ظهور واختفاء قسم اختيار المستخدمين المحددين
        function toggleUsersSelector() {
            if ($('#targetSpecific').is(':checked')) {
                $('#usersSelectorSection').slideDown(300);
            } else {
                $('#usersSelectorSection').slideUp(300);
            }
        }

        // تشغيل الدالة عند تحميل الصفحة
        toggleUsersSelector();

        // الاستماع لتغيير خيار الاستهداف
        $('input[name="target_type"]').change(function() {
            toggleUsersSelector();
        });

        // تحديد / إلغاء تحديد الكل
        $('#selectAllUsers').change(function() {
            var isChecked = $(this).is(':checked');
            $('.user-item-card:visible').find('.user-checkbox').prop('checked', isChecked);
        });

        // البحث السريع اللحظي بين المستخدمين
        $('#searchUserField').on('keyup input', function() {
            var query = $(this).val().toLowerCase().trim();
            var visibleCount = 0;

            $('.user-item-card').each(function() {
                var searchableText = $(this).data('searchable');
                if (searchableText.indexOf(query) !== -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0) {
                $('#noUsersFound').show();
            } else {
                $('#noUsersFound').hide();
            }
        });

        // إظهار حالة التحميل عند الإرسال لمنع التكرار
        $('#notificationSendForm').on('submit', function() {
            var btn = $('#submitBtn');
            btn.prop('disabled', true);
            $('#btnText').text('جاري الإرسال والمعالجة...');
            btn.prepend('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
            return true;
        });
    });
</script>

<style>
    .custom-stat-card {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .custom-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08) !important;
    }
    .border-light-success:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }
    .hover-light-bg {
        transition: background-color 0.2s ease;
    }
    .hover-light-bg:hover {
        background-color: #f0fdf4;
        border-color: #bbf7d0 !important;
    }
    .user-list-scroll-box::-webkit-scrollbar {
        width: 6px;
    }
    .user-list-scroll-box::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .user-list-scroll-box::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 4px;
    }
    .user-list-scroll-box::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    .bg-light-success {
        background-color: #e8f5e9;
    }
    .bg-primary-subtle {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
    }
    .bg-success-subtle {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
    }
</style>
@endsection
