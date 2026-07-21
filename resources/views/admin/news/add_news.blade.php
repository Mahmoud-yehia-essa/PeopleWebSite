@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('add.news.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">تصنيف الخبر</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <select class="form-select" name="news_category_id">
                            <option value="" selected disabled>اختر تصنيف الخبر</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('news_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('news_category_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea class="form-control" name="des" rows="4">{{ old('des') }}</textarea>
                        @error('des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                     {{-- Detailed Description Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالتفصيل</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_ar" name="more_des">{{ old('more_des') }}</textarea>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العرض في شريط عاجل؟</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="show_in_ticker" name="show_in_ticker" value="1" {{ old('show_in_ticker') ? 'checked' : '' }}>
                            <label class="form-check-label" for="show_in_ticker">تفعيل العرض في شريط الأخبار العاجلة</label>
                        </div>
                        @error('show_in_ticker') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الصورة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="photo" class="form-control" id="image">
                        <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Preview" width="100" class="mt-2">
                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة">
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

<!-- CKEditor 5 Classic (Text-Only Toolbar) -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Arabic editor (RTL)
    ClassicEditor.create(document.querySelector('#more_des_ar'), {
        toolbar: ['bold','italic','underline','link','bulletedList','numberedList','blockQuote'],
    }).then(editor => {
        editor.editing.view.change(writer => {
            writer.setAttribute('dir', 'rtl', editor.editing.view.document.getRoot());
        });
    }).catch(error => { console.error(error); });

});
</script>

@endsection
