@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <h5 class="mb-4">تعديل المقال</h5>

            <form method="POST" action="{{ route('edit.article.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $article->id }}">

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">كاتب المقال (من فريق العمل)</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select class="form-select" name="team_work_id">
                            <option value="">اختر كاتب المقال (اختياري)</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ old('team_work_id', $article->team_work_id) == $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                            @endforeach
                        </select>
                        @error('team_work_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title" value="{{ old('title', $article->title) }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف المختصر</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea class="form-control" name="short_description" rows="3">{{ old('short_description', $article->short_description) }}</textarea>
                        @error('short_description') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تفاصيل المقال بالكامل</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="long_description" name="long_description">{{ old('long_description', $article->long_description) }}</textarea>
                        @error('long_description') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تغيير صورة الغلاف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="image_path" class="form-control" id="image">
                        <img id="showImage" src="{{ (!empty($article->image_path) && $article->image_path != 'non') ? 'http://localhost:8888/new_wiselook/uploads/' . basename($article->image_path) : url('upload/no_image.jpg') }}" alt="Preview" width="100" class="mt-2">
                        @error('image_path') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ التغييرات">
                        <a href="{{ route('all.articles') }}" class="btn btn-light px-4">إلغاء</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#image').change(function(e){
            let reader = new FileReader();
            reader.onload = (e) => $('#showImage').attr('src', e.target.result);
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>

<!-- CKEditor 5 Classic (Full-featured block) -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    ClassicEditor.create(document.querySelector('#long_description'), {
        toolbar: ['bold','italic','underline','link','bulletedList','numberedList','blockQuote','insertTable','undo','redo'],
    }).then(editor => {
        editor.editing.view.change(writer => {
            writer.setAttribute('dir', 'rtl', editor.editing.view.document.getRoot());
        });
    }).catch(error => { console.error(error); });
});
</script>

@endsection
