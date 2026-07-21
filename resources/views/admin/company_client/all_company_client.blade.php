@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل العملاء</div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.company_client') }}">
                <button type="button" class="btn btn-primary">إضافة عميل جديد</button>
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
                        <th>الوصف</th>
                        <th>تاريخ الإضافة</th>
                        <th>الصورة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($item->des, 50) }}</td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                            <td>
                                <img onclick="showImageModal(this.src)" src="{{ (!empty($item->photo) && $item->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.basename($item->photo) : url('upload/no_image.jpg') }}" style="width: 70px; height:40px; cursor: pointer;">
                            </td>
                            <td>
                                @if($item->status == 'active')
                                    <a href="{{ route('inactive.company_client', $item->id) }}" class="btn btn-primary" title="إخفاء">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('active.company_client', $item->id) }}" class="btn btn-primary" title="إظهار">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </a>
                                @endif
                                <a href="{{ route('edit.company_client', $item->id) }}" class="btn btn-info">تعديل</a>
                                <a href="{{ route('delete.company_client', $item->id) }}" class="btn btn-danger" id="delete">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الوصف</th>
                        <th>تاريخ الإضافة</th>
                        <th>الصورة</th>
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
