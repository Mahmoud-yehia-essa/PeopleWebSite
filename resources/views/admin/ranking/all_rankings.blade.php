@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة الرتب والمستويات</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">رتب الموقع المتاحة</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.ranking') }}">
                <button type="button" class="btn btn-primary"><i class="bx bx-plus"></i> إضافة رتبة جديدة</button>
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
                        <th>الترتيب</th>
                        <th>اسم الرتبة</th>
                        <th>الوصف</th>
                        <th>نطاق النقاط المطلوبة</th>
                        <th>المكافأة (عند الوصول للرتبة)</th>
                        <th>الرتبة الأخيرة؟</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankings as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item->rank_order }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $imageSrc = url('upload/no_image.jpg');
                                    if(!empty($item->photo) && file_exists(public_path('upload/rankings/'.$item->photo))) {
                                        $imageSrc = asset('upload/rankings/'.$item->photo);
                                    }
                                @endphp
                                <img src="{{ $imageSrc }}" alt="{{ $item->rank_name }}" class="rounded-circle border p-0.5" style="width: 42px; height: 42px; object-fit: cover;">
                                <strong>{{ $item->rank_name }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">{{ Str::limit($item->rank_description, 50) ?: '-' }}</span>
                        </td>
                        <td>
                            @if($item->is_last)
                                <span class="badge bg-success-subtle text-success border border-success">
                                    تبدأ من {{ $item->rank_start_point }} نقطة فأكثر
                                </span>
                            @else
                                <span class="badge bg-light text-dark border">
                                    من {{ $item->rank_start_point }} إلى {{ $item->rank_end_point }} نقطة
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($item->level_reward_amount > 0)
                                <strong class="text-success">{{ number_format($item->level_reward_amount) }}</strong> <small class="text-muted">نقطة/مكافأة</small>
                            @else
                                <span class="text-muted">لا يوجد مكافأة</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_last)
                                <span class="badge bg-danger"><i class="bx bx-check-circle"></i> نعم (القصوى)</span>
                            @else
                                <span class="badge bg-light text-secondary border">لا</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('edit.ranking', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل الرتبة"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            <a href="{{ route('delete.ranking', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف الرتبة">
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

@endsection
