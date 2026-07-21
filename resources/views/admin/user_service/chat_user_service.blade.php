@extends('admin.master_admin')
@section('admin')

@php
    // Convert URLs to clickable links
    function makeLinks($text) {
        return preg_replace(
            '/(https?:\/\/[^\s]+)/',
            '<a href="$1" target="_blank" style="color:blue;">$1</a>',
            e($text)
        );
    }
@endphp

{{-- CSRF token for AJAX --}}
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="col-lg-9">

    {{-- Chat Card --}}
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0" style="color: white">المحادثة مع {{ $service->user->fname}} بخصوص  {{$service->service->title}}</h5>
        </div>


        <div id="chatBox" class="card-body" style="height: 400px; overflow-y: auto; background: #f9f9f9;">
            {{-- Messages will be loaded via AJAX --}}
        </div>

        <div id="newMessageAlert" class="text-center mt-1" style="display:none; cursor:pointer; color:blue;">
            رسائل جديدة. اضغط للانتقال للأسفل
        </div>

        <div class="card-footer bg-white border-top">
            <form id="chatForm" class="d-flex">
                @csrf
                <input type="hidden" name="service_id" id="service_id" value="{{$service->id}}" />
                <input type="text" name="comment" id="comment" class="form-control me-2" placeholder="اكتب رسالتك هنا...">
                <button type="submit" class="btn btn-primary">إرسال</button>
            </form>
            <div id="error-msg" class="text-danger mt-1"></div>
        </div>
    </div>

    {{-- Service Info --}}
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
                            <span class="badge bg-warning text-dark">تأكيد العمل وجاري العمل عليه</span>
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

{{-- jQuery --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    var chatBox = $('#chatBox');
    var lastMessageId = 0;
    var atBottom = true;

    // Convert URLs to clickable links
    function makeLinks(text) {
        if(!text) return '';
        var urlPattern = /(https?:\/\/[^\s]+)/g;
        return text.replace(urlPattern, function(url) {
            return `<a href="${url}" target="_blank" style="color:blue;">${url}</a>`;
        });
    }

    // Render a single message
    function renderMessage(msg) {
        if(!msg || !msg.user) return '';
        var userInitial = msg.user.fname.charAt(0);
        var userName = msg.user.fname;
        var time = new Date(msg.created_at).toLocaleTimeString();
        var comment = makeLinks(msg.comment ?? '');
        var isCurrentUser = msg.user_id === {{ Auth::id() }};
        var html = '';

        if(isCurrentUser){
            html += `<div class="d-flex mb-4">
                        <div class="me-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                 style="width:45px; height:45px; font-size:18px;">
                                ${userInitial}
                            </div>
                        <small class="text-muted">الادارة</small>

                        </div>
                        <div class="flex-grow-1">
                            <div class="p-3 rounded shadow-sm bg-white">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong>${userName}</strong>
                                    <small class="text-muted">${time}</small>
                                </div>
                                <p class="mb-0">${comment}</p>
                            </div>
                        </div>
                    </div>`;
        } else {
            html += `<div class="d-flex mb-4">
                        <div class="me-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                 style="width:45px; height:45px; font-size:18px;">
                                ${userInitial}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="p-3 rounded shadow-sm bg-white">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong>${userName}</strong>
                                    <small class="text-muted">${time}</small>
                                </div>
                                <p class="mb-0">${comment}</p>
                            </div>
                        </div>
                    </div>`;
        }

        return html;
    }

    // Track user scroll
    chatBox.on('scroll', function() {
        atBottom = chatBox.scrollTop() + chatBox.innerHeight() >= chatBox[0].scrollHeight - 50;
        if(atBottom){
            $('#newMessageAlert').hide();
        }
    });

    // Scroll chat
    function scrollChatToBottom(force = false) {
        if(atBottom || force){
            chatBox.scrollTop(chatBox[0].scrollHeight);
            $('#newMessageAlert').hide();
        }
    }

    // Append message
    function appendMessage(msg){
        chatBox.append(renderMessage(msg));
        lastMessageId = msg.id;
        scrollChatToBottom(true);
    }

    // Initial fetch
    $.get("/admin/messages/fetch/" + $('#service_id').val(), function(data){
        data.forEach(function(msg){
            appendMessage(msg);
        });
    });

    // Fetch new messages every 2 seconds
    setInterval(function(){
        var service_id = $('#service_id').val();
        $.get("/admin/messages/fetch/" + service_id, function(data) {
            data.forEach(function(msg){
                if(msg.id > lastMessageId){
                    appendMessage(msg);
                }
            });
        });
    }, 2000);

    // Send message via AJAX
    $('#chatForm').submit(function(e) {
        e.preventDefault();
        var comment = $('#comment').val();
        var service_id = $('#service_id').val();
        if(comment.trim() === '') return;

        $.ajax({
            url: "{{ route('add.admin.messages.ajax') }}",
            method: "POST",
            data: {comment: comment, service_id: service_id},
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function(data){
                appendMessage(data);
                $('#comment').val('');
                $('#error-msg').text('');
            },
            error: function(xhr){
                if(xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.comment){
                    $('#error-msg').text(xhr.responseJSON.errors.comment[0]);
                }
            }
        });
    });

    // Click on new message alert scrolls
    $('#newMessageAlert').click(function(){
        scrollChatToBottom(true);
    });

});
</script>

@endsection
