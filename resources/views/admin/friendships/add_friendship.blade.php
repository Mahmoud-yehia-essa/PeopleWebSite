@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!--breadcrumb-->
<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
    <div class="breadcrumb-title pe-3">إضافة علاقة صداقة جديدة</div>
</div>
<!--end breadcrumb-->

<div class="container">
    <div class="main-body">
        <div class="row">
            <div class="col-lg-8">
                <form action="{{ route('store.friendship') }}" method="POST">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            
                            <!-- Sender (مرسل الطلب) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">مرسل الطلب (Sender)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="sender_id" id="sender_id" class="form-select" required>
                                        <option value="" selected disabled>اختر المستخدم المرسل...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('sender_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email ?? $user->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('sender_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Receiver (مستقبل الطلب) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">مستقبل الطلب (Receiver)</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="receiver_id" id="receiver_id" class="form-select" required>
                                        <option value="" selected disabled>اختر المستخدم المستقبل...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('receiver_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email ?? $user->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('receiver_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Status (حالة العلاقة) -->
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">حالة العلاقة</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <select name="is_active" class="form-select" required>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>طلب معلق (قيد الانتظار)</option>
                                        <option value="1" {{ old('is_active') == '1' ? 'selected' : '' }}>أصدقاء (نشط ومقبول)</option>
                                    </select>
                                    @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Submit / Cancel Buttons -->
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9 text-secondary">
                                    <input type="submit" class="btn btn-primary px-4" value="حفظ العلاقة" />
                                    <a href="{{ route('all.friendships') }}" class="btn btn-secondary px-4 ms-2">إلغاء</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // منع اختيار نفس المستخدم كمرسل ومستقبل
        $('#sender_id').change(function() {
            var senderId = $(this).val();
            $('#receiver_id option').prop('disabled', false); // تفعيل جميع الخيارات أولاً
            if (senderId) {
                $('#receiver_id option[value="' + senderId + '"]').prop('disabled', true); // إلغاء تفعيل الخيار المختار كمرسل
            }
        });

        $('#receiver_id').change(function() {
            var receiverId = $(this).val();
            $('#sender_id option').prop('disabled', false); // تفعيل جميع الخيارات أولاً
            if (receiverId) {
                $('#sender_id option[value="' + receiverId + '"]').prop('disabled', true); // إلغاء تفعيل الخيار المختار كمستقبل
            }
        });

        // تشغيل المنطق في البداية عند وجود قيم قديمة
        var initialSender = $('#sender_id').val();
        if (initialSender) {
            $('#receiver_id option[value="' + initialSender + '"]').prop('disabled', true);
        }
        var initialReceiver = $('#receiver_id').val();
        if (initialReceiver) {
            $('#sender_id option[value="' + initialReceiver + '"]').prop('disabled', true);
        }
    });
</script>
@endsection
