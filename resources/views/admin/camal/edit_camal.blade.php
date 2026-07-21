@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">تعديل المطية</div>
    </div>
    <!--end breadcrumb-->

    <div class="container">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <!-- Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <!-- Edit Form -->
                            <form method="post" action="{{ route('edit.camal.store') }}" enctype="multipart/form-data">
                                @csrf

                                    <input type="hidden" name="id" value="{{ $camal->id }}">


                                <!-- اختر المالك -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اختر المالك</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <select name="owner_id" class="form-select">
                                            <option value="non">الرجاء اختيار المالك</option>
                                            @foreach ($owners as $item)
                                                <option value="{{ $item->id }}"
                                                    {{ (old('owner_id', $camal->owner_id) == $item->id) ? 'selected' : '' }}>
                                                    {{ $item->fname }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('owner_id') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- اسم المطية -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اسم المطية</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="name" class="form-control"
                                               value="{{ old('name', $camal->name) }}" />
                                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>


                                <!-- زر الحفظ -->
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary px-4" value="حفظ التعديلات" />
                                    </div>
                                </div>
                            </form>

                            <!-- Preview Script -->
                            <script type="text/javascript">
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

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
