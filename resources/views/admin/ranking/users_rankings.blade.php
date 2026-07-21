@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">رتب ومستويات المستخدمين</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">سجل نقاط ورتب المستخدمين</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        
        <!-- فلتر تصفية المستخدمين حسب الرتبة -->
        <form method="GET" action="{{ route('users.rankings') }}" class="mb-4">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label for="rank_id" class="form-label fw-bold"><i class="bx bx-filter-alt text-primary"></i> تصفية حسب الرتبة والمستوى:</label>
                    <select name="rank_id" id="rank_id" class="form-select border-2 border-primary-subtle" onchange="this.form.submit()">
                        <option value="">-- كل المستخدمين --</option>
                        @foreach($rankings as $rank)
                            <option value="{{ $rank->id }}" {{ $selectedRankId == $rank->id ? 'selected' : '' }}>
                                {{ $rank->rank_name }} 
                                (
                                @if($rank->is_last)
                                    {{ $rank->rank_start_point }} نقطة فأكثر
                                @else
                                    من {{ $rank->rank_start_point }} إلى {{ $rank->rank_end_point }} نقطة
                                @endif
                                )
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mt-2 mt-md-0">
                    @if(!empty($selectedRankId))
                        <a href="{{ route('users.rankings') }}" class="btn btn-secondary w-100"><i class="bx bx-refresh"></i> إعادة تعيين</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>المستخدم</th>
                        <th>النقاط الحالية</th>
                        <th>الرتبة الحالية</th>
                        <th>نطاق الرتبة الحالي</th>
                        <th>حالة الحساب</th>
                        <th>تاريخ التسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ (!empty($item->profile_picture)) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->profile_picture : url('upload/no_image.jpg') }}" class="rounded-circle p-1 border shadow-sm me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0">{{ $item->first_name }} {{ $item->last_name }}</h6>
                                    <small class="text-muted">{{ $item->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $item->points ?? 0 }} <small>نقطة</small></span>
                        </td>
                        <td>
                            @if($item->current_rank)
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $rankImg = url('upload/no_image.jpg');
                                        if(!empty($item->current_rank->photo) && file_exists(public_path('upload/rankings/'.$item->current_rank->photo))) {
                                            $rankImg = asset('upload/rankings/'.$item->current_rank->photo);
                                        }
                                    @endphp
                                    <img src="{{ $rankImg }}" alt="{{ $item->current_rank->rank_name }}" class="rounded-circle border" style="width: 30px; height: 30px; object-fit: cover;">
                                    <span class="badge bg-success-subtle text-success border border-success fs-6">
                                        {{ $item->current_rank->rank_name }}
                                    </span>
                                </div>
                            @else
                                <span class="badge bg-light text-secondary border">بدون رتبة</span>
                            @endif
                        </td>
                        <td>
                            @if($item->current_rank)
                                @if($item->current_rank->is_last)
                                    <small class="text-muted">تبدأ من {{ $item->current_rank->rank_start_point }} نقطة فأكثر</small>
                                @else
                                    <small class="text-muted">من {{ $item->current_rank->rank_start_point }} إلى {{ $item->current_rank->rank_end_point }}</small>
                                @endif
                            @else
                                <small class="text-muted">-</small>
                            @endif
                        </td>
                        <td>
                            @if($item->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success">نشط</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger">معطل</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d') : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
