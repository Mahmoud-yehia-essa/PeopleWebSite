@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل المجموعة</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.group') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $group->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Group Name (اسم المجموعة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اسم المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="name" type="text" class="form-control" value="{{ old('name', $group->name) }}" required />
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Group Description (الوصف) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">وصف المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <textarea name="descriptions" rows="4" class="form-control">{{ old('descriptions', $group->descriptions) }}</textarea>
                                    @error('descriptions') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Creator (منشئ المجموعة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">منشئ المجموعة (Creator)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="created_by_user_id" class="form-select" required>
                                        <option value="" disabled>اختر منشئ المجموعة...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('created_by_user_id', $group->created_by_user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email ?? $user->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('created_by_user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Group Image (الصورة والشعار) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">تغيير صورة المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image" type="file" id="imageInput" class="form-control" accept="image/*" />
                                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    @php
                                        $imageUrl = url('upload/no_image.jpg');
                                        if (!empty($group->image)) {
                                            $imageUrl = filter_var($group->image, FILTER_VALIDATE_URL) ? $group->image : 'http://localhost:8888/new_wiselook/uploads/' . basename($group->image);
                                        }
                                    @endphp
                                    <img id="imagePreview" src="{{ $imageUrl }}" alt="Group logo" width="110" class="rounded border">
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تحديث المجموعة" />
                                    <a href="{{ route('all.groups') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
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
        // معاينة الصورة المرفوعة فوراً
        $('#imageInput').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#imagePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endsection
