@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة حماك الخير</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.help.categories') }}">فئات المساعدات</a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل فئة المساعدة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('update.help.category') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $category->id }}">

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">اسم فئة المساعدة <span class="text-danger">*</span></h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="text" class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
                            @error('name') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3"><h6 class="mb-0">صورة الفئة التوضيحية</h6></div>
                        <div class="col-sm-9 text-secondary">
                            <input type="file" name="image" class="form-control" id="imageInput" accept="image/*">
                            <img id="showImage" src="{{ ($category->image_path && $category->image_path != 'non') ? 'http://localhost:8888/new_wiselook/uploads/' . basename($category->image_path) : url('upload/no_image.jpg') }}" alt="Preview" width="120" class="mt-2 rounded border">
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
