@extends('admin.master_admin')
@section('admin')

<div class="page-content">
    <!--breadcrumb-->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
        <div class="breadcrumb-title pe-3" style="padding-right: 15px;">السوق الإلكتروني</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item active" aria-current="page">المنتجات المضافة من المستخدمين</li>
                </ol>
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
                            <th>#</th>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>المعلن</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if($item->image_path)
                                    <img onclick="showImageModal(this.src)" src="{{ asset($item->image_path) }}" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; border-radius: 5px;">
                                @else
                                    <img src="{{ asset('upload/no_image.jpg') }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('market.public.item_details', $item->id) }}" target="_blank" class="fw-bold text-primary">{{ $item->name }}</a>
                            </td>
                            <td>
                                <div>
                                    <span class="d-block fw-bold">{{ $item->user ? ($item->user->fname . ' ' . $item->user->lname) : 'مستخدم غير معروف' }}</span>
                                    @if($item->phone)
                                        <span class="d-block text-muted small"><i class="fa fa-phone"></i> {{ $item->phone }}</span>
                                    @endif
                                    @if($item->whatsapp)
                                        <span class="d-block text-success small"><i class="fa-brands fa-whatsapp"></i> {{ $item->whatsapp }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="text-xs">
                                    <span class="badge bg-primary">{{ $item->mainCategory ? $item->mainCategory->name : 'غير محدد' }}</span>
                                    @if($item->subCategory)
                                        <span class="d-block text-muted mt-1">&rarr; {{ $item->subCategory->name }}</span>
                                    @endif
                                    @if($item->subSubCategory)
                                        <span class="d-block text-muted">&rarr; {{ $item->subSubCategory->name }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="fw-bold text-success">{{ $item->price ? ($item->price . ' د.ك') : 'غير محدد' }}</td>
                            <td>
                                @if($item->status == 'active')
                                    <span class="badge bg-success">نشط</span>
                                    <a href="{{ route('market.items.toggle_status', $item->id) }}" class="btn btn-warning btn-sm py-0 px-1 text-white block mt-1" style="font-size: 10px;">تعطيل</a>
                                @else
                                    <span class="badge bg-danger">غير نشط</span>
                                    <a href="{{ route('market.items.toggle_status', $item->id) }}" class="btn btn-success btn-sm py-0 px-1 text-white block mt-1" style="font-size: 10px;">تفعيل</a>
                                @endif
                            </td>
                            <td class="small">{{ $item->created_at ? $item->created_at->format('Y-m-d H:i') : '' }}</td>
                            <td>
                                <a href="{{ route('market.items.delete', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
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
