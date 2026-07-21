@extends('admin.master_admin')
@section('admin')

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('edit.gallery.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $gallery->id }}">

                {{-- Title Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">العنوان</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title" class="form-control" value="{{ old('title', $gallery->title) }}">
                    </div>
                </div>

                {{-- Title English --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Title</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title_en" class="form-control" value="{{ old('title_en', $gallery->title_en) }}">
                    </div>
                </div>

                {{-- Description Arabic --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الوصف</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control" rows="3">{{ old('des', $gallery->des) }}</textarea>
                    </div>
                </div>

                {{-- Description English --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Description</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control" rows="3">{{ old('des_en', $gallery->des_en) }}</textarea>
                    </div>
                </div>

                {{-- Existing Photos --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الصور الحالية</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        @foreach($gallery->photos as $photo)
                            <div style="display:inline-block; position:relative; margin:3px;">
                                <img src="{{ url($photo->photo) }}" width="80" height="80" class="border rounded">
                                <a href="{{ route('delete.gallery.photo', $photo->id) }}"
                                   onclick="return confirm('Are you sure you want to delete this photo?')"
                                   style="position:absolute; top:0; right:0; color:red; font-weight:bold; text-decoration:none;">
                                   &times;
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Add New Photos --}}
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">اضافة مزيد من الصور</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="photos[]" class="form-control" multiple id="photos">
                    </div>
                </div>

                {{-- Preview Selected Photos --}}
                <div class="row mb-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary" id="preview_photos"></div>
                </div>

                {{-- Submit --}}
                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="تحديث معرض الصور">
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#photos').on('change', function(){
        $('#preview_photos').html('');
        var files = this.files;
        if(files){
            $.each(files, function(i, file){
                var reader = new FileReader();
                reader.onload = function(e){
                    $('#preview_photos').append('<img src="'+e.target.result+'" width="80" height="80" style="margin:3px; border:1px solid #ccc;">');
                }
                reader.readAsDataURL(file);
            });
        }
    });
});
</script>

@endsection
