@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">

    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">إضافة مطايا</div>
    </div>
    <!--end breadcrumb-->

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
                <button type="button" class="btn btn-secondary select-all-gender" id="select-bkar" data-value="بكار">اختر الكل بكار</button>
                <button type="button" class="btn btn-secondary select-all-gender" id="select-gadan" data-value="قعدان">اختر الكل قعدان</button>
            </div>


                   @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
            <form method="post" action="{{ route('add.camal.store') }}">
                @csrf

                <!-- اختر المالك -->
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">اختر المالك</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <select name="owner_id" class="form-select">
                            <option value="non">الرجاء اختيار المالك</option>
                            @foreach ($owners as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->fname }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div id="mtya-container">
                    <!-- صف مطية -->
                    <div class="row mb-3 mtya-row">
                        <div class="col-sm-3">
                            <label class="form-label">اسم المطية</label>
                            <input type="text" name="names[]" class="form-control" placeholder="اسم المطية">
                        </div>

                        <div class="col-sm-3">
                            <label class="form-label">الفئة العمرية</label>
                            <select name="age_name[]" class="form-control age-select">
                                <option value="non">-- اختر العمر --</option>
                                <option value="الحقايق">الحقايق</option>
                                <option value="اللقايا">اللقايا</option>
                                <option value="الجذاع">الجذاع</option>
                                <option value="الثنايا">الثنايا</option>
                                <option value="زمول">زمول</option>
                                <option value="الحيل">الحيل</option>
                            </select>
                        </div>

                        <div class="col-sm-3">
                            <label class="form-label">النوع</label>
                            <select name="gender[]" class="form-control gender-select">
                                <option value="non">-- اختر النوع --</option>
                                <option value="بكار">بكار</option>
                                <option value="قعدان">قعدان</option>
                            </select>
                        </div>

                        <div class="col-sm-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger remove-mtya">حذف</button>
                        </div>
                    </div>
                </div>

                <!-- زر إضافة صف جديد -->
                <div class="mt-3">
                    <button type="button" id="add-mtya" class="btn btn-warning">إضافة مطية جديدة</button>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- سكربت التحكم -->
<script type="text/javascript">
// $(document).ready(function(){

//     // إضافة مطية جديدة
//     $('#add-mtya').click(function(){
//         $('#mtya-container').append(`
//             <div class="row mb-3 mtya-row">
//                 <div class="col-sm-3">
//                     <input type="text" name="names[]" class="form-control" placeholder="اسم المطية">
//                 </div>
//                 <div class="col-sm-3">
//                     <select name="age_name[]" class="form-control age-select">
//                         <option value="non">-- اختر العمر --</option>
//                         <option value="الحقايق">الحقايق</option>
//                         <option value="اللقايا">اللقايا</option>
//                         <option value="الجذاع">الجذاع</option>
//                         <option value="الثنايا">الثنايا</option>
//                         <option value="زمول">زمول</option>
//                         <option value="الحيل">الحيل</option>
//                     </select>
//                 </div>
//                 <div class="col-sm-3">
//                     <select name="gender[]" class="form-control gender-select">
//                         <option value="non">-- اختر النوع --</option>
//                         <option value="بكار">بكار</option>
//                         <option value="قعدان">قعدان</option>
//                     </select>
//                 </div>
//                 <div class="col-sm-3 d-flex align-items-end">
//                     <button type="button" class="btn btn-danger remove-mtya">حذف</button>
//                 </div>
//             </div>
//         `);
//     });

//     // حذف مطية
//     $(document).on('click', '.remove-mtya', function(){
//         $(this).closest('.mtya-row').remove();
//     });

//     // اختيار كل الفئة العمرية
//     $(document).on('click', '.select-all-age', function(){
//         var value = $(this).data('value');

//         // إعادة ضبط كل أزرار العمر
//         $('.select-all-age').removeClass('btn-success').addClass('btn-secondary');
//         $(this).removeClass('btn-secondary').addClass('btn-success');

//         // تغيير جميع select الخاصة بالعمر
//         $('select[name="age_name[]"]').val(value).trigger('change');
//     });

//     // اختيار كل النوع
//     $(document).on('click', '.select-all-gender', function(){
//         var value = $(this).data('value');

//         // إعادة ضبط كل أزرار النوع
//         $('.select-all-gender').removeClass('btn-success').addClass('btn-secondary');
//         $(this).removeClass('btn-secondary').addClass('btn-success');

//         // تغيير جميع select الخاصة بالنوع
//         $('select[name="gender[]"]').val(value);
//     });

//     // لما يتغير العمر
//     $(document).on('change', '.age-select', function(){
//         var value = $(this).val();
//         if(value === "زمول"){
//             $(this).closest('.mtya-row').find('.gender-select').val("قعدان");
//             $('#select-bkar').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
//             $('#select-gadan').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
//         } else {
//             $('#select-bkar').prop('disabled', false);
//         }
//     });

//     // زر اختر الكل زمول
//     $(document).on('click', '.select-all-age[data-value="زمول"]', function(){
//         $('select[name="gender[]"]').val("قعدان"); // الكل قعدان
//         $('#select-bkar').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
//         $('#select-gadan').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
//     });

//     // زر اختر أي عمر آخر غير زمول
//     $(document).on('click', '.select-all-age:not([data-value="زمول"])', function(){
//         $('#select-bkar').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
//         $('#select-gadan').removeClass('btn-success').addClass('btn-secondary');
//     });

// });

$(document).ready(function(){

    // إضافة مطية جديدة
    $('#add-mtya').click(function(){
        $('#mtya-container').append(`
            <div class="row mb-3 mtya-row">
                <div class="col-sm-3">
                    <input type="text" name="names[]" class="form-control" placeholder="اسم المطية">
                </div>
                <div class="col-sm-3">
                    <select name="age_name[]" class="form-control age-select">
                        <option value="non">-- اختر العمر --</option>
                        <option value="الحقايق">الحقايق</option>
                        <option value="اللقايا">اللقايا</option>
                        <option value="الجذاع">الجذاع</option>
                        <option value="الثنايا">الثنايا</option>
                        <option value="زمول">زمول</option>
                        <option value="الحيل">الحيل</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <select name="gender[]" class="form-control gender-select">
                        <option value="non">-- اختر النوع --</option>
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
        $(this).closest('.mtya-row').remove();
    });

    // زر اختيار العمر
    $(document).on('click', '.select-all-age', function(){
        var value = $(this).data('value');

        // إعادة ضبط كل أزرار العمر
        $('.select-all-age').removeClass('btn-success').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-success');

        if(value === "زمول"){
            // تغيير جميع select الخاصة بالعمر
            $('select[name="age_name[]"]').val(value).trigger('change');
            // كل الـ gender => قعدان
            $('select[name="gender[]"]').val("قعدان");
            // زر النوع
            $('#select-bkar').prop('disabled', true).removeClass('btn-success').addClass('btn-secondary');
            $('#select-gadan').prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
        } else {
            // تغيير جميع select الخاصة بالعمر
            $('select[name="age_name[]"]').val(value).trigger('change');
            // كل الـ gender => non
            $('select[name="gender[]"]').val("non");
            // الأزرار تبقى رمادية وغير محددة
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

        // إعادة ضبط كل أزرار النوع
        $('.select-all-gender').removeClass('btn-success').addClass('btn-secondary');
        $(this).removeClass('btn-secondary').addClass('btn-success');

        // تغيير جميع select الخاصة بالنوع
        $('select[name="gender[]"]').val(value);
    });

});


</script>

<script>

    $('form').submit(function(e){
    var owner = $('select[name="owner_id"]').val();
    if(owner === 'non'){
        e.preventDefault(); // Stop form from submitting
        alert('يرجى اختيار المالك قبل الحفظ!');
        return false;
    }
});
</script>
@endsection
