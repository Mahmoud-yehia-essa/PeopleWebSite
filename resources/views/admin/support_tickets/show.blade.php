@extends('admin.master_admin')
@section('admin')

<!-- Custom Premium Chat CSS -->
<style>
    .chat-container {
        display: flex;
        flex-direction: column;
        height: 550px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
    }
    .chat-header {
        padding: 16px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #f4f6f9;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .chat-message {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        max-width: 75%;
    }
    /* رسائل العضو (المرسلة من اليمين) */
    .chat-message.user-message {
        align-self: flex-start;
        flex-direction: row;
    }
    /* رسائل الإدارة (المرسلة من اليسار) */
    .chat-message.admin-message {
        align-self: flex-end;
        flex-direction: row-reverse;
    }
    .message-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .message-bubble {
        padding: 12px 16px;
        border-radius: 18px;
        position: relative;
        font-size: 14.5px;
        line-height: 1.5;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .user-message .message-bubble {
        background-color: #ffffff;
        color: #212529;
        border-bottom-right-radius: 4px;
        border: 1px solid #e9ecef;
    }
    .admin-message .message-bubble {
        background: linear-gradient(135deg, #008cff 0%, #0056b3 100%);
        color: #ffffff;
        border-bottom-left-radius: 4px;
    }
    .message-time {
        display: block;
        font-size: 11px;
        margin-top: 5px;
        text-align: left;
    }
    .user-message .message-time {
        color: #8c98a5;
    }
    .admin-message .message-time {
        color: #e3f2fd;
        text-align: right;
    }
    .chat-footer {
        padding: 15px 20px;
        background: #ffffff;
        border-top: 1px solid #e9ecef;
    }
    .attachment-preview {
        max-width: 150px;
        border-radius: 8px;
        margin-top: 8px;
        cursor: pointer;
        transition: transform 0.2s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }
    .attachment-preview:hover {
        transform: scale(1.03);
    }
    .attachment-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: rgba(0,0,0,0.04);
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
        color: inherit;
        font-size: 13px;
        margin-top: 8px;
        border: 1px solid rgba(0,0,0,0.08);
        transition: background-color 0.2s;
    }
    .attachment-link:hover {
        background-color: rgba(0,0,0,0.08);
    }
    .admin-message .attachment-link {
        background-color: rgba(255,255,255,0.15);
        border-color: rgba(255,255,255,0.2);
        color: #ffffff;
    }
    .admin-message .attachment-link:hover {
        background-color: rgba(255,255,255,0.25);
    }
</style>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
    <div class="breadcrumb-title pe-3" style="border-left: 3px solid #008cff; padding-left: 10px; font-weight: bold;">محادثة الدعم الفني</div>
    <div class="ps-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt"></i> الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.support_tickets.index') }}">تذاكر الدعم الفني</a></li>
                <li class="breadcrumb-item active" aria-current="page">تفاصيل التذكرة #{{ $ticket->id }}</li>
            </ol>
        </nav>
    </div>
    <div class="ms-auto">
        <a href="{{ route('admin.support_tickets.index') }}" class="btn btn-secondary px-4"><i class="bx bx-arrow-back"></i> العودة للقائمة</a>
    </div>
</div>
<!--end breadcrumb-->

<div class="row">
    <!-- Left Column: Chat Conversation -->
    <div class="col-lg-8 col-xl-9">
        <div class="chat-container">
            <!-- Chat Header -->
            <div class="chat-header">
                <div>
                    <h5 class="mb-0 text-dark font-weight-bold" style="font-size: 16px;">{{ $ticket->subject }}</h5>
                    <span class="text-muted" style="font-size: 13px;">تاريخ الفتح: {{ $ticket->created_at ? $ticket->created_at->format('Y-m-d h:i A') : 'غير محدد' }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($ticket->status == 'open')
                        <span class="badge bg-success px-3 py-2" style="border-radius: 20px;"><i class="fa-solid fa-envelope-open me-1"></i> مفتوحة</span>
                    @elseif($ticket->status == 'pending')
                        <span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 20px;"><i class="fa-solid fa-reply me-1"></i> جاري الرد</span>
                    @else
                        <span class="badge bg-dark px-3 py-2" style="border-radius: 20px;"><i class="fa-solid fa-folder-closed me-1"></i> مغلقة</span>
                    @endif
                </div>
            </div>

            <!-- Chat Messages -->
            <div class="chat-messages" id="chatMessagesContainer">
                <!-- First Message from ticket itself (or if created by user message) -->
                @if($ticket->messages->count() == 0)
                    <div class="text-center my-4 text-muted">
                        <i class="bx bx-message-detail" style="font-size: 40px;"></i>
                        <p class="mt-2">لا توجد رسائل في هذه التذكرة بعد.</p>
                    </div>
                @endif

                @foreach($ticket->messages as $message)
                    @php
                        $isUser = $message->sender_type === 'user';
                        $senderName = $message->sender ? ($message->sender->fname . ' ' . $message->sender->lname) : 'مستخدم غير معروف';
                        $avatarUrl = (!empty($message->sender->photo) && $message->sender->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$message->sender->photo : url('upload/no_image.jpg');
                    @endphp
                    <div class="chat-message {{ $isUser ? 'user-message' : 'admin-message' }}">
                        <img src="{{ $avatarUrl }}" class="message-avatar" alt="{{ $senderName }}" title="{{ $senderName }}">
                        <div class="message-bubble shadow-sm">
                            <span class="d-block font-weight-bold mb-1" style="font-size: 12px; opacity: 0.85;">
                                {{ $isUser ? $senderName : 'الإدارة (' . $senderName . ')' }}
                            </span>
                            <div>
                                {!! nl2br(e($message->message)) !!}
                            </div>
                            
                            <!-- Display Attachment if exists -->
                            @if($message->attachment_path)
                                @php
                                    $extension = strtolower(pathinfo($message->attachment_path, PATHINFO_EXTENSION));
                                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
                                    $fullAttachmentUrl = 'http://localhost:8888/new_wiselook/uploads/' . basename($message->attachment_path);
                                @endphp
                                @if($isImage)
                                    <div>
                                        <img src="{{ $fullAttachmentUrl }}" class="attachment-preview" onclick="showImageModal(this.src)">
                                    </div>
                                @else
                                    <div>
                                        <a href="{{ $fullAttachmentUrl }}" download class="attachment-link">
                                            <i class="fa-solid fa-download"></i>
                                            <span>تحميل المرفق (.{{ $extension }})</span>
                                        </a>
                                    </div>
                                @endif
                            @endif

                            <span class="message-time">
                                {{ $message->created_at ? $message->created_at->diffForHumans() : '' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Chat Footer (Reply Form) -->
            <div class="chat-footer">
                <form action="{{ route('admin.support_tickets.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="statusSelect" class="form-label font-weight-bold text-dark" style="font-size: 13px;">تغيير حالة التذكرة</label>
                            <select name="status" id="statusSelect" class="form-select form-select-sm">
                                <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>مفتوحة</option>
                                <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>جاري الرد / معلقة</option>
                                <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>مغلقة</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prioritySelect" class="form-label font-weight-bold text-dark" style="font-size: 13px;">تغيير درجة الأهمية</label>
                            <select name="priority" id="prioritySelect" class="form-select form-select-sm">
                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>منخفضة</option>
                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>متوسطة</option>
                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>عالية</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <input type="file" name="attachment" id="attachmentInput" class="d-none" onchange="displayFilename()">
                        <button type="button" class="btn btn-outline-secondary px-3" onclick="document.getElementById('attachmentInput').click()" title="إرفاق ملف أو صورة">
                            <i class="fa-solid fa-paperclip fs-5"></i>
                        </button>
                        <textarea name="message" class="form-control" placeholder="اكتب ردك هنا بالتفصيل..." rows="2" required style="resize: none; border-radius: 0;"></textarea>
                        <button type="submit" class="btn btn-primary px-4">
                            إرسال الرد <i class="fa-regular fa-paper-plane ms-1"></i>
                        </button>
                    </div>
                    <div id="file-name-display" class="text-success mt-1" style="font-size: 12px; display: none;"></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Ticket Info & User Info -->
    <div class="col-lg-4 col-xl-3 mt-4 mt-lg-0">
        <!-- Ticket Management Card -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-dark font-weight-bold">إدارة التذكرة السريعة</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.support_tickets.status', $ticket->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label text-secondary mb-1">الحالة الحالية</label>
                    <div class="input-group input-group-sm">
                        <select name="status" class="form-select">
                            <option value="open" {{ $ticket->status == 'open' ? 'selected' : '' }}>مفتوحة</option>
                            <option value="pending" {{ $ticket->status == 'pending' ? 'selected' : '' }}>جاري الرد</option>
                            <option value="closed" {{ $ticket->status == 'closed' ? 'selected' : '' }}>مغلقة</option>
                        </select>
                        <button type="submit" class="btn btn-success">تحديث</button>
                    </div>
                </form>

                <form action="{{ route('admin.support_tickets.priority', $ticket->id) }}" class="mb-2">
                    @csrf
                    @method('POST')
                    {{-- Note: we can use route updatePriority --}}
                </form>
                <form action="{{ route('admin.support_tickets.priority', $ticket->id) }}" method="POST" class="mb-3">
                    @csrf
                    <label class="form-label text-secondary mb-1">درجة الأهمية</label>
                    <div class="input-group input-group-sm">
                        <select name="priority" class="form-select">
                            <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>منخفضة</option>
                            <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>متوسطة</option>
                            <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>عالية</option>
                        </select>
                        <button type="submit" class="btn btn-warning text-dark">تحديث</button>
                    </div>
                </form>

                <hr>
                
                <a href="{{ route('admin.support_tickets.delete', $ticket->id) }}" class="btn btn-danger btn-sm w-100 py-2" id="delete" title="حذف التذكرة بشكل كامل">
                    <i class="fa fa-trash me-1"></i> حذف التذكرة بالكامل
                </a>
            </div>
        </div>

        <!-- User Information Card -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-dark font-weight-bold">معلومات صاحب التذكرة</h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ (!empty($ticket->user->photo) && $ticket->user->photo != 'non') ? 'http://localhost:8888/new_wiselook/uploads/'.$ticket->user->photo : url('upload/no_image.jpg') }}" class="rounded-circle shadow-sm mb-3" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid #e9ecef;">
                <h5 class="mb-1 text-dark font-weight-bold" style="font-size: 16px;">{{ $ticket->user->fname }} {{ $ticket->user->lname }}</h5>
                <span class="badge bg-light-primary text-primary px-3 mb-3" style="font-size: 11px;">عضو مسجل</span>
                
                <div class="text-end">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-secondary" style="font-size: 13px;">البريد الإلكتروني:</span>
                        <span class="text-dark font-weight-bold" style="font-size: 13px;">{{ $ticket->user->email }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-secondary" style="font-size: 13px;">الهاتف:</span>
                        <span class="text-dark font-weight-bold" style="font-size: 13px;">{{ $ticket->user->phone ?: 'غير محدد' }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <span class="text-secondary" style="font-size: 13px;">تاريخ التسجيل:</span>
                        <span class="text-dark" style="font-size: 12px;">{{ $ticket->user->created_at ? $ticket->user->created_at->format('Y-m-d') : 'غير محدد' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content position-relative bg-transparent border-0">
        <!-- Close Button -->
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
        <img id="modalImage" src="" class="img-fluid rounded shadow-lg mx-auto d-block" alt="image preview">
      </div>
    </div>
</div>

<script>
    // تمرير تلقائي لنهاية الشات عند التحميل
    document.addEventListener("DOMContentLoaded", function() {
        var container = document.getElementById("chatMessagesContainer");
        if(container) {
            container.scrollTop = container.scrollHeight;
        }
    });

    function showImageModal(src) {
        document.getElementById('modalImage').src = src;
        var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
        myModal.show();
    }

    function displayFilename() {
        var input = document.getElementById('attachmentInput');
        var display = document.getElementById('file-name-display');
        if (input.files.length > 0) {
            display.innerHTML = '<i class="fa-solid fa-paperclip me-1"></i> تم اختيار: ' + input.files[0].name;
            display.style.display = 'block';
        } else {
            display.style.display = 'none';
        }
    }
</script>

@endsection
