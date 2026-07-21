@extends('admin.master_admin')
@section('admin')

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #0d6efd; padding-left: 10px; font-weight: bold;">إصدارات التطبيق</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="javascript:;">الإعدادات</a></li>
                <li class="breadcrumb-item active" aria-current="page">عرض الإصدارات</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('admin.app_versions.create') }}" class="btn btn-primary px-4">
            <i class="bx bx-plus me-1"></i>إضافة إصدار جديد
        </a>
    </div>
</div>
<!--end breadcrumb-->

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="mb-3 font-weight-bold text-dark" style="font-size: 16px;">سجل تحديثات وإصدارات التطبيق</h5>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle" style="width:100%">
                <thead>
                    <tr class="table-light">
                        <th class="text-center" style="width: 5%">#</th>
                        <th class="text-center" style="width: 12%">رقم الإصدار</th>
                        <th style="width: 15%">نوع التحديث</th>
                        <th class="text-center" style="width: 15%">روابط المتاجر</th>
                        <th>وصف التحديث / الجديد في الإصدار</th>
                        <th style="width: 15%">الدعم / التواصل</th>
                        <th class="text-center" style="width: 13%">الاجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $key => $item)
                    <tr>
                        <td class="text-center">{{ $versions->firstItem() + $key }}</td>
                        <td class="text-center font-weight-bold text-primary" style="font-size: 15px;">v{{ $item->version }}</td>
                        <td>
                            @if($item->update_required)
                                <span class="badge bg-light-danger text-danger border border-danger px-3 py-1 font-weight-bold" style="font-size: 12px;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> تحديث إجباري
                                </span>
                            @else
                                <span class="badge bg-light-success text-success border border-success px-3 py-1 font-weight-bold" style="font-size: 12px;">
                                    <i class="fa-solid fa-circle-check me-1"></i> تحديث اختياري
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($item->android)
                                <a href="{{ $item->android }}" target="_blank" class="btn btn-sm btn-outline-success me-1" title="متجر Google Play">
                                    <i class="fa-brands fa-android"></i> أندرويد
                                </a>
                            @endif
                            @if($item->ios)
                                <a href="{{ $item->ios }}" target="_blank" class="btn btn-sm btn-outline-dark" title="متجر App Store">
                                    <i class="fa-brands fa-apple"></i> آيفون
                                </a>
                            @endif
                            @if(!$item->android && !$item->ios)
                                <span class="text-muted" style="font-size: 12px;">لا توجد روابط</span>
                            @endif
                        </td>
                        <td>
                            @if($item->des)
                                <div style="max-height: 80px; overflow-y: auto; font-size: 13px; line-height: 1.5;">
                                    {!! nl2br(e($item->des)) !!}
                                </div>
                            @else
                                <span class="text-muted font-italic" style="font-size: 12px;">لا يوجد وصف للنسخة</span>
                            @endif
                        </td>
                        <td>
                            @if($item->contact)
                                <span style="font-size: 12.5px;">{{ $item->contact }}</span>
                            @else
                                <span class="text-muted font-italic" style="font-size: 12px;">لا توجد بيانات</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.app_versions.edit', $item->id) }}" class="btn btn-sm btn-info text-white" title="تعديل">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <a href="{{ route('admin.app_versions.delete', $item->id) }}" class="btn btn-sm btn-danger" id="delete" title="حذف">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bx bx-cog fs-2 mb-2 d-block"></i>
                            لا توجد أي سجلات لإصدارات التطبيق حالياً.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $versions->links() }}
        </div>
    </div>
</div>

@endsection
