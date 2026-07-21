@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل المعارض</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.gallery') }}">
                <button type="button" class="btn btn-primary">إضافة معرض</button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>إسم المعرض (AR / EN)</th>
                        <th>الصور</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($galleries as $key => $gallery)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $gallery->title }} <br>
                                {{ $gallery->title_en }}
                            </td>
                           <td>
    <div style="display:flex; flex-wrap:wrap; gap:5px; max-width:300px;">
        @foreach($gallery->photos as $photo)
            <div style="position:relative;">
                <img onclick="showImageModal(this.src)"
                     src="{{ url($photo->photo) }}"
                     style="width:70px; height:40px; cursor:pointer; border:1px solid #ccc; border-radius:3px;">
                <a href="{{ route('delete.gallery.photo', $photo->id) }}"
                   {{-- onclick="return confirm('هل أنت متأكد من حذف هذه الصورة؟')" --}}
                   id="delete"
                   style="position:absolute; top:-5px; right:-5px; color:red; font-weight:bold; text-decoration:none; background:white; border-radius:50%; padding:0 4px;">&times;</a>
            </div>
        @endforeach
    </div>
</td>
                            <td>
                                <a href="{{ route('edit.gallery', $gallery->id) }}" class="btn btn-info">تعديل</a>
                                <a href="{{ route('delete.gallery', $gallery->id) }}" class="btn btn-danger"
                                   id="delete">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>إسم المعرض (AR / EN)</th>
                        <th>الصور</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative bg-transparent border-0">

        <!-- Close Button -->
        <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close"
                style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index:1055;">
            &times;
        </button>

        <!-- Modal Image -->
        <img id="modalImage" src="" class="img-fluid rounded shadow" alt="image">
      </div>
    </div>
</div>

<script>
function showImageModal(src) {
    document.getElementById('modalImage').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
    myModal.show();
}
</script>

@endsection



{{-- @extends('admin.master_admin')
@section('admin')

<div class="col-lg-12">
    <div class="card">
        <div class="card-body">

            <a href="{{ route('add.gallery') }}" class="btn btn-success mb-3">Add Gallery</a>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Title (AR / EN)</th>
                        <th>Photos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($galleries as $gallery)
                        <tr>
                            <td>
                                {{ $gallery->title }} <br>
                                {{ $gallery->title_en }}
                            </td>
                            <td>
                                @foreach($gallery->photos as $photo)
                                    <div style="display:inline-block; position:relative; margin:3px;">
                                        <img src="{{ url($photo->photo) }}" width="80" height="80" class="border rounded">
                                        <a href="{{ route('delete.gallery.photo', $photo->id) }}"
                                           onclick="return confirm('Are you sure you want to delete this photo?')"
                                           style="position:absolute; top:0; right:0; color:red; font-weight:bold; text-decoration:none;">
                                           &times;
                                        </a>
                                    </div>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('edit.gallery', $gallery->id) }}" class="btn btn-info btn-sm">Edit</a>
                                <a href="{{ route('delete.gallery', $gallery->id) }}"
                                   onclick="return confirm('Are you sure you want to delete this gallery?')"
                                   class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

@endsection --}}
