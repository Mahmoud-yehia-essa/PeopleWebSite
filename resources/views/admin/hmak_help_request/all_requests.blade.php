@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة حماك الخير</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">طلبات المساعدة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<hr/>

@if(session('success'))
    <div class="alert alert-success border-0 bg-success alert-dismissible fade show py-2">
        <div class="d-flex align-items-center">
            <div class="font-35 text-white"><i class='bx bxs-check-circle'></i></div>
            <div class="ms-3">
                <h6 class="mb-0 text-white">نجاح</h6>
                <div class="text-white">{{ session('success') }}</div>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>الفئة المطلوبة</th>
                        <th>العنوان</th>
                        <th>الهاتف</th>
                        <th>الجنسية</th>
                        <th>الموقع الحالي</th>
                        <th>الحالة</th>
                        <th>تاريخ الطلب</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                <span class="font-bold text-dark">
                                    {{ ($item->user->fname ?? '') . ' ' . ($item->user->lname ?? '') }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $item->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light-primary text-primary font-bold">
                                    {{ $item->category->name ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $item->address }}">
                                    {{ $item->address ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>{{ $item->phone }}</td>
                            <td>{{ $item->nationality ?? 'غير محدد' }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 120px;" title="{{ $item->current_location }}">
                                    {{ $item->current_location ?? 'غير محدد' }}
                                </span>
                            </td>
                            <td>
                                @if($item->status == 'approved')
                                    <span class="badge bg-success text-white">مقبول</span>
                                @elseif($item->status == 'rejected')
                                    <span class="badge bg-danger text-white">مرفوض</span>
                                @else
                                    <span class="badge bg-secondary text-white">قيد الانتظار</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- View Details Button -->
                                    <button type="button" class="btn btn-sm btn-info text-white view-details-btn" 
                                            data-id="{{ $item->id }}"
                                            data-user-name="{{ ($item->user->fname ?? '') . ' ' . ($item->user->lname ?? '') }}"
                                            data-user-email="{{ $item->user->email ?? '' }}"
                                            data-user-phone="{{ $item->user->phone ?? 'غير محدد' }}"
                                            data-category="{{ $item->category->name ?? 'غير محدد' }}"
                                            data-phone="{{ $item->phone }}"
                                            data-address="{{ $item->address ?? 'غير محدد' }}"
                                            data-nationality="{{ $item->nationality ?? 'غير محدد' }}"
                                            data-location="{{ $item->current_location ?? 'غير محدد' }}"
                                            data-description="{{ $item->description }}"
                                            data-status="{{ $item->status }}"
                                            data-date="{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '' }}"
                                            data-attachments="{{ json_encode($item->attachments) }}"
                                            title="عرض التفاصيل">
                                        <i class="bx bx-show-alt"></i>
                                    </button>

                                    <!-- Status actions -->
                                    @if($item->status != 'approved')
                                        <form action="{{ route('update.help.request.status') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="موافق">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($item->status != 'rejected')
                                        <button type="button" class="btn btn-sm btn-warning text-dark open-reject-modal"
                                            data-id="{{ $item->id }}"
                                            title="رفض">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    @endif

                                    <!-- Delete action -->
                                    <a href="{{ route('delete.help.request', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-bold">تفاصيل طلب المساعدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-right">
                <div class="row g-3" style="direction: rtl;">
                    <!-- User Details -->
                    <div class="col-md-6 border-start">
                        <h6 class="text-primary font-bold mb-3"><i class="bx bxs-user-detail"></i> بيانات مقدم الطلب</h6>
                        <p class="mb-2"><strong>الاسم بالكامل:</strong> <span id="modal-user-name" class="font-semibold text-dark"></span></p>
                        <p class="mb-2"><strong>البريد الإلكتروني:</strong> <span id="modal-user-email"></span></p>
                        <p class="mb-2"><strong>رقم الهاتف (الأساسي):</strong> <span id="modal-user-phone"></span></p>
                        <p class="mb-2"><strong>رقم هاتف الاتصال للطلب:</strong> <span id="modal-phone" class="font-semibold text-dark"></span></p>
                        <p class="mb-2"><strong>الجنسية:</strong> <span id="modal-nationality"></span></p>
                        <p class="mb-2"><strong>العنوان التفصيلي:</strong> <span id="modal-address"></span></p>
                        <p class="mb-2"><strong>الموقع الحالي:</strong> <span id="modal-location"></span></p>
                        <p class="mb-2"><strong>تاريخ الإرسال:</strong> <span id="modal-date"></span></p>
                    </div>
                    <!-- Status & Meta -->
                    <div class="col-md-6">
                        <h6 class="text-primary font-bold mb-3"><i class="bx bx-info-circle"></i> حالة الطلب</h6>
                        <p class="mb-2"><strong>الفئة المطلوبة:</strong> <span id="modal-category" class="badge bg-primary text-white"></span></p>
                        <p class="mb-2"><strong>حالة الطلب الحالية:</strong> <span id="modal-status-badge"></span></p>
                    </div>
                    
                    <hr>
                    
                    <!-- Content Details -->
                    <div class="col-12">
                        <h6 class="text-primary font-bold mb-2"><i class="bx bx-detail"></i> شرح وتفاصيل طلب المساعدة</h6>
                        <div class="p-3 bg-light rounded text-dark" id="modal-description" style="white-space: pre-wrap; min-height: 80px; line-height: 1.6;"></div>
                    </div>
                    
                    <!-- Media Attachment -->
                    <div class="col-12">
                        <h6 class="text-primary font-bold mb-2"><i class="bx bx-paperclip"></i> الملفات المرفقة</h6>
                        <div class="p-3 bg-light rounded" id="modal-media-section" style="min-height: 100px;">
                            <!-- Dynamically populated -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>


<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: #fff3cd;">
                <h5 class="modal-title font-bold text-dark"><i class="bx bx-x-circle me-1 text-danger"></i> رفض طلب المساعدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('update.help.request.status') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="reject-request-id">
                <input type="hidden" name="status" value="rejected">
                <div class="modal-body" style="direction: rtl;">
                    <p class="text-muted mb-3">يمكنك إدخال سبب الرفض ليتمكن صاحب الطلب من معرفة السبب عبر لوحة الخدمات.</p>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-bold">سبب الرفض <small class="text-muted">(اختياري)</small></label>
                        <textarea name="rejection_reason" id="rejection_reason" rows="4" class="form-control" placeholder="مثال: الطلب لا يندرج ضمن فئة المساعدات المتاحة حالياً..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-x me-1"></i> تأكيد الرفض</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Image View Modal (Popup for Admin Table Thumbnail Click) -->
<div id="admin-image-popup-modal" class="fixed inset-0 z-[9999] d-none flex items-center justify-center p-4 md:p-6 transition-all duration-300 opacity-0 pointer-events-none" style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 9999;">
    <!-- Backdrop -->
    <div class="absolute inset-0 w-100 h-100" style="position: absolute; inset: 0;" onclick="closeAdminImagePreview()"></div>
    
    <!-- Content container -->
    <div class="relative max-w-4xl max-h-[90vh] d-flex flex-column align-items-center justify-content-center z-10 transition-transform duration-300 scale-95" id="admin-image-popup-content" style="position: relative; max-width: 80%; max-height: 90vh;">
        <!-- Close Button -->
        <button type="button" onclick="closeAdminImagePreview()" class="absolute z-20 w-10 h-10 rounded-circle text-white d-flex align-items-center justify-center transition-all duration-300 focus:outline-none" style="position: absolute; top: -50px; left: 0; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
            <i class="bx bx-x fs-4"></i>
        </button>
        
        <!-- Image Element -->
        <img id="admin-image-popup-img" src="" alt="" class="max-w-100 rounded-3 shadow-lg" style="max-width: 100%; max-height: 75vh; object-fit: contain; border: 1px solid rgba(255,255,255,0.1);" />
        
        <!-- Title Caption -->
        <h5 id="admin-image-popup-title" class="text-white text-center mt-3 px-4 py-2 rounded-pill max-w-md text-truncate" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); font-size: 14px; max-width: 350px;"></h5>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {

        // Open reject modal
        $('.open-reject-modal').on('click', function() {
            const id = $(this).data('id');
            $('#reject-request-id').val(id);
            $('#rejection_reason').val('');
            $('#rejectRequestModal').modal('show');
        });

        $('.view-details-btn').on('click', function() {
            const userName = $(this).data('user-name');
            const userEmail = $(this).data('user-email');
            const userPhone = $(this).data('user-phone');
            const category = $(this).data('category');
            const phone = $(this).data('phone');
            const address = $(this).data('address');
            const nationality = $(this).data('nationality');
            const location = $(this).data('location');
            const description = $(this).data('description');
            const status = $(this).data('status');
            const date = $(this).data('date');
            const attachments = $(this).data('attachments') || [];
            
            // Populate fields
            $('#modal-user-name').text(userName);
            $('#modal-user-email').text(userEmail);
            $('#modal-user-phone').text(userPhone);
            $('#modal-category').text(category);
            $('#modal-phone').text(phone);
            $('#modal-address').text(address);
            $('#modal-nationality').text(nationality);
            $('#modal-location').text(location);
            $('#modal-description').text(description || 'لا يوجد وصف مضاف');
            $('#modal-date').text(date);
            
            // Status badge
            let badgeHtml = '';
            if (status === 'approved') {
                badgeHtml = '<span class="badge bg-success text-white">مقبول</span>';
            } else if (status === 'rejected') {
                badgeHtml = '<span class="badge bg-danger text-white">مرفوض</span>';
            } else {
                badgeHtml = '<span class="badge bg-secondary text-white">قيد الانتظار</span>';
            }
            $('#modal-status-badge').html(badgeHtml);
            
            // Render attachments
            $('#modal-media-section').empty();
            if (attachments.length > 0) {
                attachments.forEach(function(attach) {
                    let url = '{{ asset("") }}' + attach.file_path;
                    let container = $('<div class="mb-3 border p-2 rounded text-center bg-white shadow-sm"></div>');
                    
                    if (attach.type === 'image') {
                        container.append(`<h6 class="text-secondary mb-2"><i class="bx bx-image"></i> صورة مرفقة</h6>`);
                        container.append(`<img src="${url}" class="img-fluid rounded border" style="max-height: 250px; object-fit: contain; cursor: pointer;" onclick="openAdminImagePreview('${url}', 'معاينة صورة المرفق')">`);
                    } else if (attach.type === 'video') {
                        container.append(`<h6 class="text-secondary mb-2"><i class="bx bx-video"></i> فيديو مرفق</h6>`);
                        container.append(`<video src="${url}" controls class="w-100 rounded border" style="max-height: 250px;"></video>`);
                    } else if (attach.type === 'pdf') {
                        container.append(`<h6 class="text-secondary mb-2"><i class="bx bxs-file-pdf"></i> مستند PDF مرفق</h6>`);
                        container.append(`<a href="${url}" target="_blank" class="btn btn-outline-danger mt-1"><i class="bx bxs-file-pdf"></i> عرض أو تحميل ملف PDF</a>`);
                    }
                    $('#modal-media-section').append(container);
                });
            } else {
                $('#modal-media-section').html('<div class="text-center text-muted py-3">لا توجد مرفقات مع هذا الطلب</div>');
            }
            
            // Show modal
            $('#requestDetailsModal').modal('show');
        });

        // Listen for escape key in admin image preview
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAdminImagePreview();
            }
        });
    });

    function openAdminImagePreview(src, title) {
        const modal = document.getElementById('admin-image-popup-modal');
        const modalContent = document.getElementById('admin-image-popup-content');
        const img = document.getElementById('admin-image-popup-img');
        const titleEl = document.getElementById('admin-image-popup-title');
        
        if (modal && img && titleEl) {
            img.src = src;
            titleEl.textContent = title || 'معاينة الصورة';
            
            modal.style.display = 'flex';
            // Force reflow
            void modal.offsetWidth;
            
            modal.style.opacity = '1';
            modal.style.pointerEvents = 'auto';
            if (modalContent) {
                modalContent.classList.remove('scale-95');
                modalContent.classList.add('scale-100');
            }
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAdminImagePreview() {
        const modal = document.getElementById('admin-image-popup-modal');
        const modalContent = document.getElementById('admin-image-popup-content');
        
        if (modal) {
            modal.style.opacity = '0';
            modal.style.pointerEvents = 'none';
            if (modalContent) {
                modalContent.classList.remove('scale-100');
                modalContent.classList.add('scale-95');
            }
            
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 300);
        }
    }
</script>

@endsection
