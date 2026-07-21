@extends('admin.master_admin')
@section('admin')

<!-- CSS الخاصة بغرفة الاجتماعات التفاعلية -->
<style>
    /* تصميم غرفة الاجتماعات */
    .boardroom-container {
        position: relative;
        width: 100%;
        height: 600px;
        background: radial-gradient(circle, #2c302e 0%, #171918 100%);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: inset 0 0 50px rgba(0,0,0,0.8), 0 10px 30px rgba(0,0,0,0.3);
        margin-bottom: 30px;
        border: 2px solid #343a40;
    }

    /* تأثير الإضاءة العلوية للمقر */
    .boardroom-light {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 150px;
        background: radial-gradient(ellipse at top, rgba(0, 140, 255, 0.15) 0%, rgba(0,0,0,0) 70%);
        pointer-events: none;
        z-index: 1;
    }

    /* طاولة الاجتماعات في المنتصف */
    .meeting-table {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 480px;
        height: 240px;
        background: linear-gradient(135deg, #5d4037 0%, #3e2723 100%);
        border-radius: 50%;
        box-shadow: 
            0 15px 30px rgba(0,0,0,0.6), 
            inset 0 0 25px rgba(0,0,0,0.5),
            0 0 0 8px rgba(93, 64, 55, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 4px solid #8d6e63;
    }

    /* المنتصف المضيء للطاولة */
    .table-center {
        width: 220px;
        height: 110px;
        background: rgba(255, 255, 255, 0.03);
        border: 2px dashed rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.7);
        text-shadow: 0 0 10px rgba(0, 140, 255, 0.5);
    }

    .table-center i {
        font-size: 28px;
        margin-bottom: 5px;
        animation: pulse-light 2s infinite alternate;
    }

    /* المقاعد الفردية حول الطاولة */
    .wise-seat {
        position: absolute;
        width: 120px;
        height: 120px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 3;
        transition: transform 0.3s ease, z-index 0.3s;
    }

    .wise-seat:hover {
        transform: scale(1.12);
        z-index: 10;
    }

    /* المقعد الفعلي للمستخدم */
    .seat-avatar-wrapper {
        position: relative;
        width: 76px;
        height: 76px;
        border-radius: 50%;
        padding: 4px;
        background: #1e2120;
        box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    /* إطار مقعد الحكيم الملون حسب الحالة */
    .wise-seat.active-member .seat-avatar-wrapper {
        border: 3px solid #008cff;
        box-shadow: 0 0 15px rgba(0, 140, 255, 0.4);
    }

    .wise-seat.inactive-member .seat-avatar-wrapper {
        border: 3px solid #f41127;
        box-shadow: 0 0 15px rgba(244, 17, 39, 0.3);
    }

    .seat-avatar {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        background-color: #343a40;
    }

    /* شارة التخصص على المقعد */
    .seat-specialty-badge {
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 9px;
        background: #1e2120;
        color: #fff;
        padding: 2px 8px;
        border-radius: 10px;
        border: 1px solid #444;
        white-space: nowrap;
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wise-seat.active-member .seat-specialty-badge {
        border-color: #008cff;
    }

    .wise-seat.inactive-member .seat-specialty-badge {
        border-color: #f41127;
    }

    /* اسم العضو أسفل المقعد */
    .seat-name {
        margin-top: 10px;
        font-size: 11px;
        font-weight: bold;
        color: #e2e8f0;
        text-align: center;
        background: rgba(0,0,0,0.6);
        padding: 2px 6px;
        border-radius: 4px;
        max-width: 110px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        border: 1px solid rgba(255,255,255,0.1);
    }

    /* ميكروفون الاجتماع الصغير أمام المقعد */
    .seat-mic {
        position: absolute;
        top: -12px;
        font-size: 12px;
        padding: 4px;
        border-radius: 50%;
        background: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 4;
        border: 1px solid #444;
    }

    .wise-seat.active-member .seat-mic {
        color: #28a745;
        border-color: #28a745;
        box-shadow: 0 0 8px rgba(40,167,69,0.5);
    }

    .wise-seat.inactive-member .seat-mic {
        color: #dc3545;
        border-color: #dc3545;
    }

    /* المقعد الشاغر الفارغ */
    .wise-seat.empty-seat .seat-avatar-wrapper {
        border: 2px dashed #6c757d;
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        box-shadow: none;
    }

    .wise-seat.empty-seat:hover .seat-avatar-wrapper {
        border-color: #008cff;
        color: #008cff;
        box-shadow: 0 0 10px rgba(0, 140, 255, 0.2);
    }

    /* شاشات التفاصيل المنبثقة عند تمرير مؤشر الفأرة (Hover Info Card) */
    .seat-hover-card {
        position: absolute;
        bottom: 125px;
        width: 240px;
        background: #1e2120;
        border: 1px solid #3e4441;
        border-radius: 12px;
        padding: 15px;
        color: #f8f9fa;
        box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        display: none;
        z-index: 100;
        text-align: right;
    }

    .wise-seat:hover .seat-hover-card {
        display: block;
        animation: fadeIn 0.2s ease;
    }

    /* أنيميشن */
    @keyframes pulse-light {
        0% { opacity: 0.4; }
        100% { opacity: 1; text-shadow: 0 0 18px rgba(0, 140, 255, 0.8); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #008cff; padding-left: 10px; font-weight: bold;">لجنة الحكماء</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item active" aria-current="page">مقر اجتماع لجنة الحكماء</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#addWiseModal">
            <i class="bx bx-plus-circle"></i> تعيين حكيم جديد
        </button>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <!-- 1. The Interactive Boardroom View -->
    <div class="col-12">
        <div class="boardroom-container" id="boardroomContainer">
            <div class="boardroom-light"></div>
            
            <!-- طاولة الاجتماعات في المنتصف -->
            <div class="meeting-table">
                <div class="table-center">
                    <i class="bx bxs-group"></i>
                    <h6 class="mb-0 text-white font-weight-bold" style="font-size: 13px;">طاولة اجتماع الحكماء</h6>
                    <span style="font-size: 10px; opacity: 0.6;">مقر الحكماء الفعلي</span>
                </div>
            </div>

            <!-- مقاعد الحكماء الموزعة ديناميكياً -->
            @php
                // تحديد الحد الأدنى من المقاعد المعروضة كشكل الغرفة بـ 8 مقاعد
                $totalSeatsCount = max(8, count($committeeMembers));
            @endphp

            @for($i = 0; $i < $totalSeatsCount; $i++)
                @if(isset($committeeMembers[$i]))
                    @php
                        $member = $committeeMembers[$i];
                        $user = $member->user;
                        $avatarUrl = (!empty($user->photo) && $user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$user->photo : url('upload/no_image.jpg');
                    @endphp
                    <!-- مقعد حكيم موجود -->
                    <div class="wise-seat {{ $member->is_active ? 'active-member' : 'inactive-member' }}" data-index="{{ $i }}">
                        <!-- الميكروفون أمام الحكيم -->
                        <div class="seat-mic" title="{{ $member->is_active ? 'الميكروفون مفتوح' : 'الميكروفون مغلق / معطل' }}">
                            <i class="fa-solid {{ $member->is_active ? 'fa-microphone' : 'fa-microphone-slash' }}"></i>
                        </div>
                        
                        <div class="seat-avatar-wrapper">
                            <img src="{{ $avatarUrl }}" class="seat-avatar" alt="{{ $user->fname }}">
                            <span class="seat-specialty-badge">{{ $member->specialty ?: 'عام' }}</span>
                        </div>
                        <span class="seat-name">{{ $user->fname }} {{ $user->lname }}</span>

                        <!-- بطاقة التفاصيل المنبثقة عند الهوفر -->
                        <div class="seat-hover-card">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ $avatarUrl }}" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0 text-white font-weight-bold" style="font-size: 13px;">{{ $user->fname }} {{ $user->lname }}</h6>
                                    <span class="text-muted" style="font-size: 11px;">{{ $user->email }}</span>
                                </div>
                            </div>
                            <hr class="my-2" style="border-color: #444;">
                            <div class="mb-1"><strong>التخصص:</strong> {{ $member->specialty ?: 'غير محدد' }}</div>
                            <div class="mb-2" style="font-size: 12px; opacity: 0.8; max-height: 50px; overflow-y: auto;">
                                <strong>النبذة:</strong> {{ $member->bio ?: 'لا توجد نبذة تعريفية.' }}
                            </div>
                            <div class="mb-2" style="font-size: 11px; opacity: 0.6;">
                                <strong>تاريخ الانضمام:</strong> {{ $member->created_at ? $member->created_at->format('Y-m-d') : 'غير محدد' }}
                            </div>
                            <div class="d-flex gap-1 mt-2">
                                <a href="{{ route('admin.wise_committees.toggle', $member->id) }}" class="btn btn-xs {{ $member->is_active ? 'btn-warning text-dark' : 'btn-success' }} py-1 px-2 w-50" style="font-size: 10px;">
                                    {{ $member->is_active ? 'تجميد' : 'تنشيط' }}
                                </a>
                                <a href="{{ route('admin.wise_committees.delete', $member->id) }}" class="btn btn-xs btn-danger py-1 px-2 w-50" id="delete" style="font-size: 10px;">
                                    إنهاء العضوية
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- مقعد فارغ شاغر -->
                    <div class="wise-seat empty-seat" data-index="{{ $i }}" data-bs-toggle="modal" data-bs-target="#addWiseModal" style="cursor: pointer;">
                        <div class="seat-avatar-wrapper">
                            <i class="fa-solid fa-plus fs-3"></i>
                        </div>
                        <span class="seat-name" style="opacity: 0.7;">مقعد شاغر</span>
                    </div>
                @endif
            @endfor
        </div>
    </div>
</div>

<!-- 2. Detailed Grid Table list (Usability Backup for larger committees) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-light">
        <h6 class="mb-0 text-dark font-weight-bold">سجل وبيانات أعضاء اللجنة الحالية</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>الحكيم</th>
                        <th>التخصص</th>
                        <th>النبذة والتعريف</th>
                        <th class="text-center" style="width: 120px;">حالة العضوية</th>
                        <th class="text-center" style="width: 150px;">تاريخ التعيين</th>
                        <th class="text-center" style="width: 150px;">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committeeMembers as $key => $member)
                    <tr>
                        <td class="text-center font-weight-bold">{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ (!empty($member->user->photo) && $member->user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$member->user->photo : url('upload/no_image.jpg') }}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #e9ecef;">
                                <div>
                                    <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ $member->user->fname }} {{ $member->user->lname }}</h6>
                                    <span class="text-muted" style="font-size: 12px;">{{ $member->user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light-primary text-primary px-3 py-2" style="font-size: 12px; border-radius: 12px;">
                                {{ $member->specialty ?: 'عام' }}
                            </span>
                        </td>
                        <td style="max-width: 250px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;" title="{{ $member->bio }}">
                            {{ $member->bio ?: 'لا توجد نبذة.' }}
                        </td>
                        <td class="text-center">
                            @if($member->is_active)
                                <span class="badge bg-success px-3 py-2" style="font-size: 12px; border-radius: 20px;"><i class="fa-solid fa-check me-1"></i> نشط</span>
                            @else
                                <span class="badge bg-danger px-3 py-2" style="font-size: 12px; border-radius: 20px;"><i class="fa-solid fa-ban me-1"></i> مجمد</span>
                            @endif
                        </td>
                        <td class="text-center text-muted" style="font-size: 13px;">
                            {{ $member->created_at ? $member->created_at->format('Y-m-d') : 'غير محدد' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.wise_committees.toggle', $member->id) }}" class="btn btn-sm {{ $member->is_active ? 'btn-warning text-dark' : 'btn-success' }}" title="{{ $member->is_active ? 'تجميد' : 'تنشيط' }}">
                                    <i class="fa-solid {{ $member->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                </a>
                                <a href="{{ route('admin.wise_committees.delete', $member->id) }}" class="btn btn-sm btn-danger" id="delete" title="إنهاء العضوية">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">لا يوجد أعضاء معينين في لجنة الحكماء حالياً. شغل المقاعد الفارغة بالأعلى للبدء.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Wise Member -->
<div class="modal fade" id="addWiseModal" tabindex="-1" aria-labelledby="addWiseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="addWiseModalLabel">تعيين عضو جديد في لجنة الحكماء</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.wise_committees.store') }}" method="POST" id="wiseForm">
                @csrf
                <input type="hidden" name="user_id" id="wise_user_id" value="">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Search Member -->
                        <div class="col-12">
                            <label class="form-label font-weight-bold">اختر العضو المسجل</label>
                            
                            <!-- Selected member preview (hidden by default) -->
                            <div id="wiseSelectedPreview" class="alert alert-success d-flex align-items-center gap-2 py-2 mb-2" style="display: none !important;">
                                <i class="bx bx-check-circle fs-5"></i>
                                <span id="wiseSelectedName" class="fw-bold" style="font-size: 13px;"></span>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-auto py-0 px-2" onclick="wiseClearSelection()" style="font-size: 11px;">
                                    <i class="bx bx-x"></i> تغيير
                                </button>
                            </div>
                            
                            <!-- Search input -->
                            <div id="wiseSearchWrap">
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white"><i class="bx bx-search text-muted"></i></span>
                                    <input type="text" class="form-control" id="wiseSearchInput" placeholder="ابحث بالاسم أو البريد الإلكتروني..." autocomplete="off">
                                </div>
                                
                                <!-- Members list -->
                                <div id="wiseMembersList" style="max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px;">
                                    @forelse($availableUsers as $u)
                                        <div class="wise-member-item d-flex align-items-center gap-2 px-3 py-2 border-bottom"
                                             data-id="{{ $u->id }}"
                                             data-name="{{ $u->first_name }} {{ $u->last_name }}"
                                             data-email="{{ $u->email }}"
                                             style="cursor: pointer; transition: background 0.15s;"
                                             onclick="wiseSelectMember(this)">
                                            <img src="{{ (!empty($u->profile_picture) && $u->profile_picture != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.basename($u->profile_picture) : url('upload/no_image.jpg') }}" 
                                                 class="rounded-circle" style="width: 36px; height: 36px; object-fit: cover; border: 2px solid #e9ecef;">
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 13px;">{{ $u->first_name }} {{ $u->last_name }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            <i class="bx bx-user-x fs-4 d-block mb-1"></i>
                                            لا يوجد أعضاء متاحون للتعيين
                                        </div>
                                    @endforelse
                                </div>
                                <div id="wiseNoResults" class="text-center text-muted py-3 border rounded" style="display: none;">
                                    <i class="bx bx-user-x fs-4 d-block mb-1"></i>
                                    لا توجد نتائج مطابقة لبحثك
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">يظهر هنا الأعضاء المسجلين الذين ليسوا جزءاً من اللجنة حالياً.</small>
                        </div>

                        <!-- Specialty -->
                        <div class="col-12">
                            <label for="specialty" class="form-label font-weight-bold">تخصص الحكيم</label>
                            <input type="text" name="specialty" id="specialty" class="form-control" placeholder="مثال: أدبي، اجتماعي، فلسفي، تقني...">
                        </div>

                        <!-- Bio -->
                        <div class="col-12">
                            <label for="bio" class="form-label font-weight-bold">نبذة مختصرة عن الحكيم</label>
                            <textarea name="bio" id="bio" class="form-control" rows="3" placeholder="نبذة توضح سبب اختياره أو فكره..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" id="wiseSubmitBtn">تعيين واجلاسه في القاعة <i class="fa-solid fa-chair ms-1"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript لتوزيع مقاعد الحكماء تلقائياً حول الطاولة بشكل بيضاوي متناسق -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const seats = document.querySelectorAll('.boardroom-container .wise-seat');
        const totalSeats = seats.length;
        if(totalSeats === 0) return;

        // معاملات الشكل البيضاوي لطاولة الاجتماعات
        const rx = 360; // نصف القطر الأفقي بالبكسل
        const ry = 175; // نصف القطر العمودي بالبكسل

        seats.forEach((seat, index) => {
            // حساب زاوية التوزيع لكل مقعد (من 0 إلى 2 * PI بالتساوي)
            const angle = (2 * Math.PI / totalSeats) * index;

            // حساب الإحداثيات بالنسبة لمنتصف حاوية القاعة (50% left, 50% top)
            const x = rx * Math.cos(angle);
            const y = ry * Math.sin(angle);

            // تموضع المقعد حول الطاولة
            // نطرح 60px لكي نوسط المقاعد تماماً (حيث أن عرض المقعد هو 120px وارتفاعه 120px)
            seat.style.left = `calc(50% + ${x}px - 60px)`;
            seat.style.top = `calc(50% + ${y}px - 60px)`;
        });
    });
</script>

<!-- JavaScript البحث في قائمة الأعضاء -->
<script>
// تحديد الدوال العامة للاختيار وإلغاء الاختيار كما تم استدعاؤها في الـ HTML
function wiseSelectMember(element) {
    const userId = element.getAttribute('data-id');
    const userName = element.getAttribute('data-name');
    const userEmail = element.getAttribute('data-email');
    
    document.getElementById('wise_user_id').value = userId;
    document.getElementById('wiseSelectedName').textContent = userName + ' (' + userEmail + ')';
    
    const preview = document.getElementById('wiseSelectedPreview');
    preview.style.setProperty('display', 'flex', 'important');
    
    document.getElementById('wiseSearchWrap').style.display = 'none';
}

function wiseClearSelection() {
    document.getElementById('wise_user_id').value = '';
    document.getElementById('wiseSelectedName').textContent = '';
    
    const preview = document.getElementById('wiseSelectedPreview');
    preview.style.setProperty('display', 'none', 'important');
    
    document.getElementById('wiseSearchWrap').style.display = 'block';
    
    const searchInput = document.getElementById('wiseSearchInput');
    if (searchInput) {
        searchInput.value = '';
        filterWiseMembers('');
        searchInput.focus();
    }
}

function filterWiseMembers(query) {
    const items = document.querySelectorAll('.wise-member-item');
    const noResults = document.getElementById('wiseNoResults');
    const membersList = document.getElementById('wiseMembersList');
    let visibleCount = 0;
    
    items.forEach(function(item) {
        const name = item.getAttribute('data-name').toLowerCase();
        const email = item.getAttribute('data-email').toLowerCase();
        
        if (query === '' || name.includes(query) || email.includes(query)) {
            item.style.setProperty('display', 'flex', 'important');
            visibleCount++;
        } else {
            item.style.setProperty('display', 'none', 'important');
        }
    });
    
    if (visibleCount === 0) {
        if (noResults) noResults.style.display = 'block';
        if (membersList) membersList.style.display = 'none';
    } else {
        if (noResults) noResults.style.display = 'none';
        if (membersList) membersList.style.display = 'block';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('wiseSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterWiseMembers(this.value.trim().toLowerCase());
        });
    }
    
    // إضافة تحقق بسيط عند إرسال النموذج لمنع الإرسال بدون اختيار عضو
    const wiseForm = document.getElementById('wiseForm');
    if (wiseForm) {
        wiseForm.addEventListener('submit', function(e) {
            const userId = document.getElementById('wise_user_id').value;
            if (!userId) {
                e.preventDefault();
                alert('الرجاء اختيار عضو مسجل أولاً من القائمة.');
            }
        });
    }
    
    // إعادة ضبط الحالة عند فتح الـ Modal مجدداً
    const modal = document.getElementById('addWiseModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            wiseClearSelection();
        });
    }
});
</script>

@endsection
