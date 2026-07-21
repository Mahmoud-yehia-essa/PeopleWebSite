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

            <form method="POST" action="{{ route('edit.counter.store') }}" enctype="multipart/form-data">
                @csrf



                                <input type="hidden" name="id" value="{{ $counter->id }}">
                                <input type="hidden" name="old_image" value="{{ $counter->photo }}">
                <div class="row mb-3">
                    <div class="col-sm-3">
                        <h6 class="mb-0">العنوان </h6>
                    </div>
                    <div class="col-sm-9 text-secondary">
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               name="title" value="{{ old('title', $counter->title) }}">
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
                               name="title_en" value="{{ old('title_en', $counter->title_en) }}">
                        @error('title_en')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>







                    <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">الرقم</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <input name="counter" type="number" class="form-control" min="1" step="1" value="{{ old('counter',$counter->counter) }}" />
                                    @error('counter')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>















                <div class="row">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9 text-secondary">
                        <input type="submit" class="btn btn-primary px-4" value="حفظ">
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
