@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة علاقات الصداقة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.friendship') }}">
                <button type="button" class="btn btn-primary">إضافة علاقة جديدة</button>
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
                        <th>مرسل الطلب</th>
                        <th>مستقبل الطلب</th>
                        <th>حالة العلاقة</th>
                        <th>تاريخ الطلب</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($friendships as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @if($item->sender)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ (!empty($item->sender->profile_picture) && $item->sender->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->sender->profile_picture : url('upload/no_image.jpg') }}" style="width: 35px; height: 35px; border: 1px solid #ddd; object-fit: cover;">
                                    <span>{{ $item->sender->first_name }} {{ $item->sender->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">مستخدم غير معروف</span>
                            @endif
                        </td>
                        <td>
                            @if($item->receiver)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" src="{{ (!empty($item->receiver->profile_picture) && $item->receiver->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->receiver->profile_picture : url('upload/no_image.jpg') }}" style="width: 35px; height: 35px; border: 1px solid #ddd; object-fit: cover;">
                                    <span>{{ $item->receiver->first_name }} {{ $item->receiver->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">مستخدم غير معروف</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active == 1)
                                <span class="badge bg-success" style="font-size: 13px;"><i class="fa-solid fa-user-check me-1"></i> أصدقاء (نشط)</span>
                            @else
                                <span class="badge bg-warning text-dark" style="font-size: 13px;"><i class="fa-solid fa-clock me-1"></i> طلب معلق (قيد الانتظار)</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                        <td>
                            @if($item->is_active == 1)
                                <a href="{{ route('inactive.friendship', $item->id) }}" class="btn btn-sm btn-primary" title="إلغاء تفعيل الصداقة (إعادتها لمعلقة)"> 
                                    <i class="fa-solid fa-thumbs-down"></i> 
                                </a>
                            @else
                                <a href="{{ route('active.friendship', $item->id) }}" class="btn btn-sm btn-primary" title="قبول الطلب وتفعيل الصداقة"> 
                                    <i class="fa-solid fa-thumbs-up"></i> 
                                </a>
                            @endif
                            <a href="{{ route('delete.friendship', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف العلاقة نهائياً">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>مرسل الطلب</th>
                        <th>مستقبل الطلب</th>
                        <th>حالة العلاقة</th>
                        <th>تاريخ الطلب</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
