@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">المجموعات الخاصة والعامة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">عرض كافة المجموعات</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.group_site') }}">
                <button type="button" class="btn btn-primary"><i class="bx bx-plus"></i> إضافة مجموعة جديدة</button>
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
                        <th>عنوان المجموعة</th>
                        <th>الحالة</th>
                        <th>كود الدعوة</th>
                        <th>المشرف</th>
                        <th>عدد الأعضاء</th>
                        <th>عدد المواضيع</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @php
                                $imageUrl = url('upload/no_image.jpg');
                                if (!empty($item->image_path)) {
                                    $imageUrl = filter_var($item->image_path, FILTER_VALIDATE_URL) ? $item->image_path : 'http://localhost:8888/new_wiselook/uploads/' . basename($item->image_path);
                                }
                            @endphp
                            <img onclick="showImageModal('{{ $imageUrl }}')" src="{{ $imageUrl }}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; cursor: pointer; object-fit: cover; border: 1px solid #ddd;">
                        </td>
                        <td>
                            <strong>{{ $item->title }}</strong>
                            @if($item->description)
                                <div class="text-muted small text-truncate" style="max-width: 180px;" title="{{ $item->description }}">
                                    {{ $item->description }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->status === 'open')
                                <span class="badge bg-success">عامة (مفتوحة)</span>
                            @else
                                <span class="badge bg-danger">خاصة (مغلقة)</span>
                            @endif
                        </td>
                        <td>
                            @if($item->invite_code)
                                <code class="text-dark bg-light px-2 py-1 border rounded">{{ $item->invite_code }}</code>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->admin)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ (!empty($item->admin->profile_picture) && $item->admin->profile_picture != 'non' ) ? (filter_var($item->admin->profile_picture, FILTER_VALIDATE_URL) ? $item->admin->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$item->admin->profile_picture) : url('upload/no_image.jpg') }}" style="width: 30px; height: 30px; border: 1px solid #eee; object-fit: cover;">
                                    <span>{{ $item->admin->first_name }} {{ $item->admin->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">غير معروف</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border style-cursor" onclick="showGroupMembers({{ $item->id }}, '{{ $item->title }}')" style="font-size: 13px; cursor: pointer;">
                                <i class="bx bx-group text-primary me-1"></i> {{ $item->members_count }} مشتركين
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border style-cursor" onclick="showGroupSubjects({{ $item->id }}, '{{ $item->title }}')" style="font-size: 13px; cursor: pointer;">
                                <i class="bx bx-comment-detail text-success me-1"></i> {{ $item->subjects_count }} مواضيع
                            </span>
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                        <td>
                            <a href="{{ route('edit.group_site', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل بيانات المجموعة"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            <a href="{{ route('delete.group_site', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف المجموعة بالكامل">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 1. Image View Modal -->
<!-- ============================================== -->
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

<!-- ============================================== -->
<!-- 2. Group Members Modal -->
<!-- ============================================== -->
<div class="modal fade" id="membersModal" tabindex="-1" aria-labelledby="membersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="membersModalLabel">المشاركون في مجموعة: <span id="groupModalTitle" class="text-primary fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 500px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="membersList">
                    <!-- سيتم تحميل الأعضاء ديناميكياً -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 3. Group Subjects Modal -->
<!-- ============================================== -->
<div class="modal fade" id="subjectsModal" tabindex="-1" aria-labelledby="subjectsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="subjectsModalLabel">المواضيع في مجموعة: <span id="groupSubjectModalTitle" class="text-success fw-bold"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 600px; overflow-y: auto;">
                <div class="table-responsive">
                    <table class="table align-middle table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>الكاتب</th>
                                <th>عنوان الموضوع</th>
                                <th>المحتوى / الوصف</th>
                                <th>المرفق</th>
                                <th class="text-center">التفاعل</th>
                                <th class="text-center">الإجراءات والتعليقات</th>
                            </tr>
                        </thead>
                        <tbody id="subjectsTableBody">
                            <!-- سيتم التعبئة بالـ AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 4. Comments Modal -->
<!-- ============================================== -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="commentsModalLabel" style="color: white !important;">التعليقات على موضوع: <span id="commentSubjectTitle" class="fw-bold text-white"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="commentsList">
                    <!-- سيتم تعبئتها بالـ AJAX -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- 5. Reactions Modal -->
<!-- ============================================== -->
<div class="modal fade" id="reactionsModal" tabindex="-1" aria-labelledby="reactionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reactionsModalLabel" style="color: white !important;">التفاعلات على موضوع: <span id="reactionSubjectTitle" class="fw-bold text-white"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 450px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="reactionsList">
                    <!-- سيتم التعبئة ديناميكياً -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- JAVASCRIPT & AJAX CONTROL -->
<!-- ============================================== -->
<script type="text/javascript">
    // فتح شاشة عرض الصورة المكبرة
    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }

    // ==============================================
    // إدارة الأعضاء (View and Kick)
    // ==============================================
    var currentActiveGroupForMembers = null;
    function showGroupMembers(groupId, groupTitle) {
        currentActiveGroupForMembers = groupId;
        document.getElementById('groupModalTitle').innerText = groupTitle;
        const membersList = document.getElementById('membersList');
        membersList.innerHTML = '<li class="list-group-item text-center p-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> جاري تحميل المشاركين...</li>';
        
        var myModal = new bootstrap.Modal(document.getElementById('membersModal'));
        myModal.show();

        loadMembers(groupId);
    }

    function loadMembers(groupId) {
        const membersList = document.getElementById('membersList');
        fetch(`/admin/group-sites/${groupId}/members`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    membersList.innerHTML = '';
                    if (data.members.length === 0) {
                        membersList.innerHTML = '<li class="list-group-item text-center text-muted p-4">لا يوجد أي مشارك في هذه المجموعة بعد.</li>';
                    } else {
                        data.members.forEach(member => {
                            let roleBadge = 'bg-secondary';
                            if (member.role === 'مشرف المجموعة') {
                                roleBadge = 'bg-danger';
                            }
                            
                            let activeBadge = member.is_active == 1 
                                ? '<span class="badge bg-success-subtle text-success border border-success ms-2" style="font-size: 11px;">نشط</span>' 
                                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary ms-2" style="font-size: 11px;">محظور</span>';

                            let kickButton = '';
                            if (member.role !== 'مشرف المجموعة') {
                                kickButton = `
                                    <button class="btn btn-sm btn-outline-danger" onclick="kickMember(${member.member_id}, '${member.user_name}')">
                                        <i class="bx bx-user-minus"></i> طرد
                                    </button>
                                `;
                            }

                            membersList.innerHTML += `
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="${member.profile_picture}" class="rounded-circle me-3" style="width: 42px; height: 42px; object-fit: cover; border: 1px solid #ddd;">
                                        <div>
                                            <h6 class="mb-0 fw-bold">${member.user_name} ${activeBadge}</h6>
                                            <small class="text-muted" style="font-size: 11px;">${member.email} | انضم: ${member.joined_at}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge ${roleBadge}" style="font-size: 12px;">${member.role}</span>
                                        ${kickButton}
                                    </div>
                                </li>
                            `;
                        });
                    }
                } else {
                    membersList.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ أثناء تحميل الأعضاء</li>';
                }
            })
            .catch(error => {
                console.error(error);
                membersList.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ في الاتصال بالخادم</li>';
            });
    }

    function kickMember(memberId, userName) {
        Swal.fire({
            title: `هل أنت متأكد من طرد ${userName}؟`,
            text: "سوف يتم إزالته من هذه المجموعة فوراً.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            cancelButtonText: "إلغاء",
            confirmButtonText: "نعم، قم بالطرد!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('/admin/group-sites/members/kick', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ member_id: memberId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("تم الطرد!", data.message, "success");
                        if (currentActiveGroupForMembers) {
                            loadMembers(currentActiveGroupForMembers);
                        }
                    } else {
                        Swal.fire("تنبيه!", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("خطأ!", "حدث خطأ بالخادم أثناء العملية.", "error");
                });
            }
        });
    }

    // ==============================================
    // إدارة المواضيع (View and Delete)
    // ==============================================
    var currentActiveGroupForSubjects = null;
    function showGroupSubjects(groupId, groupTitle) {
        currentActiveGroupForSubjects = groupId;
        document.getElementById('groupSubjectModalTitle').innerText = groupTitle;
        const tbody = document.getElementById('subjectsTableBody');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center p-4"><div class="spinner-border spinner-border-sm text-success" role="status"></div> جاري تحميل المواضيع...</td></tr>';
        
        var myModal = new bootstrap.Modal(document.getElementById('subjectsModal'));
        myModal.show();

        loadSubjects(groupId);
    }

    function loadSubjects(groupId) {
        const tbody = document.getElementById('subjectsTableBody');
        fetch(`/admin/group-sites/${groupId}/subjects`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tbody.innerHTML = '';
                    if (data.subjects.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-4">لا توجد أي مواضيع منشورة في هذه المجموعة بعد.</td></tr>';
                    } else {
                        data.subjects.forEach(subject => {
                            let attachmentHtml = '<span class="text-muted small">لا يوجد</span>';
                            if (subject.attachment_path) {
                                if (subject.attachment_type === 'image') {
                                    attachmentHtml = `<img src="${subject.attachment_path}" class="rounded border" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;" onclick="showImageModal('${subject.attachment_path}')">`;
                                } else if (subject.attachment_type === 'video') {
                                    attachmentHtml = `
                                        <video width="120" height="70" controls class="rounded border bg-black">
                                            <source src="${subject.attachment_path}">
                                            الفيديو غير مدعوم
                                        </video>
                                    `;
                                } else if (subject.attachment_type === 'audio') {
                                    attachmentHtml = `
                                        <audio style="max-width: 140px;" controls>
                                            <source src="${subject.attachment_path}">
                                            صوت غير مدعوم
                                        </audio>
                                    `;
                                }
                            }

                            tbody.innerHTML += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${subject.author_picture}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #eee;">
                                            <div>
                                                <span class="fw-bold d-block text-nowrap">${subject.author_name}</span>
                                                <small class="text-muted" style="font-size: 10px;">${subject.created_at}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong class="text-dark">${subject.title}</strong></td>
                                    <td>
                                        <div class="text-wrap text-muted small" style="max-width: 250px; max-height: 70px; overflow-y: auto;">
                                            ${subject.description || '<span class="text-muted italic">لا يوجد محتوى</span>'}
                                        </div>
                                    </td>
                                    <td>${attachmentHtml}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-primary border me-1" style="font-size: 11px;">
                                            <i class="bx bx-like text-primary"></i> ${subject.likes}
                                        </span>
                                        <span class="badge bg-light text-danger border" style="font-size: 11px;">
                                            <i class="bx bx-dislike text-danger"></i> ${subject.dislikes}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-outline-success py-1 px-2" onclick="showComments(${subject.id}, '${subject.title.replace(/'/g, "\\'")}')" title="عرض تعليقات الموضوع">
                                                <i class="bx bx-message-rounded-dots"></i> التعليقات
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary py-1 px-2" onclick="showReactions(${subject.id}, '${subject.title.replace(/'/g, "\\'")}')" title="عرض تفاعلات الإعجاب">
                                                <i class="bx bx-smile"></i> التفاعلات
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger py-1" onclick="deleteSubject(${subject.id}, '${subject.title.replace(/'/g, "\\'")}')" title="حذف الموضوع نهائياً">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger p-4">حدث خطأ أثناء تحميل المواضيع</td></tr>';
                }
            })
            .catch(error => {
                console.error(error);
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger p-4">حدث خطأ بالاتصال بالخادم</td></tr>';
            });
    }

    function deleteSubject(subjectId, subjectTitle) {
        Swal.fire({
            title: `حذف موضوع: ${subjectTitle}؟`,
            text: "سوف يتم حذف الموضوع بكافة تعليقاته وتفاعلاته وملفاته نهائياً ولا يمكن استرجاعه.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            cancelButtonText: "إلغاء",
            confirmButtonText: "نعم، حذف نهائي!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/group-sites/subjects/delete/${subjectId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("تم الحذف!", data.message, "success");
                        if (currentActiveGroupForSubjects) {
                            loadSubjects(currentActiveGroupForSubjects);
                        }
                    } else {
                        Swal.fire("خطأ!", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("خطأ!", "حدث خطأ بالخادم أثناء محاولة الحذف.", "error");
                });
            }
        });
    }

    // ==============================================
    // إدارة التعليقات (View and Delete)
    // ==============================================
    var currentActiveSubjectForComments = null;
    function showComments(subjectId, subjectTitle) {
        currentActiveSubjectForComments = subjectId;
        document.getElementById('commentSubjectTitle').innerText = subjectTitle;
        const list = document.getElementById('commentsList');
        list.innerHTML = '<li class="list-group-item text-center p-4"><div class="spinner-border spinner-border-sm text-success" role="status"></div> جاري تحميل التعليقات...</li>';
        
        var myModal = new bootstrap.Modal(document.getElementById('commentsModal'));
        myModal.show();

        loadComments(subjectId);
    }

    function loadComments(subjectId) {
        const list = document.getElementById('commentsList');
        fetch(`/admin/group-sites/subjects/${subjectId}/comments`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    list.innerHTML = '';
                    if (data.comments.length === 0) {
                        list.innerHTML = '<li class="list-group-item text-center text-muted p-4">لا توجد أي تعليقات على هذا الموضوع بعد.</li>';
                    } else {
                        data.comments.forEach(comment => {
                            let attachmentHtml = '';
                            if (comment.attachment_path) {
                                if (comment.attachment_type === 'image') {
                                    attachmentHtml = `<div class="mt-2"><img src="${comment.attachment_path}" class="rounded border shadow-sm" style="max-height: 80px; max-width: 150px; object-fit: cover; cursor: pointer;" onclick="showImageModal('${comment.attachment_path}')"></div>`;
                                } else if (comment.attachment_type === 'video') {
                                    attachmentHtml = `
                                        <div class="mt-2">
                                            <video width="150" height="90" controls class="rounded border bg-black">
                                                <source src="${comment.attachment_path}">
                                            </video>
                                        </div>
                                    `;
                                } else if (comment.attachment_type === 'audio') {
                                    attachmentHtml = `
                                        <div class="mt-2">
                                            <audio style="max-width: 160px;" controls>
                                                <source src="${comment.attachment_path}">
                                            </audio>
                                        </div>
                                    `;
                                }
                            }

                            list.innerHTML += `
                                <li class="list-group-item d-flex justify-content-between align-items-start py-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <img src="${comment.author_picture}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;">
                                        <div>
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-bold me-2">${comment.author_name}</h6>
                                                <small class="text-muted" style="font-size: 10px;">${comment.created_at}</small>
                                            </div>
                                            <p class="mb-0 mt-1 text-dark text-break small" style="white-space: pre-wrap;">${comment.content || '<span class="text-muted italic">لا يوجد نص</span>'}</p>
                                            ${attachmentHtml}
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteComment(${comment.id})" title="حذف هذا التعليق">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </li>
                            `;
                        });
                    }
                } else {
                    list.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ أثناء تحميل التعليقات</li>';
                }
            })
            .catch(error => {
                console.error(error);
                list.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ بالاتصال بالخادم</li>';
            });
    }

    function deleteComment(commentId) {
        Swal.fire({
            title: "حذف التعليق نهائياً؟",
            text: "سوف يتم حذف هذا التعليق ومرفقاته نهائياً ولا يمكن استرجاعه.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            cancelButtonText: "إلغاء",
            confirmButtonText: "نعم، احذف!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/group-sites/comments/delete/${commentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("تم الحذف!", data.message, "success");
                        if (currentActiveSubjectForComments) {
                            loadComments(currentActiveSubjectForComments);
                        }
                    } else {
                        Swal.fire("خطأ!", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("خطأ!", "حدث خطأ بالخادم أثناء محاولة الحذف.", "error");
                });
            }
        });
    }

    // ==============================================
    // إدارة التفاعلات (View and Delete)
    // ==============================================
    var currentActiveSubjectForReactions = null;
    function showReactions(subjectId, subjectTitle) {
        currentActiveSubjectForReactions = subjectId;
        document.getElementById('reactionSubjectTitle').innerText = subjectTitle;
        const list = document.getElementById('reactionsList');
        list.innerHTML = '<li class="list-group-item text-center p-4"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> جاري تحميل التفاعلات...</li>';
        
        var myModal = new bootstrap.Modal(document.getElementById('reactionsModal'));
        myModal.show();

        loadReactions(subjectId);
    }

    function loadReactions(subjectId) {
        const list = document.getElementById('reactionsList');
        fetch(`/admin/group-sites/subjects/${subjectId}/reactions`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    list.innerHTML = '';
                    if (data.reactions.length === 0) {
                        list.innerHTML = '<li class="list-group-item text-center text-muted p-4">لا توجد تفاعلات على هذا الموضوع بعد.</li>';
                    } else {
                        data.reactions.forEach(reaction => {
                            let typeBadge = reaction.type === 'like' 
                                ? '<span class="badge bg-success-subtle text-success border border-success"><i class="bx bx-like"></i> إعجاب</span>' 
                                : '<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bx bx-dislike"></i> عدم إعجاب</span>';

                            list.innerHTML += `
                                <li class="list-group-item d-flex align-items-center justify-content-between py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="${reaction.author_picture}" class="rounded-circle me-3" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;">
                                        <div>
                                            <h6 class="mb-0 fw-bold">${reaction.author_name} ${typeBadge}</h6>
                                            <small class="text-muted" style="font-size: 10px;">التفاعل: ${reaction.created_at}</small>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteReaction(${reaction.id})" title="حذف هذا التفاعل وتحديث العداد">
                                        <i class="bx bx-trash"></i> إلغاء
                                    </button>
                                </li>
                            `;
                        });
                    }
                } else {
                    list.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ أثناء تحميل التفاعلات</li>';
                }
            })
            .catch(error => {
                console.error(error);
                list.innerHTML = '<li class="list-group-item text-center text-danger p-4">حدث خطأ بالاتصال بالخادم</li>';
            });
    }

    function deleteReaction(reactionId) {
        Swal.fire({
            title: "إلغاء التفاعل؟",
            text: "سوف يتم إزالة هذا التفاعل وتحديث عداد الإعجابات الخاص بالموضوع.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            cancelButtonText: "إلغاء",
            confirmButtonText: "نعم، احذف!"
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/admin/group-sites/reactions/delete/${reactionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire("تمت العملية!", data.message, "success");
                        // إعادة جلب التفاعلات والمواضيع بالخلفية لتحديث عداد الـ Like/Dislike
                        if (currentActiveSubjectForReactions) {
                            loadReactions(currentActiveSubjectForReactions);
                        }
                        if (currentActiveGroupForSubjects) {
                            loadSubjects(currentActiveGroupForSubjects);
                        }
                    } else {
                        Swal.fire("خطأ!", data.message, "error");
                    }
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire("خطأ!", "حدث خطأ بالخادم أثناء محاولة الإلغاء.", "error");
                });
            }
        });
    }
</script>
@endsection
