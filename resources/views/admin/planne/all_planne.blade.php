@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الخطط</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.plan') }}">
                <button type="button" class="btn btn-primary">
                    إضافة خطة
                </button>
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
                        <th>العنوان</th>
                        <th>تاريخ الإضافة</th>
                        <th>السعر</th>
                         <th>الخطة مقترحة</th>

                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->title }}</td>
                        <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>
                        <td>{{ number_format($item->price, 2) }} د.ك</td>
                        <td>

                            @if ($item->is_suggest === 'yes')
                                نعم
                            @else
                                لا
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('edit.plan', $item->id) }}" class="btn btn-info">تعديل</a>
                            <a href="{{ route('delete.plan', $item->id) }}" class="btn btn-danger" id="delete">حذف</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>العنوان</th>
                        <th>تاريخ الإضافة</th>
                        <th>السعر</th>
                        <th>الإجراءات</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
