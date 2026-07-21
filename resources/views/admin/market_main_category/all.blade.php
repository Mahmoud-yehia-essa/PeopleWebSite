@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3">السوق الإلكتروني</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item active" aria-current="page">الفئات الرئيسية</li>
                </ol>
            </nav>
        </div>
        <div class="ms-auto">
            <div class="btn-group">
                <a href="{{ route('market.main_categories.create') }}" class="btn btn-primary">إضافة فئة رئيسية جديدة</a>
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
                            <th>#</th>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>عدد الفئات الفرعية</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if($item->image_path)
                                    <img onclick="showImageModal(this.src)" src="{{ asset($item->image_path) }}" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border-radius: 5px;">
                                @else
                                    <img src="{{ asset('upload/no_image.jpg') }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                @endif
                            </td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->description ?? 'لا يوجد وصف' }}</td>
                            <td>
                                <span class="badge bg-dark">{{ count($item->subCategories) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('market.main_categories.edit', $item->id) }}" class="btn btn-info btn-sm text-white">تعديل</a>
                                <a href="{{ route('market.main_categories.delete', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative bg-transparent border-0">
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close" style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index: 1055;">
                &times;
            </button>
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
