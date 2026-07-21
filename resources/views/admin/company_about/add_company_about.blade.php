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

            <form method="POST" action="{{ route('add.about.store') }}" enctype="multipart/form-data">
                @csrf



                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">العنوان </h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               name="title" value="{{ old('title') }}">
                        @error('title')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>


                  <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Title </h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('title_en') is-invalid @enderror"
                               name="title_en" value="{{ old('title_en') }}">
                        @error('title_en')
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
                                  id="input11" placeholder="الوصف ..." rows="3">{{ old('des') }}</textarea>
                        @error('des')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>



                     <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">Description</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <textarea name="des_en" class="form-control @error('des_en') is-invalid @enderror"
                                  id="input11" placeholder="Description ..." rows="3">{{ old('des_en') }}</textarea>
                        @error('des_en')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>





                {{-- <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">رابط الفيديو على اليوتيوب</h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('video') is-invalid @enderror"
                               name="video" value="{{ old('video', $header->video) }}">
                        @error('video')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}








                                {{-- <div class="row mb-3">
                                    <div class="col-sm-3">
                                        <h6 class="mb-0">الصورة</h6>
                                    </div>
                                    <div class="col-sm-9 text-secondary">
                                        <input type="file" name="photo" class="form-control" id="image" />
                                        @error('photo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div> --}}

                                <!-- Image Preview -->
                                {{-- <div class="row mb-3">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9 text-secondary">
                                        <img id="showImage" src="{{ url($header->photo) }}" alt="Preview" style="width:100px; height: 100px;">

                            <img id="showImage" src="{{ url('upload/no_image.jpg') }}" alt="Admin" width="110">

                                    </div>
                                </div> --}}




                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="اضافة">
                    </div>
                </div>



            </form>
        </div>
    </div>
</div>
  <!-- jQuery for Image Preview -->
                    <script type="text/javascript">
                        $(document).ready(function(){
                            $('#image').change(function(e){
                                var reader = new FileReader();
                                reader.onload = function(e){
                                    $('#showImage').attr('src', e.target.result);
                                }
                                reader.readAsDataURL(e.target.files[0]);
                            });
                        });
                    </script>
@endsection
