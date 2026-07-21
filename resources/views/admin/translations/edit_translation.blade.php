@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">تعديل الترجمة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('all.languages') }}"><i class="bx bx-font"></i></a></li>
                <li class="breadcrumb-item"><a href="{{ route('all.translations', $translation->language->id) }}">ترجمات لغة: {{ $translation->language->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">تعديل الترجمة</li>
            </ol>
        </nav>
    </div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('update.translation') }}" method="POST">
                    @csrf
                    <input type="hidden" name="id" value="{{ $translation->id }}" />
                    
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Language Name (اسم اللغة - للقراءة فقط) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">اللغة المستهدفة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="text" class="form-control bg-light" value="{{ $translation->language->name }} ({{ $translation->language->code }})" disabled />
                                </div>
                            </div>

                            <!-- Translation Key (المفتاح البرمجي) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">المفتاح البرمجي (Key)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="key" type="text" class="form-control" value="{{ old('key', $translation->key) }}" placeholder="مثال: welcome_message, login_button_title..." required />
                                    <small class="text-muted d-block mt-1">تعديل المفتاح البرمجي قد يتطلب تحديث استدعائه في ملفات الكود البرمجي للموقع والتطبيق.</small>
                                    @error('key') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Translation Value (النص المترجم الفعلي) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">النص المترجم (Value)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <textarea name="value" rows="5" class="form-control" placeholder="أدخل النص الفعلي المقابل للمفتاح بهذه اللغة..." required>{{ old('value', $translation->value) }}</textarea>
                                    @error('value') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="حفظ التعديلات" />
                                    <a href="{{ route('all.translations', $translation->language->id) }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
