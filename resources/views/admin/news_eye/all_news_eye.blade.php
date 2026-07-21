@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة أنت عين الخبر</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">أخبار الأعضاء</li>
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
                        <th>البريد الإلكتروني</th>
                        <th>عنوان الخبر</th>
                        <th>الموقع</th>
                        <th>نوع المرفق</th>
                        <th>المرفق</th>
                        <th>الحالة</th>
                        <th>تاريخ الإرسال</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($newsEyes as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td><span class="font-bold text-dark">{{ $item->user->fname ?? 'غير محدد' }}</span></td>
                            <td><span class="text-muted">{{ $item->user->email ?? '' }}</span></td>
                            <td><span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $item->title }}">{{ $item->title }}</span></td>
                            <td>{{ $item->location ?? 'غير محدد' }}</td>
                            <td>
                                @if($item->attachment_type == 'image')
                                    <span class="badge bg-primary">صورة</span>
                                @elseif($item->attachment_type == 'video')
                                    <span class="badge bg-warning text-dark">فيديو</span>
                                @elseif($item->attachment_type == 'audio')
                                    <span class="badge bg-info text-white">صوت</span>
                                @else
                                    <span class="badge bg-secondary">لا يوجد</span>
                                @endif
                            </td>
                            <td>
                                @if($item->attachment_path)
                                    @if($item->attachment_type == 'image')
                                        <img src="{{ asset($item->attachment_path) }}" alt="مرفق" class="rounded border" style="width: 45px; height: 45px; object-fit: cover; cursor: pointer;" onclick="openAdminImagePreview('{{ asset($item->attachment_path) }}', '{{ $item->title }}')">
                                    @else
                                        <a href="{{ asset($item->attachment_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-download"></i> تحميل
                                        </a>
                                    @endif
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status == 'approved')
                                    <span class="badge bg-success text-white">مقبول</span>
                                @elseif($item->status == 'rejected')
                                    <span class="badge bg-danger text-white">مرفوض</span>
                                    @if($item->rejection_reason)
                                        <br>
                                        <small class="text-danger d-block mt-1 font-semibold" style="max-width: 150px; white-space: normal;" title="{{ $item->rejection_reason }}">السبب: {{ $item->rejection_reason }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary text-white">قيد الانتظار</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <!-- View Details Button -->
                                    <button type="button" class="btn btn-sm btn-info text-white view-details-btn" 
                                            data-title="{{ $item->title }}"
                                            data-content="{{ $item->content }}"
                                            data-location="{{ $item->location ?? 'غير محدد' }}"
                                            data-user-name="{{ $item->user->fname ?? 'غير محدد' }}"
                                            data-user-email="{{ $item->user->email ?? '' }}"
                                            data-user-phone="{{ $item->user->phone ?? 'غير محدد' }}"
                                            data-attachment-path="{{ $item->attachment_path ? asset($item->attachment_path) : '' }}"
                                            data-attachment-type="{{ $item->attachment_type }}"
                                            data-status="{{ $item->status }}"
                                            data-rejection-reason="{{ $item->rejection_reason }}"
                                            data-date="{{ $item->created_at->format('Y-m-d H:i') }}"
                                            title="عرض التفاصيل">
                                        <i class="bx bx-show-alt"></i>
                                    </button>

                                    <!-- Status actions -->
                                    @if($item->status != 'approved')
                                        <form action="{{ route('admin.news_eye.update_status') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="موافق ونشر">
                                                <i class="bx bx-check"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if($item->status != 'rejected')
                                        <form action="{{ route('admin.news_eye.update_status') }}" method="POST" class="d-inline reject-form">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $item->id }}">
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="rejection_reason" class="rejection-reason-input">
                                            <button type="button" class="btn btn-sm btn-warning text-dark reject-btn" title="رفض">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Delete action -->
                                    <a href="{{ route('admin.news_eye.delete', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف">
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
<div class="modal fade" id="newsDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-bold">تفاصيل الخبر المرسل</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-right">
                <div class="row g-3" style="direction: rtl;">
                    <!-- User Details -->
                    <div class="col-md-6 border-start">
                        <h6 class="text-primary font-bold mb-3"><i class="bx bxs-user-detail"></i> بيانات المرسل</h6>
                        <p class="mb-2"><strong>الاسم:</strong> <span id="modal-user-name" class="font-semibold text-dark"></span></p>
                        <p class="mb-2"><strong>البريد الإلكتروني:</strong> <span id="modal-user-email"></span></p>
                        <p class="mb-2"><strong>الهاتف:</strong> <span id="modal-user-phone"></span></p>
                        <p class="mb-2"><strong>الموقع الجغرافي للخبر:</strong> <span id="modal-location" class="font-medium text-dark"></span></p>
                        <p class="mb-2"><strong>تاريخ الإرسال:</strong> <span id="modal-date"></span></p>
                    </div>
                    <!-- Status & Meta -->
                    <div class="col-md-6">
                        <h6 class="text-primary font-bold mb-3"><i class="bx bx-info-circle"></i> حالة الخبر</h6>
                        <p class="mb-2"><strong>حالة الخبر الحالية:</strong> <span id="modal-status-badge"></span></p>
                        <p class="mb-2"><strong>نوع المرفق:</strong> <span id="modal-attachment-type"></span></p>
                        <div id="modal-rejection-reason-wrapper" class="mt-2 d-none">
                            <p class="mb-2 text-danger"><strong>سبب الرفض:</strong> <span id="modal-rejection-reason" class="font-semibold text-danger"></span></p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Content Details -->
                    <div class="col-12">
                        <h6 class="text-primary font-bold mb-2"><i class="bx bx-heading"></i> عنوان الخبر</h6>
                        <h5 class="mb-3 font-bold text-dark" id="modal-title"></h5>
                        
                        <h6 class="text-primary font-bold mb-2"><i class="bx bx-detail"></i> محتوى وتفاصيل الخبر</h6>
                        <div class="p-3 bg-light rounded text-dark" id="modal-content" style="white-space: pre-wrap; min-height: 100px; line-height: 1.6;"></div>
                    </div>
                    
                    <!-- Media Attachment -->
                    <div class="col-12">
                        <h6 class="text-primary font-bold mb-2"><i class="bx bx-paperclip"></i> المرفقات</h6>
                        <div class="d-flex justify-content-center p-3 bg-light rounded overflow-hidden" style="max-height: 400px;" id="modal-media-section">
                            <img id="modal-img" src="" alt="مرفق صورة" class="img-fluid rounded d-none" style="max-height: 350px; object-fit: contain;" />
                            <video id="modal-video" controls class="w-100 rounded d-none" style="max-height: 350px;"></video>
                            <audio id="modal-audio" controls class="w-75 d-none"></audio>
                            <span id="modal-no-attachment" class="text-muted d-none">لا توجد مرفقات مع هذا الخبر</span>
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
        $('.view-details-btn').on('click', function() {
            const title = $(this).data('title');
            const content = $(this).data('content');
            const location = $(this).data('location');
            const userName = $(this).data('user-name');
            const userEmail = $(this).data('user-email');
            const userPhone = $(this).data('user-phone');
            const attachmentPath = $(this).data('attachment-path');
            const attachmentType = $(this).data('attachment-type');
            const status = $(this).data('status');
            const rejectionReason = $(this).data('rejection-reason');
            const date = $(this).data('date');
            
            // Populate fields
            $('#modal-title').text(title);
            $('#modal-content').text(content);
            $('#modal-location').text(location);
            $('#modal-user-name').text(userName);
            $('#modal-user-email').text(userEmail);
            $('#modal-user-phone').text(userPhone);
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
            
            // Rejection reason
            if (status === 'rejected' && rejectionReason) {
                $('#modal-rejection-reason').text(rejectionReason);
                $('#modal-rejection-reason-wrapper').removeClass('d-none');
            } else {
                $('#modal-rejection-reason-wrapper').addClass('d-none');
            }
            
            // Reset media elements
            $('#modal-img').addClass('d-none').attr('src', '');
            $('#modal-video').addClass('d-none').attr('src', '');
            $('#modal-audio').addClass('d-none').attr('src', '');
            $('#modal-no-attachment').addClass('d-none');
            
            if (attachmentPath) {
                let typeText = 'ملف';
                if (attachmentType === 'image') {
                    typeText = 'صورة';
                    $('#modal-img').attr('src', attachmentPath).removeClass('d-none');
                } else if (attachmentType === 'video') {
                    typeText = 'فيديو';
                    $('#modal-video').attr('src', attachmentPath).removeClass('d-none');
                    $('#modal-video')[0].load();
                } else if (attachmentType === 'audio') {
                    typeText = 'ملف صوتي';
                    $('#modal-audio').attr('src', attachmentPath).removeClass('d-none');
                    $('#modal-audio')[0].load();
                }
                $('#modal-attachment-type').text(typeText);
            } else {
                $('#modal-attachment-type').text('لا يوجد');
                $('#modal-no-attachment').removeClass('d-none');
            }
            
            // Show modal
            $('#newsDetailsModal').modal('show');
        });
        
        // Intercept reject button clicks and show SweetAlert2 prompt
        $('.reject-btn').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('.reject-form');
            
            Swal.fire({
                title: 'تأكيد رفض الخبر',
                text: 'الرجاء إدخال سبب رفض هذا الخبر:',
                input: 'textarea',
                inputPlaceholder: 'اكتب سبب الرفض هنا...',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'تأكيد الرفض',
                cancelButtonText: 'إلغاء',
                inputValidator: (value) => {
                    if (!value || !value.trim()) {
                        return 'يجب كتابة سبب الرفض لتتمكن من المتابعة!';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.find('.rejection-reason-input').val(result.value);
                    form.submit();
                }
            });
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
