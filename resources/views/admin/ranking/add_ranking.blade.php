@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إضافة رتبة جديدة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.rankings') }}"><i class="bx bx-award"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">رتبة جديدة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.ranking') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="mb-4">إضافة رتبة ومستوى جديد</h5>
                            
                            <!-- اسم الرتبة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اسم الرتبة <span class="text-danger">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="rank_name" type="text" class="form-control @error('rank_name') is-invalid @enderror" value="{{ old('rank_name') }}" placeholder="مثال: محترف، مبتدئ، خبير..." required />
                                    @error('rank_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- ترتيب الرتبة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">ترتيب الرتبة <span class="text-danger">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="rank_order" type="number" class="form-control @error('rank_order') is-invalid @enderror" value="{{ old('rank_order') }}" placeholder="مثال: 1 (الرتبة الأولى)، 2 (الرتبة الثانية)..." min="1" required />
                                    @error('rank_order') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">الترتيب التصاعدي للظهور والمقارنة في لوحة التحكم.</small>
                                </div>
                            </div>

                            <!-- وصف الرتبة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">وصف الرتبة</h6>
                                </div>
                                <div class="col-sm-9">
                                    <textarea name="rank_description" class="form-control @error('rank_description') is-invalid @enderror" placeholder="وصف موجز لمتطلبات أو تفاصيل الرتبة..." rows="3">{{ old('rank_description') }}</textarea>
                                    @error('rank_description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- بداية نقاط الرتبة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">بداية نقاط الرتبة <span class="text-danger">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="rank_start_point" type="number" id="start_point" class="form-control @error('rank_start_point') is-invalid @enderror" value="{{ old('rank_start_point') }}" placeholder="مثال: 1" min="0" required />
                                    @error('rank_start_point') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- نهاية نقاط الرتبة -->
                            <div class="row mb-3" id="end_point_wrapper">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نهاية نقاط الرتبة <span class="text-danger" id="end_point_asterisk">*</span></h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="rank_end_point" type="number" id="end_point" class="form-control @error('rank_end_point') is-invalid @enderror" value="{{ old('rank_end_point') }}" placeholder="مثال: 100" min="0" required />
                                    @error('rank_end_point') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- قيمة المكافأة المالية/النقاط -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">قيمة المكافأة عند الوصول للرتبة</h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="level_reward_amount" type="number" class="form-control @error('level_reward_amount') is-invalid @enderror" value="{{ old('level_reward_amount', '0') }}" placeholder="مثال: 500" min="0" />
                                    @error('level_reward_amount') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted">المكافأة من النقاط أو النقود التي يستحقها العضو عند الترقية.</small>
                                </div>
                            </div>

                            <!-- صورة الرتبة / الشارة -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">صورة الرتبة / الشارة</h6>
                                </div>
                                <div class="col-sm-9">
                                    <input name="photo" type="file" id="image" class="form-control @error('photo') is-invalid @enderror" accept="image/*" />
                                    @error('photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    <small class="text-muted block mt-1">يُفضل استخدام صورة مربعة ذات خلفية شفافة بمقاس مناسب (مثل: 128x128 بكسل).</small>
                                </div>
                            </div>

                            <!-- معاينة الصورة -->
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Rank Badge" class="rounded border p-1" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            </div>

                            <!-- تحديد كآخر رتبة بالسيستم -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">هل هي الرتبة القصوى/الأخيرة؟</h6>
                                </div>
                                <div class="col-sm-9">
                                    <div class="form-check form-switch">
                                        <input name="is_last" class="form-check-input" type="checkbox" id="lastSwitch" {{ old('is_last') ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="lastSwitch">تعيين كآخر رتبة بنظام النقاط (لا يوجد حد أقصى للنهاية)</label>
                                    </div>
                                    @error('is_last') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- أزرار الإجراءات -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" class="btn btn-primary px-4">حفظ الرتبة</button>
                                    <a href="{{ route('all.rankings') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
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
        // عند تغيير حالة الرتبة الأخيرة
        function toggleEndPoint() {
            if ($('#lastSwitch').is(':checked')) {
                $('#end_point').prop('disabled', true).prop('required', false).val('');
                $('#end_point_asterisk').hide();
                $('#end_point_wrapper').fadeOut();
            } else {
                $('#end_point').prop('disabled', false).prop('required', true);
                $('#end_point_asterisk').show();
                $('#end_point_wrapper').fadeIn();
            }
        }

        // تشغيل التحقق عند البدء والتحميل
        toggleEndPoint();

        $('#lastSwitch').change(function(){
            toggleEndPoint();
        });

        // معاينة الصورة المرفوعة
        $('#image').change(function(e){
            var reader = new FileReader();
            reader.onload = function(e){
                $('#showImage').attr('src', e.target.result);
            }
            reader.readAsDataURL(e.target.files['0']);
        });
    });
</script>
@endsection
