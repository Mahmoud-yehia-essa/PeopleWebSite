@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #008cff; padding-left: 10px; font-weight: bold;">إنشاء تذكرة جديدة</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.support_tickets.index') }}">تذاكر الدعم الفني</a></li>
                <li class="breadcrumb-item active" aria-current="page">فتح تذكرة وتواصل جديد</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('admin.support_tickets.index') }}" class="btn btn-secondary px-4"><i class="bx bx-arrow-back"></i> العودة للقائمة</a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-top border-0 border-4 border-primary shadow-sm">
            <div class="card-body p-5">
                <div class="card-title d-flex align-items-center mb-4">
                    <div><i class="bx bx-support me-1 font-22 text-primary"></i></div>
                    <h5 class="mb-0 text-primary font-weight-bold">تواصل مباشر مع مستخدم</h5>
                </div>
                <hr>
                
                <form action="{{ route('admin.support_tickets.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    
                    <!-- Select User -->
                    <div class="col-12">
                        <label for="user_id" class="form-label font-weight-bold text-dark">اختر المستخدم المستهدف</label>
                        <select name="user_id" id="user_id" class="form-select select2" required style="font-size: 14px;">
                            <option value="" disabled selected>-- اختر العضو للبدء في مراسلته --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->fname }} {{ $user->lname }} ({{ $user->email }} - {{ $user->phone ?: 'بلا هاتف' }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div class="col-12 col-md-8">
                        <label for="subject" class="form-label font-weight-bold text-dark">عنوان التذكرة / الموضوع</label>
                        <input type="text" name="subject" id="subject" class="form-control" placeholder="مثال: استفسار بخصوص الحساب، أو تهنئة، إلخ..." required value="{{ old('subject') }}">
                        @error('subject')
                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div class="col-12 col-md-4">
                        <label for="priority" class="form-label font-weight-bold text-dark">درجة الأهمية</label>
                        <select name="priority" id="priority" class="form-select" required>
                            <option value="low">منخفضة</option>
                            <option value="medium" selected>متوسطة</option>
                            <option value="high">عالية</option>
                        </select>
                        @error('priority')
                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Message Message -->
                    <div class="col-12">
                        <label for="message" class="form-label font-weight-bold text-dark">نص الرسالة أو الرد</label>
                        <textarea name="message" id="message" class="form-control" rows="5" placeholder="اكتب تفاصيل الموضوع أو رسالتك للعضو هنا..." required>{{ old('message') }}</textarea>
                        @error('message')
                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Attachment -->
                    <div class="col-12">
                        <label for="attachment" class="form-label font-weight-bold text-dark">إرفاق ملف أو صورة (اختياري)</label>
                        <input type="file" name="attachment" id="attachment" class="form-control">
                        <small class="text-muted d-block mt-1">الامتدادات المسموح بها: الصور، PDF، ملفات Zip، Word، Excel بحد أقصى 5 ميجابايت.</small>
                        @error('attachment')
                            <span class="text-danger mt-1 d-block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mt-4 text-start">
                        <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold">
                            إنشاء التذكرة وإرسال الرسالة <i class="fa-regular fa-paper-plane ms-1"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
