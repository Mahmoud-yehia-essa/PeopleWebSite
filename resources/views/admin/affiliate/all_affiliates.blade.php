@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">التسويق بالعمولة (Affiliate)</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">روابط التسويق بالعمولة</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.affiliate') }}">
                <button type="button" class="btn btn-primary"><i class="bx bx-plus"></i> إنشاء رابط جديد</button>
            </a>
        </div>
    </div>
</div>
<!--end breadcrumb-->

<hr/>
<div class="card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="marketerFilter" class="form-label fw-bold"><i class="bx bx-filter-alt"></i> تصفية حسب المسوق:</label>
                <select id="marketerFilter" class="form-select border-2 border-primary-subtle">
                    <option value="">-- كل المسوقين --</option>
                    @foreach($links->unique('user_id') as $item)
                        @if($item->user)
                            <option value="{{ $item->user->first_name }} {{ $item->user->last_name }}">
                                {{ $item->user->first_name }} {{ $item->user->last_name }} ({{ $item->user->email }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>المسوق</th>
                        <th>كود الإحالة</th>
                        <th>رابط الإحالة</th>
                        <th>النقرات</th>
                        <th>المسجلين</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($links as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ (!empty($item->user->profile_picture)) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->user->profile_picture : url('upload/no_image.jpg') }}" class="rounded-circle p-1 border shadow-sm me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0">{{ $item->user ? ($item->user->first_name . ' ' . $item->user->last_name) : 'مستخدم غير معروف' }}</h6>
                                    <small class="text-muted">{{ $item->user ? $item->user->email : '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <code class="text-dark bg-light px-2 py-1 border rounded">{{ $item->code }}</code>
                        </td>
                        <td>
                            @php
                                $shortLink = url('/ref/' . $item->code);
                                $directLink = route('register', ['ref' => $item->code]);
                            @endphp
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex align-items-center justify-content-between bg-light p-1 border rounded">
                                    <span class="text-truncate text-muted me-2" style="max-width: 150px; font-size: 11px;">{{ $shortLink }}</span>
                                    <button class="btn btn-xs btn-outline-secondary py-0 px-1" onclick="copyToClipboard('{{ $shortLink }}')" title="نسخ الرابط القصير">
                                        <i class="bx bx-copy"></i>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center justify-content-between bg-light p-1 border rounded">
                                    <span class="text-truncate text-muted me-2" style="max-width: 150px; font-size: 11px;">{{ $directLink }}</span>
                                    <button class="btn btn-xs btn-outline-secondary py-0 px-1" onclick="copyToClipboard('{{ $directLink }}')" title="نسخ رابط التسجيل المباشر">
                                        <i class="bx bx-copy-1"></i>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $item->clicks }}</span>
                        </td>
                        <td>
                            @if($item->trackings_count > 0)
                                <span class="badge bg-success view-trackings" data-id="{{ $item->id }}" style="cursor: pointer;" title="اضغط لعرض قائمة المسجلين">
                                    <i class="bx bx-user-check"></i> {{ $item->trackings_count }}
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border">0</span>
                            @endif
                        </td>
                        <td>
                            @if($item->is_active)
                                <span class="badge bg-success-subtle text-success border border-success">نشط</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger">معطل</span>
                            @endif
                        </td>
                        <td>{{ $item->created_at ? $item->created_at->format('Y-m-d') : '-' }}</td>
                        <td>
                            <a href="{{ route('edit.affiliate', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل الرابط"> 
                                <i class="fa fa-pencil"></i> 
                            </a>
                            <a href="{{ route('delete.affiliate', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف الرابط والتتبعات">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal لعرض المسجلين الجدد -->
<div class="modal fade" id="trackingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">تفاصيل الأعضاء المسجلين من خلال كود الإحالة: <span id="modalCode" class="badge bg-white text-primary"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>اسم العضو الجديد</th>
                                <th>البريد الإلكتروني</th>
                                <th>عنوان IP</th>
                                <th>تاريخ ووقت التسجيل</th>
                            </tr>
                        </thead>
                        <tbody id="trackingsList">
                            <!-- يتم تحميلها عبر Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'تم نسخ الرابط بنجاح',
                text: text,
                showConfirmButton: false,
                timer: 1500
            });
        }, function(err) {
            Swal.fire({
                icon: 'error',
                title: 'فشل النسخ',
                text: 'يرجى نسخ الرابط يدوياً.',
                confirmButtonText: 'حسناً'
            });
        });
    }

    $(document).ready(function() {
        $('.view-trackings').click(function() {
            var id = $(this).data('id');
            var url = '{{ route("affiliate.trackings", ":id") }}';
            url = url.replace(':id', id);

            $('#trackingsList').html('<tr><td colspan="5" class="text-center"><i class="bx bx-loader-alt bx-spin fs-4"></i> جاري التحميل...</td></tr>');
            $('#modalCode').text('...');
            
            var myModal = new bootstrap.Modal(document.getElementById('trackingsModal'));
            myModal.show();

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        $('#modalCode').text(response.code);
                        var html = '';
                        if(response.trackings.length > 0) {
                            response.trackings.forEach(function(item, index) {
                                html += '<tr>' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + item.name + '</td>' +
                                    '<td>' + item.email + '</td>' +
                                    '<td><span class="badge bg-light text-dark border">' + item.ip + '</span></td>' +
                                    '<td>' + item.date + ' <small class="text-muted">(' + item.date_human + ')</small></td>' +
                                    '</tr>';
                            });
                        } else {
                            html = '<tr><td colspan="5" class="text-center text-muted">لا يوجد أعضاء مسجلين من خلال هذا الرابط حتى الآن.</td></tr>';
                        }
                        $('#trackingsList').html(html);
                    } else {
                        $('#trackingsList').html('<tr><td colspan="5" class="text-center text-danger">حدث خطأ أثناء تحميل البيانات.</td></tr>');
                    }
                },
                error: function() {
                    $('#trackingsList').html('<tr><td colspan="5" class="text-center text-danger">حدث خطأ في الاتصال بالخادم.</td></tr>');
                }
            });
        });

        // تصفية الجدول حسب المسوق المختار
        $('#marketerFilter').on('change', function() {
            var val = $(this).val();
            var table = $('#example').DataTable();
            // عمود المسوق هو العمود الثاني (فهرس 1)
            table.column(1).search(val).draw();
        });
    });
</script>
@endsection
