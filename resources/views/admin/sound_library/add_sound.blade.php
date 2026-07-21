@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">مكتبة حماك الصوتية</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.sound.libraries') }}">كل الملفات الصوتية</a></li>
                <li class="breadcrumb-item active" aria-current="page">إضافة ملف صوتي</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('store.sound.library') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">عنوان الملف الصوتي / المقطع <span class="text-danger">*</span></h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}" required placeholder="مثال: بودكاست العدد الأول، تقرير السياسة العامة">
                            @error('name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">الفئة الصوتية <span class="text-danger">*</span></h6></div>
                        <div class="col-sm-9 text-secondary">
                            <select name="sound_library_category_id" class="form-select" required>
                                <option value="" selected disabled>اختر الفئة...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('sound_library_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('sound_library_category_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">المؤلف / المقدم (اختياري)</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <select name="sound_author_id" class="form-select">
                                <option value="" selected>اختر المؤلف/المقدم (لا يوجد)...</option>
                                @foreach($authors as $auth)
                                    <option value="{{ $auth->id }}" {{ old('sound_author_id') == $auth->id ? 'selected' : '' }}>{{ $auth->name }}</option>
                                @endforeach
                            </select>
                            @error('sound_author_id') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">نوع المقطع الصوتي <span class="text-danger">*</span></h6></div>
                        <div class="col-sm-9 text-secondary">
                            <select name="sound_type" id="sound_type" class="form-select" required>
                                <option value="recorded" {{ old('sound_type', 'recorded') == 'recorded' ? 'selected' : '' }}>تسجيل صوتي (ملف مرفوع)</option>
                                <option value="episode" {{ old('sound_type') == 'episode' ? 'selected' : '' }}>حلقة إذاعية مسجلة (برنامج)</option>
                                <option value="live" {{ old('sound_type') == 'live' ? 'selected' : '' }}>بث مباشر (Live Stream)</option>
                                <option value="report" {{ old('sound_type') == 'report' ? 'selected' : '' }}>تقرير صوتي (ملف أو رابط)</option>
                            </select>
                            @error('sound_type') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- File Upload Input (Hidden if type is live) -->
                    <div class="row mb-3" id="file_upload_row">
                        <div class="col-sm-3"><h6 class="mb-0">ملف الصوت المرفوع</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="file" name="sound_file" id="soundFileInput" class="form-control" accept="audio/*">
                            <small class="text-muted block mt-1">الصيغ المدعومة: mp3, wav, ogg, aac, m4a (الحد الأقصى 50 ميجابايت)</small>
                            @error('sound_file') <div class="text-danger mt-1">{{ $message }}</div> @enderror

                            <!-- Audio Preview Player -->
                            <div id="audioPreviewContainer" class="mt-3 p-2 bg-light rounded border d-none" style="max-width: 450px;">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <span class="small text-success"><i class="bx bx-play-circle"></i> معاينة الملف الصوتي المختار:</span>
                                    <audio id="audioPreview" controls style="height: 25px; width: 220px;"></audio>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- External URL Input (Always displayed or conditionally adjusted) -->
                    <div class="row mb-3" id="url_input_row">
                        <div class="col-sm-3"><h6 class="mb-0" id="url_label">رابط الصوت الخارجي</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="text" name="sound_url" id="sound_url" class="form-control" value="{{ old('sound_url') }}" placeholder="https://example.com/stream-or-podcast">
                            <small class="text-muted block mt-1" id="url_help">يُستخدم لبث قنوات الراديو أو روابط بودكاست خارجية مثل Soundcloud و Spotify.</small>
                            @error('sound_url') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="submit" class="btn btn-primary px-4" value="إضافة الملف الصوتي">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        function toggleFields() {
            let soundType = $('#sound_type').val();
            if (soundType === 'live') {
                $('#file_upload_row').hide();
                $('#url_input_row').show();
                $('#sound_url').prop('required', true);
                $('#url_label').html('رابط البث المباشر <span class="text-danger">*</span>');
                $('#url_help').text('أدخل رابط تدفق البث الحي (Live stream URL) الخاص بالإذاعة أو البث المباشر.');
            } else if (soundType === 'report') {
                $('#file_upload_row').show();
                $('#url_input_row').show();
                $('#sound_url').prop('required', false);
                $('#url_label').text('رابط الصوت الخارجي (اختياري)');
                $('#url_help').text('رابط بديل للتقرير إذا كان مستضافاً على منصة خارجية مثل Soundcloud.');
            } else { // recorded
                $('#file_upload_row').show();
                $('#url_input_row').show();
                $('#sound_url').prop('required', false);
                $('#url_label').text('رابط الصوت الخارجي (اختياري)');
                $('#url_help').text('رابط بديل للملف الصوتي إذا كان مرفوعاً على منصة بودكاست خارجية.');
            }
        }

        // Run on page load & on change
        toggleFields();
        $('#sound_type').change(toggleFields);

        // Preview Audio before upload
        $('#soundFileInput').change(function(e) {
            let file = e.target.files[0];
            if (file) {
                let objectUrl = URL.createObjectURL(file);
                $('#audioPreview').attr('src', objectUrl);
                $('#audioPreviewContainer').removeClass('d-none');
            } else {
                $('#audioPreviewContainer').addClass('d-none');
                $('#audioPreview').attr('src', '');
            }
        });
    });
</script>

@endsection
