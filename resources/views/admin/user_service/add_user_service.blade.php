@extends('admin.master_admin')
@section('admin')
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">إضافة خدمة مستخدم</h4>

            <form method="POST" action="{{ route('store.user_service') }}" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المستخدم</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="user_id" class="form-control">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->fname }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الخدمة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="service_id" class="form-control">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">{{ $service->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالعربية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control">{{ old('des') }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالإنجليزية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control">{{ old('des_en') }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">ملف المستخدم</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="attach" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">حالة الطلب</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="status" class="form-control">
                            <option value="pending_review">جاري مراجعة الطلب</option>
                            <option value="approved">تمت الموافقة وتحديد السعر والدفعات</option>
                            <option value="rejected">رفض الطلب</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">السعر الكلي</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="total_price" class="form-control" step="0.01">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">عدد الدفعات</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="num_payments" class="form-control">
                        <small class="text-muted">سيتم إنشاء الدفعات تلقائياً بعد الحفظ.</small>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة">
                    </div>
                </div>





            </form>
        </div>
    </div>
</div>
@endsection
