@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">المواضيع المحفوظة للمستخدمين</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
        </nav>
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
                        <th>المستخدِم الحافِظ</th>
                        <th>الناشر الأصلي</th>
                        <th>محتوى المنشور</th>
                        <th>الوسائط</th>
                        <th>تاريخ الحفظ</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($savedPosts as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            @if($item->user)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" onclick="showMediaModal('image', this.src)" src="{{ (!empty($item->user->profile_picture) && $item->user->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->user->profile_picture : url('upload/no_image.jpg') }}" style="width: 35px; height: 35px; border: 1px solid #ddd; object-fit: cover; cursor: pointer;">
                                    <span>{{ $item->user->first_name }} {{ $item->user->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">غير معروف</span>
                            @endif
                        </td>
                        <td>
                            @if($item->post && $item->post->user)
                                <div class="d-flex align-items-center">
                                    <img class="rounded-circle me-2" onclick="showMediaModal('image', this.src)" src="{{ (!empty($item->post->user->profile_picture) && $item->post->user->profile_picture != 'non' ) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->post->user->profile_picture : url('upload/no_image.jpg') }}" style="width: 30px; height: 30px; border: 1px solid #eee; object-fit: cover; cursor: pointer;">
                                    <span>{{ $item->post->user->first_name }} {{ $item->post->user->last_name }}</span>
                                </div>
                            @else
                                <span class="text-muted">غير معروف</span>
                            @endif
                        </td>
                        <td>
                            @if($item->post)
                                <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->post->content }}">
                                    {{ $item->post->content ?: 'بدون نص' }}
                                </div>
                            @else
                                <span class="text-danger small">المنشور محذوف</span>
                            @endif
                        </td>
                        <td>
                            @if($item->post && $item->post->image)
                                <img onclick="showMediaModal('image', '{{ 'http://localhost:8888/new_wiselook/uploads/'.$item->post->image }}')" src="{{ 'http://localhost:8888/new_wiselook/uploads/'.$item->post->image }}" class="rounded shadow-sm" style="width: 45px; height: 45px; cursor: pointer; object-fit: cover; border: 1px solid #eee;">
                            @elseif($item->post && $item->post->video)
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showMediaModal('video', '{{ 'http://localhost:8888/new_wiselook/uploads/'.$item->post->video }}')">
                                    <i class="fa-solid fa-play"></i> فيديو
                                </button>
                            @else
                                <span class="text-muted small">لا يوجد</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                        <td>
                            <a href="{{ route('delete.saved_post', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="إلغاء حفظ المنشور للمستخدم">
                                <i class="fa-solid fa-bookmark-slash"></i> إلغاء الحفظ
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>الرقم</th>
                        <th>المستخدِم الحافِظ</th>
                        <th>الناشر الأصلي</th>
                        <th>محتوى المنشور</th>
                        <th>الوسائط</th>
                        <th>تاريخ الحفظ</th>
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
        <div class="modal-content position-relative bg-transparent border-0">
            <button type="button" class="btn text-white" data-bs-dismiss="modal" aria-label="Close"
                style="position: absolute; top: 15px; right: 15px; background-color: black; font-size: 30px; padding: 1px 10px; border-radius: 8px; z-index: 1055;">
                &times;
            </button>
            <div class="text-center p-3">
                <img id="modalImage" src="" class="img-fluid rounded shadow d-none" alt="image" style="max-height: 80vh;">
                <video id="modalVideo" src="" controls class="w-100 rounded shadow d-none" style="max-height: 80vh;"></video>
            </div>
        </div>
    </div>
</div>

<script>
    function showMediaModal(type, src) {
        const modalImg = document.getElementById('modalImage');
        const modalVid = document.getElementById('modalVideo');
        
        modalVid.pause();
        modalVid.src = "";
        modalImg.src = "";
        modalImg.classList.add('d-none');
        modalVid.classList.add('d-none');

        if (type === 'image') {
            modalImg.src = src;
            modalImg.classList.remove('d-none');
        } else if (type === 'video') {
            modalVid.src = src;
            modalVid.classList.remove('d-none');
        }

        var myModal = new bootstrap.Modal(document.getElementById('mediaModal'));
        myModal.show();
    }

    document.getElementById('mediaModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalVideo').pause();
    });
</script>

@endsection
