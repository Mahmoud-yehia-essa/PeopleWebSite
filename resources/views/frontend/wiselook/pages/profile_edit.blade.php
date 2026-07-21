@extends('frontend.wiselook.master_dashboard')

@section('main')
@php
    $dir = current_language()->direction ?? 'rtl';
    $textAlign = $dir === 'rtl' ? 'text-right' : 'text-left';
@endphp
<!-- Main Container -->
<div class="pt-24 px-margin-mobile md:px-margin-desktop max-w-container-max-width mx-auto pb-24 {{ $textAlign }}" style="direction: {{ $dir }};">
    
    <!-- Page Header -->
    <div class="mb-8 bg-surface-container-lowest/70 backdrop-blur-[20px] rounded-2xl p-6 border border-primary/10 shadow-sm max-w-3xl mx-auto">
        <h1 class="font-headline-lg text-xl md:text-2xl font-bold text-primary">{{ __t('edit_profile') }}</h1>
        <p class="font-body-md text-xs text-on-surface-variant mt-1">{{ __t('edit_profile_desc') }}</p>
    </div>

    <!-- Edit Profile Form Card -->
    <div class="bg-white rounded-2xl border border-primary/10 shadow-sm overflow-hidden max-w-3xl mx-auto">
        <form action="{{ route('profile.update_form') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Cover Photo Upload Container -->
            <div class="w-full h-48 md:h-64 relative bg-gray-100 overflow-hidden group">
                <!-- Cover Preview Image -->
                @php
                    $coverUrl = (!empty($user->cover_picture) && $user->cover_picture != 'non') 
                        ? asset('new_wiselook/uploads/' . $user->cover_picture) 
                        : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1964&auto=format&fit=crop';
                @endphp
                <img id="cover-preview" alt="Cover Photo" class="w-full h-full object-cover" src="{{ $coverUrl }}">
                
                <!-- Hover Upload Button -->
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <label for="cover-upload" class="cursor-pointer bg-white/20 hover:bg-white/30 text-white border border-white/40 px-4 py-2 rounded-lg font-bold text-xs flex items-center gap-2 backdrop-blur-sm transition-all">
                        <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                        <span>{{ __t('change_cover_picture') }}</span>
                    </label>
                    <input id="cover-upload" name="cover_photo" type="file" class="hidden" accept="image/*">
                </div>
            </div>

            @error('cover_photo')
                <div class="px-6 pt-4 {{ $textAlign }}">
                    <span class="text-red-600 text-xs font-bold bg-red-50 border border-red-200 rounded-lg px-3 py-1.5 inline-block">{{ $message }}</span>
                </div>
            @enderror

            <!-- Profile Info Container -->
            <div class="px-6 md:px-8 pb-8 relative">
                <!-- Profile Avatar Upload -->
                <div class="relative -mt-16 mb-6 flex justify-start">
                    <div class="w-32 h-32 rounded-full border-4 border-white bg-surface-container-high overflow-hidden shadow-lg relative group/avatar shrink-0">
                        @php
                            $avatarUrl = url('upload/no_image.jpg');
                            if ($user->profile_picture && $user->profile_picture !== 'non') {
                                $avatarUrl = filter_var($user->profile_picture, FILTER_VALIDATE_URL)
                                    ? $user->profile_picture
                                    : asset('new_wiselook/uploads/' . $user->profile_picture);
                            }
                        @endphp
                        <img id="avatar-preview" alt="Avatar" class="w-full h-full object-cover" src="{{ $avatarUrl }}">
                        
                        <!-- Hover Upload Avatar Button -->
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover/avatar:opacity-100 transition-opacity duration-300">
                            <label for="avatar-upload" class="cursor-pointer text-white">
                                <span class="material-symbols-outlined text-[24px]">photo_camera</span>
                            </label>
                            <input id="avatar-upload" name="photo" type="file" class="hidden" accept="image/*">
                        </div>
                    </div>
                </div>

                @error('photo')
                    <div class="mb-6 {{ $textAlign }}">
                        <span class="text-red-600 text-xs font-bold bg-red-50 border border-red-200 rounded-lg px-3 py-1.5 inline-block">{{ $message }}</span>
                    </div>
                @enderror

                <!-- Input Fields Grid -->
                <div class="space-y-6">
                    <!-- Names Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div class="flex flex-col gap-1.5">
                            <label for="fname" class="text-xs font-bold text-primary">{{ __t('first_name') }}</label>
                            <input id="fname" name="fname" type="text" value="{{ old('fname', $user->first_name) }}" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" required>
                            @error('fname') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Last Name -->
                        <div class="flex flex-col gap-1.5">
                            <label for="lname" class="text-xs font-bold text-primary">{{ __t('last_name') }}</label>
                            <input id="lname" name="lname" type="text" value="{{ old('lname', $user->last_name) }}" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" required>
                            @error('lname') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Email & Phone Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-xs font-bold text-primary">{{ __t('email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" required>
                            @error('email') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <!-- Phone Number & Country Code -->
                        <div class="flex flex-col gap-1.5">
                            <label for="phone" class="text-xs font-bold text-primary">{{ __t('phone_number') }}</label>
                            <div class="flex gap-2">
                                <!-- Phone input -->
                                <div class="flex-grow">
                                    <input id="phone" name="phone" dir="ltr" type="text" value="{{ old('phone', $user->phone) }}" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" placeholder="51234567">
                                </div>
                                <!-- Country prefix select -->
                                <div class="w-32 shrink-0">
                                    <select name="country_data" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-3 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}">
                                        @foreach($countryList as $country)
                                            <option {{ $country['dial'] === $user->country_code ? 'selected' : '' }} value="{{ json_encode(['dial' => $country['dial'], 'code' => $country['code'], 'name' => $country['name'], 'flag' => $country['flag']]) }}">
                                                {{ $country['code'] }} {{ $country['flag'] }} {{ $country['dial'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('phone') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                            @error('country_data') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="flex flex-col gap-1.5">
                        <label for="address" class="text-xs font-bold text-primary">{{ __t('address_and_country') }}</label>
                        <input id="address" name="address" type="text" value="{{ old('address', $user->address) }}" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" placeholder="{{ __t('address_placeholder') }}">
                        @error('address') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Bio -->
                    <div class="flex flex-col gap-1.5">
                        <label for="bio" class="text-xs font-bold text-primary">{{ __t('bio_label') }}</label>
                        <textarea id="bio" name="bio" rows="4" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }} leading-relaxed" placeholder="{{ __t('bio_placeholder') }}">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password Fields Grid -->
                    <div class="border-t border-primary/5 pt-6 mt-6">
                        <h4 class="text-xs font-bold text-primary mb-4 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[18px]">lock</span>
                            <span>{{ __t('change_password_optional') }}</span>
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- New Password -->
                            <div class="flex flex-col gap-1.5">
                                <label for="password" class="text-[11px] font-semibold text-on-surface-variant">{{ __t('new_password') }}</label>
                                <input id="password" name="password" type="password" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" autocomplete="new-password">
                                @error('password') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="flex flex-col gap-1.5">
                                <label for="password_confirmation" class="text-[11px] font-semibold text-on-surface-variant">{{ __t('confirm_password') }}</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="w-full bg-[#f2f4f0]/40 border border-primary/10 rounded-xl py-2.5 px-4 font-body-md text-xs focus:ring-1 focus:ring-primary focus:border-primary focus:bg-white transition-all {{ $textAlign }}" autocomplete="new-password">
                                @error('password_confirmation') <span class="text-danger text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end">
                    <button type="submit" class="w-full md:w-auto px-8 py-3 bg-primary hover:bg-primary-container text-white rounded-xl font-label-md text-xs font-bold transition-all shadow-sm cursor-pointer text-center">
                        {{ __t('save_changes') }}
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Live preview for profile avatar picture
    $('#avatar-upload').change(function(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#avatar-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    });

    // Live preview for cover photo
    $('#cover-upload').change(function(e) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#cover-preview').attr('src', e.target.result);
        }
        reader.readAsDataURL(e.target.files[0]);
    });
});
</script>
@endpush
