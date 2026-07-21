@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';
@endphp
<div id="network-grid-meta" data-has-more="{{ ($hasMore ?? false) ? 'true' : 'false' }}" data-current-page="{{ $page ?? 1 }}" class="hidden"></div>

@if($users->isEmpty())
    <div class="col-span-full bg-white rounded-2xl p-12 border border-primary/10 text-center w-full">
        <span class="material-symbols-outlined text-[64px] text-on-surface-variant opacity-40 mb-3">group</span>
        <h3 class="font-headline-lg text-base font-bold text-primary">{{ __t('no_contacts_title') }}</h3>
        <p class="font-body-md text-xs text-on-surface-variant mt-2 leading-relaxed">
            @if($filter === 'pending')
                {{ __t('no_incoming_requests_msg') }}
            @elseif($filter === 'sent_requests')
                {{ __t('no_sent_requests_msg') }}
            @elseif($filter === 'suggested')
                {{ __t('no_suggested_friends_msg') }}
            @else
                {{ __t('no_friends_matching_filter') }}
            @endif
        </p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 w-full col-span-full">
        @foreach($users as $user)
            @php
                $userPhoto = (!empty($user->profile_picture) && $user->profile_picture != 'non') 
                    ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : asset('new_wiselook/uploads/'.$user->profile_picture)) 
                    : asset('upload/no_image.jpg');
                $fullName = $user->first_name . ' ' . $user->last_name;
            @endphp
            <div class="bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between {{ $textAlign }} network-card" data-user-id="{{ $user->id }}" style="direction: {{ $dir }};">
                <div>
                    <div class="flex gap-4 {{ $textAlign }}">
                        <!-- Avatar -->
                        <div class="w-20 h-20 rounded-full bg-surface-container overflow-hidden border-2 border-primary/10 relative shrink-0">
                            <a href="{{ route('profile.edit', $user->id) }}">
                                <img alt="{{ $user->first_name }}" class="w-full h-full object-cover hover:opacity-90 transition-opacity" src="{{ $userPhoto }}">
                            </a>
                        </div>
                        <div class="flex-grow min-w-0">
                            <div class="flex justify-between items-start">
                                <div class="truncate">
                                    <a href="{{ route('profile.edit', $user->id) }}" class="hover:underline hover:text-primary transition-all">
                                        <h3 class="font-title-lg text-sm font-bold text-primary flex items-center gap-1 truncate">
                                            <span>{{ $fullName }}</span>
                                            @if($user->role == 'admin')
                                                <span class="material-symbols-outlined text-secondary text-[16px]">verified</span>
                                            @endif
                                        </h3>
                                    </a>
                                    <p class="font-body-md text-xs text-on-surface-variant line-clamp-1 mt-0.5" dir="ltr">{{ $user->email }}</p>
                                    @if($filter === 'suggested')
                                        <p class="text-[11px] text-on-surface-variant mt-1">{{ __t('wisdom_member') }}</p>
                                        @if($user->mutual_count > 0)
                                            <p class="text-[11px] text-on-surface-variant mt-0.5">
                                                <span class="mutual-friends-trigger cursor-pointer text-primary hover:underline font-semibold" data-user-id="{{ $user->id }}" data-user-name="{{ $fullName }}">
                                                    {{ $user->mutual_count }} {{ $user->mutual_count == 1 ? __t('mutual_friend') : __t('mutual_friends') }}
                                                </span>
                                            </p>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 inline-flex items-center gap-1 bg-secondary/10 text-secondary px-2.5 py-0.5 rounded-full font-label-sm text-[10px] font-bold">
                                <span class="material-symbols-outlined text-[14px]">workspace_premium</span>
                                <span>{{ $user->points ?? 0 }} {{ __t('point_unit') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                @if(($user->friendship_type ?? '') === 'pending_received')
                    <div class="mt-6 flex gap-3">
                        <form action="{{ route('frontend.friendships.accept', $user->friendship_id) }}" method="POST" class="flex-1 friendship-action-form" data-action="accept">
                            @csrf
                            <button type="submit" class="w-full bg-primary text-white hover:bg-primary-container text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors shadow-sm cursor-pointer flex items-center justify-center">
                                {{ __t('accept_request') }}
                            </button>
                        </form>
                        <form action="{{ route('frontend.friendships.delete', $user->friendship_id) }}" method="POST" class="flex-1 friendship-action-form" data-action="reject">
                            @csrf
                            <button type="submit" class="w-full bg-transparent border-2 border-error text-error hover:bg-error/5 text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors cursor-pointer flex items-center justify-center">
                                {{ __t('reject_request') }}
                            </button>
                        </form>
                    </div>
                @elseif(($user->friendship_type ?? '') === 'pending_sent')
                    <div class="mt-6 flex flex-col gap-2">
                        <div class="bg-surface p-2 rounded-xl text-center border border-primary/5 font-label-sm text-[11px] font-bold text-on-surface-variant flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-secondary">pending</span>
                            <span>{{ __t('request_status_pending') }}</span>
                        </div>
                        <form action="{{ route('frontend.friendships.delete', $user->friendship_id) }}" method="POST" class="w-full friendship-action-form" data-action="cancel">
                            @csrf
                            <button type="submit" class="w-full bg-error text-white hover:bg-error-dark text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors cursor-pointer flex items-center justify-center" onclick="return confirm('{{ addslashes(__t('confirm_cancel_friend_request')) }}')">
                                {{ __t('cancel_request') }}
                            </button>
                        </form>
                    </div>
                @elseif(($user->friendship_type ?? '') === 'suggested')
                    <div class="mt-6 flex gap-3">
                        <button class="w-full bg-primary text-white hover:bg-primary-container text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors shadow-sm cursor-pointer send-friend-request-btn flex items-center justify-center gap-1" data-receiver-id="{{ $user->id }}">
                            <span class="material-symbols-outlined text-sm">person_add</span>
                            <span>{{ __t('add_friend') }}</span>
                        </button>
                    </div>
                @else
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('frontend.messages', $user->id) }}" class="flex-1 bg-primary text-white hover:bg-primary-container text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors shadow-sm flex items-center justify-center">
                            {{ __t('send_message') }}
                        </a>
                        <form action="{{ route('frontend.friendships.delete', $user->friendship_id) }}" method="POST" class="flex-1 friendship-action-form" data-action="remove">
                            @csrf
                            <button type="submit" class="w-full bg-transparent border-2 border-error text-error hover:bg-error/5 text-center font-label-md text-xs font-bold py-2 rounded-lg transition-colors cursor-pointer flex items-center justify-center" onclick="return confirm('{{ addslashes(__t('confirm_remove_friend')) }}')">
                                {{ __t('remove_friend') }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
