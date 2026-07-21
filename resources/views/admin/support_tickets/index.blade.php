@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #008cff; padding-left: 10px; font-weight: bold;">الدعم الفني</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item active" aria-current="page">تذاكر الدعم الفني</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('admin.support_tickets.create') }}" class="btn btn-primary px-4">
            <i class="bx bx-plus-circle"></i> بدء تواصل جديد
        </a>
    </div>
</div>
<!--end breadcrumb-->

<!-- Statistics Cards -->
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-4 mb-4">
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-info shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">إجمالي التذاكر</p>
                        <h4 class="my-1 text-info font-weight-bold">{{ $totalCount }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-info text-info ms-auto">
                        <i class='bx bx-list-ul'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-success shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">تذاكر مفتوحة</p>
                        <h4 class="my-1 text-success font-weight-bold">{{ $openCount }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-success text-success ms-auto">
                        <i class='bx bx-envelope-open'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-warning shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">بانتظار الرد (جاري الرد)</p>
                        <h4 class="my-1 text-warning font-weight-bold">{{ $pendingCount }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-warning text-warning ms-auto">
                        <i class='bx bx-time'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card radius-10 border-start border-0 border-4 border-danger shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <p class="mb-0 text-secondary">تذاكر مغلقة</p>
                        <h4 class="my-1 text-danger font-weight-bold">{{ $closedCount }}</h4>
                    </div>
                    <div class="widgets-icons-2 rounded-circle bg-light-danger text-danger ms-auto">
                        <i class='bx bx-lock'></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.support_tickets.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="status" class="form-label font-weight-bold text-dark">تصفية حسب الحالة</label>
                <select name="status" id="status" class="form-select">
                    <option value="">كل الحالات</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>مفتوحة</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>جاري الرد / معلقة</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>مغلقة</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="priority" class="form-label font-weight-bold text-dark">تصفية حسب الأهمية</label>
                <select name="priority" id="priority" class="form-select">
                    <option value="">كل الأولويات</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>منخفضة</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>متوسطة</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>عالية</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 w-50"><i class="bx bx-filter-alt"></i> تصفية</button>
                <a href="{{ route('admin.support_tickets.index') }}" class="btn btn-secondary px-4 w-50"><i class="bx bx-refresh"></i> إعادة ضبط</a>
            </div>
        </form>
    </div>
</div>

<!-- Tickets Table Card -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered align-middle" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>المستخدم</th>
                        <th>عنوان التذكرة / الموضوع</th>
                        <th class="text-center" style="width: 120px;">درجة الأهمية</th>
                        <th class="text-center" style="width: 120px;">الحالة</th>
                        <th class="text-center" style="width: 150px;">تاريخ الفتح</th>
                        <th class="text-center" style="width: 150px;">آخر تحديث</th>
                        <th class="text-center" style="width: 120px;">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $key => $ticket)
                    <tr>
                        <td class="text-center font-weight-bold">{{ $key + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ (!empty($ticket->user->photo) && $ticket->user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$ticket->user->photo : url('upload/no_image.jpg') }}" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: cover; border: 2px solid #e9ecef;">
                                <div>
                                    <h6 class="mb-0 text-dark font-weight-bold" style="font-size: 14px;">{{ $ticket->user->fname }} {{ $ticket->user->lname }}</h6>
                                    <span class="text-muted" style="font-size: 12px;">{{ $ticket->user->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.support_tickets.show', $ticket->id) }}" class="text-primary font-weight-bold text-decoration-none">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td class="text-center">
                            @if($ticket->priority == 'high')
                                <span class="badge bg-light-danger text-danger border border-danger px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px;">عالية</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge bg-light-warning text-warning border border-warning px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px;">متوسطة</span>
                            @else
                                <span class="badge bg-light-info text-info border border-info px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px;">منخفضة</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($ticket->status == 'open')
                                <span class="badge bg-success px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px; box-shadow: 0 4px 6px rgba(40,167,69,0.15);"><i class="fa-solid fa-envelope-open me-1"></i> مفتوحة</span>
                            @elseif($ticket->status == 'pending')
                                <span class="badge bg-warning text-dark px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px; box-shadow: 0 4px 6px rgba(255,193,7,0.15);"><i class="fa-solid fa-reply me-1"></i> جاري الرد</span>
                            @else
                                <span class="badge bg-dark px-3 py-2" style="font-size: 12px; font-weight: 600; border-radius: 20px;"><i class="fa-solid fa-folder-closed me-1"></i> مغلقة</span>
                            @endif
                        </td>
                        <td class="text-center text-muted" style="font-size: 13px;">
                            {{ $ticket->created_at ? $ticket->created_at->diffForHumans() : 'غير محدد' }}
                        </td>
                        <td class="text-center text-muted" style="font-size: 13px;">
                            {{ $ticket->updated_at ? $ticket->updated_at->diffForHumans() : 'غير محدد' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.support_tickets.show', $ticket->id) }}" class="btn btn-sm btn-info text-white shadow-sm" title="عرض تفاصيل التذكرة والرد">
                                    <i class="fa-solid fa-comments"></i>
                                </a>
                                <a href="{{ route('admin.support_tickets.delete', $ticket->id) }}" class="btn btn-sm btn-danger shadow-sm" id="delete" title="حذف التذكرة">
                                    <i class="fa fa-trash"></i>
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

@endsection
