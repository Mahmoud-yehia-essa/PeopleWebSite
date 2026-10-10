@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل المديرين</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">

        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{route('add.user')}}" >

<button type="button" class="btn btn-primary">

    اضافة مستخدم

</button>
</a>


        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-md-6 mb-2 mb-md-0">
                <span class="badge bg-primary fs-6 px-3 py-2">
                    <i class="fa-solid fa-user-shield me-1"></i> إجمالي المديرين: {{ number_format($users->total()) }}
                </span>
                @if(!empty($search))
                    <span class="badge bg-info fs-6 px-3 py-2 ms-2">
                        نتائج البحث عن: "{{ $search }}"
                    </span>
                @endif
            </div>
            <div class="col-md-6">
                <form action="{{ route('all.admin') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="ابحث بالإسم، البريد، أو الهاتف..." value="{{ $search ?? '' }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i> بحث
                        </button>
                        @if(!empty($search))
                            <a href="{{ route('all.admin') }}" class="btn btn-secondary" title="إلغاء البحث">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
<tr>
<th>الرقم</th>
<th>الإسم</th>
<th>البريد الإلكتروني</th>
<th>رقم الهاتف</th>
<th>تاريخ التسجيل</th>
<th>الصورة</th>
<th>الاجراء</th>
</tr>
</thead>
<tbody>
@forelse($users as $key => $item)
<tr>
<td> {{ $users->firstItem() + $key }} </td>
<td><strong>{{ $item->name ?: ($item->fname ?: 'مدير') }}</strong></td>
<td><small class="text-muted">{{ $item->email ?: '—' }}</small></td>
<td><span dir="ltr">{{ $item->phone ?: '—' }}</span></td>
<td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>

<td> <img onclick="showImageModal(this.src)" class="rounded-circle" src="{{ (!empty($item->profile_picture) && $item->profile_picture != 'non') ? (filter_var($item->profile_picture, FILTER_VALIDATE_URL) ? $item->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$item->profile_picture) : url('upload/no_image.jpg') }}" style="width: 45px; height:45px; cursor: pointer; border: 2px solid #0aa2dd; object-fit: cover;" alt="avatar"> </td>

<td>
@if($item->status == 'active')
<a href="{{ route('inactive.user',$item->id) }}" class="btn btn-sm btn-primary" title="ايقاف التفعيل"> <i class="fa-solid fa-thumbs-down"></i> </a>
@else
<a href="{{ route('active.user',$item->id) }}" class="btn btn-sm btn-primary" title="تفعيل"> <i class="fa-solid fa-thumbs-up"></i> </a>
@endif
<a href="{{ route('edit.user',$item->id) }}" class="btn btn-sm btn-info" title="تعديل"> <i class="fa fa-pencil"></i> </a>
<a href="{{ route('delete.user',$item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف"><i class="fa fa-trash"></i></a>
</td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-center py-4 text-muted">
        <i class="fa-solid fa-user-slash fs-3 d-block mb-2"></i>
        لا يوجد مديرين مطابقين للبحث
    </td>
</tr>
@endforelse
</tbody>
</table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
            <div class="text-muted small">
                عرض من {{ $users->firstItem() ?? 0 }} إلى {{ $users->lastItem() ?? 0 }} من أصل {{ number_format($users->total()) }} مدير
            </div>
            <div>
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>



<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content position-relative bg-transparent border-0">

        <!-- Rectangular Close Button -->
        <button type="button"
                class="btn text-white"
                data-bs-dismiss="modal"
                aria-label="Close"
                style="
                  position: absolute;
                  top: 15px;
                  right: 15px;
                  background-color: black;
                  font-size: 30px;
                  padding: 1px 10px;
                  border-radius: 8px;
                  z-index: 1055;
                ">
            &times;
        </button>

        <!-- Image -->
        <img id="modalImage" src="" class="img-fluid rounded shadow"  alt="image">
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
