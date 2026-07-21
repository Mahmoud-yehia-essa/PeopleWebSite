@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">كل الرسائل</div>
    <div class="ms-auto">
        {{-- <div class="btn-group">
            <a href="{{ route('add.teamwork') }}">
                <button type="button" class="btn btn-primary">إضافة عضو جديد</button>
            </a>
        </div> --}}
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
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                         <th>التاريخ</th>

                        <th>رقم الهاتف</th>
                        {{-- <th>الرسالة</th> --}}
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($values as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                             <td>{{ $item->phone }}</td>
                             {{-- <td>{{ $item->message }}</td> --}}

                            <td>

                                  <!-- Show Full Message -->
    <button type="button" class="btn btn-warning btn-sm" title="عرض الرسالة"
            onclick="showMessageModal(`{{ $item->message }}`)">
        <i class="fa-solid fa-eye"></i>
    </button>
 <!-- WhatsApp -->
    <a href="https://wa.me/{{ $item->phone }}" target="_blank" class="btn btn-success btn-sm" title="تواصل عبر واتساب">
        <i class="fa-brands fa-whatsapp"></i>
    </a>

    <!-- Email -->
    <a href="mailto:{{ $item->email }}" target="_blank" class="btn btn-primary btn-sm" title="إرسال بريد إلكتروني">
        <i class="fa-solid fa-envelope"></i>
    </a>
                                <a href="{{ route('delete.contact.us',$item->id) }}" class="btn btn-danger" id="delete" title="Delete Data" ><i class="fa fa-trash"></i></a>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                         <th>#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                         <th>التاريخ</th>

                        <th>رقم الهاتف</th>
                        {{-- <th>الرسالة</th> --}}
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

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">نص الرسالة</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
      </div>
      <div class="modal-body">
        <p id="modalMessage"></p>
      </div>
    </div>
  </div>
</div>

<script>
    function showMessageModal(message) {
        document.getElementById('modalMessage').innerText = message;
        var myModal = new bootstrap.Modal(document.getElementById('messageModal'));
        myModal.show();
    }
</script>


@endsection
