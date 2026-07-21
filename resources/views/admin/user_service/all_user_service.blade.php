@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل خدمات المستخدمين</div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.user_service') }}">
                <button type="button" class="btn btn-primary">إضافة خدمة جديدة</button>
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
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>الخدمة</th>
                        <th>حالة الطلب</th>
                        <th>السعر الكلي</th>
                        {{-- <th>عدد الدفعات</th> --}}
                        <th>الملف</th>
                        <th>الملف النهائي</th>
                        {{-- <th>الدفعات</th> --}}
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->user->fname ?? 'غير محدد' }}</td>
                            <td>{{ $item->service->title ?? 'غير محدد' }}</td>
                            <td>

                                                                           @if($item->status == 'completed')
        <span class="badge bg-success text-white">مكتمل</span>
    @elseif($item->status == 'approved')
        <span class="badge bg-info text-white">الموافقة على العمل وتحديد السعر</span>
    @elseif($item->status == 'working')
        <span class="badge bg-warning text-white">تأكيد العمل وجاري العمل عليه</span>
    @elseif($item->status == 'pending')
        <span class="badge bg-secondary text-white">قيد الانتظار</span>
    @else
        <span class="badge bg-danger text-white">مرفوض</span>
    @endif


                            </td>
                            <td>{{ $item->total_price ?? 0 }}</td>
                            {{-- <td>{{ $item->num_payments ?? 0 }}</td> --}}
                            <td>
                                @if($item->attach)
                                    <a href="{{ asset($item->attach) }}" target="_blank">ملف المستخدم</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($item->admin_attach)
                                    <a href="{{ asset($item->admin_attach) }}" target="_blank">الملف النهائي</a>
                                @else
                                    -
                                @endif
                            </td>
                            {{-- <td>
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>رقم الدفعة</th>
                                            <th>المبلغ</th>
                                            <th>الحالة</th>
                                            <th>تاريخ الاستحقاق</th>
                                            <th>إجراء</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($item->payments as $pay)
                                            <tr>
                                                <td>{{ $pay->payment_number }}</td>
                                                <td>{{ $pay->amount }}</td>
                                                <td>{{ $pay->status }}</td>
                                                <td>{{ $pay->due_date?->format('Y-m-d') ?? '-' }}</td>
                                                <td>
                                                    @if($pay->status != 'paid')
                                                        <form method="POST" action="{{ route('update.payment.status') }}">
                                                            @csrf
                                                            <input type="hidden" name="id" value="{{ $pay->id }}">
                                                            <input type="hidden" name="status" value="paid">
                                                            <button type="submit" class="btn btn-sm btn-success">تأكيد الدفع</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td> --}}
                            <td>


                                    <a href="{{route('all.chat.admin.message',$item->id)}}" type="button" class="btn btn-sm btn-outline-success">
                                        <i class="bi bi-chat-dots"></i>
                                    </a>
                                <a href="{{ route('edit.user_service', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                <a href="{{ route('delete.user_service', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                     <th>#</th>
                        <th>المستخدم</th>
                        <th>الخدمة</th>
                        <th>حالة الطلب</th>
                        <th>السعر الكلي</th>
                        {{-- <th>عدد الدفعات</th> --}}
                        <th>الملف</th>
                        <th>الملف النهائي</th>
                        {{-- <th>الدفعات</th> --}}
                        <th>إجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
