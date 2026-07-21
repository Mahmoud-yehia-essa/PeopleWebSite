@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<div class="col-lg-16">
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-3">
                    <h6 class="mb-0">عنوان الاشعار</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    <input type="text" class="form-control" name="title">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-3">
                    <h6 class="mb-0">الوصف</h6>
                </div>
                <div class="col-sm-9 text-secondary">
                    <textarea id="elm1" name="description"></textarea>

                </div>
            </div>










            <div class="row">
                <div class="col-sm-3"></div>
                <div class="col-sm-9 text-secondary">
                    <input type="submit" class="btn btn-primary px-4" value="ارسال الاشعار">
                </div>
            </div>


        </div>
    </div>

</div>



@endsection
