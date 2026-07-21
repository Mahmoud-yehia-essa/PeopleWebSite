@extends('admin.master_admin')

@section('admin')
<div class="page-content">
    <div class="container-fluid">

        <!-- Title -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="mb-0">معلومات الاتصال بنا</h4>
            </div>
        </div>

        <!-- Contact Info Form -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form action="{{ route('contact.info.update') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">العنوان (AR)</label>
                                <input type="text" name="title" value="{{ $contact?->title }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">العنوان (EN)</label>
                                <input type="text" name="title_en" value="{{ $contact?->title_en }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الوصف (AR)</label>
                                <textarea name="des" class="form-control" rows="3">{{ $contact?->des }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">الوصف (EN)</label>
                                <textarea name="des_en" class="form-control" rows="3">{{ $contact?->des_en }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">رقم الهاتف</label>
                                <input type="text" dir="ltr" name="phone" value="{{ $contact?->phone }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">البريد الإلكتروني</label>
                                <input type="email" name="email" value="{{ $contact?->email }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" dir="ltr" name="latitude" value="{{ $contact?->latitude }}" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" dir="ltr" name="longitude" value="{{ $contact?->longitude }}" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary">تحديث</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
