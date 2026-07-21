@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة اللغات والترجمات</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">اللغات المتاحة</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.language') }}">
                <button type="button" class="btn btn-primary"><i class="bx bx-plus"></i> إضافة لغة جديدة</button>
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
                        <th>العلم</th>
                        <th>اسم اللغة</th>
                        <th>كود اللغة</th>
                        <th>الاتجاه</th>
                        <th>الافتراضية</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($languages as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @php
                                $isEmoji = !empty($item->flag_path) && !str_contains($item->flag_path, '.') && !filter_var($item->flag_path, FILTER_VALIDATE_URL);
                                $flagUrl = url('upload/no_image.jpg');
                                if (!$isEmoji && !empty($item->flag_path)) {
                                    $flagUrl = filter_var($item->flag_path, FILTER_VALIDATE_URL) ? $item->flag_path : 'http://localhost:8888/new_wiselook/uploads/' . basename($item->flag_path);
                                }
                            @endphp
                            @if($isEmoji)
                                <span style="font-size: 26px; line-height: 1; display: inline-block;">{{ $item->flag_path }}</span>
                            @else
                                <img src="{{ $flagUrl }}" class="rounded shadow-sm border" style="width: 38px; height: 25px; object-fit: cover;">
                            @endif
                        </td>
                        <td>
                            <strong>{{ $item->name }}</strong>
                        </td>
                        <td>
                            <code class="text-dark bg-light px-2 py-1 border rounded">{{ $item->code }}</code>
                        </td>
                        <td>
                            @if($item->direction === 'rtl')
                                <span class="badge bg-light text-dark border">يمين إلى يسار (RTL)</span>
                            @else
                                <span class="badge bg-light text-dark border">يسار إلى يمين (LTR)</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_default)
                                <span class="badge bg-success"><i class="bx bx-check-circle"></i> افتراضية</span>
                            @else
                                <span class="badge bg-light text-secondary border">-</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="badge bg-success-subtle text-success border border-success">نشطة</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger">معطلة</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : 'غير محدد' }}</td>
                        <td>
                            <a href="{{ route('all.translations', $item->id) }}" class="btn btn-sm btn-success text-white" title="إدارة ترجمات هذه اللغة">
                                <i class="fa fa-language"></i> الترجمات
                            </a>
                            <a href="{{ route('edit.language', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل اللغة"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            @if(!$item->is_default)
                                <a href="{{ route('delete.language', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف اللغة بالكامل">
                                    <i class="fa fa-trash"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
