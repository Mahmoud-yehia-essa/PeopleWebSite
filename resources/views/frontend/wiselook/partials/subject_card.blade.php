@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
    $textAlignInverse = $dir === 'rtl' ? 'text-left' : 'text-right';

    $subjectUser = $subject->user;
    $subjectAvatar = url('upload/no_image.jpg');
    if ($subjectUser && $subjectUser->profile_picture && $subjectUser->profile_picture !== 'non') {
        $subjectAvatar = filter_var($subjectUser->profile_picture, FILTER_VALIDATE_URL)
            ? $subjectUser->profile_picture
            : asset('new_wiselook/uploads/' . $subjectUser->profile_picture);
    }
    $subjectAuthorName = $subjectUser ? trim($subjectUser->first_name . ' ' . $subjectUser->last_name) : __t('unknown_user');
    
    // Check if current user supported this subject
    $userLiked = false;
    if (auth()->check()) {
        $userLiked = $subject->reactions->where('user_id', auth()->id())->where('type', 'like')->isNotEmpty();
    }
    
    // Check if isMember
    if (!isset($isMember)) {
        $isMember = false;
        if (auth()->check()) {
            $isMember = \App\Models\GroupSiteUser::where('group_site_id', $subject->group_site_id)
                ->where('user_id', auth()->id())
                ->exists();
        }
    }
    
    // Check if group is passed
    if (!isset($group)) {
        $group = $subject->groupSite;
    }
    if (!$group) {
        $group = \App\Models\GroupSite::find($subject->group_site_id);
    }
@endphp

<article class="wisdom-card overflow-hidden bg-white rounded-2xl border border-[#E1E8E1] shadow-sm mb-6" data-subject-id="{{ $subject->id }}" style="direction: {{ $dir }};">
    <div class="p-6">
        <div class="flex justify-between items-start mb-4">
            <div class="flex gap-3 {{ $textAlign }}">
                @if($subjectUser)
                    <a href="{{ route('profile.edit', $subjectUser->id) }}" class="w-12 h-12 rounded-full overflow-hidden border border-outline-variant shrink-0 block hover:opacity-90 transition-opacity">
                        <img class="w-full h-full object-cover" src="{{ $subjectAvatar }}" alt="{{ $subjectAuthorName }}">
                    </a>
                @else
                    <div class="w-12 h-12 rounded-full overflow-hidden border border-outline-variant shrink-0">
                        <img class="w-full h-full object-cover" src="{{ $subjectAvatar }}" alt="{{ $subjectAuthorName }}">
                    </div>
                @endif
                <div class="{{ $textAlign }}">
                    @if($subjectUser)
                        <a href="{{ route('profile.edit', $subjectUser->id) }}" class="hover:underline text-primary">
                            <p class="font-title-lg text-sm font-bold">{{ $subjectAuthorName }}</p>
                        </a>
                    @else
                        <p class="font-title-lg text-primary text-sm font-bold">{{ $subjectAuthorName }}</p>
                    @endif
                    <p class="font-label-sm text-[10px] text-on-surface-variant mt-0.5">
                        {{ $subject->created_at ? $subject->created_at->diffForHumans() : __t('now') }}
                    </p>
                </div>
            </div>
            
            @if(auth()->check() && ($subject->user_id === auth()->id() || ($group && $group->admin_user_id === auth()->id())))
                <!-- Delete Button -->
                <button class="delete-subject-btn text-error hover:bg-error/10 p-2 rounded-full transition-all cursor-pointer border-0 bg-transparent flex items-center justify-center" data-subject-id="{{ $subject->id }}" title="{{ __t('delete_topic') }}">
                    <span class="material-symbols-outlined text-[20px]">delete</span>
                </button>
            @endif
        </div>
        
        <h4 class="font-title-lg text-sm font-bold text-primary mb-2 {{ $textAlign }}">{{ $subject->title }}</h4>
        <div class="post-text-container mb-4 {{ $textAlign }}">
            <div class="post-text-content line-clamp-4 overflow-hidden font-body-lg text-sm text-on-surface leading-[1.8] whitespace-pre-line {{ $textAlign }}">
                {!! \App\Models\GroupSubject::formatHashtags($subject->description) !!}
            </div>
            <button class="show-more-btn hidden text-primary font-bold text-xs mt-2 hover:underline focus:outline-none bg-transparent border-0 cursor-pointer block {{ $textAlign }} w-full">{{ __t('show_more') }}</button>
        </div>
    </div>
    
    <!-- Attachment if exists -->
    @if($subject->attachment_path)
        @php
            $mediaUrl = filter_var($subject->attachment_path, FILTER_VALIDATE_URL)
                ? $subject->attachment_path
                : asset('upload/group_subjects/' . $subject->attachment_path);
        @endphp
        <div class="w-full bg-surface flex items-center justify-center border-t border-primary/5">
            @if($subject->attachment_type === 'image')
                <img class="w-full max-h-[450px] object-cover" src="{{ $mediaUrl }}" alt="{{ $subject->title }}">
            @elseif($subject->attachment_type === 'video')
                <video class="w-full max-h-[450px] object-contain bg-black" controls>
                    <source src="{{ $mediaUrl }}">
                    {{ __t('browser_video_unsupported') }}
                </video>
            @elseif($subject->attachment_type === 'audio')
                <div class="w-full p-4 bg-surface-container flex items-center justify-center">
                    <audio class="w-full" controls>
                        <source src="{{ $mediaUrl }}">
                    </audio>
                </div>
            @endif
        </div>
    @endif
    
    <!-- Actions Section -->
    <div class="p-6 border-t border-[#E1E8E1]">
        <div class="flex justify-between items-center mb-4">
            <button class="open-supporters-btn flex items-center gap-2 hover:underline cursor-pointer bg-transparent border-0 {{ $textAlign }}" 
                    data-post-id="{{ $subject->id }}" 
                    data-is-group-subject="true"
                    data-total-supports="{{ $subject->likes }}">
                <div class="w-6 h-6 rounded-full bg-primary-container flex items-center justify-center text-[10px] text-white">👍</div>
                <span class="text-on-surface-variant font-label-md text-xs {{ $dir === 'rtl' ? 'mr-1.5' : 'ml-1.5' }}">
                    <span class="like-counter">{{ $subject->likes }}</span> 
                    {{ $subject->likes == 1 ? __t('supporter') : __t('supporters') }}
                </span>
            </button>
        </div>
        
        <div class="flex border-t border-primary/5 pt-4">
            <!-- Support Button -->
            @if(auth()->check() && $isMember)
                <button class="subject-support-btn flex-grow flex items-center justify-center gap-2 py-2 hover:bg-surface rounded-lg transition-all text-on-surface-variant text-xs font-bold cursor-pointer bg-transparent border-0"
                        data-subject-id="{{ $subject->id }}"
                        data-active="{{ $userLiked ? 'true' : 'false' }}">
                    <span class="material-symbols-outlined {{ $userLiked ? 'fill-1 text-primary' : '' }}">lightbulb</span>
                    <span>{{ __t('support_btn') }}</span>
                </button>
            @elseif(!auth()->check())
                <button class="subject-support-btn flex-grow flex items-center justify-center gap-2 py-2 hover:bg-surface rounded-lg transition-all text-on-surface-variant text-xs font-bold cursor-pointer bg-transparent border-0"
                        data-subject-id="{{ $subject->id }}"
                        data-active="false">
                    <span class="material-symbols-outlined">lightbulb</span>
                    <span>{{ __t('support_btn') }}</span>
                </button>
            @else
                <button class="flex-grow flex items-center justify-center gap-2 py-2 hover:bg-surface rounded-lg transition-all text-on-surface-variant/40 text-xs font-bold cursor-not-allowed bg-transparent border-0" disabled>
                    <span class="material-symbols-outlined">lightbulb</span>
                    <span>{{ __t('support_btn') }}</span>
                </button>
            @endif
            
            <!-- Comment / discussion button -->
            <button class="open-discussion-btn flex-grow flex items-center justify-center gap-2 py-2 hover:bg-surface rounded-lg transition-all text-on-surface-variant text-xs font-bold cursor-pointer bg-transparent border-0"
                    data-post-id="{{ $subject->id }}"
                    data-author-name="{{ $subjectAuthorName }}"
                    data-author-avatar="{{ $subjectAvatar }}"
                    data-post-title="{{ $subject->title }}"
                    data-post-snippet="{{ Str::limit($subject->description, 120) }}"
                    data-is-group-subject="true">
                <span class="material-symbols-outlined">forum</span>
                <span class="font-label-sm text-label-sm">
                    {{ $subject->comments ? $subject->comments->count() : 0 }} 
                    {{ __t('discussion') }}
                </span>
            </button>
        </div>
    </div>
</article>
