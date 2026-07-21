@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('add.service.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Title Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Title English --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Title</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror" value="{{ old('title_en') }}">
                        @error('title_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Short Description Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror" rows="3">{{ old('des') }}</textarea>
                        @error('des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Short Description English --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control @error('des_en') is-invalid @enderror" rows="3">{{ old('des_en') }}</textarea>
                        @error('des_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Detailed Description Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالتفصيل</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_ar" name="more_des">{{ old('more_des') }}</textarea>
                    </div>
                </div>

                {{-- Detailed Description English --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف بالتفصيل بالانجليزية</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea id="more_des_en" name="more_des_en">{{ old('more_des_en') }}</textarea>
                    </div>
                </div>

                {{-- Image Upload --}}
                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الصورة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="photo" class="form-control" id="image">
                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Image Preview --}}
                <div class="row mb-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Admin" width="110">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="اضافة">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Image Preview Script -->
<script>
$(document).ready(function(){
    $('#image').change(function(e){
        var reader = new FileReader();
        reader.onload = function(e){
            $('#showImage').attr('src', e.target.result);
        }
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

    // English editor (LTR)
    ClassicEditor.create(document.querySelector('#more_des_en'), {
        toolbar: ['bold','italic','underline','link','bulletedList','numberedList','blockQuote'],
    }).then(editor => {
        editor.editing.view.change(writer => {
            writer.setAttribute('dir', 'ltr', editor.editing.view.document.getRoot());
        });
    }).catch(error => { console.error(error); });

});
</script>

@endsection
