@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إضافة موضوع جديد</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">

                            <!-- Publisher (User) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الناشر</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="user_id" class="form-select" required>
                                        <option value="">اختر الناشر</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Post Type -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نوع الموضوع</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="post_type_id" id="post_type_select" class="form-select" required>
                                        <option value="1" {{ old('post_type_id') == 1 ? 'selected' : '' }}>منشور عادي</option>
                                        <option value="2" {{ old('post_type_id') == 2 ? 'selected' : '' }}>استطلاع رأي</option>
                                    </select>
                                    @error('post_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Privacy Level -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">مستوى الخصوصية</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="privacy_level_id" class="form-select" required>
                                        @foreach($privacyLevels as $level)
                                            <option value="{{ $level->id }}" {{ old('privacy_level_id') == $level->id ? 'selected' : '' }}>
                                                {{ $level->name == 'public' ? 'عام (Public)' : 'خاص (Private)' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('privacy_level_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- الصورة والفيديو (مشترك لكلا المنشور واستطلاع الرأي) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الصورة المرفقة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image" type="file" id="image_input" class="form-control" accept="image/*" />
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Image Preview" width="110" class="rounded shadow-sm">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الفيديو المرفق</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="video" type="file" class="form-control" accept="video/*" />
                                    @error('video') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <hr>

                            <!-- Regular Post Fields -->
                            <div id="post_fields_container">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">المحتوى</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <textarea name="content" class="form-control" rows="5" placeholder="أدخل محتوى المنشور هنا...">{{ old('content') }}</textarea>
                                        @error('content') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Poll Fields -->
                            <div id="poll_fields_container" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">سؤال الاستطلاع</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="question" type="text" class="form-control" placeholder="أدخل السؤال هنا..." value="{{ old('question') }}" />
                                        @error('question') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الخيار الأول (مطلوب)</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="options[]" type="text" class="form-control" placeholder="الخيار الأول" value="{{ old('options.0') }}" />
                                        @error('options.0') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الخيار الثاني (مطلوب)</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="options[]" type="text" class="form-control" placeholder="الخيار الثاني" value="{{ old('options.1') }}" />
                                        @error('options.1') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الخيار الثالث (اختياري)</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="options[]" type="text" class="form-control" placeholder="الخيار الثالث" value="{{ old('options.2') }}" />
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الخيار الرابع (اختياري)</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="options[]" type="text" class="form-control" placeholder="الخيار الرابع" value="{{ old('options.3') }}" />
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="اضافة الموضوع" />
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
        function toggleFields() {
            var selectedType = $('#post_type_select').val();
            if(selectedType == '1') {
                $('#post_fields_container').show();
                $('#poll_fields_container').hide();
            } else {
                $('#post_fields_container').hide();
                $('#poll_fields_container').show();
            }
        }

        $('#post_type_select').change(toggleFields);
        toggleFields(); // تشغيل عند تحميل الصفحة أول مرة

        // معاينة الصورة المرفوعة
        $('#image_input').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endsection
