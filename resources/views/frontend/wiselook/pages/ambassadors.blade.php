@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24 {{ $textAlign }}" style="direction: {{ $dir }};">
    
    @if(session('message'))
        <div class="mb-6 p-4 rounded-xl {{ session('alert-type') == 'success' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-error/10 text-error border border-error/20' }} {{ $textAlign }} font-semibold text-xs max-w-3xl mx-auto">
            {{ session('message') }}
        </div>
    @endif

    <!-- Header Section -->
    <section class="mb-8 text-center bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-8 border border-primary/10 shadow-sm max-w-3xl mx-auto" style="direction: {{ $dir }};">
        <h1 class="font-display-lg text-2xl md:text-3xl font-bold text-primary mb-2">{{ __t('ambassadors') }}</h1>
        <p class="font-body-lg text-xs md:text-sm text-on-surface-variant max-w-2xl mx-auto leading-relaxed">
            {{ __t('ambassadors_desc') }}
        </p>
        @if(auth()->user()->rank)
            @php
                $rankPhoto = auth()->user()->rank->photo;
                $rankPhotoPath = url('upload/no_image.jpg');
                if (!empty($rankPhoto) && file_exists(public_path('upload/rankings/' . $rankPhoto))) {
                    $rankPhotoPath = asset('upload/rankings/' . $rankPhoto);
                }
            @endphp
            <div class="mt-4 inline-flex items-center gap-1.5 bg-secondary/10 text-secondary px-4 py-1.5 rounded-full font-label-md text-xs font-bold">
                <img src="{{ $rankPhotoPath }}" alt="{{ __t(auth()->user()->rank->rank_name) }}" style="width: 22px; height: 22px; object-fit: contain; margin-left: 2px;">
                <span class="font-bold text-sm" style="color: #cda225; text-shadow: 0 1px 2px rgba(0,0,0,0.15);">{{ __t(auth()->user()->rank->rank_name) }}</span>
            </div>
        @else
            <div class="mt-4 inline-flex items-center gap-1.5 bg-secondary/10 text-secondary px-4 py-1.5 rounded-full font-label-md text-xs font-bold">
                <span class="material-symbols-outlined text-[18px]">military_tech</span>
                <span>{{ __t('current_rank') }}: {{ __t('honorary_member') }}</span>
            </div>
        @endif
    </section>

    <!-- Referral Link Card -->
    <section class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm mb-8 max-w-3xl mx-auto {{ $textAlign }}">
        <h3 class="font-title-lg text-sm font-bold text-primary mb-4 flex items-center gap-2 {{ $textAlign }}">
            <span class="material-symbols-outlined text-primary text-[20px]">link</span>
            <span>{{ __t('custom_referral_link') }}</span>
        </h3>
        <div class="flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-grow w-full bg-[#f2f4f0]/60 rounded-lg border border-primary/10 p-3 flex items-center justify-between overflow-hidden {{ $textAlign }}" style="direction: {{ $dir }};">
                <span id="referral-link" class="font-body-md text-xs text-on-surface truncate select-all" style="direction: ltr;">{{ url('/ref/' . $link->code) }}</span>
            </div>
            <button id="copy-link-btn" class="w-full md:w-auto px-6 py-3 bg-primary text-white hover:bg-primary-container rounded-lg font-label-md text-xs font-bold transition-colors flex items-center justify-center gap-2 cursor-pointer shadow-sm shrink-0 {{ $dir === 'rtl' ? 'flex-row-reverse' : '' }}">
                <span class="material-symbols-outlined text-[18px]">content_copy</span>
                <span>{{ __t('copy_link') }}</span>
            </button>
        </div>
    </section>

    <!-- Customize Referral Code Form -->
    <section class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm mb-8 max-w-3xl mx-auto {{ $textAlign }}" style="direction: {{ $dir }};">
        <h3 class="font-title-lg text-sm font-bold text-primary mb-4 flex items-center gap-2 {{ $textAlign }}">
            <span class="material-symbols-outlined text-primary text-[20px]">edit_note</span>
            <span>{{ __t('customize_referral_code') }}</span>
        </h3>
        <form method="POST" action="{{ route('frontend.ambassadors.update_code') }}" class="space-y-4">
            @csrf
            <div class="flex flex-col gap-2">
                <label for="custom-code" class="font-body-md text-xs text-on-surface-variant {{ $textAlign }}">{{ __t('customize_referral_code_label') }}</label>
                <div class="flex gap-2">
                    <input id="custom-code" name="code" value="{{ $link->code }}" class="flex-grow bg-surface py-2.5 px-4 rounded-lg border border-primary/10 focus:border-primary focus:ring-1 focus:ring-primary outline-none font-body-md text-sm text-on-surface text-left" dir="ltr" type="text" required>
                    <button type="submit" class="px-6 py-2.5 bg-secondary text-white hover:bg-secondary-dark rounded-lg font-label-md text-xs font-bold transition-all cursor-pointer">
                        {{ __t('update_code') }}
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Analytics Grid -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 max-w-3xl mx-auto" style="direction: {{ $dir }};">
        <!-- Clicks -->
        <div class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[28px] text-primary" style="font-variation-settings: 'FILL' 1;">ads_click</span>
            </div>
            <h4 class="font-label-md text-xs font-bold text-on-surface-variant mb-1">{{ __t('total_clicks') }}</h4>
            <span class="text-2xl font-bold text-primary mt-1">{{ $clicksCount }}</span>
        </div>

        <!-- Successful Invitations -->
        <div class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-primary/5 rounded-full flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[28px] text-primary" style="font-variation-settings: 'FILL' 1;">group_add</span>
            </div>
            <h4 class="font-label-md text-xs font-bold text-on-surface-variant mb-1">{{ __t('successful_invitations') }}</h4>
            <span class="text-2xl font-bold text-primary mt-1">{{ $referralsCount }}</span>
        </div>
        
        <!-- Reward Points -->
        <div class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-[#ffe174]/10 rounded-full flex items-center justify-center mb-3">
                <span class="material-symbols-outlined text-[28px] text-[#caa800]" style="font-variation-settings: 'FILL' 1;">stars</span>
            </div>
            <h4 class="font-label-md text-xs font-bold text-on-surface-variant mb-1">{{ __t('estimated_reward_points') }}</h4>
            <span class="text-2xl font-bold text-[#caa800] mt-1">{{ $rewardPoints }}</span>
        </div>
    </section>

    <!-- Recently Joined Members Card -->
    <section class="bg-white rounded-2xl p-6 border border-primary/10 shadow-sm max-w-3xl mx-auto {{ $textAlign }}" style="direction: {{ $dir }};">
        <h3 class="font-title-lg text-sm font-bold text-primary mb-6 flex items-center gap-2 {{ $textAlign }}">
            <span class="material-symbols-outlined text-primary text-[20px]">verified_user</span>
            <span>{{ __t('members_joined_via_link') }}</span>
        </h3>
        
        <div class="flex flex-col gap-4">
            @forelse($recentReferrals as $tracking)
                @php
                    $referredUser = $tracking->registeredUser;
                    if (!$referredUser) continue;
                    $rPhoto = (!empty($referredUser->profile_picture) && $referredUser->profile_picture != 'non') 
                        ? (filter_var($referredUser->profile_picture, FILTER_VALIDATE_URL) ? $referredUser->profile_picture : asset('new_wiselook/uploads/'.$referredUser->profile_picture)) 
                        : asset('upload/no_image.jpg');
                    $referredName = $referredUser->first_name . ' ' . $referredUser->last_name;
                @endphp
                <div class="flex items-center justify-between p-3 hover:bg-surface-container-low rounded-xl transition-colors border-b border-primary/5 last:border-0 {{ $textAlign }}" style="direction: {{ $dir }};">
                    <div class="flex items-center gap-4">
                        <img src="{{ $rPhoto }}" alt="{{ $referredName }}" class="w-11 h-11 rounded-full object-cover border border-primary/10">
                        <div class="flex flex-col {{ $textAlign }}">
                            <span class="font-title-lg text-xs font-bold text-on-surface">{{ $referredName }}</span>
                            <span class="text-[10px] text-on-surface-variant mt-0.5">{{ __t('joined_prefix', ['time' => $tracking->created_at->diffForHumans()]) }}</span>
                        </div>
                    </div>
                    @if($referredUser->role == 'admin')
                        <span class="material-symbols-outlined text-secondary text-[20px]" title="{{ __t('platform_admin') }}">verified</span>
                    @else
                        <span class="material-symbols-outlined text-secondary text-[20px]" title="{{ __t('verified_member') }}">check_circle</span>
                    @endif
                </div>
            @empty
                <div class="p-6 text-center text-on-surface-variant font-body-md text-xs">
                    {{ __t('no_referrals_yet') }}
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const _tp = {
        copiedSuccess: {!! json_encode(__t('referral_link_copied')) !!},
        copiedBtn: {!! json_encode(__t('copied_btn')) !!}
    };

    // Copy Link functionality
    $('#copy-link-btn').on('click', function() {
        var linkText = $('#referral-link').text();
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(linkText).select();
        document.execCommand('copy');
        tempInput.remove();
        
        // Show success message if toastr is defined
        if (typeof toastr !== "undefined") {
            toastr.success(_tp.copiedSuccess);
        } else {
            alert(_tp.copiedSuccess);
        }
        
        // Minor visual feedback on button
        var $btn = $(this);
        var originalHtml = $btn.html();
        $btn.html('<span class="material-symbols-outlined text-[18px]">done</span><span>' + _tp.copiedBtn + '</span>');
        $btn.addClass('bg-secondary').removeClass('bg-primary');
        setTimeout(function() {
            $btn.html(originalHtml);
            $btn.removeClass('bg-secondary').addClass('bg-primary');
        }, 2000);
    });
});
</script>
@endpush
