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
                <li class="breadcrumb-item active" aria-current="page">تعديل الرابط</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.affiliate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $link->id }}">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="mb-4">تعديل رابط التسويق بالعمولة</h5>
                            
                            <!-- اسم المسوق (للقراءة فقط) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">المستخدم المسوق</h6>
                                </div>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control bg-light" value="{{ $link->user ? ($link->user->first_name . ' ' . $link->user->last_name . ' (' . $link->user->email . ')') : 'مستخدم محذوف' }}" readonly />
                                </div>
                            </div>

                            <!-- كود الإحالة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">كود الإحالة <span class="text-danger">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="code" type="text" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $link->code) }}" required placeholder="كود الإحالة الفريد" />
                                    @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">ملاحظة: تجنب استخدام المسافات أو الرموز الخاصة في كود الإحالة.</small>
                                </div>
                            </div>

                            <!-- حالة التفعيل -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">حالة التفعيل</h6>
                                </div>
                                <div class="col-sm-9">
                                    <div class="form-check form-switch">
                                        <input name="is_active" class="form-check-input" type="checkbox" id="activeSwitch" {{ old('is_active', $link->is_active) ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="activeSwitch">تفعيل الرابط للاستخدام الفوري</label>
                                    </div>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary px-4">تحديث الرابط</button>
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
