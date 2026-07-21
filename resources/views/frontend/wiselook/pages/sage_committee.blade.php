@extends('frontend.wiselook.master_dashboard')

@section('title', __t('sage_committee_page_title'))

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp

@push('styles')
<style>
    /* ========== Chamber Atmosphere ========== */
    .chamber-hero {
        background: linear-gradient(135deg, #0a2218 0%, #1a5235 40%, #0d3522 70%, #061a0f 100%);
        position: relative;
        overflow: hidden;
    }
    .chamber-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: 
            radial-gradient(ellipse at 20% 50%, rgba(163, 230, 53, 0.04) 0%, transparent 60%),
            radial-gradient(ellipse at 80% 20%, rgba(212, 175, 55, 0.08) 0%, transparent 50%),
            radial-gradient(ellipse at 60% 80%, rgba(26, 82, 55, 0.15) 0%, transparent 50%);
        z-index: 1;
    }
    .chamber-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4af37' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        z-index: 1;
    }

    /* Gold dividers */
    .gold-divider {
        height: 2px;
        background: linear-gradient(90deg, transparent, #d4af37, #f0d060, #d4af37, transparent);
    }

    /* Member card atmosphere */
    .wise-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.98), rgba(248,250,248,0.95));
        border: 1px solid rgba(163, 230, 53, 0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .wise-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #d4af37, #1a5235);
        opacity: 0.5;
        transition: opacity 0.3s;
    }
    .wise-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(26, 82, 55, 0.12), 0 4px 16px rgba(212, 175, 55, 0.1);
        border-color: rgba(212, 175, 55, 0.3);
    }
    .wise-card:hover::before {
        opacity: 1;
    }

    /* Avatar ring */
    .wise-avatar-ring {
        background: linear-gradient(135deg, #d4af37, #f0d060, #1a5235, #d4af37);
        padding: 2px;
        border-radius: 50%;
        animation: ringPulse 4s ease-in-out infinite;
    }
    @keyframes ringPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.3); }
        50% { box-shadow: 0 0 0 8px rgba(212, 175, 55, 0); }
    }

    /* Number badge */
    .member-number {
        background: linear-gradient(135deg, #d4af37, #f0d060);
        color: #0a2218;
        font-weight: 800;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        box-shadow: 0 2px 8px rgba(212, 175, 55, 0.4);
        position: absolute;
        bottom: -4px;
        left: -4px;
        z-index: 10;
        border: 2px solid white;
    }

    /* Stats cards */
    .stat-card {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(212, 175, 55, 0.25);
        backdrop-filter: blur(10px);
        transition: all 0.3s;
    }
    .stat-card:hover {
        background: rgba(255,255,255,0.08);
        border-color: rgba(212, 175, 55, 0.5);
        transform: scale(1.02);
    }

    /* Appointment date badge */
    .appointment-badge {
        background: linear-gradient(135deg, rgba(26,82,55,0.08), rgba(163,230,53,0.05));
        border: 1px solid rgba(26,82,55,0.15);
    }

    /* Points badge */
    .points-badge {
        background: linear-gradient(135deg, rgba(212,175,55,0.12), rgba(240,208,96,0.06));
        border: 1px solid rgba(212,175,55,0.2);
    }

    /* Specialty pill */
    .specialty-pill {
        background: linear-gradient(135deg, rgba(26, 82, 55, 0.08), rgba(26, 82, 55, 0.04));
        border: 1px solid rgba(26, 82, 55, 0.15);
        color: #1a5235;
    }

    /* Session decor */
    .session-gavel {
        animation: gavelFloat 3s ease-in-out infinite;
    }
    @keyframes gavelFloat {
        0%, 100% { transform: translateY(0) rotate(-5deg); }
        50% { transform: translateY(-6px) rotate(0deg); }
    }

    /* Particle dots */
    .particle {
        position: absolute;
        border-radius: 50%;
        background: rgba(212, 175, 55, 0.3);
        animation: particleFloat linear infinite;
    }
    @keyframes particleFloat {
        0% { transform: translateY(0) translateX(0); opacity: 0; }
        20% { opacity: 0.6; }
        80% { opacity: 0.3; }
        100% { transform: translateY(-120px) translateX(30px); opacity: 0; }
    }

    /* Entrance animation */
    .card-enter {
        animation: cardEnter 0.5s ease-out both;
    }
    @keyframes cardEnter {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Profile link effect */
    .profile-link-btn {
        background: linear-gradient(135deg, #1a5235, #2d7a52);
        transition: all 0.3s;
    }
    .profile-link-btn:hover {
        background: linear-gradient(135deg, #2d7a52, #1a5235);
        box-shadow: 0 4px 16px rgba(26, 82, 55, 0.3);
        transform: scale(1.02);
    }

    /* Gold text */
    .text-gold { color: #d4af37; }
    .border-gold { border-color: rgba(212,175,55,0.3); }
</style>
@endpush

{{-- ============================================================
     HERO: Chamber Atmosphere
     ============================================================ --}}
<div class="chamber-hero min-h-[480px] flex flex-col" style="direction: {{ $dir }};">

    {{-- Floating particles --}}
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        @for($p = 0; $p < 12; $p++)
            <div class="particle" style="
                width: {{ rand(3,7) }}px;
                height: {{ rand(3,7) }}px;
                left: {{ rand(5,95) }}%;
                bottom: {{ rand(0,30) }}%;
                animation-duration: {{ rand(6,14) }}s;
                animation-delay: {{ rand(0,8) }}s;
            "></div>
        @endfor
    </div>

    <div class="relative z-10 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pt-28 pb-16 flex flex-col md:flex-row items-center gap-10 w-full">

        {{-- Text content --}}
        <div class="flex-grow flex-1 {{ $textAlign }}">
            <span class="inline-flex items-center gap-2 bg-[#d4af37]/15 border border-[#d4af37]/30 text-[#d4af37] px-4 py-1.5 rounded-full font-bold text-xs mb-5 backdrop-blur-sm">
                <span class="material-symbols-outlined text-[16px]">gavel</span>
                <span>{{ __t('open_session') }}</span>
            </span>

            <h1 class="text-3xl md:text-5xl font-extrabold text-white leading-tight mb-4">
                {{ __t('meeting_headquarters') }}<br>
                <span class="text-gold">{{ __t('wise_committee') }}</span>
            </h1>

            <p class="text-white/80 text-base md:text-lg leading-relaxed mb-8 max-w-lg">
                {{ __t('sage_committee_desc') }}
            </p>

            {{-- Stats row --}}
            <div class="flex gap-4 flex-wrap {{ $dir === 'rtl' ? 'justify-start' : 'justify-end' }}">
                <div class="stat-card rounded-2xl px-5 py-3 text-center min-w-[90px]">
                    <div class="text-2xl font-extrabold text-gold">{{ $committeeMembers->count() }}</div>
                    <div class="text-white/60 text-[10px] font-semibold mt-0.5">{{ __t('sage_label') }}</div>
                </div>
                <div class="stat-card rounded-2xl px-5 py-3 text-center min-w-[90px]">
                    <div class="text-2xl font-extrabold text-gold">{{ $committeeMembers->unique(fn($m) => $m->specialty)->count() }}</div>
                    <div class="text-white/60 text-[10px] font-semibold mt-0.5">{{ __t('specialty_label') }}</div>
                </div>
            </div>
        </div>

        {{-- Gavel Icon Decoration --}}
        <div class="hidden md:flex flex-col items-center gap-4 opacity-80">
            <div class="session-gavel w-40 h-40 rounded-full border border-[#d4af37]/20 bg-white/5 backdrop-blur-sm flex items-center justify-center">
                <span class="material-symbols-outlined text-[80px] text-gold" style="font-variation-settings: 'FILL' 0, 'wght' 200, 'GRAD' 0, 'opsz' 96">gavel</span>
            </div>
            <div class="text-gold/60 text-xs font-semibold tracking-widest">WISDOM COUNCIL</div>
        </div>

    </div>

    {{-- Bottom Gold Divider --}}
    <div class="gold-divider w-full"></div>
</div>

{{-- ============================================================
     MAIN CONTENT
     ============================================================ --}}
<div class="pt-12 pb-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto" style="direction: {{ $dir }};">

    @if($committeeMembers->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-3xl border border-primary/10 shadow-sm p-20 text-center">
            <span class="material-symbols-outlined text-[80px] text-outline/40 block mb-4">diversity_3</span>
            <h2 class="font-headline-lg text-xl font-bold text-primary mb-2">{{ __t('no_sages_appointed_yet') }}</h2>
            <p class="text-on-surface-variant text-sm">{{ __t('no_sages_appointed_desc') }}</p>
        </div>
    @else
        {{-- Section Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/8 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[22px]">diversity_3</span>
                </div>
                <div class="{{ $textAlign }}">
                    <h2 class="font-headline-lg text-xl font-bold text-primary">{{ __t('committee_members_title') }}</h2>
                    <p class="text-on-surface-variant text-sm mt-0.5">{{ $committeeMembers->count() }} {{ __t('active_members_in_committee') }}</p>
                </div>
            </div>
            <div class="hidden md:flex items-center gap-2 bg-surface rounded-2xl px-4 py-2 border border-primary/8 text-xs text-on-surface-variant font-medium">
                <span class="material-symbols-outlined text-[16px] text-gold" style="color:#d4af37">workspace_premium</span>
                <span>{{ __t('elite_approved_by_admin') }}</span>
            </div>
        </div>

        {{-- Members Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($committeeMembers as $index => $member)
                @php
                    $user = $member->user;
                    if (!$user) continue;

                    $avatarUrl = url('upload/no_image.jpg');
                    if (!empty($user->profile_picture) && $user->profile_picture !== 'non') {
                        $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                            ? $user->profile_picture
                            : asset('new_wiselook/uploads/' . $user->profile_picture);
                    }

                    $fullName = trim($user->first_name . ' ' . $user->last_name);
                    $appointedAt = $member->created_at ? $member->created_at->format('d M Y') : '—';
                    $specialty = $member->specialty ?? 'حكيم عام';
                    $bio = $member->bio ?? 'لا توجد نبذة مضافة لهذا الحكيم حتى الآن.';
                    $points = $user->points ?? 0;
                    $cardDelay = $index * 80;
                @endphp

                <div class="wise-card rounded-3xl p-6 shadow-sm card-enter flex flex-col justify-between {{ $textAlign }}"
                     style="animation-delay: {{ $cardDelay }}ms;">

                    {{-- Top: Avatar + Name + Specialty --}}
                    <div class="flex items-start gap-4 mb-5">
                        {{-- Avatar with gold ring --}}
                        <div class="shrink-0 relative">
                            <a href="{{ route('profile.edit', $user->id) }}" class="block">
                                <div class="wise-avatar-ring w-20 h-20">
                                    <div class="w-full h-full rounded-full overflow-hidden bg-surface-container">
                                        <img src="{{ $avatarUrl }}"
                                             alt="{{ $fullName }}"
                                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                    </div>
                                </div>
                                {{-- Member number badge --}}
                                <div class="member-number">{{ $index + 1 }}</div>
                            </a>
                        </div>

                        {{-- Name + Info --}}
                        <div class="flex-grow min-w-0 pt-1">
                            <a href="{{ route('profile.edit', $user->id) }}" class="hover:text-primary transition-colors">
                                <h3 class="font-bold text-lg text-on-surface leading-tight flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $fullName }}</span>
                                    @if($user->role === 'admin')
                                        <span class="material-symbols-outlined text-[16px] text-secondary"
                                              style="font-variation-settings:'FILL' 1">verified</span>
                                    @endif
                                </h3>
                            </a>

                            {{-- Specialty --}}
                            <span class="inline-block specialty-pill text-xs font-bold px-3 py-1 rounded-full mt-2">
                                <span class="material-symbols-outlined text-[13px] align-middle {{ $dir === 'rtl' ? 'ml-0.5' : 'mr-0.5' }}">hub</span>
                                {{ $specialty }}
                            </span>

                            {{-- Points --}}
                            <div class="points-badge inline-flex items-center gap-1.5 px-3 py-1 rounded-full mt-1.5">
                                <span class="material-symbols-outlined text-[13px]" style="color:#d4af37; font-variation-settings:'FILL' 1">workspace_premium</span>
                                <span class="text-xs font-bold" style="color:#b8922a">{{ number_format($points) }} {{ __t('points_label') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Gold divider --}}
                    <div class="gold-divider mb-4 opacity-40"></div>

                    {{-- Bio --}}
                    <div class="flex-grow mb-5">
                        <p class="text-sm text-on-surface leading-relaxed line-clamp-3" title="{{ $bio }}">
                            {{ $bio }}
                        </p>
                    </div>

                    {{-- Appointment Date --}}
                    <div class="appointment-badge flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl mb-4">
                        <div class="flex items-center gap-1.5 text-xs text-on-surface-variant font-medium">
                            <span class="material-symbols-outlined text-[15px] text-primary">event</span>
                            <span>{{ __t('appointment_date') }}</span>
                        </div>
                        <span class="text-xs font-bold text-primary">{{ $appointedAt }}</span>
                    </div>

                    {{-- CTA: View Profile --}}
                    <a href="{{ route('profile.edit', $user->id) }}"
                       class="profile-link-btn w-full text-white text-sm font-bold py-3 rounded-2xl flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">account_circle</span>
                        <span>{{ __t('view_profile') }}</span>
                        <span class="material-symbols-outlined text-[16px] {{ $dir === 'rtl' ? 'scale-x-[-1]' : '' }}">arrow_back</span>
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Bottom Info Banner --}}
        <div class="mt-12 rounded-3xl overflow-hidden" style="background: linear-gradient(135deg, #0a2218 0%, #1a5235 60%, #0d3522 100%); direction: {{ $dir }};">
            <div class="gold-divider"></div>
            <div class="px-8 py-8 flex flex-col md:flex-row items-center gap-6 {{ $textAlign }}">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center" style="background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.25);">
                        <span class="material-symbols-outlined text-[36px]" style="color:#d4af37">shield_with_heart</span>
                    </div>
                </div>
                <div class="flex-grow">
                    <h3 class="text-white font-bold text-lg mb-2">{{ __t('role_of_sage_committee') }}</h3>
                    <p class="text-white/75 text-sm leading-relaxed">
                        {{ __t('role_of_sage_committee_desc') }}
                    </p>
                </div>
                <div class="flex-shrink-0 md:{{ $textAlignInverse }} {{ $textAlign }}">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-4 py-2 rounded-full"
                          style="background: rgba(212,175,55,0.15); border: 1px solid rgba(212,175,55,0.3); color:#d4af37;">
                        <span class="material-symbols-outlined text-[14px]">verified</span>
                        {{ __t('officially_certified') }}
                    </span>
                </div>
            </div>
            <div class="gold-divider"></div>
        </div>
    @endif

</div>
@endsection
