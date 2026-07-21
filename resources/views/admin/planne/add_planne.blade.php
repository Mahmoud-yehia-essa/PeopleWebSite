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

            <form method="POST" action="{{ route('add.plan.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">العنوان</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
                        @error('title') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Title</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" name="title_en" class="form-control @error('title_en') is-invalid @enderror" value="{{ old('title_en') }}">
                        @error('title_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">الوصف</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des" class="form-control @error('des') is-invalid @enderror" rows="6">{{ old('des') }}</textarea>
                        @error('des') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Description</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control @error('des_en') is-invalid @enderror" rows="6">{{ old('des_en') }}</textarea>
                        @error('des_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">اضافة الخدمات</h6></div>
                    <div class="col-sm-9 text-secondary">

                        <textarea name="service" class="form-control @error('service') is-invalid @enderror" rows="7">{{ old('service') }}</textarea>
                        @error('service') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Services</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="service_en" class="form-control @error('service_en') is-invalid @enderror" rows="7">{{ old('service_en') }}</textarea>
                        @error('service_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">التلميح</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="hint" class="form-control @error('hint') is-invalid @enderror" rows="2">{{ old('hint') }}</textarea>
                        @error('hint') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">Hint</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="hint_en" class="form-control @error('hint_en') is-invalid @enderror" rows="2">{{ old('hint_en') }}</textarea>
                        @error('hint_en') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"><h6 class="mb-0">السعر</h6></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}">
                        @error('price') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>




                          <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">هل الخطة مقترحة للمستخدم</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">


                                        <select  name="is_suggest" class="form-select" aria-label="Default select example">

                                        <option  value="no">لا</option>

                                         <option value="yes">نعم</option>



                                        </select>

                                        @error('is_suggest') <span class="text-danger">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="إضافة">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
