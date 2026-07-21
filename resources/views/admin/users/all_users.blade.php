@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل المستخدمين</div>
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
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
<tr>
<th>الرقم</th>
<th>الإسم</th>
<th>رقم الهاتف</th>
<th>الدولة</th>
<th>وسيلة التسجيل</th>

<th>تاريخ التسجيل</th>

<th> الصورة</th>
<th>الاجراء</th>
</tr>
</thead>
<tbody>
@foreach($users as $key => $item)
<tr>
<td> {{ $key+1 }} </td>
<td>{{ $item->fname }}</td>
<td>{{ $item->phone }}</td>
<td style="font-size: 32px; " >
    <p >{{ $item->country_flag }}</p>
</td>
<td>
    @if($item->provider === 'google')
        <span class="badge bg-danger" style="font-size: 13px;"><i class="fa-brands fa-google me-1"></i> Google</span>
    @elseif($item->provider === 'apple')
        <span class="badge bg-dark" style="font-size: 13px;"><i class="fa-brands fa-apple me-1"></i> Apple</span>
    @elseif($item->provider === 'phone')
        <span class="badge bg-success" style="font-size: 13px;"><i class="fa-solid fa-phone me-1"></i> هاتف</span>
    @else
        <span class="badge bg-primary" style="font-size: 13px;"><i class="fa-solid fa-envelope me-1"></i> بريد إلكتروني</span>
    @endif
</td>

<td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'لم يتم التحديد' }}</td>


<td> <img onclick="showImageModal(this.src)" class="rounded-circle"  src="{{  (!empty($item->profile_picture) && $item->profile_picture != 'non' )  ? 'http://localhost:8888/new_wiselook/uploads/'.$item->profile_picture : url('upload/no_image.jpg') }}" style="width: 50px; height:50px; cursor: pointer; border: 2px solid #0aa2dd;" >  </td>

<td>

@if($item->status == 'active')
<a href="{{ route('inactive.user',$item->id) }}" class="btn btn-primary" title="ايقاف التفعيل"> <i class="fa-solid fa-thumbs-down"></i> </a>
@else
<a href="{{ route('active.user',$item->id) }}" class="btn btn-primary" title="تفعيل"> <i class="fa-solid fa-thumbs-up"></i> </a>
@endif
<a href="{{ route('edit.user',$item->id) }}" class="btn btn-info" title="Edit Data"> <i class="fa fa-pencil"></i> </a>

<a href="{{ route('delete.user',$item->id) }}" class="btn btn-danger" id="delete" title="Delete Data" ><i class="fa fa-trash"></i></a>

</td>
</tr>
@endforeach


</tbody>
<tfoot>
<tr>
    <th>الرقم</th>
    <th>الاسم</th>
<th>رقم الهاتف</th>
    <th>الدولة</th>
    <th>وسيلة التسجيل</th>
    <th>تاريخ التسجيل</th>

    <th> الصورة</th>
    <th>الاجراء</th>
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
