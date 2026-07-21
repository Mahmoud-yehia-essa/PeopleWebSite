@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة الترجمات</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.languages') }}"><i class="bx bx-font"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">ترجمات لغة: {{ $language->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto d-flex align-items-center gap-3">
        <!-- مبدل لغة سريع -->
        <div class="d-flex align-items-center gap-2">
            <label class="mb-0 text-nowrap small fw-bold text-secondary">اختر لغة أخرى:</label>
            <select class="form-select form-select-sm" onchange="window.location.href='/admin/translations/language/'+this.value">
                @foreach($languages as $lang)
                    <option value="{{ $lang->id }}" {{ $lang->id == $language->id ? 'selected' : '' }}>
                        {{ $lang->name }} ({{ $lang->code }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <a href="{{ route('add.translation', $language->id) }}">
                <button type="button" class="btn btn-sm btn-primary"><i class="bx bx-plus"></i> إضافة ترجمة جديدة</button>
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
                        <th>المفتاح البرمجي (Key)</th>
                        <th>النص المترجم (Value)</th>
                        <th>آخر تحديث</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($translations as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            <code class="text-primary fw-bold" style="font-size: 13px;">{{ $item->key }}</code>
                        </td>
                        <td>
                            <span class="text-dark">{{ $item->value }}</span>
                        </td>
                        <td>{{ $item->updated_at ? $item->updated_at->format('Y-m-d H:i') : ($item->created_at ? $item->created_at->format('Y-m-d H:i') : 'غير محدد') }}</td>
                        <td>
                            <a href="{{ route('edit.translation', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل قيمة الترجمة"> 
                                <i class="fa fa-pencil"></i> تعديل
                            </a>
                            <a href="{{ route('delete.translation', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف الترجمة">
                                <i class="fa fa-trash"></i> حذف
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
