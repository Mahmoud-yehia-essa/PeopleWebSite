@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            <form method="POST" action="{{ route('edit.teamwork.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{ $value->id }}">
                <input type="hidden" name="old_image" value="{{ $value->photo }}">

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الاسم</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="name" value="{{ old('name',$value->name) }}">
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Name</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="name_en" value="{{ old('name_en',$value->name_en) }}">
                        @error('name_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">المنصب</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="position" value="{{ old('position',$value->position) }}">
                        @error('position') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Position</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control" name="position_en" value="{{ old('position_en',$value->position_en) }}">
                        @error('position_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>


                  <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror" rows="6">{{ old('des',$value->des) }}</textarea>
                        @error('des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control @error('des_en') is-invalid @enderror" rows="6">{{ old('des_en',$value->des_en) }}</textarea>
                        @error('des_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الصورة</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="file" name="photo" class="form-control" id="image">
                        <img id="showImage" src="{{ (!empty($value->photo) && $value->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.basename($value->photo) : url('upload/no_image.jpg') }}" width="100" class="mt-2">
                        @error('photo') <span class="text-danger">{{ $message }}</span> @enderror
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

<script>
    $(document).ready(function(){
        $('#image').change(function(e){
            let reader = new FileReader();
            reader.onload = (e) => $('#showImage').attr('src', e.target.result);
            reader.readAsDataURL(e.target.files[0]);
        });
    });
</script>
@endsection
