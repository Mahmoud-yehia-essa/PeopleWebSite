@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل المقالات</div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.article') }}">
                <button type="button" class="btn btn-primary">إضافة مقال جديد</button>
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
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الكاتب (عضو الفريق)</th>
                        <th>الوصف المختصر</th>
                        <th>الصورة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->title }}</td>
                            <td>
                                @if($item->author)
                                    <span class="badge bg-success">{{ $item->author->name }}</span>
                                @else
                                    <span class="badge bg-secondary">غير محدد</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($item->short_description, 50) }}</td>
                            <td>
                                <img onclick="showImageModal(this.src)" src="{{ (!empty($item->image_path) && $item->image_path != 'non') ? 'http://localhost:8888/new_wiselook/uploads/' . basename($item->image_path) : url('upload/no_image.jpg') }}" style="width: 70px; height:40px; cursor: pointer; object-fit: cover;">
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                            <td>
                                <a href="{{ route('edit.article', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                <a href="{{ route('delete.article', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الكاتب (عضو الفريق)</th>
                        <th>الوصف المختصر</th>
                        <th>الصورة</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content bg-transparent border-0">
        <button type="button" class="btn text-white" data-bs-dismiss="modal" style="position:absolute;top:10px;right:10px;background:black;">&times;</button>
        <img id="modalImage" src="" class="img-fluid rounded shadow">
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
