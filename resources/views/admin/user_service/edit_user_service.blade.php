@extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h4 class="mb-4">تعديل خدمة المستخدم</h4>

            <form method="POST" action="{{ route('update.user_service') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $value->id }}">

                <!-- اختيار المستخدم -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المستخدم</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="user_id" class="form-control">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $value->user_id==$user->id?'selected':'' }}>
                                    {{ $user->fname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- اختيار الخدمة -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الخدمة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="service_id" class="form-control">
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ $value->service_id==$service->id?'selected':'' }}>
                                    {{ $service->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- الوصف -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالعربية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control">{{ $value->des }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالإنجليزية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control">{{ $value->des_en }}</textarea>
                    </div>
                </div>

                <!-- ملف المستخدم -->
                {{-- <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">ملف المستخدم</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="attach" class="form-control mb-2">
                        @if($value->attach)
                            <a href="{{ asset($value->attach) }}" target="_blank">الملف الحالي</a>
                        @endif
                    </div>
                </div> --}}


                 <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">ملف المستخدم</h6></div>
                    <div class="col-sm-9 text-secondary">
                         @if($value->attach)
                            <a href="{{ asset($value->attach) }}" target="_blank">الملف الحالي</a>
                        @endif
                    </div>
                </div>
                <!-- حالة الطلب -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">حالة الطلب</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="status" class="form-control">
                            <option value="pending" {{ $value->status=='pending'?'selected':'' }}>جاري مراجعة الطلب</option>
                            <option value="approved" {{ $value->status=='approved'?'selected':'' }}> التأكيد وتحديد السعر</option>
                            <option value="working" {{ $value->status=='working'?'selected':'' }}>اعتماد العمل</option>

                            <option value="completed" {{ $value->status=='completed'?'selected':'' }}>العمل مكتمل</option>


                            <option value="rejected" {{ $value->status=='rejected'?'selected':'' }}>رفض الطلب</option>

                        </select>
                    </div>
                </div>

                <!-- السعر والدفعات -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">السعر الكلي</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="total_price" class="form-control" value="{{ $value->total_price ?? '' }}" step="0.01">
                                                        @error('total_price') <span class="text-danger">{{ $message }}</span> @enderror

                    </div>
                </div>

                {{-- <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">عدد الدفعات</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" name="num_payments" class="form-control" value="{{ $value->num_payments ?? '' }}">
                        <small class="text-muted">يتم إنشاء الدفعات تلقائياً بعد الحفظ.</small>
                    </div>
                </div> --}}

                <!-- الملف النهائي (Admin) -->
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الملف النهائي (Admin)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="admin_attach" class="form-control mb-2">
                        @if($value->admin_attach)
                            <a href="{{ asset($value->admin_attach) }}" target="_blank">الملف النهائي الحالي</a>
                        @endif
                    </div>
                </div>

                <!-- زر الحفظ -->
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ التغييرات">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
