@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="col-lg-16">
    <div class="card">
        <div class="card-body">

            {{-- Display Success Message --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Display Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('update.versions.store') }}">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">اصدار التطبيق الحالي</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('version') is-invalid @enderror"
                               name="version" value="{{ old('version', $appVersion->version) }}">
                        @error('version')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">App Store رابط التطبيق على</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('ios') is-invalid @enderror"
                               name="ios" value="{{ old('ios', $appVersion->ios) }}">
                        @error('ios')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Google Play  رابط التطبيق على</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('android') is-invalid @enderror"
                               name="android" value="{{ old('android', $appVersion->android) }}">
                        @error('android')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الوصف</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror"
                                  id="input11" placeholder="Description ..." rows="3">{{ old('des', $appVersion->des) }}</textarea>
                        @error('des')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>



                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">التحديث في التطبيق الزامي ؟</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">


                        <select  name="update_required" class="form-select" aria-label="Default select example">



                            <option value="yes" {{ old('update_required',$appVersion->update_required) == 'yes' ? 'selected' : '' }} >نعم</option>

                            <option value="no" {{ old('update_required',$appVersion->update_required) == 'no' ? 'selected' : '' }} >لا</option>




                        </select>

                        @error('special') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>


                {{-- <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">الألعاب في التطبيق ؟</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">


                        <select  name="app_type" class="form-select" aria-label="Default select example">



                            <option value="free" {{ old('app_type',$appVersion->app_type) == 'free' ? 'selected' : '' }} >مجانية</option>

                            <option value="paid" {{ old('app_type',$appVersion->app_type) == 'paid' ? 'selected' : '' }} >مدفوعة</option>




                        </select>

                        @error('special') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="تحديث">
                    </div>
                </div>



            </form>
        </div>
    </div>
</div>
@endsection
