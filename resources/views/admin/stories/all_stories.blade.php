@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة القصص (Stories)</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.story') }}">
                <button type="button" class="btn btn-primary">إضافة قصة جديدة</button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>الناشر</th>
                        <th>محتوى القصة</th>
                        <th>الوسائط المرفقة</th>
                        <th>المشاهدات</th>
                        <th>تاريخ الانتهاء</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stories as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @if($item->user)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ (!empty($item->user->profile_picture) && $item->user->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->user->profile_picture : url('upload/no_image.jpg') }}" style="width: 35px; height: 35px; border: 1px solid #ddd; object-fit: cover;">
                                    <span>{{ $item->user->first_name }} {{ $item->user->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">مستخدم غير معروف</span>
                            @endif
                        </td>
                        <td>
                            <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->content }}">
                                {{ $item->content ?: 'لا يوجد نص' }}
                            </div>
                        </td>
                        <td>
                            @if($item->image)
                                @php
                                    $imageUrl = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : 'http://localhost:8888/new_wiselook/uploads/' . basename($item->image);
                                @endphp
                                <img onclick="showMediaModal('image', '{{ $imageUrl }}')" src="{{ $imageUrl }}" class="rounded shadow-sm" style="width: 45px; height: 45px; cursor: pointer; object-fit: cover; border: 1px solid #eee;">
                            @elseif($item->video)
                                @php
                                    $videoUrl = filter_var($item->video, FILTER_VALIDATE_URL) ? $item->video : 'http://localhost:8888/new_wiselook/uploads/' . basename($item->video);
                                @endphp
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showMediaModal('video', '{{ $videoUrl }}')">
                                    <i class="fa-solid fa-play"></i> فيديو
                                </button>
                            @else
                                <span class="text-muted small">لا يوجد وسائط</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border" style="font-size: 13px; cursor: pointer;" onclick="showViewersModal({{ $item->id }})" title="عرض من شاهد القصة">
                                <i class="fa-regular fa-eye text-primary me-1"></i> {{ $item->views_count }} مشاهدة
                            </span>
                        </td>
                        <td>
                            @if($item->expires_at)
                                @if($item->expires_at->isPast())
                                    <span class="text-danger small fw-bold" title="{{ $item->expires_at->toDateTimeString() }}">
                                        <i class="fa-regular fa-calendar-times"></i> منتهية ({{ $item->expires_at->diffForHumans() }})
                                    </span>
                                @else
                                    <span class="text-success small fw-bold" title="{{ $item->expires_at->toDateTimeString() }}">
                                        <i class="fa-regular fa-clock"></i> ينتهي ({{ $item->expires_at->diffForHumans() }})
                                    </span>
                                @endif
                            @else
                                <span class="text-muted small">غير محدد</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active == 1)
                                <span class="badge bg-success" style="font-size: 12px;">نشط</span>
                            @else
                                <span class="badge bg-secondary" style="font-size: 12px;">معطل</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active == 1)
                                <a href="{{ route('inactive.story', $item->id) }}" class="btn btn-sm btn-primary" title="إيقاف تفعيل القصة"> 
                                    <i class="fa-solid fa-thumbs-down"></i> 
                                </a>
                            @else
                                <a href="{{ route('active.story', $item->id) }}" class="btn btn-sm btn-primary" title="تفعيل القصة"> 
                                    <i class="fa-solid fa-thumbs-up"></i> 
                                </a>
                            @endif
                            <a href="{{ route('edit.story', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل القصة"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            <a href="{{ route('delete.story', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف القصة نهائياً">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>الناشر</th>
                        <th>محتوى القصة</th>
                        <th>الوسائط المرفقة</th>
                        <th>المشاهدات</th>
                        <th>تاريخ الانتهاء</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content position-relative bg-transparent border-0">
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close"
                style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index: 1055;">
                &times;
            </button>
            <div class="text-center p-3">
                <img id="modalImage" src="" class="img-fluid rounded shadow d-none" alt="image" style="max-height: 80vh;">
                <video id="modalVideo" src="" controls class="w-100 rounded shadow d-none" style="max-height: 80vh;"></video>
            </div>
        </div>
    </div>
</div>

<!-- Viewers Modal -->
<div class="modal fade" id="viewersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fa-solid fa-users text-primary me-2"></i>من شاهد هذه القصة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="viewersLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
                <div id="viewersEmpty" class="text-center py-4 d-none">
                    <i class="fa-regular fa-face-frown text-muted fs-1 mb-2"></i>
                    <p class="text-muted mb-0">لا توجد مشاهدات لهذه القصة بعد.</p>
                </div>
                <ul class="list-group list-group-flush d-none" id="viewersList">
                    <!-- Loaded dynamically via JS -->
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function showMediaModal(type, src) {
        const modalImg = document.getElementById('modalImage');
        const modalVid = document.getElementById('modalVideo');
        
        // إيقاف تشغيل الفيديو القديم وإخفاء العناصر
        modalVid.pause();
        modalVid.src = "";
        modalImg.src = "";
        modalImg.classList.add('d-none');
        modalVid.classList.add('d-none');

        if (type === 'image') {
            modalImg.src = src;
            modalImg.classList.remove('d-none');
        } else if (type === 'video') {
            modalVid.src = src;
            modalVid.classList.remove('d-none');
        }

        var myModal = new bootstrap.Modal(document.getElementById('mediaModal'));
        myModal.show();
    }

    // إيقاف الفيديو عند إغلاق المودال
    document.getElementById('mediaModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalVideo').pause();
    });

    function showViewersModal(storyId) {
        const modalList = document.getElementById('viewersList');
        const loading = document.getElementById('viewersLoading');
        const empty = document.getElementById('viewersEmpty');
        
        // Reset state
        modalList.innerHTML = '';
        modalList.classList.add('d-none');
        loading.classList.remove('d-none');
        empty.classList.add('d-none');
        
        var myModal = new bootstrap.Modal(document.getElementById('viewersModal'));
        myModal.show();
        
        fetch(`/admin/stories/${storyId}/viewers`)
            .then(response => response.json())
            .then(data => {
                loading.classList.add('d-none');
                
                if (data.success && data.viewers.length > 0) {
                    data.viewers.forEach(viewer => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex align-items-center justify-content-between py-3';
                        li.innerHTML = `
                            <div class="d-flex align-items-center">
                                <img src="${viewer.profile_picture}" class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover; border: 1px solid #eee;">
                                <div>
                                    <h6 class="mb-0 fw-bold">${viewer.user_name}</h6>
                                    <small class="text-muted">${viewer.email}</small>
                                </div>
                            </div>
                            <span class="badge bg-light text-secondary border small">
                                <i class="fa-regular fa-clock me-1"></i>${viewer.viewed_at}
                            </span>
                        `;
                        modalList.appendChild(li);
                    });
                    modalList.classList.remove('d-none');
                } else {
                    empty.classList.remove('d-none');
                }
            })
            .catch(error => {
                loading.classList.add('d-none');
                empty.classList.remove('d-none');
                empty.querySelector('p').innerText = 'حدث خطأ أثناء جلب البيانات.';
                console.error('Error fetching viewers:', error);
            });
    }
</script>

@endsection
