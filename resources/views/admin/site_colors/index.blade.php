@extends('admin.master_admin')
@section('admin')

<div class="container mt-4">
    <h4>إعدادات ألوان الموقع</h4>
    <form action="{{ route('site.colors.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label class="form-label">لون البانر</label>
            <input type="color" name="primary_color" value="{{ $color->primary_color ?? '#ED7032' }}" class="form-control form-control-color">
        </div>

        {{-- <div class="mb-3">
            <label class="form-label">اللون الثانوي</label>
            <input type="color" name="secondary_color" value="{{ $color->secondary_color ?? '#000000' }}" class="form-control form-control-color">
        </div> --}}







           <div class="mb-3">
            <label class="form-label">لون العنوان الهيدر في الرئيسية</label>
            <input type="color" name="font_color_main_header_home" value="{{ $color->font_color_main_header_home ?? '#FFFFFF' }}" class="form-control form-control-color">
        </div>


            <div class="mb-3">
            <label class="form-label">لون الخط العادي الهيدر في الرئيسية</label>
            <input type="color" name="font_color_normal_header_home" value="{{ $color->font_color_normal_header_home ?? '#FFFFFF' }}" class="form-control form-control-color">
        </div>





           <div class="mb-3">
            <label class="form-label">لون العنواين في الرئيسية</label>
            <input type="color" name="font_color_main_home" value="{{ $color->font_color_main_home ?? '#FFFFFF' }}" class="form-control form-control-color">
        </div>


            <div class="mb-3">
            <label class="form-label">لون الخط في الرئيسية</label>
            <input type="color" name="font_color_normal_home" value="{{ $color->font_color_normal_home ?? '#FFFFFF' }}" class="form-control form-control-color">
        </div>


        {{-- <div class="mb-3">
            <label class="form-label">لون الخط</label>
            <input type="color" name="font_color" value="{{ $color->font_color ?? '#FFFFFF' }}" class="form-control form-control-color">
        </div> --}}

        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
    </form>
</div>

@endsection
