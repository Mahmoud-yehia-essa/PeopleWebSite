@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">خيارات تثبيت موضوع</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('posts.pin.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $post->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Post Content Preview -->
                            <div class="row mb-4">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">محتوى المنشور</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="p-3 bg-light rounded border">
                                        <strong>{{ $post->content }}</strong>
                                        @if($post->image)
                                            <div class="mt-2">
                                                <img src="{{ 'http://localhost:8888/new_wiselook/uploads/'.$post->image }}" class="rounded shadow-sm" style="max-height: 100px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Pin Scope (نطاق التثبيت) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نطاق التثبيت (Scope)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="pin_scope" class="form-select" required>
                                        <option value="home" {{ old('pin_scope') == 'home' ? 'selected' : '' }}>الرئيسية (Home Feed)</option>
                                        <option value="profile" {{ old('pin_scope') == 'profile' ? 'selected' : '' }}>الملف الشخصي للناشر (Profile)</option>
                                    </select>
                                    @error('pin_scope') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Pin Order (ترتيب التثبيت) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">ترتيب التثبيت (Pin Order)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="pin_order" type="number" class="form-control" value="{{ old('pin_order', 0) }}" min="0" required />
                                    <small class="text-muted">الرقم المخصص لتحديد أولوية الظهور بين الموضوعات المثبتة.</small>
                                    @error('pin_order') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Submit / Cancel Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تثبيت الموضوع الآن" />
                                    <a href="{{ route('all.posts') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
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
