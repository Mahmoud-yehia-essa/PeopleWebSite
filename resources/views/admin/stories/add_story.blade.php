@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إضافة قصة جديدة</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.story') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Publisher (الناشر) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">ناشر القصة (Publisher)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="user_id" class="form-select" required>
                                        <option value="" selected disabled>اختر ناشر القصة...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email ?? $user->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Content (نص القصة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نص القصة (اختياري)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <textarea name="content" rows="3" class="form-control" placeholder="أدخل نص القصة هنا...">{{ old('content') }}</textarea>
                                    @error('content') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image (الصورة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">رفع صورة (اختياري)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image" type="file" id="imageInput" class="form-control" accept="image/*" />
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <div class="row mb-3 d-none" id="imagePreviewContainer">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="imagePreview" src="" alt="Story Image" width="120" class="rounded border">
                                </div>
                            </div>

                            <!-- Video (الفيديو) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">رفع فيديو (اختياري)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="video" type="file" id="videoInput" class="form-control" accept="video/*" />
                                    @error('video') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Video Preview -->
                            <div class="row mb-3 d-none" id="videoPreviewContainer">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <video id="videoPreview" controls width="200" class="rounded border"></video>
                                </div>
                            </div>

                            <!-- Expiry (تاريخ انتهاء الصلاحية) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">تاريخ انتهاء الصلاحية</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    @php
                                        // الوقت الافتراضي هو 24 ساعة من الآن
                                        $defaultExpiry = now()->addDay()->format('Y-m-d\TH:i');
                                    @endphp
                                    <input name="expires_at" type="datetime-local" class="form-control" value="{{ old('expires_at', $defaultExpiry) }}" />
                                    <small class="text-muted">ينتهي ظهور القصة تلقائياً بعد 24 ساعة إن ترك فارغاً.</small>
                                    @error('expires_at') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status (الحالة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الحالة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="is_active" class="form-select" required>
                                        <option value="1" {{ old('is_active') != '0' ? 'selected' : '' }}>نشط (تظهر للمستخدمين)</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>معطل (مخفية)</option>
                                    </select>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Submit / Cancel Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="نشر القصة" />
                                    <a href="{{ route('all.stories') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        // معاينة الصورة المرفوعة
        $('#imageInput').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreviewContainer').removeClass('d-none');
            }
            if(e.target.files['0']) {
                reader.readAsDataURL(e.target.files['0']);
            } else {
                $('#imagePreviewContainer').addClass('d-none');
            }
        });

        // معاينة الفيديو المرفوع
        $('#videoInput').change(function(e){
            var file = e.target.files['0'];
            if(file) {
                var fileURL = URL.createObjectURL(file);
                $('#videoPreview').attr('src', fileURL);
                $('#videoPreviewContainer').removeClass('d-none');
            } else {
                $('#videoPreviewContainer').addClass('d-none');
                $('#videoPreview').attr('src', '');
            }
        });
    });
</script>
@endsection
