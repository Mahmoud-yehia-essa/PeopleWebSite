@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">مكتبة حماك الصوتية</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.sound.authors') }}">مؤلفو ومقدمو الصوتيات</a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل مؤلف/مقدم</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('update.sound.author') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $author->id }}">

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">الاسم الكامل <span class="text-danger">*</span></h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $author->name) }}" required>
                            @error('name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">صورة الشخصية / الشعار (اختياري)</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="file" name="image" class="form-control" id="imageInput">
                            <img id="showImage" src="{{ ($author->image_path && $author->image_path != 'non') ? 'http://localhost:8888/new_wiselook/uploads/' . basename($author->image_path) : url('upload/no_image.jpg') }}" alt="Preview" width="120" class="mt-2 rounded-circle border shadow-sm" style="width:100px;height:100px;object-fit:cover;">
                            @error('image') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="submit" class="btn btn-primary px-4" value="حفظ التغييرات">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        $('#imageInput').change(function(e){
            let reader = new FileReader();
            reader.onload = (e) => $('#showImage').attr('src', e.target.result);
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>

@endsection
