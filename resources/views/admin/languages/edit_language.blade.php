@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل بيانات اللغة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.languages') }}"><i class="bx bx-font"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل لغة: {{ $language->name }}</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.language') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $language->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Language Name (اسم اللغة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اسم اللغة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="name" type="text" class="form-control" value="{{ old('name', $language->name) }}" placeholder="مثل: العربية، English..." required />
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Language Code (كود اللغة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">كود اللغة (ISO Code)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="code" type="text" class="form-control" value="{{ old('code', $language->code) }}" placeholder="مثل: ar, en, fr..." required />
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
                                        <option value="ltr" {{ old('direction', $language->direction) == 'ltr' ? 'selected' : '' }}>يسار إلى يمين (LTR) - للغات الأجنبية</option>
                                        <option value="rtl" {{ old('direction', $language->direction) == 'rtl' ? 'selected' : '' }}>يمين إلى يسار (RTL) - للعربية واللغات الشبيهة</option>
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
                                        <input name="is_default" class="form-check-input" type="checkbox" id="defaultSwitch" {{ old('is_default', $language->is_default) ? 'checked' : '' }} value="1" {{ $language->is_default ? 'onclick="return false;"' : '' }}>
                                        <label class="form-check-label" for="defaultSwitch">تعيين كلغة افتراضية للنظام بأكمله</label>
                                    </div>
                                    @if($language->is_default)
                                        <small class="text-muted d-block mt-1">هذه هي اللغة الافتراضية الحالية للنظام، ولا يمكن إلغاء تعيينها إلا عند تحديد لغة أخرى كافتراضية.</small>
                                    @endif
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
                                        <input name="is_active" class="form-check-input" type="checkbox" id="activeSwitch" {{ old('is_active', $language->is_active) ? 'checked' : '' }} value="1">
                                        <label class="form-check-label" for="activeSwitch">تفعيل اللغة للاستخدام في الموقع والتطبيق</label>
                                    </div>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @php
                                $isEmoji = !empty($language->flag_path) && !str_contains($language->flag_path, '.') && !filter_var($language->flag_path, FILTER_VALIDATE_URL);
                            @endphp

                            <!-- Flag Type (نوع العلم) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">نوع العلم</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flag_type" id="flagTypeEmoji" value="emoji" {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'emoji' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flagTypeEmoji">رمز تعبيري (Emoji)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="flag_type" id="flagTypeImage" value="image" {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'image' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="flagTypeImage">رفع صورة علم</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Flag Emoji Select (اختيار الرمز التعبيري) -->
                            <div class="row mb-3" id="flagEmojiSection" style="display: {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'emoji' ? 'flex' : 'none' }};">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اختر علم الدولة (Emoji)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="flag_emoji" id="flagEmojiSelect" class="form-select" style="font-size: 18px;">
                                        @php
                                            $currentEmoji = $isEmoji ? $language->flag_path : '🇸🇦';
                                        @endphp
                                        <option value="🇸🇦" {{ old('flag_emoji', $currentEmoji) == '🇸🇦' ? 'selected' : '' }}>العربية 🇸🇦</option>
                                        <option value="🇬🇧" {{ old('flag_emoji', $currentEmoji) == '🇬🇧' ? 'selected' : '' }}>الإنجليزية (بريطانيا) 🇬🇧</option>
                                        <option value="🇺🇸" {{ old('flag_emoji', $currentEmoji) == '🇺🇸' ? 'selected' : '' }}>الإنجليزية (أمريكا) 🇺🇸</option>
                                        <option value="🇫🇷" {{ old('flag_emoji', $currentEmoji) == '🇫🇷' ? 'selected' : '' }}>الفرنسية 🇫🇷</option>
                                        <option value="🇹🇷" {{ old('flag_emoji', $currentEmoji) == '🇹🇷' ? 'selected' : '' }}>التركية 🇹🇷</option>
                                        <option value="🇪🇬" {{ old('flag_emoji', $currentEmoji) == '🇪🇬' ? 'selected' : '' }}>العربية (مصر) 🇪🇬</option>
                                        <option value="🇦🇪" {{ old('flag_emoji', $currentEmoji) == '🇦🇪' ? 'selected' : '' }}>العربية (الإمارات) 🇦🇪</option>
                                        <option value="🇰🇼" {{ old('flag_emoji', $currentEmoji) == '🇰🇼' ? 'selected' : '' }}>العربية (الكويت) 🇰🇼</option>
                                        <option value="🇶🇦" {{ old('flag_emoji', $currentEmoji) == '🇶🇦' ? 'selected' : '' }}>العربية (قطر) 🇶🇦</option>
                                        <option value="🇴🇲" {{ old('flag_emoji', $currentEmoji) == '🇴🇲' ? 'selected' : '' }}>العربية (عمان) 🇴🇲</option>
                                        <option value="🇧🇭" {{ old('flag_emoji', $currentEmoji) == '🇧🇭' ? 'selected' : '' }}>العربية (البحرين) 🇧🇭</option>
                                        <option value="🇯🇴" {{ old('flag_emoji', $currentEmoji) == '🇯🇴' ? 'selected' : '' }}>العربية (الأردن) 🇯🇴</option>
                                        <option value="🇵🇸" {{ old('flag_emoji', $currentEmoji) == '🇵🇸' ? 'selected' : '' }}>العربية (فلسطين) 🇵🇸</option>
                                        <option value="🇸🇾" {{ old('flag_emoji', $currentEmoji) == '🇸🇾' ? 'selected' : '' }}>العربية (سوريا) 🇸🇾</option>
                                        <option value="🇱🇧" {{ old('flag_emoji', $currentEmoji) == '🇱🇧' ? 'selected' : '' }}>العربية (لبنان) 🇱🇧</option>
                                        <option value="🇮🇶" {{ old('flag_emoji', $currentEmoji) == '🇮🇶' ? 'selected' : '' }}>العربية (العراق) 🇮🇶</option>
                                        <option value="🇾🇪" {{ old('flag_emoji', $currentEmoji) == '🇾🇪' ? 'selected' : '' }}>العربية (اليمن) 🇾🇪</option>
                                        <option value="🇲🇦" {{ old('flag_emoji', $currentEmoji) == '🇲🇦' ? 'selected' : '' }}>العربية (المغرب) 🇲🇦</option>
                                        <option value="🇩🇿" {{ old('flag_emoji', $currentEmoji) == '🇩🇿' ? 'selected' : '' }}>العربية (الجزائر) 🇩🇿</option>
                                        <option value="🇹🇳" {{ old('flag_emoji', $currentEmoji) == '🇹🇳' ? 'selected' : '' }}>العربية (تونس) 🇹🇳</option>
                                        <option value="🇱🇾" {{ old('flag_emoji', $currentEmoji) == '🇱🇾' ? 'selected' : '' }}>العربية (ليبيا) 🇱🇾</option>
                                        <option value="🇸🇩" {{ old('flag_emoji', $currentEmoji) == '🇸🇩' ? 'selected' : '' }}>العربية (السودان) 🇸🇩</option>
                                        <option value="🇩🇪" {{ old('flag_emoji', $currentEmoji) == '🇩🇪' ? 'selected' : '' }}>الألمانية 🇩🇪</option>
                                        <option value="🇪🇸" {{ old('flag_emoji', $currentEmoji) == '🇪🇸' ? 'selected' : '' }}>الإسبانية 🇪🇸</option>
                                        <option value="🇮🇹" {{ old('flag_emoji', $currentEmoji) == '🇮🇹' ? 'selected' : '' }}>الإيطالية 🇮🇹</option>
                                        <option value="🇨🇳" {{ old('flag_emoji', $currentEmoji) == '🇨🇳' ? 'selected' : '' }}>الصينية 🇨🇳</option>
                                        <option value="🇷🇺" {{ old('flag_emoji', $currentEmoji) == '🇷🇺' ? 'selected' : '' }}>الروسية 🇷🇺</option>
                                        <option value="🇯🇵" {{ old('flag_emoji', $currentEmoji) == '🇯🇵' ? 'selected' : '' }}>اليابانية 🇯🇵</option>
                                        <option value="🇮🇳" {{ old('flag_emoji', $currentEmoji) == '🇮🇳' ? 'selected' : '' }}>الهندية 🇮🇳</option>
                                        <option value="🇵🇰" {{ old('flag_emoji', $currentEmoji) == '🇵🇰' ? 'selected' : '' }}>الأردية 🇵🇰</option>
                                        <option value="🇮🇷" {{ old('flag_emoji', $currentEmoji) == '🇮🇷' ? 'selected' : '' }}>الفارسية 🇮🇷</option>
                                    </select>
                                    @error('flag_emoji') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Flag Image upload (رفع صورة العلم) -->
                            <div class="row mb-3" id="flagImageSection" style="display: {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'image' ? 'flex' : 'none' }};">
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
                                    <div id="emojiPreviewContainer" style="display: {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'emoji' ? 'block' : 'none' }};">
                                        <span id="emojiPreviewText" style="font-size: 50px; line-height: 1; display: inline-block;">{{ $isEmoji ? $language->flag_path : '🇸🇦' }}</span>
                                    </div>
                                    <div id="imagePreviewContainer" style="display: {{ old('flag_type', $isEmoji ? 'emoji' : 'image') === 'image' ? 'block' : 'none' }};">
                                        @php
                                            $flagUrl = url('upload/no_image.jpg');
                                            if (!$isEmoji && $language->flag_path) {
                                                $flagUrl = filter_var($language->flag_path, FILTER_VALIDATE_URL) ? $language->flag_path : 'http://localhost:8888/new_wiselook/uploads/' . basename($language->flag_path);
                                            }
                                        @endphp
                                        <img id="imagePreview" src="{{ $flagUrl }}" alt="Flag preview" width="80" class="rounded border shadow-sm">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="تحديث البيانات" />
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
            if(!$(this).is(':checked') && ($('#defaultSwitch').is(':checked') || {{$language->is_default ? 'true' : 'false'}})){
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
