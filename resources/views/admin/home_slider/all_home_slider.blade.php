@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">معرض الشاشة الرئيسية</div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.home_slider') }}">
                <button type="button" class="btn btn-primary">إضافة جديد</button>
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
                        <th>نوع المرفق</th>
                        <th>المرفق</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sliders as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->title ?? 'بدون عنوان' }}</td>
                            <td>{{ Str::limit($item->description, 50) ?? 'بدون وصف' }}</td>
                            <td>
                                @if($item->attachment_type == 'image')
                                    <span class="badge bg-success">صورة</span>
                                @else
                                    <span class="badge bg-danger">فيديو</span>
                                @endif
                            </td>
                            <td>
                                @if($item->attachment_type == 'image')
                                    <img onclick="showMediaModal('{{ asset($item->attachment_path) }}', 'image')" src="{{ asset($item->attachment_path) }}" style="width: 70px; height:40px; cursor: pointer; object-fit: cover;">
                                @else
                                    <div onclick="showMediaModal('{{ asset($item->attachment_path) }}', 'video')" style="width: 70px; height:40px; background: #000; display: flex; align-items: center; justify-content: center; color: #fff; cursor: pointer; border-radius: 4px;">
                                        <i class="fa fa-play"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                            <td>
                                <a href="{{ route('edit.home_slider', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                <a href="{{ route('delete.home_slider', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>الوصف</th>
                        <th>نوع المرفق</th>
                        <th>المرفق</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content bg-dark border-0">
        <div class="modal-header border-0 text-white">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-0">
            <img id="modalImage" src="" class="img-fluid rounded-bottom d-none" style="max-height: 500px; object-fit: contain;">
            <video id="modalVideo" src="" class="w-100 rounded-bottom d-none" controls style="max-height: 500px;"></video>
        </div>
      </div>
    </div>
</div>

<script>
    function showMediaModal(src, type) {
        const modalImage = document.getElementById('modalImage');
        const modalVideo = document.getElementById('modalVideo');
        
        // Reset
        modalImage.classList.add('d-none');
        modalImage.src = '';
        modalVideo.classList.add('d-none');
        modalVideo.src = '';
        modalVideo.pause();

        if (type === 'image') {
            modalImage.src = src;
            modalImage.classList.remove('d-none');
        } else if (type === 'video') {
            modalVideo.src = src;
            modalVideo.classList.remove('d-none');
        }

        var myModal = new bootstrap.Modal(document.getElementById('mediaModal'));
        myModal.show();
    }

    // Pause video when modal is closed
    document.getElementById('mediaModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalVideo').pause();
    });
</script>

@endsection
