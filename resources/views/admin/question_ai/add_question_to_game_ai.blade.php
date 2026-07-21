@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">اضافة سؤال جديد</div>
</div>
<!--end breadcrumb-->
<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('add.question.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">


                        <div class="card-body">

                                <!-- Question  category-->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اختر الفئة</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">


                                        <select  name="category_id" class="form-select" aria-label="Default select example">
                                            <option selected="" value="non">الرجاء إختيار الفئة</option>

                                            @foreach ($category as $item )
                                            <option value="{{$item->id}}" {{ old('category_id',$categoryId) == $item->id ? 'selected' : '' }}>{{$item->category_name}}</option>

                                            @endforeach

                                        </select>

                                        @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                            <!-- Question Title -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">السؤال</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="qu_title" type="text" class="form-control" value="{{ old('qu_title',$quTitle) }}" />
                                    @error('qu_title') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Question Points -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نقاط السؤال</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="qu_points" type="text" class="form-control" value="{{ old('qu_points') }}" />
                                    @error('qu_points') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Question counter -->

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">توقيت السؤال</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="time_counter" type="number" class="form-control" min="1" step="1" value="{{ old('time_counter') }}" />
                                    @error('time_counter')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        ملاحظة : اذا تم ترك الحقل فارغ سيتم عدم حساب توقيت للسؤال ومعيار الحساب دقائق يعني اذا تم ادخال 1 تعني دقيقة واحدة
                                    </small>
                                </div>
                            </div>

                            <!-- Question Type -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نوع السؤال</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <label>
                                        <input type="radio" name="questions_type" value="text" checked /> نصي
                                    </label>
                                    <label class="ms-3">
                                        <input type="radio" name="questions_type" value="image" /> صورة
                                    </label>
                                    <label class="ms-3">
                                        <input type="radio" name="questions_type" value="sound" /> ملف صوتي
                                    </label>
                                    <label class="ms-3">
                                        <input type="radio" name="questions_type" value="video" /> فيديو
                                    </label>
                                    @error('questions_type') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                @error('answerـfile') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            <!-- Question File Upload (Initially Hidden) -->
                            <div class="row mb-3" id="question_file_input" style="display: none;">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الملف</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="questionsـfile" type="file" class="form-control" id="questionFile" />
                                    <div id="questionPreview" class="mt-2"></div> <!-- Preview Area -->
                                </div>
                            </div>

                        </div>
                    </div>

            </div>
        </div>

        <!-- Answer Section -->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">اضافة الاجابة </div>
        </div>
        <div class="row">
            <div class="col-lg-8">

                <div class="card">
                    <div class="card-body">

                        <!-- Answer Title -->
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">الاجابة</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="answer_title" type="text" class="form-control" value="{{ old('answer_title',$answerTitle) }}" />
                                @error('answer_title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Answer Type -->
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">نوع الاجابة</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <label>
                                    <input type="radio" name="answer_type" value="text" checked /> نصي
                                </label>
                                <label class="ms-3">
                                    <input type="radio" name="answer_type" value="image" /> صورة
                                </label>
                                <label class="ms-3">
                                    <input type="radio" name="answer_type" value="sound" /> ملف صوتي
                                </label>
                                <label class="ms-3">
                                    <input type="radio" name="answer_type" value="video" /> فيديو
                                </label>
                                @error('answer_type') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Answer File Upload (Initially Hidden) -->
                        <div class="mb-3">
                            @error('answerـfile') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="row mb-3" id="answer_file_input" style="display: none;">

                            <div class="col-sm-3">
                                <h6 class="mb-0">الملف</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="answerـfile" type="file" class="form-control" id="answerFile" />
                                <div id="answerPreview" class="mt-2"></div> <!-- Preview Area -->
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                <input type="submit" class="btn btn-primary px-4" value="اضافة سؤال جديد" />
                            </div>
                        </div>

                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery to Show/Hide File Inputs -->
<script type="text/javascript">
/*
    $(document).ready(function(){
        // Initially hide file inputs
        $('#question_file_input').hide();
        $('#answer_file_input').hide();

        // Function to show/hide file inputs based on selection
        function toggleFileInput(typeSelector, fileInputSelector) {
            $(typeSelector).change(function() {
                if ($(this).val() == "image" || $(this).val() == "sound") {
                    $(fileInputSelector).show();
                } else {
                    $(fileInputSelector).hide();
                    $(fileInputSelector).find('input[type="file"]').val(''); // Clear the file input
                    $(fileInputSelector).find('#questionPreview, #answerPreview').empty(); // Clear the preview
                }
            });
        }

        // Preview function for files
        function previewFile(inputSelector, previewSelector) {
            $(inputSelector).change(function() {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (file.type.startsWith('image/')) {
                        $(previewSelector).html('<img src="' + e.target.result + '" alt="Preview" width="110" class="mt-2">');
                    } else if (file.type.startsWith('audio/')) {
                        $(previewSelector).html('<audio controls><source src="' + e.target.result + '" type="audio/mpeg">Your browser does not support the audio tag.</audio>');
                    }
                };
                if (file) {
                    reader.readAsDataURL(file);
                }
            });
        }

        // Apply function to question and answer types
        toggleFileInput('input[name="questions_type"]', '#question_file_input');
        toggleFileInput('input[name="answer_type"]', '#answer_file_input');

        // Preview the selected files
        previewFile('#questionFile', '#questionPreview');
        previewFile('#answerFile', '#answerPreview');
    });
    */

    $(document).ready(function(){
    function toggleFileInput(typeSelector, fileInputSelector) {
        $(typeSelector).change(function() {
            if ($(this).val() == "image" || $(this).val() == "sound" || $(this).val() == "video") {
                $(fileInputSelector).show();
            } else {
                $(fileInputSelector).hide();
                $(fileInputSelector).find('input[type="file"]').val('');
                $(fileInputSelector).find('#questionPreview, #answerPreview').empty();
            }
        });
    }

    function previewFile(inputSelector, previewSelector) {
        $(inputSelector).change(function() {
            const file = this.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    $(previewSelector).html('<img src="' + e.target.result + '" alt="Preview" width="110" class="mt-2">');
                } else if (file.type.startsWith('audio/')) {
                    $(previewSelector).html('<audio controls><source src="' + e.target.result + '" type="audio/mpeg"></audio>');
                } else if (file.type.startsWith('video/')) {
                    $(previewSelector).html('<video width="400px"  controls><source src="' + e.target.result + '" type="video/mp4"></video>');
                }
            };
            if (file) {
                reader.readAsDataURL(file);
            }
        });
    }

    toggleFileInput('input[name="questions_type"]', '#question_file_input');
    toggleFileInput('input[name="answer_type"]', '#answer_file_input');

    previewFile('#questionFile', '#questionPreview');
    previewFile('#answerFile', '#answerPreview');
});

</script>

@endsection
