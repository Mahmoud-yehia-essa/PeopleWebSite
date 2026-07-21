@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إضافة لغة جديدة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.languages') }}"><i class="bx bx-font"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">لغة جديدة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.language') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Language Name (اسم اللغة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اسم اللغة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="name" type="text" class="form-control" value="{{ old('name') }}" placeholder="مثل: العربية، English..." required />
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Language Code (كود اللغة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">كود اللغة (ISO Code)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="code" type="text" class="form-control" value="{{ old('code') }}" placeholder="مثل: ar, en, fr..." required />
                                    @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Text Direction (اتجاه النص) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اتجاه النص (Direction)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="direction" class="form-select" required>
                                        <option value="ltr" {{ old('direction') == 'ltr' ? 'selected' : '' }}>يسار إلى يمين (LTR) - للغات الأجنبية</option>
                                        <option value="rtl" {{ old('direction') == 'rtl' ? 'selected' : '' }}>يمين إلى يسار (RTL) - للعربية واللغات الشبيهة</option>
                                    </select>
                                    @error('direction') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Default (الافتراضية) -->
                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اللغة الافتراضية</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="form-check form-switch">
                                        <input name="is_default" class="form-check-input" type="checkbox" id="defaultSwitch" {{ old('is_default') ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="defaultSwitch">تعيين كلغة افتراضية للنظام بأكمله</label>
                                    </div>
                                    @error('is_default') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Active (التفعيل) -->
                            <div class="row mb-3 align-items-center">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">حالة التفعيل</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="form-check form-switch">
                                        <input name="is_active" class="form-check-input" type="checkbox" id="activeSwitch" {{ old('is_active', '1') == '1' ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="activeSwitch">تفعيل اللغة للاستخدام في الموقع والتطبيق</label>
                                    </div>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Flag Type (نوع العلم) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نوع العلم</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flag_type" id="flagTypeEmoji" value="emoji" {{ old('flag_type', 'emoji') === 'emoji' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flagTypeEmoji">رمز تعبيري (Emoji)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flag_type" id="flagTypeImage" value="image" {{ old('flag_type') === 'image' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flagTypeImage">رفع صورة علم</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Flag Emoji Select (اختيار الرمز التعبيري) -->
                            <div class="row mb-3" id="flagEmojiSection" style="display: {{ old('flag_type', 'emoji') === 'emoji' ? 'flex' : 'none' }};">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اختر علم الدولة (Emoji)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="flag_emoji" id="flagEmojiSelect" class="form-select" style="font-size: 18px;">
                                        <option value="🇸🇦" {{ old('flag_emoji') == '🇸🇦' ? 'selected' : '' }}>العربية 🇸🇦</option>
                                        <option value="🇬🇧" {{ old('flag_emoji') == '🇬🇧' ? 'selected' : '' }}>الإنجليزية (بريطانيا) 🇬🇧</option>
                                        <option value="🇺🇸" {{ old('flag_emoji') == '🇺🇸' ? 'selected' : '' }}>الإنجليزية (أمريكا) 🇺🇸</option>
                                        <option value="🇫🇷" {{ old('flag_emoji') == '🇫🇷' ? 'selected' : '' }}>الفرنسية 🇫🇷</option>
                                        <option value="🇹🇷" {{ old('flag_emoji') == '🇹🇷' ? 'selected' : '' }}>التركية 🇹🇷</option>
                                        <option value="🇪🇬" {{ old('flag_emoji') == '🇪🇬' ? 'selected' : '' }}>العربية (مصر) 🇪🇬</option>
                                        <option value="🇦🇪" {{ old('flag_emoji') == '🇦🇪' ? 'selected' : '' }}>العربية (الإمارات) 🇦🇪</option>
                                        <option value="🇰🇼" {{ old('flag_emoji') == '🇰🇼' ? 'selected' : '' }}>العربية (الكويت) 🇰🇼</option>
                                        <option value="🇶🇦" {{ old('flag_emoji') == '🇶🇦' ? 'selected' : '' }}>العربية (قطر) 🇶🇦</option>
                                        <option value="🇴🇲" {{ old('flag_emoji') == '🇴🇲' ? 'selected' : '' }}>العربية (عمان) 🇴🇲</option>
                                        <option value="🇧🇭" {{ old('flag_emoji') == '🇧🇭' ? 'selected' : '' }}>العربية (البحرين) 🇧🇭</option>
                                        <option value="🇯🇴" {{ old('flag_emoji') == '🇯🇴' ? 'selected' : '' }}>العربية (الأردن) 🇯🇴</option>
                                        <option value="🇵🇸" {{ old('flag_emoji') == '🇵🇸' ? 'selected' : '' }}>العربية (فلسطين) 🇵🇸</option>
                                        <option value="🇸🇾" {{ old('flag_emoji') == '🇸🇾' ? 'selected' : '' }}>العربية (سوريا) 🇸🇾</option>
                                        <option value="🇱🇧" {{ old('flag_emoji') == '🇱🇧' ? 'selected' : '' }}>العربية (لبنان) 🇱🇧</option>
                                        <option value="🇮🇶" {{ old('flag_emoji') == '🇮🇶' ? 'selected' : '' }}>العربية (العراق) 🇮🇶</option>
                                        <option value="🇾🇪" {{ old('flag_emoji') == '🇾🇪' ? 'selected' : '' }}>العربية (اليمن) 🇾🇪</option>
                                        <option value="🇲🇦" {{ old('flag_emoji') == '🇲🇦' ? 'selected' : '' }}>العربية (المغرب) 🇲🇦</option>
                                        <option value="🇩🇿" {{ old('flag_emoji') == '🇩🇿' ? 'selected' : '' }}>العربية (الجزائر) 🇩🇿</option>
                                        <option value="🇹🇳" {{ old('flag_emoji') == '🇹🇳' ? 'selected' : '' }}>العربية (تونس) 🇹🇳</option>
                                        <option value="🇱🇾" {{ old('flag_emoji') == '🇱🇾' ? 'selected' : '' }}>العربية (ليبيا) 🇱🇾</option>
                                        <option value="🇸🇩" {{ old('flag_emoji') == '🇸🇩' ? 'selected' : '' }}>العربية (السودان) 🇸🇩</option>
                                        <option value="🇩🇪" {{ old('flag_emoji') == '🇩🇪' ? 'selected' : '' }}>الألمانية 🇩🇪</option>
                                        <option value="🇪🇸" {{ old('flag_emoji') == '🇪🇸' ? 'selected' : '' }}>الإسبانية 🇪🇸</option>
                                        <option value="🇮🇹" {{ old('flag_emoji') == '🇮🇹' ? 'selected' : '' }}>الإيطالية 🇮🇹</option>
                                        <option value="🇨🇳" {{ old('flag_emoji') == '🇨🇳' ? 'selected' : '' }}>الصينية 🇨🇳</option>
                                        <option value="🇷🇺" {{ old('flag_emoji') == '🇷🇺' ? 'selected' : '' }}>الروسية 🇷🇺</option>
                                        <option value="🇯🇵" {{ old('flag_emoji') == '🇯🇵' ? 'selected' : '' }}>اليابانية 🇯🇵</option>
                                        <option value="🇮🇳" {{ old('flag_emoji') == '🇮🇳' ? 'selected' : '' }}>الهندية 🇮🇳</option>
                                        <option value="🇵🇰" {{ old('flag_emoji') == '🇵🇰' ? 'selected' : '' }}>الأردية 🇵🇰</option>
                                        <option value="🇮🇷" {{ old('flag_emoji') == '🇮🇷' ? 'selected' : '' }}>الفارسية 🇮🇷</option>
                                    </select>
                                    @error('flag_emoji') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Flag Image upload (رفع صورة العلم) -->
                            <div class="row mb-3" id="flagImageSection" style="display: {{ old('flag_type') === 'image' ? 'flex' : 'none' }};">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">أيقونة/علم اللغة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="flag_path" type="file" id="imageInput" class="form-control" accept="image/*" />
                                    @error('flag_path') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Image/Emoji Preview (معاينة العلم) -->
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <div id="emojiPreviewContainer" style="display: {{ old('flag_type', 'emoji') === 'emoji' ? 'block' : 'none' }};">
                                        <span id="emojiPreviewText" style="font-size: 50px; line-height: 1; display: inline-block;">🇸🇦</span>
                                    </div>
                                    <div id="imagePreviewContainer" style="display: {{ old('flag_type') === 'image' ? 'block' : 'none' }};">
                                        <img id="imagePreview" src="{{ url('upload/no_image.jpg') }}" alt="Flag preview" width="80" class="rounded border shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="حفظ اللغة" />
                                    <a href="{{ route('all.languages') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
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
        // إذا تم تعيين اللغة كافتراضية، يجب تفعيلها تلقائياً وإلغاء تعطيل التفعيل
        $('#defaultSwitch').change(function(){
            if($(this).is(':checked')){
                $('#activeSwitch').prop('checked', true);
            }
        });

        $('#activeSwitch').change(function(){
            if(!$(this).is(':checked') && $('#defaultSwitch').is(':checked')){
                // لا يمكن تعطيل لغة افتراضية
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'لا يمكن تعطيل اللغة الافتراضية للنظام!',
                    confirmButtonText: 'حسناً'
                });
                $(this).prop('checked', true);
            }
        });

        // التغيير بين الرمز التعبيري والصورة
        $('input[name="flag_type"]').change(function(){
            var type = $(this).val();
            if(type === 'emoji'){
                $('#flagEmojiSection').fadeIn().css('display', 'flex');
                $('#flagImageSection').hide();
                $('#emojiPreviewContainer').show();
                $('#imagePreviewContainer').hide();
            } else {
                $('#flagEmojiSection').hide();
                $('#flagImageSection').fadeIn().css('display', 'flex');
                $('#emojiPreviewContainer').hide();
                $('#imagePreviewContainer').show();
            }
        });

        // تحديث معاينة الرمز التعبيري عند الاختيار
        $('#flagEmojiSelect').change(function(){
            $('#emojiPreviewText').text($(this).val());
        });
        
        // تعيين الرمز التعبيري الافتراضي في المعاينة عند التحميل
        $('#emojiPreviewText').text($('#flagEmojiSelect').val());

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
