@extends('admin.master_admin')
@section('admin')

@php
    // وظيفة لتحويل الروابط في النص إلى روابط قابلة للنقر
    function makeLinks($text) {
        return preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank">$1</a>',
            e($text) // نستخدم e() لتجنب XSS
        );
    }
@endphp
<div class="col-lg-9">

    {{-- قسم التعليقات --}}
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0" style="color: white">التعليقات على الخدمة المطلوبة</h5>
        </div>
        <div id="chatBox" class="card-body" style="height: 400px; overflow-y: auto; background: #f9f9f9;">
            @foreach($serviceComments as $item)
                @if ($item->user_id === Auth::user()->id)
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                 style="width:45px; height:45px; font-size:18px;">
                                {{ mb_substr(Auth::user()->fname,0,1) }}
                            </div>
                                                        <small class="text-muted">الادارة</small>

                        </div>
                        <div class="flex-grow-1">
                            <div class="p-3 rounded shadow-sm bg-white">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong>{{ Auth::user()->fname }}</strong>
                                    <small class="text-muted">{{$item->created_at->diffForHumans()}}</small>
                                </div>
                                <p class="mb-0">{!! makeLinks($item->comment) !!}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="d-flex mb-4">
                        <div class="me-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:45px; height:45px; font-size:18px;">
                                {{ mb_substr($item->user->fname,0,1) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="p-3 rounded shadow-sm bg-white">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong>{{$item->user->fname}}</strong>
                                    <small class="text-muted">{{$item->created_at->diffForHumans()}}</small>
                                </div>
                                <p class="mb-0">{!! makeLinks($item->comment) !!}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="card-footer bg-white border-top">
            <form method="POST" action="{{ route('add.chat.admin.store') }}" class="d-flex">
                @csrf
                <input type="hidden" name="service_id" value="{{$service->id}}" />
                <input type="text" name="comment" class="form-control me-2" placeholder="اكتب رسالتك هنا...">
                <button type="submit" class="btn btn-primary">إرسال</button>
            </form>
            @error('comment') <div class="text-danger">{{ $message }}</div> @enderror
        </div>
    </div>


    <div class="card shadow mt-5">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0" style="color: white">معلومات الطلب</h5>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>اسم الخدمة:</strong>
                        <span>{{ $service->service->title ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>حالة الطلب:</strong>

                                                                                     @if($service->status == 'completed')
        <span class="badge bg-success text-white">مكتمل</span>
    @elseif($service->status == 'approved')
        <span class="badge bg-info text-white">الموافقة على العمل وتحديد السعر</span>
    @elseif($service->status == 'working')
        <span class="badge bg-warning text-white">تأكيد العمل وجاري العمل عليه</span>
    @elseif($service->status == 'pending')
        <span class="badge bg-secondary text-white">قيد الانتظار</span>
    @else
        <span class="badge bg-danger text-white">مرفوض</span>
    @endif
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>تاريخ الطلب:</strong>
                        <span>{{ $service->created_at->format('d/m/Y H:i') ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded bg-light">
                        <strong>آخر تعديل على الطلب:</strong>
                        <span>{{ $service->updated_at->diffForHumans() ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        var chatBox = document.getElementById("chatBox");
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>
@endsection
