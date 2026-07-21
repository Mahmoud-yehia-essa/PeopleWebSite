@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة المجموعات</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.group') }}">
                <button type="button" class="btn btn-primary">إضافة مجموعة جديدة</button>
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
                        <th>الصورة</th>
                        <th>اسم المجموعة</th>
                        <th>المنشئ</th>
                        <th>عدد الأعضاء</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @php
                                $imageUrl = url('upload/no_image.jpg');
                                if (!empty($item->image)) {
                                    $imageUrl = filter_var($item->image, FILTER_VALIDATE_URL) ? $item->image : 'http://localhost:8888/new_wiselook/uploads/' . basename($item->image);
                                }
                            @endphp
                            <img onclick="showImageModal('{{ $imageUrl }}')" src="{{ $imageUrl }}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; cursor: pointer; object-fit: cover; border: 1px solid #ddd;">
                        </td>
                        <td>
                            <strong title="{{ $item->descriptions }}">{{ $item->name }}</strong>
                            @if($item->descriptions)
                                <div class="text-muted small" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $item->descriptions }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->creator)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ (!empty($item->creator->profile_picture) && $item->creator->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->creator->profile_picture : url('upload/no_image.jpg') }}" style="width: 30px; height: 30px; border: 1px solid #eee; object-fit: cover;">
                                    <span>{{ $item->creator->first_name }} {{ $item->creator->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">غير معروف</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark" style="font-size: 13px; border: 1px solid #ddd; cursor: pointer;" onclick="showGroupMembers({{ $item->id }}, '{{ $item->name }}')">
                                <i class="fa-solid fa-users text-primary me-1"></i> {{ $item->member_count }} أعضاء
                            </span>
                        </td>
                        <td>{{ $item->date_created ? date('Y-m-d H:i', strtotime($item->date_created)) : 'غير محدد' }}</td>
                        <td>
                            <a href="{{ route('edit.group', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل بيانات المجموعة"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            <a href="{{ route('delete.group', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف المجموعة">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>الصورة</th>
                        <th>اسم المجموعة</th>
                        <th>المنشئ</th>
                        <th>عدد الأعضاء</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative bg-transparent border-0">
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close"
                style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index: 1055;">
                &times;
            </button>
            <div class="text-center p-3">
                <img id="modalImage" src="" class="img-fluid rounded shadow" alt="image" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<!-- Group Members Modal -->
<div class="modal fade" id="membersModal" tabindex="-1" aria-labelledby="membersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="membersModalLabel">أعضاء مجموعة: <span id="groupModalName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 450px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="membersList">
                    <!-- سيتم تعبئتها ديناميكياً عبر AJAX -->
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }

    function showGroupMembers(groupId, groupName) {
        document.getElementById('groupModalName').innerText = groupName;
        const membersList = document.getElementById('membersList');
        membersList.innerHTML = '<li class="list-group-item text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> جاري تحميل الأعضاء...</li>';
        
        var myModal = new bootstrap.Modal(document.getElementById('membersModal'));
        myModal.show();

        // AJAX Request
        fetch(`/admin/groups/${groupId}/members`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    membersList.innerHTML = '';
                    if (data.members.length === 0) {
                        membersList.innerHTML = '<li class="list-group-item text-center text-muted p-3">لا يوجد أعضاء في هذه المجموعة</li>';
                    } else {
                        data.members.forEach(member => {
                            let badgeClass = 'bg-secondary';
                            if (member.role_name === 'مالك المجموعة') {
                                badgeClass = 'bg-danger';
                            } else if (member.role_name === 'مشرف المجموعة') {
                                badgeClass = 'bg-info text-dark';
                            }
                            
                            let activeBadge = member.is_active == 1 
                                ? '<span class="badge bg-success-subtle text-success border border-success ms-2" style="font-size: 11px;">نشط</span>' 
                                : '<span class="badge bg-danger-subtle text-danger border border-danger ms-2" style="font-size: 11px;">غادر</span>';

                            membersList.innerHTML += `
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="${member.profile_picture}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd;">
                                        <div>
                                            <h6 class="mb-0 fw-bold">${member.user_name} ${activeBadge}</h6>
                                            <small class="text-muted" style="font-size: 11px;">تاريخ الانضمام: ${member.joined_at}</small>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="badge ${badgeClass}" style="font-size: 12px;">${member.role_name}</span>
                                    </div>
                                </li>
                            `;
                        });
                    }
                } else {
                    membersList.innerHTML = '<li class="list-group-item text-center text-danger p-3">حدث خطأ أثناء تحميل الأعضاء</li>';
                }
            })
            .catch(error => {
                console.error(error);
                membersList.innerHTML = '<li class="list-group-item text-center text-danger p-3">حدث خطأ في الاتصال بالخادم</li>';
            });
    }
</script>

@endsection
