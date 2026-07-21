@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp

<!-- Header Hero Banner -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto mt-stack-md" style="direction: {{ $dir }};">
    <div class="bg-primary text-white rounded-2xl p-8 relative overflow-hidden shadow-lg border border-primary/20" style="background: linear-gradient(135deg, #003a23 0%, #1a5237 100%);">
        <div class="absolute -right-16 -top-16 w-48 h-48 rounded-full bg-white/5 blur-2xl"></div>
        <div class="absolute -left-16 -bottom-16 w-48 h-48 rounded-full bg-secondary/15 blur-2xl"></div>
        
        <div class="relative z-10 max-w-2xl {{ $textAlign }}">
            <h1 class="text-white font-headline-lg text-2xl md:text-3xl font-bold mb-3 flex items-center space-x-2 space-x-reverse justify-start">
                <span class="material-symbols-outlined text-white text-3xl">groups</span>
                <span>{{ __t('discussion_groups') }}</span>
            </h1>
            <p class="font-body-md text-sm md:text-base text-white/80 leading-relaxed">
                {{ __t('discussion_groups_desc') }}
            </p>
        </div>
    </div>
</div>

<!-- Groups Directory Grid -->
<div class="px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto mt-8 pb-24" style="direction: {{ $dir }};">
    
    <!-- Tabs Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 mb-6">
        <!-- Tabs Bar -->
        <div class="flex items-center gap-1 bg-surface-container-low rounded-2xl p-1 border border-primary/5 overflow-x-auto scrollbar-hide w-full sm:w-auto">
            @auth
            <!-- Tab: My Groups -->
            <button data-tab="my-groups" class="groups-tab active-tab flex-1 sm:flex-initial justify-center flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
                <span>{{ __t('my_groups') }}</span>
                <span class="tab-count bg-primary/15 text-primary px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $totalMyGroupsCount }}</span>
            </button>
            <!-- Tab: Joined Groups -->
            <button data-tab="joined-groups" class="groups-tab flex-1 sm:flex-initial justify-center flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                <span class="material-symbols-outlined text-[16px]">group</span>
                <span>{{ __t('joined') }}</span>
                <span class="tab-count bg-transparent text-on-surface-variant px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $totalJoinedGroupsCount }}</span>
            </button>
            @endauth
            <!-- Tab: All Groups -->
            <button data-tab="all-groups" class="groups-tab {{ !auth()->check() ? 'active-tab' : '' }} flex-1 sm:flex-initial justify-center flex items-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                <span class="material-symbols-outlined text-[16px]">public</span>
                <span>{{ __t('available_groups') }}</span>
                <span class="tab-count {{ !auth()->check() ? 'bg-primary/15 text-primary' : 'bg-transparent text-on-surface-variant' }} px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $totalGroupsCount }}</span>
            </button>
        </div>

        <!-- Create Button -->
        @auth
        <button id="trigger-create-group-btn" class="bg-secondary hover:bg-secondary/90 text-on-secondary px-4 py-2.5 sm:py-2 rounded-xl sm:rounded-full text-xs font-bold transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-sm shrink-0 w-full sm:w-auto">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>{{ __t('create_group') }}</span>
        </button>
        @endauth
    </div>

    {{-- =================== TAB: My Groups =================== --}}
    @auth
    <div id="tab-my-groups" class="tab-panel">
        @if($myGroups->isEmpty())
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-12 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4 block">manage_accounts</span>
                <p class="font-body-lg text-base font-bold text-on-surface">{{ __t('no_created_groups_yet') }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('no_created_groups_desc') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($myGroups as $group)
                    @include('frontend.wiselook.partials.group_card', ['group' => $group])
                @endforeach
            </div>
            <!-- Spinner -->
            <div id="tab-my-groups-spinner" class="hidden text-center my-6">
                <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>
        @endif
    </div>

    {{-- =================== TAB: Joined Groups =================== --}}
    <div id="tab-joined-groups" class="tab-panel hidden">
        @if($joinedGroups->isEmpty())
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-12 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4 block">group_off</span>
                <p class="font-body-lg text-base font-bold text-on-surface">{{ __t('no_joined_groups_yet') }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('no_joined_groups_desc') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($joinedGroups as $group)
                    @include('frontend.wiselook.partials.group_card', ['group' => $group])
                @endforeach
            </div>
            <!-- Spinner -->
            <div id="tab-joined-groups-spinner" class="hidden text-center my-6">
                <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>
        @endif
    </div>
    @endauth

    {{-- =================== TAB: All Groups =================== --}}
    <div id="tab-all-groups" class="tab-panel {{ auth()->check() ? 'hidden' : '' }}">
        @if($groups->isEmpty())
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-xl border border-primary/10 p-12 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant mb-4 block">group_off</span>
                <p class="font-body-lg text-base font-bold text-on-surface">{{ __t('no_groups_registered_yet') }}</p>
                <p class="text-xs text-on-surface-variant mt-1">{{ __t('no_groups_registered_desc') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($groups as $group)
                    @include('frontend.wiselook.partials.group_card', ['group' => $group])
                @endforeach
            </div>
            <!-- Spinner -->
            <div id="tab-all-groups-spinner" class="hidden text-center my-6">
                <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>
        @endif
    </div>

</div>

<!-- Create Group Modal -->
<div id="create-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="create-group-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-lg w-full bg-white rounded-2xl border border-primary/10 shadow-2xl overflow-hidden z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col max-h-[90vh] {{ $textAlign }}" style="direction: {{ $dir }};">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-primary/5 bg-surface-container-low">
            <h3 class="font-headline-md text-sm font-bold text-primary flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                <span>{{ __t('create_new_discussion_group') }}</span>
            </h3>
            <button type="button" id="close-create-group-btn" class="text-on-surface-variant hover:text-primary p-1.5 rounded-full transition-all duration-200 cursor-pointer flex items-center justify-center">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
        
        <!-- Form -->
        <form action="{{ route('frontend.groups.store') }}" method="POST" enctype="multipart/form-data" class="p-6 overflow-y-auto space-y-4">
            @csrf
            <!-- Title -->
            <div>
                <label for="group-title" class="block text-xs font-bold text-primary mb-1.5">{{ __t('group_name_label') }}</label>
                <input type="text" id="group-title" name="title" required class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('group_name_placeholder') }}">
            </div>
            
            <!-- Description -->
            <div>
                <label for="group-description" class="block text-xs font-bold text-primary mb-1.5">{{ __t('group_description_label') }}</label>
                <textarea id="group-description" name="description" rows="3" class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary" placeholder="{{ __t('group_description_placeholder') }}"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Status -->
                <div>
                    <label for="group-status" class="block text-xs font-bold text-primary mb-1.5">{{ __t('group_status_label') }}</label>
                    <select id="group-status" name="status" required class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-xs text-on-surface focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="open">{{ __t('status_open_desc') }}</option>
                        <option value="closed">{{ __t('status_closed_desc') }}</option>
                    </select>
                </div>
                
                <!-- Invite Code Notice -->
                <div id="invite-code-wrapper" class="hidden items-center justify-center p-3 bg-secondary/10 rounded-xl border border-secondary/15">
                    <div class="{{ $textAlign }}">
                        <p class="text-[11px] font-bold text-secondary flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            <span>{{ __t('auto_invite_code_notice') }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Images (Logo & Cover) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Group Logo/Image -->
                <div>
                    <label class="block text-xs font-bold text-primary mb-1.5">{{ __t('group_logo_label') }}</label>
                    <div id="group-logo-dropzone" class="border-2 border-dashed border-primary/10 hover:border-primary/30 rounded-xl p-5 text-center cursor-pointer transition-all flex flex-col items-center justify-center gap-2 bg-surface">
                        <span class="material-symbols-outlined text-primary text-3xl">account_circle</span>
                        <p class="text-[11px] font-bold text-on-surface">{{ __t('select_logo_label') }}</p>
                        <p class="text-[9px] text-on-surface-variant">{{ __t('max_size_4mb') }}</p>
                        
                        <!-- Preview logo -->
                        <img id="group-logo-preview" class="hidden max-h-16 rounded-lg mt-2 object-cover border border-outline-variant">
                        <input type="file" id="group-logo-input" name="logo_path" accept="image/*" class="hidden">
                    </div>
                </div>
                
                <!-- Cover Image -->
                <div>
                    <label class="block text-xs font-bold text-primary mb-1.5">{{ __t('group_cover_label') }}</label>
                    <div id="group-dropzone" class="border-2 border-dashed border-primary/10 hover:border-primary/30 rounded-xl p-5 text-center cursor-pointer transition-all flex flex-col items-center justify-center gap-2 bg-surface">
                        <span class="material-symbols-outlined text-primary text-3xl">image</span>
                        <p class="text-[11px] font-bold text-on-surface">{{ __t('select_cover_label') }}</p>
                        <p class="text-[9px] text-on-surface-variant">{{ __t('max_size_4mb') }}</p>
                        
                        <!-- Preview cover -->
                        <img id="group-image-preview" class="hidden max-h-16 rounded-lg mt-2 object-cover border border-outline-variant">
                        <input type="file" id="group-file-input" name="image_path" accept="image/*" class="hidden">
                    </div>
                </div>
            </div>
            
            <!-- Submit buttons -->
            <div class="flex gap-3 pt-3 border-t border-primary/5">
                <button type="submit" class="flex-grow bg-primary text-white py-2.5 rounded-full text-xs font-bold hover:bg-primary-dark transition-all shadow-sm">{{ __t('save_group_btn') }}</button>
                <button type="button" id="cancel-create-group-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Join Closed Group Modal -->
<div id="join-group-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="modal-backdrop absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="join-group-backdrop"></div>
    
    <!-- Container -->
    <div class="modal-container relative max-w-sm w-full bg-white rounded-2xl border border-primary/10 shadow-2xl p-6 text-center z-10 translate-y-10 scale-95 opacity-0 transition-all duration-300 flex flex-col items-center justify-center" style="direction: {{ $dir }};">
        <div class="w-12 h-12 rounded-full bg-secondary-container text-secondary flex items-center justify-center mb-4">
            <span class="material-symbols-outlined text-[26px]">vpn_key</span>
        </div>
        
        <h3 class="font-headline-md text-base font-bold text-primary mb-2">{{ __t('join_closed_group_request') }}</h3>
        <p class="text-xs text-on-surface-variant leading-relaxed mb-4">{{ __t('join_closed_group_desc') }}</p>
        
        <form id="join-group-form" class="w-full space-y-4">
            <input type="hidden" id="join-group-id" name="group_id">
            <div>
                <input type="text" id="join-invite-code" required class="w-full bg-surface border border-primary/10 rounded-xl py-2.5 px-4 text-center text-xs text-on-surface placeholder:text-on-surface-variant focus:outline-none focus:ring-1 focus:ring-primary font-bold" placeholder="{{ __t('enter_join_code') }}">
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-grow bg-primary text-white py-2.5 rounded-full text-xs font-bold hover:bg-primary-dark transition-all shadow-sm flex items-center justify-center gap-1.5">
                    <span>{{ __t('confirm_join') }}</span>
                    <div id="join-group-spinner" class="hidden w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
                <button type="button" id="cancel-join-group-btn" class="flex-grow py-2.5 rounded-full border border-outline-variant text-xs font-bold text-on-surface-variant hover:bg-surface-container-high transition-all">{{ __t('cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    #create-group-modal, #join-group-modal {
        transition: visibility 0.3s ease, opacity 0.3s ease;
    }
    #create-group-modal.modal-show, #join-group-modal.modal-show {
        display: flex !important;
    }
    #create-group-modal.modal-show .modal-backdrop, #join-group-modal.modal-show .modal-backdrop {
        opacity: 1;
    }
    #create-group-modal.modal-show .modal-container, #join-group-modal.modal-show .modal-container {
        transform: translateY(0) scale(1);
        opacity: 1;
    }

    /* Tab styling */
    .groups-tab {
        color: var(--color-on-surface-variant, #5f6368);
        background: transparent;
    }
    .groups-tab.active-tab {
        background: white;
        color: var(--color-primary, #003a23);
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
    }
    .groups-tab.active-tab .tab-count {
        background: rgba(0, 58, 35, 0.12) !important;
        color: var(--color-primary, #003a23) !important;
    }
    .groups-tab:not(.active-tab) .tab-count {
        background: transparent !important;
        color: var(--color-on-surface-variant, #5f6368) !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // --- Lazy Loading / Infinite Scroll Logic ---
    let tabStates = {
        'my-groups': {
            page: 2,
            hasMore: {{ $myGroups->hasMorePages() ? 'true' : 'false' }},
            loading: false
        },
        'joined-groups': {
            page: 2,
            hasMore: {{ $joinedGroups->hasMorePages() ? 'true' : 'false' }},
            loading: false
        },
        'all-groups': {
            page: 2,
            hasMore: {{ $groups->hasMorePages() ? 'true' : 'false' }},
            loading: false
        }
    };

    function loadMoreGroups(tabId) {
        const state = tabStates[tabId];
        if (!state || !state.hasMore || state.loading) return;

        state.loading = true;
        $(`#tab-${tabId}-spinner`).removeClass('hidden');

        $.ajax({
            url: "{{ route('frontend.groups') }}",
            type: "GET",
            data: {
                page: state.page,
                type: tabId
            },
            success: function(res) {
                if (res.html && res.html.trim() !== '') {
                    $(`#tab-${tabId} .grid`).append(res.html);
                    state.page = res.nextPage;
                    state.hasMore = res.hasMore;
                } else {
                    state.hasMore = false;
                }
            },
            error: function() {
                console.error('Error loading more groups');
            },
            complete: function() {
                state.loading = false;
                $(`#tab-${tabId}-spinner`).addClass('hidden');
            }
        });
    }

    function checkScroll() {
        const activeTabButton = $('.groups-tab.active-tab');
        if (activeTabButton.length === 0) return;
        
        const activeTabId = activeTabButton.data('tab');
        const state = tabStates[activeTabId];
        
        if (!state || !state.hasMore || state.loading) return;

        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 300) {
            loadMoreGroups(activeTabId);
        }
    }

    $(window).on('scroll', checkScroll);

    // --- Tabs Switching Logic ---
    $('.groups-tab').on('click', function() {
        const tabId = $(this).data('tab');

        // Update tab buttons
        $('.groups-tab').removeClass('active-tab');
        $(this).addClass('active-tab');

        // Show/hide tab panels
        $('.tab-panel').addClass('hidden');
        $('#tab-' + tabId).removeClass('hidden');

        // Check if we need to load more immediately for the selected tab
        checkScroll();
    });

    // --- Create Group Modal Handlers ---
    const createModal = $('#create-group-modal');
    
    $('#trigger-create-group-btn').on('click', function() {
        createModal.removeClass('hidden').addClass('flex');
        setTimeout(() => createModal.addClass('modal-show'), 10);
    });
    
    function closeCreateModal() {
        createModal.removeClass('modal-show');
        setTimeout(() => {
            createModal.removeClass('flex').addClass('hidden');
            createModal.find('form')[0].reset();
            $('#group-image-preview').addClass('hidden').attr('src', '');
            $('#group-logo-preview').addClass('hidden').attr('src', '');
            $('#invite-code-wrapper').addClass('hidden');
        }, 300);
    }
    
    $('#close-create-group-btn, #cancel-create-group-btn, #create-group-backdrop').on('click', closeCreateModal);
    
    // Toggle Invite Code input
    $('#group-status').on('change', function() {
        if ($(this).val() === 'closed') {
            $('#invite-code-wrapper').removeClass('hidden').addClass('flex');
        } else {
            $('#invite-code-wrapper').addClass('hidden').removeClass('flex');
        }
    });
    
    // Cover Image Upload Preview
    const dropzone = $('#group-dropzone');
    const fileInput = $('#group-file-input');
    const imgPreview = $('#group-image-preview');
    
    dropzone.on('click', () => fileInput[0].click());
    
    fileInput.on('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.attr('src', e.target.result).removeClass('hidden');
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // Logo Image Upload Preview
    const logoDropzone = $('#group-logo-dropzone');
    const logoInput = $('#group-logo-input');
    const logoPreview = $('#group-logo-preview');
    
    logoDropzone.on('click', () => logoInput[0].click());
    
    logoInput.on('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.attr('src', e.target.result).removeClass('hidden');
                };
                reader.readAsDataURL(file);
            }
        }
    });

    // --- Join Closed Group Handlers ---
    const joinModal = $('#join-group-modal');
    
    $(document).on('click', '.trigger-join-closed-modal', function() {
        const groupId = $(this).attr('data-group-id');
        $('#join-group-id').val(groupId);
        
        joinModal.removeClass('hidden').addClass('flex');
        setTimeout(() => joinModal.addClass('modal-show'), 10);
    });
    
    function closeJoinModal() {
        joinModal.removeClass('modal-show');
        setTimeout(() => {
            joinModal.removeClass('flex').addClass('hidden');
            $('#join-group-form')[0].reset();
            $('#join-group-id').val('');
        }, 300);
    }
    
    $('#cancel-join-group-btn, #join-group-backdrop').on('click', closeJoinModal);
    
    $('#join-group-form').on('submit', function(e) {
        e.preventDefault();
        const groupId = $('#join-group-id').val();
        const inviteCode = $('#join-invite-code').val().trim();
        if (!groupId || !inviteCode) return;
        
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        const spinner = $('#join-group-spinner');
        
        btn.prop('disabled', true);
        spinner.removeClass('hidden');
        
        $.ajax({
            url: `/groups/${groupId}/join`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                invite_code: inviteCode
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    closeJoinModal();
                    location.reload();
                } else {
                    toastr.error(res.message);
                }
            },
            error: function() {
                toastr.error('{{ __t("server_connection_error") }}');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('hidden');
            }
        });
    });

    // --- Join Open Group Handler ---
    $(document).on('click', '.join-open-group-btn', function() {
        const btn = $(this);
        const groupId = btn.attr('data-group-id');
        if (!groupId) return;
        
        btn.prop('disabled', true);
        
        $.ajax({
            url: `/groups/${groupId}/join`,
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    location.reload();
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('{{ __t("server_connection_error") }}');
                btn.prop('disabled', false);
            }
        });
    });
});
</script>
@endpush

@endsection
