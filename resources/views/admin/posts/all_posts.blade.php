@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل المواضيع</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group gap-2">
            <button type="button" onclick="submitBulkDelete()" class="btn btn-danger me-2">
                <i class="fa-solid fa-trash me-1"></i> حذف المحدد (<span class="selectedCount">0</span>)
            </button>
            <a href="{{ route('add.post') }}">
                <button type="button" class="btn btn-primary">اضافة موضوع جديد</button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        <!-- Search and Info Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-3 mb-4">
            <form action="{{ route('all.posts') }}" method="GET" class="d-flex align-items-center gap-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="ابحث بالمحتوى أو الناشر..." value="{{ request('search') }}" style="max-width: 250px;">
                    <select name="media_filter" class="form-select" style="max-width: 220px;">
                        <option value="">جميع الوسائط (الكل)</option>
                        <option value="has_media" {{ request('media_filter') == 'has_media' ? 'selected' : '' }}>يحتوي على صورة أو فيديو</option>
                        <option value="has_image" {{ request('media_filter') == 'has_image' ? 'selected' : '' }}>يحتوي على صورة فقط</option>
                        <option value="has_video" {{ request('media_filter') == 'has_video' ? 'selected' : '' }}>يحتوي على فيديو فقط</option>
                        <option value="no_media" {{ request('media_filter') == 'no_media' ? 'selected' : '' }}>لا يحتوي على وسائط</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> تصفية</button>
                    @if(request('search') || request('media_filter'))
                        <a href="{{ route('all.posts') }}" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> إلغاء</a>
                    @endif
                </div>
            </form>
            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="submitBulkDelete()" class="btn btn-danger">
                    <i class="fa-solid fa-trash me-1"></i> حذف المحدد (<span class="selectedCount">0</span>)
                </button>
                <div class="badge bg-dark text-white p-2" style="font-size: 14px;">
                    إجمالي المواضيع: {{ $posts->total() }}
                </div>
            </div>
        </div>

        <form id="bulkDeleteForm" action="{{ route('posts.bulk.delete') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table id="postsTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllPosts" class="form-check-input" style="cursor: pointer;">
                            </th>
                            <th>الرقم</th>
                            <th>الناشر</th>
                            <th>نوع الموضوع</th>
                            <th>المحتوى / السؤال</th>
                            <th>الوسائط المرفقة</th>
                            <th>التفاعلات</th>
                            <th>التعليقات</th>
                            <th>الخصوصية</th>
                            <th>تاريخ النشر</th>
                            <th>الاجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $key => $item)
                        <tr>
                            <td class="text-center align-middle">
                                <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input post-checkbox" style="cursor: pointer;">
                            </td>
                            <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $key + 1 }}</td>
                            <td>
                                @if($item->user)
                                    <div class="d-flex align-items-center">
                                        <img class="rounded-circle me-2" onclick="showMediaModal('image', this.src)" src="{{ (!empty($item->user->profile_picture) && $item->user->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->user->profile_picture : url('upload/no_image.jpg') }}" style="width: 35px; height: 35px; border: 1px solid #ddd; cursor: pointer;">
                                        <span>{{ $item->user->first_name }}</span>
                                    </div>
                                @else
                                    <span class="text-muted">غير معروف</span>
                                @endif
                            </td>
                            <td>
                                @if($item->post_type_id == 2)
                                    <span class="badge bg-info text-dark" style="font-size: 13px;"><i class="fa-solid fa-square-poll-horizontal me-1"></i> استطلاع رأي</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 13px;"><i class="fa-solid fa-newspaper me-1"></i> منشور عادي</span>
                                @endif
                            </td>
                            <td>
                                <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->content }}">
                                    <strong>{{ $item->content }}</strong>
                                </div>
                                @if($item->post_type_id == 2 && $item->poll && $item->poll->options)
                                    <ul class="list-unstyled mt-1 ps-2" style="font-size: 12px; margin-bottom: 0;">
                                        @foreach($item->poll->options as $opt)
                                            <li>
                                                <i class="fa-regular fa-circle-dot text-primary me-1" style="font-size: 10px;"></i> 
                                                {{ $opt->content }} 
                                                <span class="text-muted" style="font-size: 11px;">({{ $opt->vote_count }} أصوات)</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($item->pin)
                                    <span class="badge bg-danger mt-1" style="font-size: 11px;"><i class="fa-solid fa-thumbtack me-1"></i> مثبت ({{ $item->pin->pin_scope == 'home' ? 'الرئيسية' : 'الملف الشخصي' }})</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="font-size: 13px; border: 1px solid #ddd; cursor: pointer;" onclick="showReactions({{ $item->id }})">
                                    <i class="fa-solid fa-thumbs-up text-primary me-1"></i> {{ $item->reactions_count }} تفاعل
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark" style="font-size: 13px; border: 1px solid #ddd; cursor: pointer;" onclick="showComments({{ $item->id }})">
                                    <i class="fa-solid fa-comments text-success me-1"></i> {{ $item->comment_count }} تعليق
                                </span>
                            </td>
                            <td>
                                @if($item->privacy_level_id == 1)
                                    <span class="badge bg-success" style="font-size: 12px;"><i class="fa-solid fa-globe"></i> عام</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size: 12px;"><i class="fa-solid fa-lock"></i> خاص</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>
                            <td>
                                @if($item->is_active == 1)
                                    <a href="{{ route('inactive.post', $item->id) }}" class="btn btn-sm btn-primary" title="ايقاف التفعيل"> <i class="fa-solid fa-thumbs-down"></i> </a>
                                @else
                                    <a href="{{ route('active.post', $item->id) }}" class="btn btn-sm btn-primary" title="تفعيل"> <i class="fa-solid fa-thumbs-up"></i> </a>
                                @endif
                                @if($item->pin)
                                    <a href="{{ route('posts.unpin', $item->id) }}" class="btn btn-sm btn-warning text-white" title="إلغاء التثبيت"> <i class="fa-solid fa-ban"></i> </a>
                                @else
                                    <a href="{{ route('posts.pin.form', $item->id) }}" class="btn btn-sm btn-secondary text-white" style="background-color: #6c757d; border-color: #6c757d;" title="تثبيت الموضوع"> <i class="fa-solid fa-thumbtack"></i> </a>
                                @endif
                                <button type="button" onclick="showPostDetails({{ $item->id }})" class="btn btn-sm btn-dark text-white me-1" title="عرض التفاصيل" style="background-color: #3b3f5c; border-color: #3b3f5c;">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <a href="{{ route('edit.post', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل"> <i class="fa fa-pencil"></i> </a>
                                <a href="{{ route('delete.post', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف" ><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>الرقم</th>
                            <th>الناشر</th>
                            <th>نوع الموضوع</th>
                            <th>المحتوى / السؤال</th>
                            <th>الوسائط المرفقة</th>
                            <th>التفاعلات</th>
                            <th>التعليقات</th>
                            <th>الخصوصية</th>
                            <th>تاريخ النشر</th>
                            <th>الاجراء</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </form>
        
        <!-- Pagination Links -->
        <div class="d-flex justify-content-center mt-4">
            {{ $posts->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-labelledby="mediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content position-relative bg-transparent border-0">
            <!-- Rectangular Close Button -->
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close"
                style="
                  position: absolute;
                  top: 15px;
                  right: 15px;
                  background-color: black;
                  font-size: 30px;
                  padding: 1px 10px;
                  border-radius: 8px;
                  z-index: 1055;
                ">
                &times;
            </button>
            <div class="text-center p-3">
                <img id="modalImage" src="" class="img-fluid rounded shadow d-none" alt="image" style="max-height: 80vh;">
                <video id="modalVideo" src="" controls class="w-100 rounded shadow d-none" style="max-height: 80vh;"></video>
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
</script>

<!-- Reactions Modal -->
<div class="modal fade" id="reactionsModal" tabindex="-1" aria-labelledby="reactionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reactionsModalLabel">المتفاعلون مع هذا الموضوع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                <ul class="list-group list-group-flush" id="reactionsList">
                    <!-- سيتم تعبئتها ديناميكياً باستخدام الـ AJAX -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1" aria-labelledby="commentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel">التعليقات والردود</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                <div id="commentsContainer">
                    <!-- سيتم تعبئتها ديناميكياً باستخدام الـ AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showReactions(postId) {
        const reactionsList = document.getElementById('reactionsList');
        reactionsList.innerHTML = '<li class="list-group-item text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> جاري التحميل...</li>';
        
        var myModal = new bootstrap.Modal(document.getElementById('reactionsModal'));
        myModal.show();

        // AJAX Request
        fetch(`/admin/posts/${postId}/reactions`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    reactionsList.innerHTML = '';
                    if (data.reactions.length === 0) {
                        reactionsList.innerHTML = '<li class="list-group-item text-center text-muted">لا توجد تفاعلات بعد</li>';
                    } else {
                        data.reactions.forEach(reaction => {
                            let icon = '<i class="fa-solid fa-thumbs-up text-primary"></i>';
                            
                            reactionsList.innerHTML += `
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img src="${reaction.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover; border: 1px solid #ddd; cursor: pointer;">
                                        <span>${reaction.user_name}</span>
                                    </div>
                                    <div>
                                        ${icon}
                                    </div>
                                </li>
                            `;
                        });
                    }
                } else {
                    reactionsList.innerHTML = '<li class="list-group-item text-center text-danger">حدث خطأ أثناء تحميل التفاعلات</li>';
                }
            })
            .catch(error => {
                console.error(error);
                reactionsList.innerHTML = '<li class="list-group-item text-center text-danger">حدث خطأ في الاتصال بالخادم</li>';
            });
    }

    function showComments(postId) {
        const commentsContainer = document.getElementById('commentsContainer');
        commentsContainer.innerHTML = '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> جاري تحميل التعليقات...</div>';
        
        var myModal = new bootstrap.Modal(document.getElementById('commentsModal'));
        myModal.show();

        // AJAX Request
        fetch(`/admin/posts/${postId}/comments`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    commentsContainer.innerHTML = '';
                    if (data.comments.length === 0) {
                        commentsContainer.innerHTML = '<div class="text-center text-muted p-3">لا توجد تعليقات على هذا الموضوع بعد</div>';
                    } else {
                        data.comments.forEach(comment => {
                            let repliesHtml = '';
                            if (comment.replies && comment.replies.length > 0) {
                                repliesHtml = `<div class="ms-5 mt-2 border-start ps-3" style="border-width: 2px !important; border-color: #ddd !important;">`;
                                comment.replies.forEach(reply => {
                                    repliesHtml += `
                                        <div class="d-flex align-items-start mb-2">
                                            <img src="${reply.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover; border: 1px solid #ddd; cursor: pointer;">
                                            <div class="bg-light p-2 rounded w-100">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <strong>${reply.user_name}</strong>
                                                    <small class="text-muted" style="font-size: 11px;">${reply.created_at}</small>
                                                </div>
                                                <p class="mb-0 mt-1" style="font-size: 13px; color: #333;">${reply.content}</p>
                                            </div>
                                        </div>
                                    `;
                                });
                                repliesHtml += `</div>`;
                            }

                            commentsContainer.innerHTML += `
                                <div class="mb-4">
                                    <div class="d-flex align-items-start">
                                        <img src="${comment.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #ddd; cursor: pointer;">
                                        <div class="p-2 rounded w-100" style="background-color: #f8f9fa; border: 1px solid #eee;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong>${comment.user_name}</strong>
                                                <small class="text-muted" style="font-size: 11px;">${comment.created_at}</small>
                                            </div>
                                            <p class="mb-0 mt-1" style="font-size: 14px;">${comment.content}</p>
                                        </div>
                                    </div>
                                    ${repliesHtml}
                                </div>
                            `;
                        });
                    }
                } else {
                    commentsContainer.innerHTML = '<div class="text-center text-danger p-3">حدث خطأ أثناء تحميل التعليقات</div>';
                }
            })
            .catch(error => {
                console.error(error);
                commentsContainer.innerHTML = '<div class="text-center text-danger p-3">حدث خطأ في الاتصال بالخادم</div>';
            });
    }
</script>

<!-- CSS for postDetailsModal -->
<style>
#postDetailsModal .nav-link {
    background: rgba(255, 255, 255, 0.05);
    color: #a0aec0;
}
#postDetailsModal .nav-link.active {
    background: #5e72e4 !important;
    color: #fff !important;
    box-shadow: 0 4px 15px rgba(94, 114, 228, 0.4);
}
#postDetailsModal .modal-content::-webkit-scrollbar,
#detailCommentsList::-webkit-scrollbar,
#detailReactionsList::-webkit-scrollbar {
    width: 6px;
}
#postDetailsModal .modal-content::-webkit-scrollbar-thumb,
#detailCommentsList::-webkit-scrollbar-thumb,
#detailReactionsList::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 4px;
}
#postDetailsModal .modal-content::-webkit-scrollbar-track,
#detailCommentsList::-webkit-scrollbar-track,
#detailReactionsList::-webkit-scrollbar-track {
    background: transparent;
}
</style>

<!-- Post Details Modal -->
<div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0" style="border-radius: 16px; overflow: hidden; background: #1e2235; color: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
            <div class="modal-header border-bottom border-secondary" style="background: rgba(255,255,255,0.03);">
                <div class="d-flex align-items-center">
                                                    <img id="detailUserAvatar" src="" onclick="showMediaModal('image', this.src)" class="rounded-circle me-3" style="width: 50px; height: 50px; border: 2px solid #5e72e4; box-shadow: 0 0 10px rgba(94, 114, 228, 0.3); object-fit: cover; cursor: pointer;">
                                                    <div>
                        <h6 class="modal-title mb-0" id="detailUserName" style="font-weight: 700; font-size: 16px; color: #fff;"></h6>
                        <small class="text-muted" style="font-size: 12px;">
                            <span id="detailPostTime"></span>
                            <span class="mx-1">•</span>
                            <span id="detailPostPrivacy"></span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #151824;">
                <!-- Post Content -->
                <div class="post-content-wrap mb-4">
                    <p id="detailPostContent" style="font-size: 18px; line-height: 1.9; color: #ffffff; white-space: pre-wrap; font-weight: 500; letter-spacing: 0.2px; text-shadow: 0 1px 2px rgba(0,0,0,0.1);"></p>
                    
                    <!-- Media Wrap -->
                    <div id="detailMediaWrap" class="mt-3 text-center d-none">
                        <img id="detailPostImage" src="" class="img-fluid rounded shadow-lg d-none" style="max-height: 400px; width: 100%; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);">
                        <video id="detailPostVideo" src="" controls class="w-100 rounded shadow-lg d-none" style="max-height: 400px; border: 1px solid rgba(255,255,255,0.1);"></video>
                    </div>

                    <!-- Poll Wrap -->
                    <div id="detailPollWrap" class="mt-3 p-3 rounded d-none" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                        <h6 style="color: #5e72e4; font-weight: 700; margin-bottom: 15px;"><i class="fa-solid fa-square-poll-horizontal me-2"></i>استطلاع رأي</h6>
                        <div id="detailPollOptions"></div>
                    </div>
                </div>

                <hr class="border-secondary">

                <!-- Tabs for Reactions & Comments -->
                <ul class="nav nav-tabs border-0 justify-content-center mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active border-0 px-4 py-2 me-2" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments-pane" type="button" role="tab" aria-selected="true" style="border-radius: 30px; font-weight: bold; transition: all 0.3s;">
                            <i class="fa-solid fa-comments me-2 text-success"></i>التعليقات (<span id="detailCommentsCount">0</span>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link border-0 px-4 py-2" id="reactions-tab" data-bs-toggle="tab" data-bs-target="#reactions-pane" type="button" role="tab" aria-selected="false" style="border-radius: 30px; font-weight: bold; transition: all 0.3s;">
                            <i class="fa-solid fa-thumbs-up me-2 text-primary"></i>التفاعلات (<span id="detailReactionsCount">0</span>)
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Comments Pane -->
                    <div class="tab-pane fade show active" id="comments-pane" role="tabpanel" tabindex="0">
                        <div id="detailCommentsList" style="max-height: 350px; overflow-y: auto;">
                            <!-- Dynamic Comments -->
                        </div>
                    </div>

                    <!-- Reactions Pane -->
                    <div class="tab-pane fade" id="reactions-pane" role="tabpanel" tabindex="0">
                        <div id="detailReactionsList" class="row g-2" style="max-height: 350px; overflow-y: auto;">
                            <!-- Dynamic Reactions -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showPostDetails(postId) {
        // Find or create bootstrap modal instance
        var modalEl = document.getElementById('postDetailsModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();

        // Elements
        const avatar = document.getElementById('detailUserAvatar');
        const name = document.getElementById('detailUserName');
        const time = document.getElementById('detailPostTime');
        const privacy = document.getElementById('detailPostPrivacy');
        const content = document.getElementById('detailPostContent');
        const mediaWrap = document.getElementById('detailMediaWrap');
        const postImage = document.getElementById('detailPostImage');
        const postVideo = document.getElementById('detailPostVideo');
        const pollWrap = document.getElementById('detailPollWrap');
        const pollOptions = document.getElementById('detailPollOptions');
        const commentsCount = document.getElementById('detailCommentsCount');
        const reactionsCount = document.getElementById('detailReactionsCount');
        const commentsList = document.getElementById('detailCommentsList');
        const reactionsList = document.getElementById('detailReactionsList');

        // Reset to loading state
        name.innerHTML = 'جاري التحميل...';
        time.innerHTML = '';
        privacy.innerHTML = '';
        content.innerHTML = '';
        mediaWrap.classList.add('d-none');
        postImage.classList.add('d-none');
        postVideo.classList.add('d-none');
        pollWrap.classList.add('d-none');
        pollOptions.innerHTML = '';
        commentsCount.innerText = '0';
        reactionsCount.innerText = '0';
        commentsList.innerHTML = '<div class="text-center p-4"><div class="spinner-border spinner-border-sm text-primary"></div> جاري تحميل البيانات...</div>';
        reactionsList.innerHTML = '<div class="text-center p-4"><div class="spinner-border spinner-border-sm text-primary"></div> جاري تحميل البيانات...</div>';

        // Pause/stop previous video
        postVideo.pause();
        postVideo.src = "";

        // API Call
        fetch(`/admin/posts/${postId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const post = data.post;

                    // User
                    if (post.user) {
                        name.innerText = post.user.name;
                        avatar.src = post.user.profile_picture;
                    } else {
                        name.innerText = 'مستخدم غير معروف';
                        avatar.src = '/upload/no_image.jpg';
                    }

                    time.innerText = post.created_at;
                    privacy.innerHTML = post.privacy_level_id === 1 
                        ? '<span class="badge bg-success"><i class="fa-solid fa-globe me-1"></i>عام</span>' 
                        : '<span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i>خاص</span>';

                    content.innerText = post.content || '';

                    // Media
                    if (post.image || post.video) {
                        mediaWrap.classList.remove('d-none');
                        if (post.image) {
                            postImage.src = post.image;
                            postImage.classList.remove('d-none');
                        }
                        if (post.video) {
                            postVideo.src = post.video;
                            postVideo.classList.remove('d-none');
                        }
                    }

                    // Poll
                    if (post.post_type_id === 2 && post.poll) {
                        pollWrap.classList.remove('d-none');
                        let pollHtml = '';
                        const totalVotes = post.poll.total_votes || 0;
                        const votesBase = totalVotes > 0 ? totalVotes : 1;
                        post.poll.options.forEach(opt => {
                            const percent = totalVotes > 0 ? Math.round((opt.vote_count / votesBase) * 100) : 0;
                            pollHtml += `
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1" style="font-size: 13px;">
                                        <span>${opt.content}</span>
                                        <span class="text-muted">${opt.vote_count} أصوات (${percent}%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px; background: rgba(255,255,255,0.08);">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${percent}%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            `;
                        });
                        pollOptions.innerHTML = pollHtml;
                    }

                    // Counts
                    commentsCount.innerText = post.comments.length;
                    reactionsCount.innerText = post.reactions.length;

                    // Comments
                    let commentsHtml = '';
                    if (post.comments.length === 0) {
                        commentsHtml = '<div class="text-center text-muted p-4">لا توجد تعليقات بعد</div>';
                    } else {
                        post.comments.forEach(comment => {
                            let repliesHtml = '';
                            if (comment.replies && comment.replies.length > 0) {
                                repliesHtml = `<div class="ms-5 mt-2 border-start ps-3" style="border-width: 2px !important; border-color: rgba(255,255,255,0.1) !important;">`;
                                comment.replies.forEach(reply => {
                                    repliesHtml += `
                                        <div class="d-flex align-items-start mb-2">
                                            <img src="${reply.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                                            <div class="p-2 rounded w-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <strong style="font-size: 12px; color: #fff;">${reply.user_name}</strong>
                                                    <small class="text-muted" style="font-size: 10px;">${reply.created_at}</small>
                                                </div>
                                                <p class="mb-0 mt-1" style="font-size: 13.5px; line-height: 1.6; color: #e9ecef;">${reply.content}</p>
                                            </div>
                                        </div>
                                    `;
                                });
                                repliesHtml += `</div>`;
                            }

                            commentsHtml += `
                                <div class="mb-3 p-2 rounded" style="background: rgba(255,255,255,0.01);">
                                    <div class="d-flex align-items-start">
                                        <img src="${comment.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                                        <div class="p-2 rounded w-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <strong style="font-size: 13px; color: #fff;">${comment.user_name}</strong>
                                                <small class="text-muted" style="font-size: 11px;">${comment.created_at}</small>
                                            </div>
                                            <p class="mb-0 mt-1" style="font-size: 15px; line-height: 1.7; color: #ffffff;">${comment.content}</p>
                                        </div>
                                    </div>
                                    ${repliesHtml}
                                </div>
                            `;
                        });
                    }
                    commentsList.innerHTML = commentsHtml;

                    // Reactions
                    let reactionsHtml = '';
                    if (post.reactions.length === 0) {
                        reactionsHtml = '<div class="text-center text-muted p-4 col-12">لا توجد تفاعلات بعد</div>';
                    } else {
                        post.reactions.forEach(react => {
                            reactionsHtml += `
                                <div class="col-md-6 col-sm-12">
                                    <div class="d-flex align-items-center p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                                        <img src="${react.profile_picture}" onclick="showMediaModal('image', this.src)" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">
                                        <span style="font-size: 13px; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">${react.user_name}</span>
                                        <i class="fa-solid fa-thumbs-up text-primary ms-auto me-1"></i>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    reactionsList.innerHTML = reactionsHtml;

                } else {
                    name.innerHTML = '<span class="text-danger">حدث خطأ أثناء تحميل البيانات</span>';
                }
            })
            .catch(error => {
                console.error(error);
                name.innerHTML = '<span class="text-danger">خطأ في الاتصال بالخادم</span>';
            });
    }

    document.getElementById('postDetailsModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('detailPostVideo').pause();
    });
</script>

<script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#postsTable')) {
            $('#postsTable').DataTable().destroy();
        }
        $('#postsTable').DataTable({
            "paging": false,
            "info": false,
            "searching": false,
            "ordering": true,
            "order": [], // keep default server-side order
            "columnDefs": [
                { "orderable": false, "targets": 0 } // disable sorting on checkbox column
            ],
            "language": {
                "zeroRecords": "لا توجد نتائج مطابقة"
            }
        });
    });

    // دالة تنفيذ الحذف الجماعي مباشرة
    window.submitBulkDelete = function() {
        var checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
        var count = checkedBoxes.length;

        if (count === 0) {
            alert('يرجى تحديد موضوع واحد على الأقل من الجدول للحذف.');
            return false;
        }

        if (confirm('هل أنت متأكد من رغبتك في حذف المواضيع المحددة (عددها: ' + count + ' موضوع)؟')) {
            document.getElementById('bulkDeleteForm').submit();
        }
    };

    // تحديث عداد العناصر المحددة
    function updateSelectedCounter() {
        var count = document.querySelectorAll('.post-checkbox:checked').length;
        var countSpans = document.querySelectorAll('.selectedCount');
        countSpans.forEach(function(span) {
            span.textContent = count;
        });

        var total = document.querySelectorAll('.post-checkbox').length;
        var selectAll = document.getElementById('selectAllPosts');
        if (selectAll && total > 0) {
            selectAll.checked = (count === total);
        }
    }

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'selectAllPosts') {
            var isChecked = e.target.checked;
            var checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = isChecked;
            });
            updateSelectedCounter();
        } else if (e.target && e.target.classList.contains('post-checkbox')) {
            updateSelectedCounter();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target && (e.target.id === 'selectAllPosts' || e.target.classList.contains('post-checkbox'))) {
            setTimeout(updateSelectedCounter, 10);
        }
    });
</script>

@endsection
