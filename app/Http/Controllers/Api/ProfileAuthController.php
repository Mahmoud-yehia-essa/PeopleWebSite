<?php

namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Str;
// use App\Models\User;
// use App\Models\PhoneVerification;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // <--- مضاف لدعم رفع الملفات والصور
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PhoneVerification;
use App\Models\Friendship; // <--- مضاف لفحص حالة الصداقة في الـ Profile




class ProfileAuthController extends Controller
{
    /**
     * 1.1 تسجيل الدخول
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'token'    => 'nullable|string', // FCM Token
            'lang'     => 'nullable|string|in:ar,en'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // البحث بالـ email بناءً على مستند الـ API (بعد إلغاء حقل username)
        $user = User::where('email', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $msg = ($request->lang === 'en') ? 'Invalid credentials' : 'بيانات الدخول غير صحيحة';
            return response()->json(['success' => false, 'message' => $msg], 401);
        }

        // تحديث رمز إشعارات Firebase لحفظه بالخادم
        if ($request->has('token')) {
            $user->update(['token' => $request->token]);
        }

        // توليد الـ Access Token الجديد عبر Sanctum لتأمين الجلسة
        $accessToken = $user->createToken('WiselookAuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Login successful' : 'تم تسجيل الدخول بنجاح',
            'access_token' => $accessToken, // مضاف لتأمين الـ Flutter برمجياً
            'data' => [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'username'        => $user->email, // إرجاع البريد الإلكتروني كـ username للتوافق مع واجهة التطبيق
                'email'           => $user->email,
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'cover_picture'   => $user->cover_picture,
                'phone_number'    => $user->phone_number
            ]
        ]);
    }

    /**
     * 1.2 إنشاء حساب جديد
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_type' => 'nullable|string|in:email,phone',
            'email'             => 'required_if:verification_type,email|nullable|email|unique:users,email',
            'password'          => 'required|string|min:6',
            'first_name'        => 'required|string|max:50',
            'last_name'         => 'required|string|max:50',
            'phone_number'      => 'required_if:verification_type,phone|nullable|string|max:20',
            'token'             => 'nullable|string',
            'lang'              => 'nullable|string',
            'is_verified'       => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // حفظ المستخدم الجديد وتشفير كلمات المرور للحقلين تماشياً مع قاعدة بياناتك
        $user = User::create([
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'password_hash' => md5($request->password), // للتوافق التام والرجوع الآمن للأنظمة القديمة
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'phone_number'  => $request->phone_number,
            'token'         => $request->token,
            'is_verified'   => $request->is_verified,
            'status'        => 1,
            'is_active'     => 1
        ]);

        $accessToken = $user->createToken('WiselookAuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Registration successful' : 'تم إنشاء الحساب بنجاح',
            'access_token' => $accessToken,
            'data' => [
                'id'           => (int)$user->id,
                'first_name'   => $user->first_name,
                'last_name'    => $user->last_name,
                'email'        => $user->email,
                'phone_number' => $user->phone_number
            ]
        ], 201);
    }

    /**
     * 1.3 تسجيل الخروج
     */
    public function logout(Request $request)
    {
        // إبطال وتدمير الـ Token الحالي المستخدم من الموبايل فوراً
        $request->user()->currentAccessToken()->delete();

        // إبطال حقل الـ token الإشعارات لعدم إرسال إشعارات بعد الخروج
        $request->user()->update(['token' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * 1.4 حذف الحساب نهائياً
     */
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        
        // استخدام Soft Delete أو Delete نهائي متوافق مع هيكلتك
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Account deleted successfully' : 'تم حذف الحساب بنجاح'
        ]);
    }

    /**
     * 1.5 إرسال رمز التحقق OTP
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|string|in:phone,email',
            'phone_number' => 'required_if:type,phone|string',
            'code'         => 'required_if:type,phone|string', // رمز الدولة
            'email'        => 'required_if:type,email|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->type === 'phone') {
            $countryCode = $request->code;
            $mobileNumber = $request->phone_number;

            $messageCentral = new \App\Services\MessageCentralService();
            $result = $messageCentral->sendOtp($countryCode, $mobileNumber);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

            $verificationId = $result['verification_id'];
            $fullPhoneNumber = ltrim($countryCode, '+') . ltrim($mobileNumber, '0');

            // حفظ البيانات في جدول phone_verifications لتعقب الجلسة
            PhoneVerification::create([
                'verification_id' => $verificationId,
                'phone_number'    => $fullPhoneNumber,
                'otp_code'        => $result['otp'] ?? '000000',
                'expires_at'      => now()->addMinutes(10),
                'used'            => 0,
                'verified'        => 0
            ]);

            return response()->json([
                'success'         => true,
                'verification_id' => $verificationId,
                'message'         => 'OTP sent successfully via WhatsApp'
            ]);
        } else {
            $target = $request->email;
            $otpCode = (string) rand(100000, 999995);
            $verificationId = Str::uuid()->toString();

            PhoneVerification::create([
                'verification_id' => $verificationId,
                'phone_number'    => $target,
                'otp_code'        => $otpCode,
                'expires_at'      => now()->addMinutes(10),
                'used'            => 0,
                'verified'        => 0
            ]);

            \Illuminate\Support\Facades\Log::info("OTP Code generated for email {$target}: {$otpCode}");

            return response()->json([
                'success'         => true,
                'verification_id' => $verificationId,
                'otp'             => $otpCode,
                'message'         => 'OTP sent successfully'
            ]);
        }
    }

    /**
     * 1.6 التحقق من الـ OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|string',
            'otp_code'        => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // فحص وجود الجلسة وصلاحيتها
        $verification = PhoneVerification::where('verification_id', $request->verification_id)
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP session'], 400);
        }

        $isVerified = false;

        if (!str_contains($verification->phone_number, '@')) {
            // للتحقق من الهاتف، نرسل طلب للبوابة
            $messageCentral = new \App\Services\MessageCentralService();
            $isVerified = $messageCentral->verifyOtp($request->verification_id, $request->otp_code);
        } else {
            // للبريد الإلكتروني نقارنه محلياً
            $isVerified = ($verification->otp_code === $request->otp_code);
        }

        if (!$isVerified) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired OTP code'], 400);
        }

        // تحديث الجلسة كـ موثقة ومستخدمة بالكامل
        $verification->update([
            'used'        => 1,
            'verified'    => 1,
            'verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP Verified successfully'
        ]);
    }


    /**
     * 1.9 تغيير كلمة المرور من الإعدادات
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        // تحديث الحقلين المشفرين لحفظ الأمان والتزامن التام
        $user->update([
            'password'      => Hash::make($request->new_password),
            'password_hash' => md5($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }





public function changeProfile(Request $request)
    {
        // التحقق من المدخلات (النصوص والصور اختياري تماشياً مع الـ Native)
        $validator = Validator::make($request->all(), [
            'first_name'      => 'nullable|string|max:50',
            'last_name'       => 'nullable|string|max:50',
            'email'           => 'nullable|email|unique:users,email,' . $request->user()->id,
            'phone_number'    => 'nullable|string|max:20',
            'cover_text'      => 'nullable|string', // سيتم حفظه في حقل bio المتواجد بقاعدة بياناتك
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'cover_picture'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $user = $request->user();

        // تحديث الحقول النصية إذا تم إرسالها
        if ($request->has('first_name')) $user->first_name = $request->first_name;
        if ($request->has('last_name'))  $user->last_name = $request->last_name;
        if ($request->has('email'))      $user->email = $request->email;
        if ($request->has('phone_number')) $user->phone_number = $request->phone_number;
        if ($request->has('cover_text'))  $user->bio = $request->cover_text; // ربط النص بحقل الـ bio

        // معالجة ورفع الصورة الشخصية (Profile Picture)
        if ($request->hasFile('profile_picture')) {
            // حذف الصورة القديمة إذا كانت موجودة لتوفير المساحة
            if ($user->profile_picture) {
                Storage::disk('public')->delete(str_replace(asset('storage/'), '', $user->profile_picture));
            }
            $profilePath = $request->file('profile_picture')->store('profiles', 'public');
            $user->profile_picture = asset('storage/' . $profilePath); // إرجاع رابط كامل للموبايل
        }

        // معالجة ورفع صورة الغلاف (Cover Picture)
        if ($request->hasFile('cover_picture')) {
            if ($user->cover_picture) {
                Storage::disk('public')->delete(str_replace(asset('storage/'), '', $user->cover_picture));
            }
            $coverPath = $request->file('cover_picture')->store('covers', 'public');
            $user->cover_picture = asset('storage/' . $coverPath);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'cover_picture'   => $user->cover_picture
            ]
        ]);
    }

    /**
     * 1.8 جلب بيانات ملف مستخدم معين
     */
    public function viewProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_id' => 'required|integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $currentUser = $request->user();
        $targetUser = User::find($request->profile_id);

        // التحقق من حالة الصداقة بين المستخدم الحالي والمستهدف في جدول friendships
        $isFriend = Friendship::where(function ($query) use ($currentUser, $targetUser) {
                        $query->where('sender_id', $currentUser->id)->where('receiver_id', $targetUser->id);
                    })->orWhere(function ($query) use ($currentUser, $targetUser) {
                        $query->where('sender_id', $targetUser->id)->where('receiver_id', $currentUser->id);
                    })
                    ->where('is_active', 1) // 1 تعني صديق نشط
                    ->exists();

        // بناء الاستجابة داخل مصفوفة (Array) تحت مفتاح 'user' لتطابق تماماً الـ Native القديم
        return response()->json([
            'user' => [
                [
                    'id'              => (int)$targetUser->id,
                    'first_name'      => $targetUser->first_name,
                    'last_name'       => $targetUser->last_name,
                    'profile_picture' => $targetUser->profile_picture ?: asset('images/default_profile.png'),
                    'cover_picture'   => $targetUser->cover_picture,
                    'is_friend'       => $isFriend,
                    'bio'             => $targetUser->bio ?? '',
                    'followers_count' => (int)$targetUser->friend_count // مساوية لعدد الأصدقاء بناءً على هيكلة جدولك
                ]
            ]
        ]);
    }

    /**
     * 1.10 إرسال رمز استعادة كلمة المرور
     */
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'  => 'required|string',
            'method' => 'required|string|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $target = $request->email;
        $method = $request->method; // 'email' or 'phone'

        // التحقق من وجود المستخدم أولاً قبل الإرسال
        $userField = ($method === 'email') ? 'email' : 'phone_number';
        $userExists = User::where($userField, $target)->exists();
        if (!$userExists) {
            $msg = ($request->lang === 'en') ? 'User not found' : 'المستخدم غير موجود';
            return response()->json(['success' => false, 'message' => $msg], 404);
        }

        $otpCode = (string) rand(100000, 999995);
        $verificationId = Str::uuid()->toString();

        PhoneVerification::create([
            'verification_id' => $verificationId,
            'phone_number'    => $target,
            'otp_code'        => $otpCode,
            'expires_at'      => now()->addMinutes(10),
            'used'            => 0,
            'verified'        => 0
        ]);

        // لغايات التجربة المحلية وعدم إرسال بريد حقيقي
        \Illuminate\Support\Facades\Log::info("Password Reset Code generated for {$target}: {$otpCode}");

        return response()->json([
            'success'         => true,
            'verification_id' => $verificationId,
            'otp'             => $otpCode, // للتجربة المحلية
            'message'         => 'Verification code sent successfully'
        ]);
    }

    /**
     * 1.11 إعادة تعيين كلمة المرور
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'           => 'required|string',
            'reset_code'      => 'required|string|size:6',
            'new_password'    => 'required|string|min:6',
            'verification_id' => 'required|string',
            'method'          => 'required|string|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // التحقق من صلاحية وجودة الرمز المدخل
        $verification = PhoneVerification::where('verification_id', $request->verification_id)
            ->where('phone_number', $request->email)
            ->where('otp_code', $request->reset_code)
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired verification code'], 400);
        }

        // البحث عن المستخدم لتغيير كلمة مروره
        $userField = ($request->method === 'email') ? 'email' : 'phone_number';
        $user = User::where($userField, $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        // تحديث كلمة المرور للحقلين
        $user->update([
            'password'      => Hash::make($request->new_password),
            'password_hash' => md5($request->new_password)
        ]);

        // تحديث حالة الجلسة لمستعمل ومكتمل
        $verification->update([
            'used'        => 1,
            'verified'    => 1,
            'verified_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Password has been reset successfully' : 'تم إعادة تعيين كلمة المرور بنجاح'
        ]);
    }





}