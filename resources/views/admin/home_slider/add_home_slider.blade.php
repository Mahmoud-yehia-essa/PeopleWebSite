@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">إضافة سلايدر / معرض جديد</h5>
            <form method="POST" action="{{ route('add.home_slider.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان (اختياري)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف (اختياري)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                        @error('description') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">نوع المرفق</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select name="attachment_type" id="attachment_type" class="form-select">
                            <option value="image" {{ old('attachment_type') == 'image' ? 'selected' : '' }}>صورة</option>
                            <option value="video" {{ old('attachment_type') == 'video' ? 'selected' : '' }}>فيديو</option>
                        </select>
                        @error('attachment_type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المرفق (صورة أو فيديو)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="attachment" class="form-control" id="attachment">
                        
                        <div id="preview_container" class="mt-2" style="display: none;">
                            <img id="showImage" src="" alt="Preview Image" width="200" class="rounded d-none" style="max-height: 200px; object-fit: contain;">
                            <video id="showVideo" src="" class="rounded d-none" width="300" controls style="max-height: 200px;"></video>
                        </div>

                        @error('attachment') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة">
                        <a href="{{ route('all.home_sliders') }}" class="btn btn-light px-4">إلغاء</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        function handleFilePreview() {
            const fileInput = $('#attachment')[0];
            const type = $('#attachment_type').val();
            const showImage = $('#showImage');
            const showVideo = $('#showVideo');
            const container = $('#preview_container');

            showImage.addClass('d-none').attr('src', '');
            showVideo.addClass('d-none').attr('src', '');
            container.hide();

            if (fileInput.files && fileInput.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    if (type === 'image') {
                        showImage.attr('src', e.target.result).removeClass('d-none');
                    } else if (type === 'video') {
                        showVideo.attr('src', e.target.result).removeClass('d-none');
                    }
                    container.show();
                };
                reader.readAsDataURL(fileInput.files[0]);
            }
        }

        $('#attachment').change(function(){
            handleFilePreview();
        });

        $('#attachment_type').change(function(){
            handleFilePreview();
        });
    });
</script>
@endsection
