@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #0d6efd; padding-left: 10px; font-weight: bold;">إصدارات التطبيق</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.app_versions.index') }}">إصدارات التطبيق</a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل الإصدار</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-xl-8 mx-auto">
        <div class="card border-top border-0 border-4 border-info shadow-sm">
            <div class="card-body p-5">
                <div class="card-title d-flex align-items-center mb-4">
                    <div><i class="bx bx-pencil me-1 font-22 text-info"></i></div>
                    <h5 class="mb-0 text-info font-weight-bold">تعديل بيانات إصدار التطبيق</h5>
                </div>
                <hr>
                
                <form action="{{ route('admin.app_versions.update') }}" method="POST" class="row g-3">
                    @csrf
                    <input type="hidden" name="id" value="{{ $version->id }}">
                    
                    <!-- 1. رقم الإصدار -->
                    <div class="col-md-6">
                        <label for="version" class="form-label font-weight-bold text-dark">رقم الإصدار <span class="text-danger">*</span></label>
                        <input type="text" name="version" class="form-control @error('version') is-invalid @enderror" id="version" placeholder="مثال: 1.0.0 أو 2.1.4" required value="{{ old('version', $version->version) }}">
                        @error('version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- 2. خيار التحديث الإجباري -->
                    <div class="col-md-6 d-flex align-items-center pt-4 ps-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="update_required" id="update_required" value="1" {{ old('update_required', $version->update_required) ? 'checked' : '' }}>
                            <label class="form-check-label font-weight-bold text-dark ms-2" for="update_required">هل هذا التحديث إجباري؟</label>
                        </div>
                    </div>

                    <!-- 3. رابط تحميل أندرويد -->
                    <div class="col-md-6">
                        <label for="android" class="form-label font-weight-bold text-dark">رابط تطبيق الأندرويد (Google Play)</label>
                        <input type="url" name="android" class="form-control" id="android" placeholder="https://play.google.com/store/apps/..." value="{{ old('android', $version->android) }}">
                    </div>

                    <!-- 4. رابط تحميل آي أو إس -->
                    <div class="col-md-6">
                        <label for="ios" class="form-label font-weight-bold text-dark">رابط تطبيق الآيفون (App Store)</label>
                        <input type="url" name="ios" class="form-control" id="ios" placeholder="https://apps.apple.com/app/..." value="{{ old('ios', $version->ios) }}">
                    </div>

                    <!-- 5. الدعم / التواصل -->
                    <div class="col-12">
                        <label for="contact" class="form-label font-weight-bold text-dark">بيانات التواصل والدعم الفني</label>
                        <input type="text" name="contact" class="form-control" id="contact" placeholder="مثال: بريد الدعم أو رقم واتساب أو رابط مركز المساعدة" value="{{ old('contact', $version->contact) }}">
                    </div>

                    <!-- 6. وصف التحديث -->
                    <div class="col-12">
                        <label for="des" class="form-label font-weight-bold text-dark">ما الجديد في هذا الإصدار (وصف التحديث)</label>
                        <textarea name="des" class="form-control" id="des" rows="5" placeholder="اكتب الميزات والتحسينات المضافة في هذا التحديث...">{{ old('des', $version->des) }}</textarea>
                    </div>

                    <!-- أزرار الإجراءات -->
                    <div class="col-12 text-end mt-4">
                        <a href="{{ route('admin.app_versions.index') }}" class="btn btn-secondary px-4 me-2">إلغاء</a>
                        <button type="submit" class="btn btn-info text-white px-4">تحديث الإصدار <i class="bx bx-save ms-1"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
