@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">مكتبة حماك الصوتية</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item active" aria-current="page">كل الملفات الصوتية</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <div class="btn-group">
            <a href="{{ route('add.sound.library') }}">
                <button type="button" class="btn btn-primary">إضافة ملف صوتي جديد</button>
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
                        <th>العنوان / المقطع</th>
                        <th>الفئة</th>
                        <th>المؤلف/المقدم</th>
                        <th>النوع</th>
                        <th>المصدر / الاستماع</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sounds as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td class="font-weight-bold">{{ $item->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->category->name ?? 'غير محدد' }}</span>
                            </td>
                            <td>
                                @if($item->author)
                                    <span class="text-primary font-weight-bold">{{ $item->author->name }}</span>
                                @else
                                    <span class="text-muted">غير محدد</span>
                                @endif
                            </td>
                            <td>
                                @if($item->sound_type == 'recorded')
                                    <span class="badge bg-success">تسجيل صوتي</span>
                                @elseif($item->sound_type == 'episode')
                                    <span class="badge bg-warning text-dark">حلقة إذاعية مسجلة</span>
                                @elseif($item->sound_type == 'live')
                                    <span class="badge bg-danger">بث مباشر (Live)</span>
                                @elseif($item->sound_type == 'report')
                                    <span class="badge bg-info text-dark">تقرير صوتي</span>
                                @endif
                            </td>
                            <td>
                                @if($item->sound_file_path)
                                    <audio controls style="height: 30px; width: 220px;" class="mt-1">
                                        <source src="{{ asset($item->sound_file_path) }}" type="audio/mpeg">
                                        متصفحك لا يدعم مشغل الصوت.
                                    </audio>
                                @elseif($item->sound_url)
                                    <div class="d-flex flex-col gap-1">
                                        <a href="{{ $item->sound_url }}" target="_blank" class="btn btn-outline-dark btn-sm text-truncate" style="max-width: 220px;">
                                            <i class="bx bx-link-external"></i> رابط خارجي
                                        </a>
                                        @if($item->sound_type == 'live')
                                            <span class="text-danger small"><i class="bx bx-radio animate-pulse"></i> بث حي نشط</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">لا يوجد ملف أو رابط</span>
                                @endif
                            </td>
                            <td>{{ $item->created_at ? $item->created_at->diffForHumans() : 'غير محدد' }}</td>
                            <td>
                                <a href="{{ route('edit.sound.library', $item->id) }}" class="btn btn-info btn-sm">تعديل</a>
                                <a href="{{ route('delete.sound.library', $item->id) }}" class="btn btn-danger btn-sm" id="delete">حذف</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>العنوان / المقطع</th>
                        <th>الفئة</th>
                        <th>المؤلف/المقدم</th>
                        <th>النوع</th>
                        <th>المصدر / الاستماع</th>
                        <th>تاريخ الإضافة</th>
                        <th>الإجراء</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@endsection
