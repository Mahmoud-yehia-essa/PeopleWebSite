@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">اضافة سعر جديد</div>
    </div>
    <!--end breadcrumb-->

    <div class="container">
        <div class="main-body">
            <div class="row">
                <div class="col-lg-10">
                    <div class="card">
                        <div class="card-body">
                            <!-- Display Validation Errors -->
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="post" action="{{ route('add.price.store') }}" enctype="multipart/form-data">
                                @csrf

                                <!-- Title -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الوصف</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" />
                                        @error('title')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Price -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">السعر</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="number" step="0.0001" name="price" id="price" class="form-control" value="{{ old('price') }}" />
                                        @error('price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                  <!-- Price -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">عدد الألعاب</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="number" name="games_number" class="form-control" value="{{ old('games_number') }}" />
                                        @error('games_number')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Color 1 -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اختر لون الخلفية 1</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="color" name="color1" id="color1" class="form-control form-control-color" value="{{ old('color1', '#000000') }}" />
                                        @error('color1')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Color 2 -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">اختر لون الخلفية 2</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="color" name="color2" id="color2" class="form-control form-control-color" value="{{ old('color2', '#ffffff') }}" />
                                        @error('color2')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Gradient Preview -->
                                <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">معاينة التدرج</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <div id="gradientPreview" style="height: 120px; border-radius: 10px; border: 1px solid #ccc; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-shadow: 1px 1px 3px #000;">
                                            <div id="previewPrice" style="font-size: 26px; font-weight: bold;">0 دك</div>
                                            <div id="previewTitle" style="font-size: 18px;">الوصف</div>
                                        </div>
                                    </div>
                                </div>




                                <!-- Submit Button -->
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="submit" class="btn btn-primary px-4" value="اضافة سعر جديدة" />
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Scripts -->
                    <script type="text/javascript">
                        // Image Preview
                        $(document).ready(function(){
                            $('#image').change(function(e){
                                var reader = new FileReader();
                                reader.onload = function(e){
                                    $('#showImage').attr('src', e.target.result);
                                }
                                reader.readAsDataURL(e.target.files[0]);
                            });
                        });

                        // Live Gradient Preview Update
                        function updateGradientPreview() {
                            const color1 = document.getElementById('color1').value;
                            const color2 = document.getElementById('color2').value;
                            const title = document.getElementById('title').value || 'الوصف';
                            const priceVal = document.getElementById('price').value;
                            const price = priceVal ? `${priceVal} دك` : '0 دك';

                            const preview = document.getElementById('gradientPreview');
                            preview.style.background = `linear-gradient(90deg, ${color1}, ${color2})`;

                            document.getElementById('previewPrice').textContent = price;
                            document.getElementById('previewTitle').textContent = title;
                        }

                        document.addEventListener('DOMContentLoaded', function () {
                            updateGradientPreview();
                            document.getElementById('color1').addEventListener('input', updateGradientPreview);
                            document.getElementById('color2').addEventListener('input', updateGradientPreview);
                            document.getElementById('title').addEventListener('input', updateGradientPreview);
                            document.getElementById('price').addEventListener('input', updateGradientPreview);
                        });
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
