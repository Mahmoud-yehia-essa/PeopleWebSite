@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل المجموعة الخاصة/العامة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.group_sites') }}"><i class="bx bx-globe"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل مجموعة: {{ $group->title }}</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.group_site') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $group->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Title (عنوان المجموعة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">عنوان المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="title" type="text" class="form-control" value="{{ old('title', $group->title) }}" placeholder="أدخل عنوان المجموعة..." required />
                                    @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Description (الوصف) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">وصف المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <textarea name="description" rows="4" class="form-control" placeholder="أدخل وصفاً للمجموعة...">{{ old('description', $group->description) }}</textarea>
                                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status (حالة المجموعة: مفتوحة/مغلقة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">حالة المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="status" id="statusSelect" class="form-select" required>
                                        <option value="open" {{ old('status', $group->status) == 'open' ? 'selected' : '' }}>عامة (مفتوحة - Open)</option>
                                        <option value="closed" {{ old('status', $group->status) == 'closed' ? 'selected' : '' }}>خاصة (مغلقة - Closed)</option>
                                    </select>
                                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Invite Code (كود الدعوة) -->
                            <div class="row mb-3" id="inviteCodeContainer" style="display: {{ old('status', $group->status) == 'closed' ? 'flex' : 'none' }};">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">كود الدعوة (Invite Code)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="invite_code" type="text" class="form-control" value="{{ old('invite_code', $group->invite_code) }}" placeholder="أدخل كود الدعوة للمجموعة المغلقة..." />
                                    @error('invite_code') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Admin User (مشرف المجموعة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">مشرف المجموعة (Admin User)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="admin_user_id" class="form-select" required>
                                        <option value="" disabled>اختر المشرف المسؤول عن المجموعة...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('admin_user_id', $group->admin_user_id) == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email ?? $user->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('admin_user_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Group Image (الصورة والشعار) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">صورة/شعار المجموعة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="image_path" type="file" id="imageInput" class="form-control" accept="image/*" />
                                    @error('image_path') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image Preview -->
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    @php
                                        $imageUrl = url('upload/no_image.jpg');
                                        if ($group->image_path) {
                                            $imageUrl = filter_var($group->image_path, FILTER_VALIDATE_URL) ? $group->image_path : 'http://localhost:8888/new_wiselook/uploads/' . basename($group->image_path);
                                        }
                                    @endphp
                                    <img id="imagePreview" src="{{ $imageUrl }}" alt="Group logo" width="110" class="rounded border">
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تحديث البيانات" />
                                    <a href="{{ route('all.group_sites') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
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
        // إظهار/إخفاء كود الدعوة بناءً على حالة المجموعة
        $('#statusSelect').change(function(){
            if($(this).val() === 'closed'){
                $('#inviteCodeContainer').fadeIn().css('display', 'flex');
            } else {
                $('#inviteCodeContainer').fadeOut();
                $('input[name="invite_code"]').val('');
            }
        });

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
