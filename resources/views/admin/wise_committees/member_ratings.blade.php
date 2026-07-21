@extends('admin.master_admin')
@section('admin')

@php
    $activeTab = 'members';
    if (request()->has('logs_page') || request()->has('wise_user_id') || request()->has('recipient_user_id')) {
        $activeTab = 'logs';
    }
@endphp

<!-- Custom Styling for Member Ratings Dashboard -->
<style>
    .stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .badge-points {
        font-size: 14px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 20px;
        border-bottom: 3px solid transparent;
        transition: all 0.3s ease;
    }
    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: transparent;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #0d6efd;
        border-bottom-color: rgba(13, 110, 253, 0.3);
    }
</style>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #0d6efd; padding-left: 10px; font-weight: bold;">تقييم الأعضاء</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.wise_committees.index') }}">لجنة الحكماء</a></li>
                <li class="breadcrumb-item active" aria-current="page">تقييم الأعضاء وسجل النقاط</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<!-- Header Alert/Status -->
@if(!$isWiseMember)
<div class="alert alert-warning border-0 bg-light-warning alert-dismissible fade show py-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="font-35 text-warning"><i class="bx bx-info-circle"></i></div>
        <div class="ms-3">
            <h6 class="mb-1 text-dark font-weight-bold" style="font-size: 15px;">تنبيه: وضع المشاهدة فقط</h6>
            <div class="text-secondary" style="font-size: 13px;">حسابك الحالي ليس عضواً نشطاً في لجنة الحكماء. يمكنك استعراض قائمة الأعضاء وسجل التقييمات ولكن لا يمكنك تسجيل تقييمات جديدة أو منح نقاط.</div>
        </div>
    </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
    <div class="col">
        <div class="card stat-card border-0 border-start border-3 border-success shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary" style="font-size: 13px;">إجمالي النقاط الممنوحة</p>
                        <h4 class="my-1 text-success font-weight-bold">{{ $stats['total_points'] }}</h4>
                        <p class="mb-0 font-13 text-muted">مجموع رصيد التميز الممنوح من الحكماء</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                        <i class="bx bxs-award"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stat-card border-0 border-start border-3 border-info shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary" style="font-size: 13px;">عدد عمليات التقييم</p>
                        <h4 class="my-1 text-info font-weight-bold">{{ $stats['total_evaluations'] }}</h4>
                        <p class="mb-0 font-13 text-muted">إجمالي المرات التي مُنحت فيها النقاط</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                        <i class="bx bx-history"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stat-card border-0 border-start border-3 border-warning shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary" style="font-size: 13px;">العضو الأكثر تميزاً</p>
                        <h4 class="my-1 text-warning font-weight-bold">
                            @if($stats['top_recipient'])
                                {{ $stats['top_recipient']->fname }} {{ $stats['top_recipient']->lname }} ({{ $stats['top_recipient']->points }} ن)
                            @else
                                لا يوجد حالياً
                            @endif
                        </h4>
                        <p class="mb-0 font-13 text-muted">صاحب الرصيد الأعلى من النقاط</p>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                        <i class="bx bxs-user-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-0">
        <ul class="nav nav-tabs nav-tabs-custom border-bottom" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab == 'members' ? 'active' : '' }}" data-bs-toggle="tab" href="#membersTab" role="tab" aria-selected="{{ $activeTab == 'members' ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class='bx bx-user font-20 me-2'></i>
                        <span>أعضاء المنصة والتقييم المباشر</span>
                    </div>
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link {{ $activeTab == 'logs' ? 'active' : '' }}" data-bs-toggle="tab" href="#logsTab" role="tab" aria-selected="{{ $activeTab == 'logs' ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center">
                        <i class='bx bx-receipt font-20 me-2'></i>
                        <span>سجل التقييمات التي تمت ({{ $logs->total() }})</span>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content">
    
    <!-- Tab 1: Members List -->
    <div class="tab-pane fade {{ $activeTab == 'members' ? 'show active' : '' }}" id="membersTab" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
                    <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 16px;">قائمة الأعضاء المسجلين</h5>
                    <!-- Member Search Form -->
                    <form method="GET" action="{{ route('admin.wise_committees.member_ratings') }}" class="d-flex gap-2 w-100 w-md-auto">
                        <input type="text" name="member_search" class="form-control" placeholder="ابحث بالاسم أو البريد..." value="{{ request('member_search') }}">
                        <button type="submit" class="btn btn-primary"><i class="bx bx-search-alt"></i></button>
                        @if(request('member_search'))
                            <a href="{{ route('admin.wise_committees.member_ratings') }}" class="btn btn-secondary"><i class="bx bx-refresh"></i></a>
                        @endif
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr class="table-light">
                                <th class="text-center" style="width: 5%">#</th>
                                <th style="width: 25%">العضو</th>
                                <th style="width: 25%">البريد الإلكتروني</th>
                                <th class="text-center" style="width: 15%">النقاط الحالية</th>
                                <th style="width: 15%">تاريخ الانضمام</th>
                                <th class="text-center" style="width: 15%">التقييم</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($members as $key => $member)
                            <tr>
                                <td class="text-center">{{ $members->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ (!empty($member->photo) && $member->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$member->photo : url('upload/no_image.jpg') }}" class="rounded-circle me-2 shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                        <span>{{ $member->fname }} {{ $member->lname }}</span>
                                    </div>
                                </td>
                                <td>{{ $member->email }}</td>
                                <td class="text-center">
                                    <span class="badge bg-light-primary text-primary font-weight-bold px-3 py-1" style="font-size: 13px;">
                                        {{ $member->points ?? 0 }} نقطة
                                    </span>
                                </td>
                                <td>{{ $member->created_at ? $member->created_at->format('Y-m-d') : 'غير محدد' }}</td>
                                <td class="text-center">
                                    @if($isWiseMember && $member->id != Auth::id())
                                        <button type="button" class="btn btn-sm btn-success px-3" data-bs-toggle="modal" data-bs-target="#evaluateMemberModal-{{ $member->id }}">
                                            <i class="fa-solid fa-star me-1"></i> تقييم العضو
                                        </button>
                                    @elseif($member->id == Auth::id())
                                        <span class="text-muted" style="font-size: 12.5px;">حسابك الشخصي</span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-secondary px-3" disabled>
                                            <i class="fa-solid fa-lock me-1"></i> غير متاح
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Evaluation Modal for this specific member -->
                            @if($isWiseMember && $member->id != Auth::id())
                            <div class="modal fade" id="evaluateMemberModal-{{ $member->id }}" tabindex="-1" aria-labelledby="evaluateMemberModalLabel-{{ $member->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content text-start">
                                        <div class="modal-header">
                                            <h5 class="modal-title font-weight-bold" id="evaluateMemberModalLabel-{{ $member->id }}">تقييم العضو: {{ $member->fname }} {{ $member->lname }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.wise_committees.store_member_rating') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="recipient_user_id" value="{{ $member->id }}">
                                            
                                            <div class="modal-body">
                                                <div class="alert alert-info border-0 bg-light-info py-2 mb-3" style="font-size: 13px;">
                                                    <i class="fa-solid fa-circle-info me-1"></i>
                                                    النقاط الحالية للعضو: <strong>{{ $member->points ?? 0 }} نقطة</strong>
                                                </div>

                                                <!-- Select Points -->
                                                <div class="mb-3">
                                                    <label for="points_given-{{ $member->id }}" class="form-label font-weight-bold text-dark">عدد النقاط الممنوحة</label>
                                                    <select name="points_given" id="points_given-{{ $member->id }}" class="form-select form-select-lg text-success font-weight-bold" required>
                                                        <option value="5">5 نقاط</option>
                                                        <option value="10" selected>10 نقاط</option>
                                                        <option value="15">15 نقطة</option>
                                                        <option value="20">20 نقطة</option>
                                                        <option value="25">25 نقطة</option>
                                                        <option value="50">50 نقطة</option>
                                                        <option value="100">100 نقطة</option>
                                                    </select>
                                                </div>

                                                <!-- Select Related Post -->
                                                <div class="mb-3">
                                                    <label for="post_id-{{ $member->id }}" class="form-label font-weight-bold text-dark">ربط التقييم بمنشور (اختياري)</label>
                                                    <select name="post_id" id="post_id-{{ $member->id }}" class="form-select">
                                                        <option value="">-- تقييم عام (بدون تحديد منشور) --</option>
                                                        @foreach($member->posts as $post)
                                                            <option value="{{ $post->id }}">
                                                                منشور: {{ Str::limit(strip_tags($post->content), 60) }} ({{ $post->created_at ? $post->created_at->format('Y-m-d') : '' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted d-block mt-1">إذا كان منح النقاط بسبب موضوع متميز، يرجى اختياره من القائمة.</small>
                                                </div>

                                                <!-- Note / Reason -->
                                                <div class="mb-3">
                                                    <label for="note-{{ $member->id }}" class="form-label font-weight-bold text-dark">السبب أو المبرر</label>
                                                    <textarea name="note" id="note-{{ $member->id }}" class="form-control" rows="3" placeholder="اكتب مبررات التقييم ومنح النقاط (مثال: أسلوب حواري راقٍ، مساعدة الأعضاء...)"></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-success px-4">منح النقاط وتأكيد التقييم <i class="fa-solid fa-gift ms-1"></i></button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    لا توجد أي نتائج مطابقة للبحث.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Members -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $members->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab 2: Point Logs -->
    <div class="tab-pane fade {{ $activeTab == 'logs' ? 'show active' : '' }}" id="logsTab" role="tabpanel">
        <!-- Filters Card for Logs -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.wise_committees.member_ratings') }}" class="row g-3 align-items-end">
                    <!-- 1. Select Wise Member -->
                    <div class="col-md-5">
                        <label for="wise_user_id" class="form-label font-weight-bold text-dark" style="font-size: 13.5px;">الحكيم المقيّم (المانح)</label>
                        <select name="wise_user_id" id="wise_user_id" class="form-select">
                            <option value="">كل الحكماء</option>
                            @foreach($wiseMembers as $wm)
                                <option value="{{ $wm->user_id }}" {{ request('wise_user_id') == $wm->user_id ? 'selected' : '' }}>
                                    {{ $wm->user->fname }} {{ $wm->user->lname }} ({{ $wm->specialty ?: 'عام' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- 2. Select Recipient Member -->
                    <div class="col-md-5">
                        <label for="recipient_user_id" class="form-label font-weight-bold text-dark" style="font-size: 13.5px;">العضو المتلقي (المستحق)</label>
                        <select name="recipient_user_id" id="recipient_user_id" class="form-select">
                            <option value="">كل الأعضاء</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ request('recipient_user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->fname }} {{ $u->lname }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- 3. Actions -->
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3 w-50" title="تصفية البحث"><i class="bx bx-filter-alt"></i> تصفية</button>
                        <a href="{{ route('admin.wise_committees.member_ratings') }}" class="btn btn-secondary px-3 w-50" title="إعادة ضبط"><i class="bx bx-refresh"></i> إلغاء</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 font-weight-bold text-dark" style="font-size: 16px;">سجلات منح النقاط بالتفصيل</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr class="table-light">
                                <th class="text-center" style="width: 5%">#</th>
                                <th style="width: 20%">الحكيم المقيّم</th>
                                <th style="width: 20%">العضو المتلقي</th>
                                <th class="text-center" style="width: 12%">النقاط الممنوحة</th>
                                <th style="width: 15%">المنشور المرتبط</th>
                                <th style="width: 18%">الملاحظة / السبب</th>
                                <th style="width: 10%">تاريخ المنح</th>
                                <th class="text-center" style="width: 10%">الاجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $key => $log)
                            <tr>
                                <td class="text-center">{{ $logs->firstItem() + $key }}</td>
                                <td>
                                    @if($log->wiseUser)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ (!empty($log->wiseUser->photo) && $log->wiseUser->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$log->wiseUser->photo : url('upload/no_image.jpg') }}" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                                            <span>{{ $log->wiseUser->fname }} {{ $log->wiseUser->lname }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">حكيم غير معروف</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->recipientUser)
                                        <div class="d-flex align-items-center">
                                            <img src="{{ (!empty($log->recipientUser->photo) && $log->recipientUser->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$log->recipientUser->photo : url('upload/no_image.jpg') }}" class="rounded-circle me-2 shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
                                            <div>
                                                <span class="d-block">{{ $log->recipientUser->fname }} {{ $log->recipientUser->lname }}</span>
                                                <small class="text-muted" style="font-size: 11px;">إجمالي النقاط: <strong>{{ $log->recipientUser->points }}</strong></small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">عضو غير معروف</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light-success text-success border border-success badge-points">
                                        +{{ $log->points_given }} نقاط
                                    </span>
                                </td>
                                <td>
                                    @if($log->post)
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPostModal('{{ addslashes(e($log->post->content)) }}', '{{ $log->post->image ? 'http://localhost:8888/new_wiselook/uploads/'.$log->post->image : '' }}', '{{ $log->post->video ? 'http://localhost:8888/new_wiselook/uploads/'.$log->post->video : '' }}')">
                                            <i class="fa-solid fa-eye me-1"></i> عرض المنشور
                                        </button>
                                    @else
                                        <span class="text-muted" style="font-size: 12.5px;">تقييم عام (بدون منشور)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->note)
                                        <span style="font-size: 13.5px;">{{ $log->note }}</span>
                                    @else
                                        <span class="text-muted font-italic" style="font-size: 12.5px; font-style: italic;">لا توجد ملاحظة</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="font-size: 12.5px;" title="{{ $log->created_at }}">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : 'غير محدد' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.wise_committees.delete_member_rating', $log->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف التقييم وخصم النقاط">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bx bx-receipt fs-2 mb-2 d-block"></i>
                                    لا توجد أي سجلات تقييم للأعضاء حالياً.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for Logs -->
                <div class="d-flex justify-content-center mt-3">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Post Preview Modal -->
<div class="modal fade" id="postPreviewModal" tabindex="-1" aria-labelledby="postPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold" id="postPreviewModalLabel">تفاصيل المنشور المرتبط بالتقييم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <div class="p-3 bg-light rounded text-dark" id="modalPostContent" style="white-space: pre-wrap; font-size: 14.5px; line-height: 1.6;"></div>
                <div class="mt-3 text-center d-none" id="modalPostImageWrapper">
                    <img id="modalPostImage" src="" class="img-fluid rounded shadow-sm" style="max-height: 350px; object-fit: cover;">
                </div>
                <div class="mt-3 text-center d-none" id="modalPostVideoWrapper">
                    <video id="modalPostVideo" src="" controls class="img-fluid rounded shadow-sm" style="max-height: 350px; width: 100%;"></video>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showPostModal(content, imageUrl, videoUrl) {
        document.getElementById('modalPostContent').innerText = content;
        
        const imgWrapper = document.getElementById('modalPostImageWrapper');
        const imgEl = document.getElementById('modalPostImage');
        if (imageUrl) {
            imgEl.src = imageUrl;
            imgWrapper.classList.remove('d-none');
        } else {
            imgEl.src = '';
            imgWrapper.classList.add('d-none');
        }
        
        const vidWrapper = document.getElementById('modalPostVideoWrapper');
        const vidEl = document.getElementById('modalPostVideo');
        vidEl.pause();
        if (videoUrl) {
            vidEl.src = videoUrl;
            vidWrapper.classList.remove('d-none');
        } else {
            vidEl.src = '';
            vidWrapper.classList.add('d-none');
        }
        
        var previewModal = new bootstrap.Modal(document.getElementById('postPreviewModal'));
        previewModal.show();
    }

    document.getElementById('postPreviewModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalPostVideo').pause();
    });
</script>

@endsection
