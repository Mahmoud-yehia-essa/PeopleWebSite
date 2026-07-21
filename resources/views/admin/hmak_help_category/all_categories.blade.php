@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إدارة حماك الخير</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">فئات المساعدات</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.help.category') }}">
                <button type="button" class="btn btn-primary">إضافة فئة جديدة</button>
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
                        <th style="width: 5%">#</th>
                        <th style="width: 45%">اسم الفئة</th>
                        <th style="width: 25%">الصورة التوضيحية</th>
                        <th style="width: 15%">تاريخ الإضافة</th>
                        <th style="width: 10%">الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>
                                @if($item->image_path)
                                    <img onclick="showImageModal(this.src)" src="{{ asset($item->image_path) }}" style="width: 60px; height:60px; object-fit: cover; cursor: pointer;" class="rounded border">
                                @else
                                    <span class="text-muted">بدون صورة</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('edit.help.category', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                    <a href="{{ route('delete.help.category', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>اسم الفئة</th>
                        <th>الصورة التوضيحية</th>
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
