@extends('frontend.wiselook.master_dashboard')

@section('main')
@push('styles')
<style>
    #delete-subject-modal {
        transition: visibility 0.3s ease, opacity 0.3s ease;
    }
    #delete-subject-modal.modal-show {
        display: flex !important;
    }
    #delete-subject-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #delete-subject-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    @keyframes bulb-bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.25); }
    }
    .animate-bulb {
        animation: bulb-bounce 0.4s ease-out;
    }

    /* Tab Styles */
    .hashtag-tab-btn {
        background: transparent;
        border: none;
        outline: none;
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0 4px 10px 4px;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        color: #6b7280;
        transition: color 0.2s, border-color 0.2s;
    }
    .hashtag-tab-btn:hover {
        color: #1b4332;
    }
    .hashtag-tab-btn.is-active {
        color: #0a7a6b;
        border-bottom-color: #0a7a6b;
    }

    /* Tab Content */
    .hashtag-tab-content {
        display: none;
    }
    .hashtag-tab-content.is-active {
        display: block;
    }
</style>
@endpush

<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24">

    <!-- Top Hashtag Header -->
    <div class="mb-8 bg-white/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm text-right">
        <div class="flex items-center gap-3 justify-start" dir="rtl">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[32px]">tag</span>
            </div>
            <div>
                <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">هاشتاج: #{{ $name }}</h1>
                <p class="font-body-md text-xs text-on-surface-variant mt-1">المشاركات والمواضيع المرتبطة بهذا الهاشتاج.</p>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- Right Column: Feed -->
        <section class="lg:col-span-9 order-2 lg:order-1 space-y-6 text-right">

            <!-- Tabs Nav -->
            <div class="flex gap-6 border-b border-gray-100 mb-6 overflow-x-auto justify-start" style="direction:rtl;">
                <button type="button"
                        class="hashtag-tab-btn is-active"
                        data-target="panel-posts">
                    المنشورات العامة ({{ $posts->count() }})
                </button>
                <button type="button"
                        class="hashtag-tab-btn"
                        data-target="panel-subjects">
                    مواضيع في المجموعات ({{ $subjects->count() }})
                </button>
            </div>

            <!-- Panel: Posts (shown by default) -->
            <div id="panel-posts" class="hashtag-tab-content is-active space-y-6">
                @if($posts->isEmpty())
                    <div class="bg-white rounded-2xl p-12 border border-[#E1E8E1] text-center shadow-sm">
                        <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">feed</span>
                        <h3 class="font-headline-lg text-base font-bold text-primary">لا توجد منشورات عامة</h3>
                        <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">لم يتم العثور على منشورات عامة تحمل هذا الهاشتاج.</p>
                    </div>
                @else
                    @include('frontend.wiselook.pages.posts_feed', ['posts' => $posts])
                @endif
            </div>

            <!-- Panel: Group Subjects (hidden by default) -->
            <div id="panel-subjects" class="hashtag-tab-content space-y-6">
                @if($subjects->isEmpty())
                    <div class="bg-white rounded-2xl p-12 border border-[#E1E8E1] text-center shadow-sm">
                        <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">forum</span>
                        <h3 class="font-headline-lg text-base font-bold text-primary">لا توجد مواضيع في المجموعات</h3>
                        <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">لم يتم العثور على مواضيع نقاشية في المجموعات تحمل هذا الهاشتاج.</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($subjects as $subject)
                            @include('frontend.wiselook.partials.subject_card', ['subject' => $subject])
                        @endforeach
                    </div>
                @endif
            </div>

        </section>

        <!-- Left Column: Sidebar -->
        <aside class="lg:col-span-3 order-1 lg:order-2 space-y-6 text-right">
            <div class="wisdom-card p-6 bg-white rounded-2xl border border-[#E1E8E1] shadow-sm">
                <h3 class="font-title-lg text-xs font-bold text-primary mb-3">الهاشتاجات</h3>
                <p class="text-xs text-on-surface-variant leading-relaxed">تساعدك الهاشتاجات في العثور على المعارف والنقاشات المرتبطة بمواضيع محددة بسرعة وسهولة.</p>
            </div>
        </aside>
    </div>
</div>

<!-- Delete Subject Confirmation Modal -->
<div id="delete-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="delete-subject-backdrop"></div>

    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: rtl;">
        <div class="w-12 h-12 rounded-full bg-error/10 text-error flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">warning</span>
        </div>

        <h3 class="font-headline-md text-base font-bold text-primary mb-2">حذف الموضوع النقاشي</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-6">هل أنت متأكد من رغبتك في حذف هذا الموضوع نهائياً؟ لا يمكن التراجع عن هذا الإجراء لاحقاً.</p>

        <div class="flex gap-3 w-full">
            <button type="button" id="confirm-delete-subject-btn" class="flex-grow bg-error text-white py-2.5 rounded-full text-xs font-bold hover:bg-error/90 transition-all shadow-sm">تأكيد الحذف</button>
            <button type="button" id="cancel-delete-subject-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">إلغاء</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===== Tabs =====
    var tabBtns = document.querySelectorAll('.hashtag-tab-btn');

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');

            // Deactivate all buttons
            tabBtns.forEach(function(b) { b.classList.remove('is-active'); });

            // Hide all panels
            document.querySelectorAll('.hashtag-tab-content').forEach(function(p) {
                p.classList.remove('is-active');
            });

            // Activate clicked button and target panel
            this.classList.add('is-active');
            var panel = document.getElementById(targetId);
            if (panel) panel.classList.add('is-active');
        });
    });

    // ===== Subject Support (Like) — jQuery =====
    $(document).on('click', '.subject-support-btn', function() {
        var btn = $(this);
        var subjectId = btn.attr('data-subject-id');
        if (!subjectId) return;

        var isActive = btn.attr('data-active') === 'true';
        var nextAction = isActive ? 'remove' : 'like';

        btn.prop('disabled', true);

        $.ajax({
            url: '/groups/subjects/' + subjectId + '/react',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', reaction_type: nextAction },
            success: function(res) {
                if (res.success) {
                    btn.attr('data-active', (!isActive).toString());
                    var card = btn.closest('article');
                    card.find('.like-counter').text(res.like_count);
                    card.find('.open-supporters-btn').attr('data-total-supports', res.like_count);
                    if (!isActive) {
                        btn.find('.material-symbols-outlined').addClass('fill-1 text-primary animate-bulb');
                        btn.addClass('text-primary');
                    } else {
                        btn.find('.material-symbols-outlined').removeClass('fill-1 text-primary animate-bulb');
                        btn.removeClass('text-primary');
                    }
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() { toastr.error('حدث خطأ أثناء تسجيل التأييد.'); },
            complete: function() { btn.prop('disabled', false); }
        });
    });

    // ===== Delete Subject Modal =====
    var deleteModal = $('#delete-subject-modal');
    var subjectIdToDelete = null;

    $(document).on('click', '.delete-subject-btn', function() {
        subjectIdToDelete = $(this).attr('data-subject-id');
        deleteModal.removeClass('hidden').addClass('flex');
        setTimeout(function() { deleteModal.addClass('modal-show'); }, 10);
    });

    function closeDeleteModal() {
        deleteModal.removeClass('modal-show');
        setTimeout(function() {
            deleteModal.removeClass('flex').addClass('hidden');
            subjectIdToDelete = null;
        }, 300);
    }

    $('#cancel-delete-subject-btn, #delete-subject-backdrop').on('click', closeDeleteModal);

    $('#confirm-delete-subject-btn').on('click', function() {
        if (!subjectIdToDelete) return;
        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '/groups/subjects/' + subjectIdToDelete + '/delete',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeDeleteModal();
                    $('article[data-subject-id="' + subjectIdToDelete + '"]').fadeOut(400, function() {
                        $(this).remove();
                    });
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('حدث خطأ أثناء محاولة الحذف.');
                btn.prop('disabled', false);
            }
        });
    });

});
</script>
@endpush
@endsection
