@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #d4af37; padding-left: 15px;">البحث الشامل</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">البحث العام</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <!-- Search Form Section -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #d4af37 !important;">
        <div class="card-body p-4 p-md-5">
            <h4 class="mb-3 text-dark font-weight-bold">البحث الموحد في المنصة</h4>
            <p class="text-secondary small mb-4">ابحث عن أي موضوع، مجموعة، أو مستخدم مسجل في المنصة من خلال هذا النموذج الموحد.</p>

            <form method="GET" action="{{ route('admin.global_search') }}">
                <!-- Search input and button -->
                <div class="input-group input-group-lg mb-4 shadow-none">
                    <input type="text" name="query" class="form-control border-light-success @error('query') is-invalid @enderror" value="{{ $query }}" placeholder="اكتب كلمة البحث هنا (مثال: أحمد، موضوع رياضي، مجموعة الحكماء)..." required style="border-radius: 0 8px 8px 0;">
                    <button type="submit" class="btn btn-success px-4" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%); border: none; border-radius: 8px 0 0 8px;">
                        <i class="bx bx-search-alt fs-5 me-1"></i> ابدأ البحث
                    </button>
                </div>

                <!-- Filters -->
                <div class="d-flex flex-wrap gap-4 align-items-center">
                    <span class="text-secondary font-weight-bold small">نطاق البحث:</span>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="users" id="typeUsers" {{ in_array('users', $types) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark small font-weight-bold" for="typeUsers">
                            الأشخاص والمستخدمين
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="posts" id="typePosts" {{ in_array('posts', $types) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark small font-weight-bold" for="typePosts">
                            المواضيع والمنشورات
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="types[]" value="groups" id="typeGroups" {{ in_array('groups', $types) ? 'checked' : '' }}>
                        <label class="form-check-label text-dark small font-weight-bold" for="typeGroups">
                            المجموعات والصفحات
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Search Results Section -->
    @if(!empty($query))
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                    <h5 class="mb-0 text-dark font-weight-bold">نتائج البحث عن: <span class="text-success">"{{ $query }}"</span></h5>
                    <span class="badge bg-light-success text-success font-weight-bold px-3 py-2">
                        إجمالي النتائج: {{ $users->count() + $posts->count() + $groups->count() + $groupSites->count() }}
                    </span>
                </div>

                <!-- Nav Tabs -->
                <ul class="nav nav-tabs nav-primary mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tabUsers" role="tab" aria-selected="true">
                            <i class="bx bx-user fs-5"></i>
                            <span>الأشخاص</span>
                            <span class="badge rounded-pill bg-success">{{ $users->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tabPosts" role="tab" aria-selected="false">
                            <i class="bx bx-detail fs-5"></i>
                            <span>المواضيع</span>
                            <span class="badge rounded-pill bg-info">{{ $posts->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab" href="#tabGroups" role="tab" aria-selected="false">
                            <i class="bx bx-hive fs-5"></i>
                            <span>المجموعات</span>
                            <span class="badge rounded-pill bg-warning text-dark">{{ $groups->count() + $groupSites->count() }}</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content">
                    
                    <!-- 1. Users Tab -->
                    <div class="tab-pane fade show active" id="tabUsers" role="tabpanel">
                        @if($users->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>المستخدم</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>الهاتف</th>
                                            <th class="text-center">النقاط</th>
                                            <th>تاريخ الانضمام</th>
                                            <th class="text-center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            @php
                                                $userPhoto = (!empty($user->profile_picture) && $user->profile_picture != 'non') 
                                                    ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$user->profile_picture) 
                                                    : url('upload/no_image.jpg');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ $userPhoto }}" alt="{{ $user->first_name }}" class="rounded-circle me-2" style="width: 42px; height: 42px; object-fit: cover; border: 1px solid #ddd;">
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold text-dark">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                                            <span class="badge bg-light-success text-success small" style="font-size: 0.7rem;">{{ $user->role }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone_number ?? 'غير مسجل' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark font-weight-bold px-2 py-1">{{ $user->points ?? 0 }}</span>
                                                </td>
                                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-2">
                                                        <a href="{{ route('edit.user', $user->id) }}" class="btn btn-sm btn-info text-white"><i class="bx bx-edit"></i> تعديل</a>
                                                        <a href="{{ route('delete.user', $user->id) }}" id="delete" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> حذف</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-user-x fs-1 text-secondary opacity-50 mb-3"></i>
                                <h6 class="text-muted">لا توجد نتائج بحث مطابقة في الأشخاص.</h6>
                            </div>
                        @endif
                    </div>

                    <!-- 2. Posts Tab -->
                    <div class="tab-pane fade" id="tabPosts" role="tabpanel">
                        @if($posts->count() > 0)
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                @foreach($posts as $post)
                                    @php
                                        $postOwnerPhoto = (!empty($post->user->profile_picture) && $post->user->profile_picture != 'non') 
                                            ? (filter_var($post->user->profile_picture, FILTER_VALIDATE_URL) ? $post->user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$post->user->profile_picture) 
                                            : url('upload/no_image.jpg');
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border shadow-none" style="border-radius: 8px;">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="{{ $postOwnerPhoto }}" alt="owner" class="rounded-circle me-2" style="width: 38px; height: 38px; object-fit: cover; border: 1px solid #ddd;">
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $post->user->first_name }} {{ $post->user->last_name }}</h6>
                                                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                                <p class="text-secondary small mb-4" style="line-height: 1.6; min-height: 50px;">
                                                    {{ Str::limit(strip_tags($post->content), 150) }}
                                                </p>
                                                
                                                <div class="d-flex align-items-center gap-3 mb-3 border-top border-bottom py-2">
                                                    <span class="small text-secondary"><i class="bx bx-message-square-detail text-success"></i> {{ $post->comment_count ?? 0 }} تعليق</span>
                                                    <span class="small text-secondary"><i class="bx bx-like text-primary"></i> {{ $post->like_count ?? 0 }} إعجاب</span>
                                                    <span class="small text-secondary"><i class="bx bx-share text-warning"></i> {{ $post->share_count ?? 0 }} مشاركة</span>
                                                </div>

                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('posts.comments', $post->id) }}" class="btn btn-sm btn-outline-success"><i class="bx bx-message-detail"></i> التعليقات</a>
                                                    <a href="{{ route('posts.reactions', $post->id) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-like"></i> التفاعلات</a>
                                                    <a href="{{ route('edit.post', $post->id) }}" class="btn btn-sm btn-info text-white"><i class="bx bx-edit"></i> تعديل</a>
                                                    <a href="{{ route('delete.post', $post->id) }}" id="delete" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> حذف</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-message-alt-x fs-1 text-secondary opacity-50 mb-3"></i>
                                <h6 class="text-muted">لا توجد نتائج بحث مطابقة في المواضيع.</h6>
                            </div>
                        @endif
                    </div>

                    <!-- 3. Groups Tab -->
                    <div class="tab-pane fade" id="tabGroups" role="tabpanel">
                        @if(($groups->count() + $groupSites->count()) > 0)
                            <div class="row row-cols-1 row-cols-md-2 g-4">
                                <!-- Standard Groups -->
                                @foreach($groups as $group)
                                    @php
                                        $groupPhoto = (!empty($group->image) && $group->image != 'non') 
                                            ? 'http://localhost:8888/new_wiselook/uploads/'.basename($group->image) 
                                            : url('upload/no_image.jpg');
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border shadow-none" style="border-radius: 8px;">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="{{ $groupPhoto }}" alt="group" class="rounded-3 me-2" style="width: 48px; height: 48px; object-fit: cover; border: 1px solid #ddd;">
                                                    <div class="text-truncate">
                                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $group->name }}</h6>
                                                        <span class="badge bg-light-warning text-dark small" style="font-size: 0.65rem;">مجموعة عادية</span>
                                                    </div>
                                                </div>
                                                <p class="text-secondary small mb-4" style="line-height: 1.6; min-height: 50px;">
                                                    {{ Str::limit(strip_tags($group->descriptions), 120) }}
                                                </p>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3 border-top pt-2">
                                                    <span class="small text-muted">المنشئ: {{ $group->creator->first_name ?? 'غير معروف' }}</span>
                                                    <span class="badge bg-light-success text-success">{{ $group->member_count ?? 0 }} عضو</span>
                                                </div>

                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('groups.members', $group->id) }}" class="btn btn-sm btn-outline-success"><i class="bx bx-group"></i> الأعضاء</a>
                                                    <a href="{{ route('edit.group', $group->id) }}" class="btn btn-sm btn-info text-white"><i class="bx bx-edit"></i> تعديل</a>
                                                    <a href="{{ route('delete.group', $group->id) }}" id="delete" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> حذف</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Private/Public GroupSites -->
                                @foreach($groupSites as $gs)
                                    @php
                                        $gsPhoto = (!empty($gs->image_path) && $gs->image_path != 'non') 
                                            ? 'http://localhost:8888/new_wiselook/uploads/'.basename($gs->image_path) 
                                            : url('upload/no_image.jpg');
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border shadow-none" style="border-radius: 8px;">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center mb-3">
                                                    <img src="{{ $gsPhoto }}" alt="group site" class="rounded-3 me-2" style="width: 48px; height: 48px; object-fit: cover; border: 1px solid #ddd;">
                                                    <div class="text-truncate">
                                                        <h6 class="mb-0 font-weight-bold text-dark">{{ $gs->title }}</h6>
                                                        <span class="badge bg-light-primary text-primary small" style="font-size: 0.65rem;">مجموعة خاصة وعامة</span>
                                                    </div>
                                                </div>
                                                <p class="text-secondary small mb-4" style="line-height: 1.6; min-height: 50px;">
                                                    {{ Str::limit(strip_tags($gs->description), 120) }}
                                                </p>
                                                
                                                <div class="d-flex align-items-center justify-content-between mb-3 border-top pt-2">
                                                    <span class="small text-muted">المشرف: {{ $gs->admin->first_name ?? 'غير معروف' }}</span>
                                                    <span class="badge bg-info text-white">{{ $gs->status == 1 ? 'عامة' : 'خاصة' }}</span>
                                                </div>

                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('group_sites.members', $gs->id) }}" class="btn btn-sm btn-outline-success"><i class="bx bx-group"></i> الأعضاء</a>
                                                    <a href="{{ route('edit.group_site', $gs->id) }}" class="btn btn-sm btn-info text-white"><i class="bx bx-edit"></i> تعديل</a>
                                                    <a href="{{ route('delete.group_site', $gs->id) }}" id="delete" class="btn btn-sm btn-danger"><i class="bx bx-trash"></i> حذف</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-hive fs-1 text-secondary opacity-50 mb-3"></i>
                                <h6 class="text-muted">لا توجد نتائج بحث مطابقة في المجموعات.</h6>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .border-light-success:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }
    .nav-tabs.nav-primary .nav-link.active {
        border-color: #198754 #198754 #fff;
        color: #198754;
        font-weight: bold;
    }
    .nav-tabs.nav-primary .nav-link {
        color: #495057;
    }
    .nav-tabs.nav-primary .nav-link:hover {
        border-color: #e9ecef #e9ecef #fff;
        color: #0f5132;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(25, 135, 84, 0.02);
    }
</style>

@endsection
