@php
    $ratings = $post->wiseRatings ?? collect();
    $ratingsCount = $ratings->count();
    $avgRating = $ratingsCount > 0 ? $ratings->avg('rating') : null;
    $hasRatings = $ratingsCount > 0;
    $dir = current_language()->direction ?? 'rtl';

    // دالة لتحديد لون التقييم بناءً على قيمته
    $getRatingColor = function($rating) {
        if ($rating >= 8) return ['bg' => 'rgba(34,197,94,0.15)', 'text' => '#22c55e', 'border' => 'rgba(34,197,94,0.3)'];
        if ($rating >= 6) return ['bg' => 'rgba(234,179,8,0.15)', 'text' => '#eab308', 'border' => 'rgba(234,179,8,0.3)'];
        if ($rating >= 4) return ['bg' => 'rgba(249,115,22,0.15)', 'text' => '#f97316', 'border' => 'rgba(249,115,22,0.3)'];
        return ['bg' => 'rgba(239,68,68,0.15)', 'text' => '#ef4444', 'border' => 'rgba(239,68,68,0.3)'];
    };

    // لون متوسط التقييم
    $avgColor = $avgRating ? $getRatingColor($avgRating) : null;
@endphp

@if($hasRatings)
{{-- ============================================================
     قسم تقييم لجنة الحكماء - Premium Wise Committee Rating Block
     ============================================================ --}}
<div id="wise-rating-section" class="wise-rating-block relative overflow-hidden" style="
    background: linear-gradient(135deg, #071911 0%, #0c261a 40%, #113624 70%, #071911 100%);
    border-radius: 24px;
    border: 2px solid rgba(212,175,55,0.3);
    box-shadow: 0 12px 50px rgba(7,25,17,0.4), inset 0 1px 0 rgba(212,175,55,0.15);
    direction: {{ $dir }};
    margin-bottom: 24px;
">
    {{-- خلفية زخرفية --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden" style="z-index:0;">
        <div style="
            position:absolute; top:-40px; left:-40px;
            width:250px; height:250px; border-radius:50%;
            background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
        "></div>
        <div style="
            position:absolute; bottom:-30px; right:-30px;
            width:200px; height:200px; border-radius:50%;
            background: radial-gradient(circle, rgba(34,197,94,0.08) 0%, transparent 70%);
        "></div>
        {{-- نقاط زخرفية --}}
        <svg class="absolute inset-0 w-full h-full opacity-15" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="wise-dots" width="30" height="30" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#d4af37"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#wise-dots)"/>
        </svg>
    </div>

    {{-- ===== HEADER ===== --}}
    <div class="relative px-8 pt-8 pb-6" style="z-index:1;">
        {{-- شعار + عنوان --}}
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
            <div class="flex items-center gap-4">
                {{-- أيقونة المطرقة --}}
                <div class="wise-gavel-icon flex items-center justify-center animate-pulse" style="
                    width:56px; height:56px; border-radius:16px;
                    background: linear-gradient(135deg, rgba(212,175,55,0.25), rgba(240,208,96,0.15));
                    border: 1px solid rgba(212,175,55,0.4);
                    box-shadow: 0 6px 20px rgba(212,175,55,0.2);
                ">
                    <span class="material-symbols-outlined" style="color:#f0d060; font-size:32px; font-variation-settings:'FILL' 1,'wght' 300;">gavel</span>
                </div>
                <div>
                    <h3 class="font-bold text-white" style="font-size:20px; letter-spacing:-0.02em; font-family:inherit;">
                        تقييم لجنة الحكماء
                    </h3>
                    <p style="color:rgba(255,255,255,0.65); font-size:13px; margin-top:4px; font-weight: 500;">
                        {{ __t('wise_committee_rating_desc', ['count' => $ratingsCount]) }}
                    </p>
                </div>
            </div>

            {{-- شارة "معتمد" --}}
            <span class="wise-verified-badge inline-flex items-center gap-2 font-bold" style="
                background: rgba(212,175,55,0.15);
                border: 1px solid rgba(212,175,55,0.4);
                color: #f0d060;
                padding: 6px 16px;
                border-radius: 30px;
                font-size: 13px;
                white-space: nowrap;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            ">
                <span class="material-symbols-outlined" style="font-size:16px; font-variation-settings:'FILL' 1;">verified</span>
                تقييم معتمد رسمياً
            </span>
        </div>

        {{-- خط فاصل ذهبي --}}
        <div style="height:2px; background: linear-gradient(90deg, transparent, rgba(212,175,55,0.6), rgba(240,208,96,0.9), rgba(212,175,55,0.6), transparent); margin-bottom:24px;"></div>

        {{-- ===== متوسط التقييم الكبير ===== --}}
        <div class="avg-rating-showcase flex flex-col md:flex-row items-center gap-8 mb-4">

            {{-- الدائرة الكبيرة للمتوسط --}}
            <div class="relative shrink-0 flex flex-col items-center">
                <div class="avg-circle-wrap" style="position:relative; width:130px; height:130px;">
                    {{-- SVG دائرة تقدم --}}
                    <svg viewBox="0 0 130 130" width="130" height="130" style="transform: rotate(-90deg);">
                        <circle cx="65" cy="65" r="56" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="10"/>
                        @php
                            $pct = ($avgRating / 10) * 100;
                            $circumference = 2 * pi() * 56;
                            $offset = $circumference * (1 - $pct / 100);
                        @endphp
                        <circle cx="65" cy="65" r="56" fill="none"
                            stroke="url(#goldGrad)"
                            stroke-width="10"
                            stroke-linecap="round"
                            stroke-dasharray="{{ number_format($circumference, 2) }}"
                            stroke-dashoffset="{{ number_format($offset, 2) }}"
                            style="transition: stroke-dashoffset 1.5s cubic-bezier(.4,0,.2,1);"
                        />
                        <defs>
                            <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#d4af37"/>
                                <stop offset="100%" stop-color="#f5e080"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    {{-- الرقم بالداخل --}}
                    <div style="
                        position:absolute; inset:0;
                        display:flex; flex-direction:column;
                        align-items:center; justify-content:center;
                        text-align:center;
                    ">
                        <span style="
                            font-size:36px; font-weight:900; color:#f0d060;
                            line-height:1; letter-spacing:-1px;
                            text-shadow: 0 2px 16px rgba(212,175,55,0.5);
                        ">{{ number_format($avgRating, 1) }}</span>
                        <span style="color:rgba(255,255,255,0.5); font-size:13px; font-weight:700; margin-top:4px;">/ 10</span>
                    </div>
                </div>

                {{-- النجوم تحت الدائرة --}}
                <div class="flex items-center gap-1 mt-3">
                    @for($s = 1; $s <= 10; $s++)
                        @php
                            $filled = $s <= round($avgRating);
                        @endphp
                        @if($s <= 5)
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="{{ $filled ? '#f0d060' : 'none' }}" stroke="{{ $filled ? '#f0d060' : 'rgba(212,175,55,0.3)' }}" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        @endif
                    @endfor
                    <span style="color:rgba(255,255,255,0.5); font-size:12px; font-weight:700; margin-right:6px;">({{ $ratingsCount }})</span>
                </div>
            </div>

            {{-- إحصائيات جانبية --}}
            <div class="flex-1 w-full space-y-3.5">
                @php
                    // توزيع التقييمات
                    $distribution = [
                        ['label' => __t('rating_excellent') . ' (8-10)', 'min' => 8, 'max' => 10, 'color' => '#22c55e'],
                        ['label' => __t('rating_good') . ' (6-7)', 'min' => 6, 'max' => 7.99, 'color' => '#eab308'],
                        ['label' => __t('rating_acceptable') . ' (4-5)', 'min' => 4, 'max' => 5.99, 'color' => '#f97316'],
                        ['label' => __t('rating_weak') . ' (1-3)', 'min' => 1, 'max' => 3.99, 'color' => '#ef4444'],
                    ];
                @endphp

                @foreach($distribution as $dist)
                    @php
                        $count = $ratings->filter(fn($r) => $r->rating >= $dist['min'] && $r->rating <= $dist['max'])->count();
                        $pctBar = $ratingsCount > 0 ? ($count / $ratingsCount) * 100 : 0;
                    @endphp
                    <div class="flex items-center gap-3">
                        <span style="color:rgba(255,255,255,0.7); font-size:13px; min-width:95px; text-align:{{ $dir === 'rtl' ? 'right' : 'left' }}; font-weight:600; white-space:nowrap;">{{ $dist['label'] }}</span>
                        <div style="flex:1; height:8px; background:rgba(255,255,255,0.08); border-radius:10px; overflow:hidden;">
                            <div style="
                                height:100%; width:{{ $pctBar }}%; border-radius:10px;
                                background: {{ $dist['color'] }};
                                transition: width 1.2s ease;
                                opacity:0.9;
                            "></div>
                        </div>
                        <span style="color:rgba(255,255,255,0.6); font-size:12px; min-width:25px; text-align:{{ $dir === 'rtl' ? 'left' : 'right' }}; font-weight:700;">{{ $count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== تقييمات كل حكيم ===== --}}
    @if($ratings->isNotEmpty())
    <div class="relative px-8 pb-8" style="z-index:1;">
        {{-- خط فاصل --}}
        <div style="height:1px; background: rgba(255,255,255,0.08); margin-bottom:22px;"></div>

        <h4 style="color:rgba(255,255,255,0.75); font-size:14px; font-weight:800; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:18px; display:flex; align-items:center; gap:8px;">
            <span class="material-symbols-outlined" style="font-size:18px; color:#f0d060;">group</span>
            {{ __t('detailed_ratings_comments_from_sages') }}
        </h4>

        <div class="wise-ratings-grid space-y-4">
            @foreach($ratings as $rating)
                @php
                    $rUser = $rating->user;
                    $rName = $rUser ? trim($rUser->first_name . ' ' . $rUser->last_name) : __t('wise_scholar');
                    $rAvatar = url('upload/no_image.jpg');
                    if ($rUser && $rUser->profile_picture && $rUser->profile_picture !== 'non') {
                        $rAvatar = filter_var($rUser->profile_picture, FILTER_VALIDATE_URL)
                            ? $rUser->profile_picture
                            : asset('new_wiselook/uploads/' . $rUser->profile_picture);
                    }
                    $rColor = $getRatingColor($rating->rating);
                    $rBarWidth = ($rating->rating / 10) * 100;
                    $rDate = $rating->created_at ? $rating->created_at->diffForHumans() : '';
                    $hasReason = !empty($rating->reason);
                @endphp

                <div class="wise-rater-card" style="
                    background: rgba(255,255,255,0.05);
                    border: 1px solid rgba(255,255,255,0.09);
                    border-radius: 18px;
                    padding: 18px 20px;
                    transition: all 0.3s ease;
                ">
                    {{-- صف المعلومات --}}
                    <div class="flex items-start gap-4">
                        {{-- صورة الحكيم --}}
                        <div class="shrink-0 relative">
                            @if($rUser)
                                <a href="{{ route('profile.edit', $rUser->id) }}">
                                    <img src="{{ $rAvatar }}" alt="{{ $rName }}"
                                         class="rounded-full object-cover"
                                         style="width:52px; height:52px; border: 2.5px solid rgba(212,175,55,0.45); box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                </a>
                            @else
                                <img src="{{ $rAvatar }}" alt="{{ $rName }}"
                                     class="rounded-full object-cover"
                                     style="width:52px; height:52px; border: 2.5px solid rgba(212,175,55,0.45); box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                            @endif
                            {{-- شارة حكيم صغيرة --}}
                            <div style="
                                position:absolute; bottom:-3px; left:-3px;
                                width:22px; height:22px; border-radius:50%;
                                background: linear-gradient(135deg,#d4af37,#f0d060);
                                border: 2.5px solid #071911;
                                display:flex; align-items:center; justify-content:center;
                            ">
                                <span class="material-symbols-outlined" style="font-size:12px; color:#071911; font-variation-settings:'FILL' 1;">workspace_premium</span>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            {{-- اسم الحكيم + التقييم --}}
                            <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                                <div>
                                    @if($rUser)
                                        <a href="{{ route('profile.edit', $rUser->id) }}" class="hover:underline">
                                            <span style="color:#fff; font-weight:800; font-size:15px; letter-spacing:-0.01em;">{{ $rName }}</span>
                                        </a>
                                    @else
                                        <span style="color:#fff; font-weight:800; font-size:15px; letter-spacing:-0.01em;">{{ $rName }}</span>
                                    @endif
                                    @if($rDate)
                                        <span style="color:rgba(255,255,255,0.4); font-size:12px; margin-right:8px; font-weight:500;">• {{ $rDate }}</span>
                                    @endif
                                </div>

                                {{-- شارة الدرجة --}}
                                <div class="shrink-0 flex items-center gap-1.5 font-extrabold" style="
                                    background: {{ $rColor['bg'] }};
                                    border: 1px solid {{ $rColor['border'] }};
                                    color: {{ $rColor['text'] }};
                                    padding: 5px 14px; border-radius: 20px;
                                    font-size: 15px; white-space: nowrap;
                                    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                                ">
                                    <span class="material-symbols-outlined" style="font-size:16px; font-variation-settings:'FILL' 1;">star</span>
                                    {{ number_format($rating->rating, 1) }}<span style="opacity:0.75; font-size:11px; font-weight:600; margin-right:2px;">/10</span>
                                </div>
                            </div>

                            {{-- شريط التقدم --}}
                            <div style="height:6px; background:rgba(255,255,255,0.06); border-radius:10px; overflow:hidden; margin-bottom: {{ $hasReason ? '12px' : '0' }};">
                                <div style="
                                    height:100%; width:{{ $rBarWidth }}%; border-radius:10px;
                                    background: linear-gradient(90deg, {{ $rColor['text'] }}, {{ $rColor['text'] }}bb);
                                    transition: width 1.2s cubic-bezier(.4,0,.2,1);
                                "></div>
                            </div>

                            {{-- تعليق الحكيم إن وجد --}}
                            @if($hasReason)
                                <div class="wise-reason-block" style="
                                    background: rgba(255,255,255,0.04);
                                    border-{{ $dir === 'rtl' ? 'right' : 'left' }}: 4px solid {{ $rColor['text'] }};
                                    border-radius: {{ $dir === 'rtl' ? '0 10px 10px 0' : '10px 0 0 10px' }};
                                    padding: 12px 16px;
                                    position: relative;
                                    box-shadow: inset 1px 1px 0 rgba(255,255,255,0.03);
                                ">
                                    <div class="flex items-start gap-2.5">
                                        <span class="material-symbols-outlined shrink-0" style="font-size:18px; color:#f0d060; margin-top:2px; font-variation-settings:'FILL' 1;">format_quote</span>
                                        <p style="color:rgba(255,255,255,0.85); font-size:14px; line-height:1.8; font-style:italic; font-weight:500;">
                                            {{ $rating->reason }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- تذييل التقييم --}}
        <div class="mt-6" style="
            background: rgba(212,175,55,0.08);
            border: 1px solid rgba(212,175,55,0.2);
            border-radius: 16px;
            padding: 14px 20px;
            display: flex; align-items: center; gap: 12px;
        ">
            <span class="material-symbols-outlined" style="font-size:22px; color:#f0d060; flex-shrink:0; font-variation-settings:'FILL' 1;">shield_with_heart</span>
            <p style="color:rgba(255,255,255,0.55); font-size:12.5px; line-height:1.7; font-weight: 500;">
                {{ __t('wise_committee_footer_notice') }}
            </p>
        </div>
    </div>
    @endif

</div>
@endif

