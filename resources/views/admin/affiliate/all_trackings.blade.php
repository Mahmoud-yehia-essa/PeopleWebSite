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
                <li class="breadcrumb-item active" aria-current="page">سجل الإحالات والتسجيلات</li>
            </ol>
        </nav>
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
                    @php
                        $uniqueMarketers = $trackings->filter(function($item) {
                            return $item->link && $item->link->user;
                        })->map(function($item) {
                            return $item->link->user;
                        })->unique('id');
                    @endphp
                    @foreach($uniqueMarketers as $user)
                        <option value="{{ $user->first_name }} {{ $user->last_name }}">
                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>الرقم</th>
                        <th>العضو الجديد</th>
                        <th>المسوق (صاحب الإحالة)</th>
                        <th>كود الإحالة</th>
                        <th>عنوان الـ IP</th>
                        <th>تاريخ التسجيل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trackings as $key => $item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ (!empty($item->registeredUser->profile_picture)) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->registeredUser->profile_picture : url('upload/no_image.jpg') }}" class="rounded-circle p-1 border shadow-sm me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0">{{ $item->registeredUser ? ($item->registeredUser->first_name . ' ' . $item->registeredUser->last_name) : 'مستخدم محذوف' }}</h6>
                                    <small class="text-muted">{{ $item->registeredUser ? $item->registeredUser->email : '-' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($item->link && $item->link->user)
                                <div class="d-flex align-items-center">
                                    <img src="{{ (!empty($item->link->user->profile_picture)) ? 'http://localhost:8888/new_wiselook/uploads/'.$item->link->user->profile_picture : url('upload/no_image.jpg') }}" class="rounded-circle p-1 border shadow-sm me-2" style="width: 35px; height: 35px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0" style="font-size: 13px;">{{ $item->link->user->first_name }} {{ $item->link->user->last_name }}</h6>
                                        <small class="text-muted" style="font-size: 11px;">{{ $item->link->user->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <code class="text-dark bg-light px-2 py-1 border rounded">{{ $item->link ? $item->link->code : 'كود محذوف' }}</code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><i class="bx bx-network-chart me-1"></i>{{ $item->ip_address ?? '-' }}</span>
                        </td>
                        <td>
                            {{ $item->created_at ? $item->created_at->format('Y-m-d H:i:s') : '-' }}
                            <br>
                            <small class="text-muted">({{ $item->created_at ? $item->created_at->diffForHumans() : '-' }})</small>
                        </td>
                        <td>
                            <a href="{{ route('delete.affiliate_tracking', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف هذا السجل">
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

<script type="text/javascript">
    $(document).ready(function() {
        // تصفية الجدول حسب المسوق المختار
        $('#marketerFilter').on('change', function() {
            var val = $(this).val();
            var table = $('#example').DataTable();
            // عمود المسوق (صاحب الإحالة) هو العمود الثالث (فهرس 2)
            table.column(2).search(val).draw();
        });
    });
</script>
@endsection
