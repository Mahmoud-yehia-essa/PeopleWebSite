@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل الملف الشخصي</div>

    <div class="ms-auto">

    </div>
</div>
<!--end breadcrumb-->
<div class="container">
    <div class="main-body">

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="{{ (!empty($userAdmin->photo)) ? 'http://localhost:8888/new_wiselook/uploads/'.$userAdmin->photo:url('upload/no_image.jpg') }}" alt="Admin" class="rounded-circle p-1 bg-primary" height="110" width="110">
                            <div class="mt-3">
                                <h4>{{$userAdmin->fname}}</h4>
                                <p class="text-secondary mb-1">{{$userAdmin->email}}</p>
                            </div>
                        </div>
                        <hr class="my-4" />

                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <form action="{{route('admin.profile.store')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">الاسم الأول</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="fname" type="text" class="form-control" value="{{$userAdmin->fname}}" />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">اسم العائلة</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="lname" type="text" class="form-control" value="{{$userAdmin->lname}}" />
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">البريد الإلكتروني</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="email" type="text" class="form-control" value="{{$userAdmin->email}}"   />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">رقم الهاتف</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="phone" type="text" class="form-control" value="{{$userAdmin->phone}}" />
                            </div>
                        </div>


                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">العنوان</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="address" type="text" class="form-control" value="{{$userAdmin->address}}" />
                            </div>
                        </div>






                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0">الصورة</h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <input name="photo" type="file" id="image" class="form-control" value="{{$userAdmin->email}}"   />
                            </div>
                        </div>



                        <div class="row mb-3">
                            <div class="col-sm-3">
                                <h6 class="mb-0"></h6>
                            </div>
                            <div class="col-sm-9 text-secondary">
                                <img id="showImage" src="{{ (!empty($userAdmin->photo)) ? 'http://localhost:8888/new_wiselook/uploads/'.$userAdmin->photo:url('upload/no_image.jpg') }}" alt="Admin" width="110">

                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9 text-secondary">
                                <input type="submit" class="btn btn-primary px-4" value="تعديل" />
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
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src',e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endsection
