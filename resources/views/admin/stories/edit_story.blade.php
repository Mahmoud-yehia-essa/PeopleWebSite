@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل القصة</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.story') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $story->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Publisher (الناشر) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">ناشر القصة (Publisher)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="user_id" class="form-select" required>
                                        <option value="" disabled>اختر ناشر القصة...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id', $story->user_id) == $user->id ? 'selected' : '' }}>
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
                                    <textarea name="content" rows="3" class="form-control" placeholder="أدخل نص القصة هنا...">{{ old('content', $story->content) }}</textarea>
                                    @error('content') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image (الصورة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">تغيير الصورة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image" type="file" id="imageInput" class="form-control" accept="image/*" />
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image Preview -->
                            @php
                                $hasImage = !empty($story->image);
                                $imageUrl = '';
                                if ($hasImage) {
                                    $imageUrl = filter_var($story->image, FILTER_VALIDATE_URL) ? $story->image : 'http://localhost:8888/new_wiselook/uploads/' . basename($story->image);
                                }
                            @endphp
                            <div class="row mb-3 {{ $hasImage ? '' : 'd-none' }}" id="imagePreviewContainer">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">معاينة الصورة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="imagePreview" src="{{ $imageUrl }}" alt="Story Image" width="120" class="rounded border">
                                </div>
                            </div>

                            <!-- Video (الفيديو) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">تغيير الفيديو</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="video" type="file" id="videoInput" class="form-control" accept="video/*" />
                                    @error('video') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Video Preview -->
                            @php
                                $hasVideo = !empty($story->video);
                                $videoUrl = '';
                                if ($hasVideo) {
                                    $videoUrl = filter_var($story->video, FILTER_VALIDATE_URL) ? $story->video : 'http://localhost:8888/new_wiselook/uploads/' . basename($story->video);
                                }
                            @endphp
                            <div class="row mb-3 {{ $hasVideo ? '' : 'd-none' }}" id="videoPreviewContainer">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">معاينة الفيديو</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <video id="videoPreview" src="{{ $videoUrl }}" controls width="200" class="rounded border"></video>
                                </div>
                            </div>

                            <!-- Expiry (تاريخ انتهاء الصلاحية) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">تاريخ انتهاء الصلاحية</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    @php
                                        $expiryValue = '';
                                        if ($story->expires_at) {
                                            $expiryValue = $story->expires_at->format('Y-m-d\TH:i');
                                        }
                                    @endphp
                                    <input name="expires_at" type="datetime-local" class="form-control" value="{{ old('expires_at', $expiryValue) }}" />
                                    <small class="text-muted">ينتهي ظهور القصة تلقائياً بعد 24 ساعة من تاريخ التحديث إن ترك فارغاً.</small>
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
                                        <option value="1" {{ old('is_active', $story->is_active) == '1' ? 'selected' : '' }}>نشط (تظهر للمستخدمين)</option>
                                        <option value="0" {{ old('is_active', $story->is_active) == '0' ? 'selected' : '' }}>معطل (مخفية)</option>
                                    </select>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Submit / Cancel Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تحديث القصة" />
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
                
                // إخفاء الفيديو لمعاينة الصورة الجديدة
                $('#videoPreviewContainer').addClass('d-none');
                $('#videoPreview').attr('src', '');
            }
            if(e.target.files['0']) {
                reader.readAsDataURL(e.target.files['0']);
            }
        });

        // معاينة الفيديو المرفوع
        $('#videoInput').change(function(e){
            var file = e.target.files['0'];
            if(file) {
                var fileURL = URL.createObjectURL(file);
                $('#videoPreview').attr('src', fileURL);
                $('#videoPreviewContainer').removeClass('d-none');
                
                // إخفاء الصورة لمعاينة الفيديو الجديد
                $('#imagePreviewContainer').addClass('d-none');
                $('#imagePreview').attr('src', '');
            }
        });
    });
</script>
@endsection
