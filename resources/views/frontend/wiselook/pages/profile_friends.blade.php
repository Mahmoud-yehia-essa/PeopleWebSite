@extends('frontend.wiselook.master_dashboard')

@section('title', 'أصدقاء ' . $user->first_name . ' ' . $user->last_name)

@section('main')
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24">
    
    <!-- Page Header -->
    <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 text-right mb-8" style="direction: rtl;">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('profile.edit', $user->id) }}" class="text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center p-1.5 bg-surface rounded-full border border-primary/5">
                    <span class="material-symbols-outlined text-[20px] scale-x-[-1]">arrow_back</span>
                </a>
                <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">أصدقاء {{ $user->first_name }} {{ $user->last_name }}</h1>
            </div>
            <p class="font-body-md text-xs text-on-surface-variant mt-1.5 pr-9">تصفح قائمة الأصدقاء والمستشارين المضافين في شبكة هذا المستخدم.</p>
        </div>
        <div class="bg-primary/5 border border-primary/15 px-4 py-2 rounded-2xl text-primary font-bold text-xs shrink-0 self-end md:self-auto pr-3 pl-3">
            إجمالي الأصدقاء: {{ $friends->total() }}
        </div>
    </div>

    <!-- Friends Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full text-right" style="direction: rtl;">
        @forelse($friends as $friend)
            @php
                $friendPhoto = (!empty($friend->profile_picture) && $friend->profile_picture != 'non') 
                    ? (filter_var($friend->profile_picture, FILTER_VALIDATE_URL) ? $friend->profile_picture : asset('new_wiselook/uploads/'.$friend->profile_picture)) 
                    : asset('upload/no_image.jpg');
                $friendName = $friend->first_name . ' ' . $friend->last_name;
                $theirFriendIds = $friendsFriendsMap[$friend->id] ?? [];
                $mutualCount = count(array_intersect($myFriendIds, $theirFriendIds));
            @endphp
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between text-right">
                <div>
                    <div class="flex gap-4">
                        <!-- Avatar -->
                        <div class="w-20 h-20 rounded-full bg-surface-container overflow-hidden border-2 border-primary/10 relative shrink-0">
                            <a href="{{ route('profile.edit', $friend->id) }}">
                                <img alt="{{ $friend->first_name }}" class="w-full h-full object-cover hover:opacity-90 transition-opacity" src="{{ $friendPhoto }}">
                            </a>
                        </div>
                        <div class="flex-grow min-w-0">
                            <div class="truncate">
                                <a href="{{ route('profile.edit', $friend->id) }}" class="hover:underline hover:text-primary transition-all">
                                    <h3 class="font-title-lg text-sm font-bold text-primary flex items-center gap-1 truncate">
                                        <span>{{ $friendName }}</span>
                                        @if($friend->role == 'admin')
                                            <span class="material-symbols-outlined text-secondary text-[16px]">verified</span>
                                        @endif
                                    </h3>
                                </a>
                                <p class="font-body-md text-xs text-on-surface-variant line-clamp-1 mt-0.5">{{ $friend->email }}</p>
                                @if(auth()->check() && auth()->id() !== $friend->id && $mutualCount > 0)
                                    <p class="text-[11px] text-on-surface-variant mt-1.5">
                                        <span class="open-mutual-btn cursor-pointer text-primary hover:underline font-bold" data-user-id="{{ $friend->id }}" data-user-name="{{ $friendName }}">
                                            {{ $mutualCount }} صديق مشترك
                                        </span>
                                    </p>
                                @endif
                            </div>
                            <div class="mt-2 inline-flex items-center gap-1 bg-secondary/10 text-secondary px-2.5 py-0.5 rounded-full font-label-sm text-[10px] font-bold">
                                <span class="material-symbols-outlined text-[14px]">workspace_premium</span>
                                <span>{{ $friend->points ?? 0 }} نقطة حكمة</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mt-6">
                    <a href="{{ route('profile.edit', $friend->id) }}" class="w-full bg-primary text-white hover:bg-primary-container text-center font-label-md text-xs font-bold py-2.5 rounded-xl transition-colors shadow-sm cursor-pointer flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">account_circle</span>
                        <span>عرض الملف الشخصي</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl p-12 border border-primary/10 text-center">
                <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">group</span>
                <h3 class="font-headline-lg text-base font-bold text-primary">لا يوجد أصدقاء</h3>
                <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">قائمة أصدقاء هذا المستخدم فارغة حالياً.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex justify-center" style="direction: rtl;">
        {{ $friends->links() }}
    </div>
</div>

<!-- Mutual Friends Modal -->
<div id="mutual-friends-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/50 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
    
    <!-- Modal Content Container -->
    <div class="modal-container relative max-w-md w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[70vh]">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low text-right" style="direction: rtl;">
            <h3 class="font-headline-md text-base font-bold text-primary" id="mutual-friends-title">الأصدقاء المشتركون</h3>
            <button id="close-mutual-friends-btn" class="text-on-surface-variant hover:text-on-surface p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        
        <!-- Modal Body (List) -->
        <div class="p-6 overflow-y-auto flex-grow text-right space-y-4" id="mutual-friends-list" style="direction: rtl;">
            <!-- Dynamic list will be rendered here -->
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Mutual Friends Modal Transitions */
    #mutual-friends-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #mutual-friends-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // --- Mutual Friends Modal Handlers ---
    const mutualModal = $('#mutual-friends-modal');
    const mutualList = $('#mutual-friends-list');
    const mutualTitle = $('#mutual-friends-title');

    function openMutualModal(userName, userId) {
        mutualTitle.text('الأصدقاء المشتركون مع ' + userName);
        mutualList.html(`
            <div class="flex items-center justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
            </div>
        `);

        // Show layout first
        mutualModal.removeClass('hidden').addClass('flex');
        setTimeout(() => {
            mutualModal.addClass('modal-show');
        }, 10);

        // Fetch mutual friends via AJAX
        $.ajax({
            url: "/friends/mutual/" + userId,
            type: "GET",
            success: function(data) {
                mutualList.empty();
                if (data.length === 0) {
                    mutualList.append(`
                        <p class="text-xs text-on-surface-variant text-center py-4">لا يوجد أصدقاء مشتركين</p>
                    `);
                } else {
                    data.forEach(friend => {
                        mutualList.append(`
                            <div class="flex items-center justify-between py-2 border-b border-primary/5 last:border-0" style="direction: rtl;">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <a href="${friend.profile_url}">
                                        <img src="${friend.avatar}" class="w-10 h-10 rounded-full object-cover border border-outline-variant hover:opacity-85 transition-opacity" alt="${friend.name}">
                                    </a>
                                    <div class="text-right pr-2">
                                        <a href="${friend.profile_url}" class="font-body-md text-sm font-bold text-on-surface hover:text-primary transition-colors block">${friend.name}</a>
                                        <p class="text-[10px] text-on-surface-variant">صديق مشترك</p>
                                    </div>
                                </div>
                                <a href="${friend.profile_url}" class="text-xs font-bold text-primary hover:underline">عرض الملف</a>
                            </div>
                        `);
                    });
                }
            },
            error: function() {
                mutualList.html(`
                    <p class="text-xs text-error text-center py-4">حدث خطأ أثناء تحميل البيانات</p>
                `);
            }
        });
    }

    function closeMutualModal() {
        mutualModal.removeClass('modal-show');
        setTimeout(() => {
            mutualModal.removeClass('flex').addClass('hidden');
        }, 300);
    }

    $(document).on('click', '.open-mutual-btn', function(e) {
        e.preventDefault();
        const btn = $(this);
        const userId = btn.attr('data-user-id');
        const userName = btn.attr('data-user-name');
        openMutualModal(userName, userId);
    });

    $(document).on('click', '#close-mutual-friends-btn, #mutual-friends-modal .modal-backdrop', function() {
        closeMutualModal();
    });
});
</script>
@endpush
