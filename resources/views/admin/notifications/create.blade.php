@extends('admin.master_admin')
@section('admin')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<div class="page-content">
    <!-- Breadcrumb -->
    <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
        <div class="breadcrumb-title pe-3 text-success font-weight-bold" style="border-left: 3px solid #d4af37; padding-left: 15px;">الإشعارات</div>
        <div class="ps-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 p-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home-alt text-warning"></i></a></li>
                    <li class="breadcrumb-item active" aria-current="page">إرسال إشعار جديد</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- End Breadcrumb -->

    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <!-- Session Alert Messages -->
                @if(session('message'))
                    <div class="alert alert-{{ session('alert-type') == 'success' ? 'success' : 'danger' }} border-0 bg-{{ session('alert-type') == 'success' ? 'success' : 'danger' }} alert-dismissible fade show text-white mb-4">
                        <div class="d-flex align-items-center">
                            <div class="font-35 text-white"><i class="bx bx-check-circle"></i></div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white font-weight-bold">تنبيه</h6>
                                <div>{{ session('message') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="button" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Form Card -->
                <form action="{{ route('admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card shadow-sm border-0" style="border-radius: 12px; border-top: 5px solid #0f5132 !important;">
                        <div class="card-body p-5">
                            <div class="card-title d-flex align-items-center mb-4">
                                <div>
                                    <i class="bx bx-send me-1 font-22 text-success"></i>
                                </div>
                                <h5 class="mb-0 text-dark font-weight-bold">إنشاء وإرسال إشعار للمنصة</h5>
                            </div>
                            <hr>

                            <!-- Notification Title -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-secondary">عنوان الإشعار <span class="text-danger">*</span></label>
                                <input name="title" type="text" class="form-control border-light-success @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="أدخل عنواناً جذاباً ومختصراً للإشعار..." required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notification Body -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-secondary">موضوع ونص الإشعار <span class="text-danger">*</span></label>
                                <textarea name="body" class="form-control border-light-success @error('body') is-invalid @enderror" rows="6" placeholder="اكتب تفاصيل ومحتوى الإشعار هنا بشكل واضح وجذاب..." required>{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- File Attachment -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-secondary">إرفاق ملف أو مستند (اختياري)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white"><i class="bx bx-paperclip"></i></span>
                                    <input name="attachment" type="file" class="form-control border-light-success @error('attachment') is-invalid @enderror">
                                </div>
                                <small class="text-muted d-block mt-1">الملفات المدعومة: صور، مستندات PDF، ملفات مضغوطة. الحد الأقصى للحجم 10 ميجابايت.</small>
                                @error('attachment')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Target Group Selection -->
                            <div class="mb-4">
                                <label class="form-label font-weight-bold text-secondary d-block mb-3">الجمهور المستهدف (المرسل إليهم) <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="targetAll" value="all" {{ old('target_type', 'all') == 'all' ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold text-dark" for="targetAll">
                                            <i class="bx bx-group text-success me-1"></i> إرسال لجميع مستخدمي المنصة
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="target_type" id="targetSpecific" value="specific" {{ old('target_type') == 'specific' ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold text-dark" for="targetSpecific">
                                            <i class="bx bx-check-square text-warning me-1"></i> اختيار مستخدمين محددين
                                        </label>
                                    </div>
                                </div>
                                @error('target_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Collapsible Specific Users Selection Box -->
                            <div id="usersSelectorSection" class="card shadow-none border mb-4" style="display: none; background-color: #fafafa; border-radius: 8px;">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
                                        <h6 class="mb-0 font-weight-bold text-dark">حدد المستخدمين المستهدفين من القائمة أدناه:</h6>
                                        <!-- Select All / Deselect All Toggle -->
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="selectAllUsers">
                                            <label class="form-check-label font-weight-bold text-secondary small" for="selectAllUsers">تحديد الكل</label>
                                        </div>
                                    </div>

                                    <!-- Quick Search Input -->
                                    <div class="input-group mb-3">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                                        <input type="text" id="searchUserField" class="form-control border-start-0" placeholder="ابحث عن مستخدم بالاسم أو البريد الإلكتروني...">
                                    </div>

                                    <!-- Users Checkboxes Container -->
                                    <div class="user-list-scroll-box border p-3 bg-white" style="max-height: 280px; overflow-y: auto; border-radius: 6px;">
                                        <div class="row row-cols-1 row-cols-md-2 g-3" id="usersCheckboxGrid">
                                            @foreach($users as $user)
                                                @php
                                                    $userPhoto = (!empty($user->profile_picture) && $user->profile_picture != 'non') 
                                                        ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : 'http://localhost:8888/new_wiselook/uploads/'.$user->profile_picture) 
                                                        : url('upload/no_image.jpg');
                                                @endphp
                                                <div class="col user-item-card" data-searchable="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                                    <div class="d-flex align-items-center p-2 rounded hover-light-bg" style="border: 1px solid #f0f0f0;">
                                                        <div class="form-check me-2">
                                                            <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="userCheckbox_{{ $user->id }}" {{ is_array(old('user_ids')) && in_array($user->id, old('user_ids')) ? 'checked' : '' }}>
                                                        </div>
                                                        <label class="form-check-label d-flex align-items-center w-100 mb-0" for="userCheckbox_{{ $user->id }}" style="cursor: pointer;">
                                                            <img src="{{ $userPhoto }}" alt="{{ $user->first_name }}" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                                            <div class="text-truncate">
                                                                <h6 class="mb-0 text-dark small font-weight-bold">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                                                <span class="text-muted d-block" style="font-size: 0.75rem;">{{ $user->email }}</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div id="noUsersFound" class="text-center py-4" style="display: none;">
                                            <i class="bx bx-user-x fs-2 text-secondary opacity-50 mb-2"></i>
                                            <p class="text-muted mb-0 small">لم يتم العثور على أي مستخدمين يطابقون كلمة البحث.</p>
                                        </div>
                                    </div>
                                    @error('user_ids')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Buttons Section -->
                            <div class="row mt-4">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-success px-5 d-flex align-items-center gap-2" style="background: linear-gradient(135deg, #198754 0%, #0f5132 100%); border: none;">
                                        <i class="bx bx-paper-plane"></i> إرسال الإشعار
                                    </button>
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
        // Toggle visibility of the users selector based on target type selection
        function toggleUsersSelector() {
            if ($('#targetSpecific').is(':checked')) {
                $('#usersSelectorSection').slideDown(300);
            } else {
                $('#usersSelectorSection').slideUp(300);
            }
        }

        // Run on load
        toggleUsersSelector();

        // Listen for changes
        $('input[name="target_type"]').change(function() {
            toggleUsersSelector();
        });

        // Select All / Deselect All functionality
        $('#selectAllUsers').change(function() {
            var isChecked = $(this).is(':checked');
            // Only check/uncheck checkboxes that are currently visible
            $('.user-item-card:visible').find('.user-checkbox').prop('checked', isChecked);
        });

        // Client-side quick filter/search
        $('#searchUserField').on('keyup input', function() {
            var query = $(this).val().toLowerCase().trim();
            var visibleCount = 0;

            $('.user-item-card').each(function() {
                var searchableText = $(this).data('searchable');
                if (searchableText.indexOf(query) !== -1) {
                    $(this).show();
                    visibleCount++;
                } else {
                    $(this).hide();
                }
            });

            if (visibleCount === 0) {
                $('#noUsersFound').show();
            } else {
                $('#noUsersFound').hide();
            }
        });
    });
</script>

<style>
    .border-light-success:focus {
        border-color: #198754;
        box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }
    .hover-light-bg {
        transition: background-color 0.2s ease;
    }
    .hover-light-bg:hover {
        background-color: #f8f9fa;
    }
    .user-list-scroll-box::-webkit-scrollbar {
        width: 6px;
    }
    .user-list-scroll-box::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .user-list-scroll-box::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 4px;
    }
    .user-list-scroll-box::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endsection
