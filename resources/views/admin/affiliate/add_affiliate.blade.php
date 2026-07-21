@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">التسويق بالعمولة (Affiliate)</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.affiliates') }}"><i class="bx bx-link-external"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">إنشاء رابط جديد</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.affiliate') }}" method="POST">
                    @csrf
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="mb-4">إنشاء رابط تسويق بالعمولة جديد</h5>
                            
                            <!-- اختيار المسوق -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0 align-middle">المستخدم المسوق <span class="text-danger">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                        <option value="">-- اختر المستخدم --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">ملاحظة: تظهر فقط الحسابات النشطة التي لا تملك رابط أفيليت حالياً.</small>
                                </div>
                            </div>

                            <!-- كود الإحالة المخصص -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">كود الإحالة المخصص</h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="مثال: custom_code (اتركه فارغاً للتوليد التلقائي)" />
                                    @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">إذا تم تركه فارغاً، سيقوم النظام بتوليد كود فريد تلقائياً يدمج اسم العضو مع رقم عشوائي.</small>
                                </div>
                            </div>

                            <!-- حالة التفعيل -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">حالة التفعيل</h6>
                                </div>
                                <div class="col-sm-9">
                                    <div class="form-check form-switch">
                                        <input name="is_active" class="form-check-input" type="checkbox" id="activeSwitch" {{ old('is_active', '1') == '1' ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="activeSwitch">تفعيل الرابط للاستخدام الفوري</label>
                                    </div>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary px-4">حفظ الرابط</button>
                                    <a href="{{ route('all.affiliates') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
