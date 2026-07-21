@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل الموضوع</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $post->id }}">
                    
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
                                            <option value="{{ $user->id }}" {{ $post->user_id == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Post Type (Locked) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نوع الموضوع</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="post_type_id_disabled" id="post_type_select" class="form-select" disabled>
                                        <option value="1" {{ $post->post_type_id == 1 ? 'selected' : '' }}>منشور عادي</option>
                                        <option value="2" {{ $post->post_type_id == 2 ? 'selected' : '' }}>استطلاع رأي</option>
                                    </select>
                                    <input type="hidden" name="post_type_id" value="{{ $post->post_type_id }}">
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
                                            <option value="{{ $level->id }}" {{ $post->privacy_level_id == $level->id ? 'selected' : '' }}>
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
                                    <h6 class="mb-0">الصورة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image" type="file" id="image_input" class="form-control" accept="image/*" />
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <img id="showImage" src="{{ (!empty($post->image)) ? 'http://localhost:8888/new_wiselook/uploads/'.$post->image : url('upload/no_image.jpg') }}" alt="Image Preview" width="110" class="rounded shadow-sm">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الفيديو</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="video" type="file" class="form-control" accept="video/*" />
                                    @if($post->video)
                                        <div class="mt-2">
                                            <span class="text-success"><i class="fa-solid fa-circle-check"></i> يوجد فيديو مرفق حالياً</span>
                                            <a href="{{ 'http://localhost:8888/new_wiselook/uploads/'.$post->video }}" target="_blank" class="btn btn-xs btn-outline-info ms-2">عرض الفيديو الحالي</a>
                                        </div>
                                    @endif
                                    @error('video') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <hr>

                            <!-- Regular Post Fields -->
                            @if($post->post_type_id == 1)
                            <div id="post_fields_container">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">المحتوى</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <textarea name="content" class="form-control" rows="5" required>{{ old('content', $post->content) }}</textarea>
                                        @error('content') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Poll Fields -->
                            @if($post->post_type_id == 2)
                            <div id="poll_fields_container">
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">سؤال الاستطلاع</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input name="question" type="text" class="form-control" value="{{ old('question', $post->poll->question ?? $post->content) }}" required />
                                        @error('question') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                @if($post->poll && $post->poll->options)
                                    @foreach($post->poll->options as $index => $option)
                                        <div class="row mb-3">
                                            <div class="col-sm-3">
                                                <h6 class="mb-0">الخيار {{ $index + 1 }} {{ $index < 2 ? '(مطلب)' : '(اختياري)' }}</h6>
                                            </div>
                                            <div class="col-sm-9 text-secondary">
                                                <input type="hidden" name="option_ids[]" value="{{ $option->id }}">
                                                <input name="options[]" type="text" class="form-control" value="{{ $option->content }}" required />
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @endif

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تحديث الموضوع" />
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
