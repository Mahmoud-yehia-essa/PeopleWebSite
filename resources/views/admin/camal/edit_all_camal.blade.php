{{-- @extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">عرض جميع المطايا الخاصة بالمالك</div>
    </div>

    <div class="container">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">


                                  @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

<form method="post" action="{{ route('edit.all.camal.store') }}" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="owner_id" value="{{ $owner->id }}">

    <div class="row mb-3">
        <div class="col-sm-3">
            <h6 class="mb-0">اسم المالك</h6>
        </div>
        <div class="col-sm-7 text-secondary">
            <input type="text" value="{{ $owner->fname }}" class="form-control" readonly  />
        </div>
    </div>

    <div id="mtya-container">
        @foreach ($camals as $camal)
        <div class="row mb-3 mtya-row">
            <div class="col-sm-3">
                <h6 class="mb-0">اسم المطية</h6>
            </div>
            <div class="col-sm-3 text-secondary">
                <input type="text" name="names[{{ $camal->id }}]" value="{{ $camal->name }}" class="form-control" />
            </div>
            <div class="col-sm-3 text-secondary">
                <select name="age_name[{{ $camal->id }}]" class="form-select">
                    <option value="non" {{ $camal->age_name == 'non' ? 'selected' : '' }}>اختر الفئة العمرية</option>
                    <option value="الحقايق" {{ $camal->age_name == 'الحقايق' ? 'selected' : '' }}>الحقايق</option>
                    <option value="اللقايا" {{ $camal->age_name == 'اللقايا' ? 'selected' : '' }}>اللقايا</option>
                    <option value="الجذاع" {{ $camal->age_name == 'الجذاع' ? 'selected' : '' }}>الجذاع</option>
                    <option value="الثنايا" {{ $camal->age_name == 'الثنايا' ? 'selected' : '' }}>الثنايا</option>
                     <option value="زمول" {{ $camal->age_name == 'زمول' ? 'selected' : '' }}>زمول</option>

                    <option value="الحيل" {{ $camal->age_name == 'الحيل' ? 'selected' : '' }}>الحيل</option>
                </select>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger remove-mtya" data-id="{{ $camal->id }}">حذف</button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row mb-3">
        <div class="col-sm-3"></div>
        <div class="col-sm-9 text-secondary">
            <button type="button" id="add-mtya" class="btn btn-success">+ إضافة مطية جديدة</button>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-3"></div>
        <div class="col-sm-9 text-secondary">
            <input type="submit" class="btn btn-primary px-4" value="حفظ" />
        </div>
    </div>
</form>

<script type="text/javascript">
$(document).ready(function(){

    // إضافة مطية جديدة (ما لها ID)
    $('#add-mtya').click(function(){
        $('#mtya-container').append(`
            <div class="row mb-3 mtya-row">
                <div class="col-sm-3"><h6 class="mb-0">اسم المطية</h6></div>
                <div class="col-sm-3 text-secondary">
                    <input type="text" name="names[new][]" class="form-control" />
                </div>
                <div class="col-sm-3 text-secondary">
                    <select name="age_name[new][]" class="form-select">
                        <option value="non">اختر الفئة العمرية</option>
                        <option value="الحقايق">الحقايق</option>
                        <option value="اللقايا">اللقايا</option>
                        <option value="الجذاع">الجذاع</option>
                        <option value="الثنايا">الثنايا</option>
                        <option value="الحيل">الحيل</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="button" class="btn btn-danger remove-mtya">حذف</button>
                </div>
            </div>
        `);
    });

    // حذف مطية
    $(document).on('click', '.remove-mtya', function(){
        let camalId = $(this).data('id');
        let row = $(this).closest('.mtya-row');

        if (camalId) {
            row.append(`<input type="hidden" name="deleted[]" value="${camalId}">`);
            row.hide();
        } else {
            row.remove();
        }
    });

});
</script>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection --}}



@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">تعديل المطايا الخاصة بالمالك</div>
    </div>

    <div class="card">
        <div class="card-body">

            <!-- أزرار اختيار العمر -->
            <div class="mb-3">
                <button type="button" class="btn btn-secondary select-all-age" data-value="الحقايق">اختر الكل الحقايق</button>
                <button type="button" class="btn btn-secondary select-all-age" data-value="اللقايا">اختر الكل اللقايا</button>
                <button type="button" class="btn btn-secondary select-all-age" data-value="الجذاع">اختر الكل الجذاع</button>
                <button type="button" class="btn btn-secondary select-all-age" data-value="الثنايا">اختر الكل الثنايا</button>
                <button type="button" class="btn btn-secondary select-all-age" data-value="زمول">اختر الكل زمول</button>
                <button type="button" class="btn btn-secondary select-all-age" data-value="الحيل">اختر الكل الحيل</button>
            </div>

            <!-- أزرار اختيار النوع -->
            <div class="mb-3">
                <button type="button" id="select-bkar" class="btn btn-secondary select-all-gender" data-value="بكار">اختر الكل بكار</button>
                <button type="button" id="select-gadan" class="btn btn-secondary select-all-gender" data-value="قعدان">اختر الكل قعدان</button>
            </div>

            <form method="post" action="{{ route('edit.all.camal.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="owner_id" value="{{ $owner->id }}">

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">اسم المالك</h6>
                    </div>
                    <div class="col-sm-7 text-secondary">
                        <input type="text" value="{{ $owner->fname }}" class="form-control" readonly  />
                    </div>
                </div>

                <div id="mtya-container">
                    @foreach ($camals as $camal)
                        <div class="row mb-3 mtya-row">
                            <div class="col-sm-3"><h6 class="mb-0">اسم المطية</h6></div>
                            <div class="col-sm-3 text-secondary">
                                <input type="text" name="names[{{ $camal->id }}]" value="{{ $camal->name }}" class="form-control" />
                            </div>
                            <div class="col-sm-3 text-secondary">
                                <select name="age_name[{{ $camal->id }}]" class="form-control age-select">
                                    <option value="non" {{ $camal->age_name == 'non' ? 'selected' : '' }}>اختر الفئة العمرية</option>
                                    <option value="الحقايق" {{ $camal->age_name == 'الحقايق' ? 'selected' : '' }}>الحقايق</option>
                                    <option value="اللقايا" {{ $camal->age_name == 'اللقايا' ? 'selected' : '' }}>اللقايا</option>
                                    <option value="الجذاع" {{ $camal->age_name == 'الجذاع' ? 'selected' : '' }}>الجذاع</option>
                                    <option value="الثنايا" {{ $camal->age_name == 'الثنايا' ? 'selected' : '' }}>الثنايا</option>
                                    <option value="زمول" {{ $camal->age_name == 'زمول' ? 'selected' : '' }}>زمول</option>
                                    <option value="الحيل" {{ $camal->age_name == 'الحيل' ? 'selected' : '' }}>الحيل</option>
                                </select>
                            </div>
                            <div class="col-sm-3 text-secondary">
                                <select name="gender[{{ $camal->id }}]" class="form-control gender-select">
                                    <option value="non" {{ $camal->gender == 'non' ? 'selected' : '' }}>اختر النوع</option>
                                    <option value="بكار" {{ $camal->gender == 'بكار' ? 'selected' : '' }}>بكار</option>
                                    <option value="قعدان" {{ $camal->gender == 'قعدان' ? 'selected' : '' }}>قعدان</option>
                                </select>
                            </div>
                            <div class="col-sm-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger remove-mtya" data-id="{{ $camal->id }}">حذف</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <button type="button" id="add-mtya" class="btn btn-warning">+ إضافة مطية جديدة</button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ" />
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    // إضافة مطية جديدة
    $('#add-mtya').click(function(){
        $('#mtya-container').append(`
            <div class="row mb-3 mtya-row">
                <div class="col-sm-3"><input type="text" name="names[new][]" class="form-control" placeholder="اسم المطية"></div>
                <div class="col-sm-3">
                    <select name="age_name[new][]" class="form-control age-select">
                        <option value="non">اختر الفئة العمرية</option>
                        <option value="الحقايق">الحقايق</option>
                        <option value="اللقايا">اللقايا</option>
                        <option value="الجذاع">الجذاع</option>
                        <option value="الثنايا">الثنايا</option>
                        <option value="زمول">زمول</option>
                        <option value="الحيل">الحيل</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <select name="gender[new][]" class="form-control gender-select">
                        <option value="non">اختر النوع</option>
                        <option value="بكار">بكار</option>
                        <option value="قعدان">قعدان</option>
                    </select>
                </div>
                <div class="col-sm-3 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-mtya">حذف</button>
                </div>
            </div>
        `);
    });

    // حذف مطية
    $(document).on('click', '.remove-mtya', function(){
        let camalId = $(this).data('id');
        let row = $(this).closest('.mtya-row');
        if(camalId){
            row.append(`<input type="hidden" name="deleted[]" value="${camalId}">`);
            row.hide();
        } else {
            row.remove();
        }
    });

    // اختيار العمر من الأزرار
    $(document).on('click', '.select-all-age', function(){
        var value = $(this).data('value');

        $('.select-all-age').removeClass('btn-success').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-success');

        $('select[name^="age_name"]').val(value).trigger('change');

        if(value === 'زمول'){
            $('select[name^="gender"]').val('قعدان');
            $('#select-bkar').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
            $('#select-gadan').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        } else {
            $('select[name^="gender"]').val('non');
            $('#select-bkar').prop('disabled', false).removeClass('btn-success').addClass('btn-secondary');
            $('#select-gadan').prop('disabled', false).removeClass('btn-success').addClass('btn-secondary');
        }
    });

    // تغيير أي age select يدويًا
    $(document).on('change', '.age-select', function(){
        var value = $(this).val();
        if(value === "زمول"){
            $(this).closest('.mtya-row').find('.gender-select').val("قعدان");
            $('#select-bkar').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
            $('#select-gadan').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        }
    });

    // اختيار كل النوع
    $(document).on('click', '.select-all-gender', function(){
        var value = $(this).data('value');
        $('.select-all-gender').removeClass('btn-success').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-success');
        $('select[name^="gender"]').val(value);
    });

});
</script>
@endsection

