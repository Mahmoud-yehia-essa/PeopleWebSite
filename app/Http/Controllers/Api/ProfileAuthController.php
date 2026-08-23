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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PhoneVerification;
use App\Models\Friendship; // <--- مضاف لفحص حالة الصداقة في الـ Profile
use Illuminate\Support\Facades\DB;
use App\Services\BrevoMailService;




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

        // البحث بالـ email أو رقم الهاتف مع دعم كافة الصيغ
        $username = trim($request->username);
        $user = User::where('email', $username)
            ->orWhere('phone_number', $username)
            ->orWhere('phone_number', ltrim($username, '+'))
            ->orWhere('phone_number', '+' . ltrim($username, '+'))
            ->first();

        $passwordValid = false;
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $passwordValid = true;
            } elseif (!empty($user->password_hash) && $user->password_hash === md5($request->password)) {
                $passwordValid = true;
                $user->update(['password' => Hash::make($request->password)]);
            } elseif ($user->password === md5($request->password)) {
                $passwordValid = true;
                $user->update(['password' => Hash::make($request->password)]);
            }
        }

        if (!$user || !$passwordValid) {
            $msg = ($request->lang === 'en') ? 'Invalid credentials' : 'بيانات الدخول غير صحيحة';
            return response()->json(['success' => false, 'message' => $msg], 401);
        }

        // تحديث رمز إشعارات Firebase لحفظه بالخادم
        if ($request->has('token')) {
            $user->update(['token' => $request->token]);
        }

        // توليد الـ Access Token الجديد عبر Sanctum لتأمين الجلسة
        // معالجة مكافأة الإحالة وزيادة نقاط المسوق
        $refCode = $request->referral_code ?? $request->ref ?? session('affiliate_ref');
        \App\Http\Controllers\AffiliateController::processReferralReward($user, $refCode, $request->ip());

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
     * 1.3 تسجيل الدخول أو إنشاء حساب عبر Google (للتطبيق والـ API)
     */
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'           => 'required|email',
            'google_id'       => 'nullable|string',
            'first_name'      => 'nullable|string|max:100',
            'last_name'       => 'nullable|string|max:100',
            'profile_picture' => 'nullable|string',
            'token'           => 'nullable|string', // FCM Token
            'lang'            => 'nullable|string|in:ar,en'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        $googleId = $request->google_id;

        // البحث عن المستخدم باستخدام google_id أولاً ثم البريد الإلكتروني
        $user = null;
        if (!empty($googleId)) {
            $user = User::where('google_id', $googleId)->first();
        }
        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // تحديث الـ google_id والـ provider و FCM Token إن لزم
            $updates = [];
            if (empty($user->google_id) && !empty($googleId)) {
                $updates['google_id'] = $googleId;
                $updates['provider'] = 'google';
            }
            if ($request->has('token') && !empty($request->token)) {
                $updates['token'] = $request->token;
            }
            if (empty($user->profile_picture) && !empty($request->profile_picture)) {
                $updates['profile_picture'] = $request->profile_picture;
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
        } else {
            // استخراج الاسم
            $firstName = $request->first_name;
            $lastName = $request->last_name;

            if (empty($firstName)) {
                $parts = explode('@', $email);
                $firstName = $parts[0] ?? 'مستخدم';
                $lastName = 'جوجل';
            }

            $user = User::create([
                'first_name'      => $firstName,
                'last_name'       => $lastName ?: 'جوجل',
                'email'           => $email,
                'google_id'       => $googleId,
                'provider'        => 'google',
                'password'        => Hash::make(Str::random(24)),
                'password_hash'   => md5(Str::random(24)),
                'status'          => 1,
                'is_active'       => 1,
                'is_verified'     => 1,
                'profile_picture' => $request->profile_picture ?? null,
                'token'           => $request->token ?? null,
            ]);
        }

        $accessToken = $user->createToken('WiselookAuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Google login successful' : 'تم تسجيل الدخول بنجاح عبر Google',
            'access_token' => $accessToken,
            'data' => [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'username'        => $user->email,
                'email'           => $user->email,
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'cover_picture'   => $user->cover_picture,
                'phone_number'    => $user->phone_number
            ]
        ]);
    }

    /**
     * تحديث رمز إشعارات الهاتف (FCM Token)
     */

    /**
     * 1.4 تسجيل الدخول أو إنشاء حساب عبر Apple (للتطبيق والـ API)
     */
    public function appleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'apple_id'          => 'nullable|string',
            'email'             => 'nullable|email',
            'first_name'        => 'nullable|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'identity_token'    => 'nullable|string',
            'authorization_code'=> 'nullable|string',
            'token'             => 'nullable|string', // FCM Token
            'lang'              => 'nullable|string|in:ar,en'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $appleId = $request->apple_id;
        $email = $request->email;
        $firstName = $request->first_name;
        $lastName = $request->last_name;

        // فك واستخراج المعرف والبريد من identity_token (JWT Payload) إن لم يأتيا كحقول صريحة
        if ((empty($appleId) || empty($email)) && !empty($request->identity_token)) {
            try {
                $tokenParts = explode('.', $request->identity_token);
                if (count($tokenParts) >= 2) {
                    $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
                    $jwtPayload = json_decode($payloadJson, true);
                    if (is_array($jwtPayload)) {
                        if (empty($appleId) && !empty($jwtPayload['sub'])) {
                            $appleId = $jwtPayload['sub'];
                        }
                        if (empty($email) && !empty($jwtPayload['email'])) {
                            $email = $jwtPayload['email'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // تجاهل أخطاء فك التوكن
            }
        }

        if (empty($appleId) && empty($email)) {
            $msg = ($request->lang === 'en') 
                ? 'Could not retrieve Apple user identifier or email.' 
                : 'لم نتمكن من الحصول على معرف حساب Apple أو البريد الإلكتروني.';
            return response()->json(['success' => false, 'message' => $msg], 422);
        }

        // البحث عن المستخدم باستخدام apple_id أولاً ثم البريد الإلكتروني
        $user = null;
        if (!empty($appleId)) {
            $user = User::where('apple_id', $appleId)->first();
        }
        if (!$user && !empty($email)) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            // مستخدم مسجل مسبقاً (تسجيل دخول)
            $updates = [];
            if (empty($user->apple_id) && !empty($appleId)) {
                $updates['apple_id'] = $appleId;
                $updates['provider'] = 'apple';
            }
            if ($request->has('token') && !empty($request->token)) {
                $updates['token'] = $request->token;
            }
            if (!empty($updates)) {
                $user->update($updates);
            }
        } else {
            // مستخدم غير مسجل (إنشاء حساب جديد تلقائياً)
            if (empty($firstName)) {
                if (!empty($email)) {
                    $parts = explode('@', $email);
                    $firstName = $parts[0] ?? 'مستخدم';
                } else {
                    $firstName = 'مستخدم';
                }
                $lastName = 'Apple';
            }

            if (empty($email)) {
                $email = 'apple_' . substr(md5($appleId ?? Str::random(10)), 0, 10) . '@privaterelay.appleid.com';
            }

            $user = User::create([
                'first_name'      => $firstName,
                'last_name'       => $lastName ?: 'Apple',
                'email'           => $email,
                'apple_id'        => $appleId,
                'provider'        => 'apple',
                'password'        => Hash::make(Str::random(24)),
                'password_hash'   => md5(Str::random(24)),
                'status'          => 1,
                'is_active'       => 1,
                'is_verified'     => 1,
                'profile_picture' => null,
                'token'           => $request->token ?? null,
            ]);
        }

        // معالجة مكافأة الإحالة وزيادة نقاط المسوق
        $refCode = $request->referral_code ?? $request->ref ?? session('affiliate_ref');
        \App\Http\Controllers\AffiliateController::processReferralReward($user, $refCode, $request->ip());

        $accessToken = $user->createToken('WiselookAuthToken')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => ($request->lang === 'en') ? 'Apple sign-in successful' : 'تم تسجيل الدخول بنجاح عبر Apple',
            'access_token' => $accessToken,
            'data' => [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'username'        => $user->email,
                'email'           => $user->email,
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'cover_picture'   => $user->cover_picture,
                'phone_number'    => $user->phone_number
            ]
        ]);
    }

    public function updateToken(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->user()?->id;
        $token = $request->input('token');

        if ($userId && $token) {
            User::where('id', $userId)->update(['token' => $token]);
            return response()->json([
                'success' => true,
                'message' => 'Token updated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User ID and token required'
        ], 400);
    }

    /**
     * 1.3 تسجيل الخروج
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user && $request->filled('id')) {
            $user = User::find($request->input('id'));
        }

        if ($user) {
            if ($user->currentAccessToken() && method_exists($user->currentAccessToken(), 'delete')) {
                try {
                    $user->currentAccessToken()->delete();
                } catch (\Throwable $e) {}
            }

            try {
                $user->update(['token' => null]);
            } catch (\Throwable $e) {}
        }

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
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user && $request->filled('id')) {
            $user = User::find($request->input('id'));
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }
        
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
     * 1.7 إرسال رمز التحقق عبر واتساب (WhatsApp OTP) باستخدام TextMeBot
     * POST /api/profile/send_whatsapp_otp.php
     */
    public function sendWhatsappOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'        => 'required|string',
            'is_register'  => 'nullable|boolean',
            'country_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $phone = trim($request->input('phone'));
        $isRegister = $request->boolean('is_register');

        // تنظيف وتوحيد رقم الهاتف
        if (!str_starts_with($phone, '+')) {
            if ($request->filled('country_code')) {
                $code = trim($request->input('country_code'));
                if (!str_starts_with($code, '+')) $code = '+' . $code;
                $phone = $code . ltrim($phone, '0');
            } else {
                $phone = '+' . $phone;
            }
        }

        $cleanPhone = ltrim($phone, '+');

        // في حالة تسجيل حساب جديد، نتأكد أن الرقم غير مسجل مسبقاً
        if ($isRegister) {
            $exists = User::where('phone_number', $phone)
                ->orWhere('phone_number', $cleanPhone)
                ->orWhere('phone_number', '+' . $cleanPhone)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف هذا مسجل بالفعل. يرجى تسجيل الدخول.',
                ], 400);
            }
        }

        // توليد رمز OTP مكون من 6 أرقام
        $otp = (string) rand(100000, 999999);
        $verificationId = Str::uuid()->toString();

        // حذف / إلغاء الجلسات القديمة غير المستخدمة لهذا الرقم
        PhoneVerification::where(function ($q) use ($phone, $cleanPhone) {
            $q->where('phone_number', $phone)
              ->orWhere('phone_number', $cleanPhone)
              ->orWhere('phone_number', '+' . $cleanPhone);
        })->where('used', 0)->delete();

        // حفظ رمز التحقق في جدول phone_verifications
        PhoneVerification::create([
            'verification_id' => $verificationId,
            'phone_number'    => $phone,
            'otp_code'        => $otp,
            'expires_at'      => now()->addMinutes(10),
            'used'            => 0,
            'verified'        => 0,
        ]);

        try {
            $apiUrl = config('services.textmebot.url', 'http://api.textmebot.com/send.php');
            $apiKey = config('services.textmebot.api_key', 'zh9d51Rp9csh');

            $response = Http::timeout(15)->get($apiUrl, [
                'recipient' => $phone,
                'apikey'    => $apiKey,
                'text'      => 'رمز التحقق الخاص بك في حكماء العالم هو : ' . $otp,
            ]);

            Log::info('API WhatsApp OTP sent via TextMeBot', [
                'phone'  => $phone,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'success'         => true,
                'verification_id' => $verificationId,
                'message'         => 'تم إرسال رمز التحقق عبر الواتساب بنجاح.',
            ]);
        } catch (\Throwable $e) {
            Log::error('API TextMeBot error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال رسالة الواتساب. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }

    /**
     * 1.8 التحقق من رمز الواتساب وتسجيل الدخول / إنشاء الحساب
     * POST /api/profile/verify_whatsapp_otp.php
     */
    public function verifyWhatsappOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone'           => 'required|string',
            'code'            => 'required|string|size:6',
            'verification_id' => 'nullable|string',
            'is_register'     => 'nullable|boolean',
            'first_name'      => 'nullable|string|max:50',
            'last_name'       => 'nullable|string|max:50',
            'token'           => 'nullable|string', // FCM Token
            'lang'            => 'nullable|string|in:ar,en'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $phone = trim($request->input('phone'));
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        $cleanPhone = ltrim($phone, '+');
        $code = trim($request->input('code'));
        $isRegister = $request->boolean('is_register');

        // البحث عن سجل التحقق
        $verificationQuery = PhoneVerification::where('used', 0)
            ->where('expires_at', '>', now());

        if ($request->filled('verification_id')) {
            $verification = (clone $verificationQuery)->where('verification_id', $request->input('verification_id'))->first();
        } else {
            $verification = null;
        }

        if (!$verification) {
            $verification = (clone $verificationQuery)->where(function ($q) use ($phone, $cleanPhone) {
                $q->where('phone_number', $phone)
                  ->orWhere('phone_number', $cleanPhone)
                  ->orWhere('phone_number', '+' . $cleanPhone);
            })->orderBy('created_at', 'desc')->first();
        }

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'جلسة التحقق منتهية الصلاحية أو غير موجودة. يرجى طلب رمز جديد.',
            ], 400);
        }

        if ($verification->otp_code !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح.',
            ], 400);
        }

        // تحديث جلسة التحقق كمستخدمة وموثقة
        $verification->update([
            'used'        => 1,
            'verified'    => 1,
            'verified_at' => now(),
        ]);

        // البحث عن المستخدم
        $user = User::where('phone_number', $phone)
            ->orWhere('phone_number', $cleanPhone)
            ->orWhere('phone_number', '+' . $cleanPhone)
            ->first();

        if ($isRegister && $user) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف هذا مسجل بالفعل. يرجى تسجيل الدخول.',
            ], 400);
        }

        $firstName = $request->input('first_name') ?: ($request->input('fname') ?: 'مستخدم');
        $lastName  = $request->input('last_name')  ?: ($request->input('lname')  ?: 'جديد');

        if (!$user) {
            $randomPassword = Str::random(24);
            $user = User::create([
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => null,
                'password'      => Hash::make($randomPassword),
                'password_hash' => md5($randomPassword),
                'phone_number'  => $phone,
                'status'        => 1,
                'is_active'     => 1,
                'is_verified'   => 1,
                'token'         => $request->input('token') ?? null,
            ]);
        } else {
            // تحديث رمز FCM إن وُجد
            if ($request->filled('token')) {
                $user->update(['token' => $request->token]);
            }
        }

        // توليد Sanctum Token
        $accessToken = $user->createToken('WiselookAuthToken')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => $isRegister ? 'تم إنشاء الحساب بنجاح' : 'تم تسجيل الدخول بنجاح',
            'access_token' => $accessToken,
            'data'         => [
                'id'              => (int)$user->id,
                'first_name'      => $user->first_name,
                'last_name'       => $user->last_name,
                'username'        => $user->email ?? $user->phone_number,
                'email'           => $user->email,
                'profile_picture' => $user->profile_picture ?: asset('images/default_profile.png'),
                'cover_picture'   => $user->cover_picture,
                'phone_number'    => $user->phone_number
            ]
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
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->input('user_id'));
        }
        if (!$user && $request->filled('id')) {
            $user = User::find($request->input('id'));
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

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
        $user = $request->user();
        if (!$user && $request->input('user_id')) {
            $user = User::find($request->input('user_id'));
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        // التحقق من المدخلات (رقم الهاتف والنصوص والصور اختيارية)
        $validator = Validator::make($request->all(), [
            'first_name'      => 'nullable|string|max:50',
            'last_name'       => 'nullable|string|max:50',
            'email'           => 'nullable|email|unique:users,email,' . $user->id,
            'phone_number'    => 'nullable|string|max:30',
            'bio'             => 'nullable|string',
            'cover_text'      => 'nullable|string',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
            'cover_picture'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // تحديث الحقول النصية إذا تم إرسالها
        if ($request->has('first_name')) $user->first_name = $request->first_name;
        if ($request->has('last_name'))  $user->last_name = $request->last_name;
        if ($request->has('email'))      $user->email = $request->email;
        if ($request->has('phone_number')) $user->phone_number = $request->phone_number;
        if ($request->has('bio'))        $user->bio = $request->bio;
        if ($request->has('cover_text'))  $user->bio = $request->cover_text;

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
        $profileId = $request->input('profile_id') ?? $request->input('id');

        if (!$profileId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile ID is required',
                'user'    => null
            ], 200);
        }

        $targetUser = User::find($profileId);

        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود',
                'user'    => null
            ], 200);
        }

        $currentUser = $request->user() ?? auth('sanctum')->user() ?? ($request->has('id') && $request->id != $profileId ? User::find($request->id) : null);

        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        $isFriend = false;
        $friendActionType = 'add';
        $blockedByMe = 0;
        $blockedMe = 0;
        $canInteract = 1;

        if ($currentUser) {
            if ($currentUser->id == $targetUser->id) {
                $friendActionType = 'self';
            } else {
                $friendship = Friendship::where(function ($query) use ($currentUser, $targetUser) {
                                $query->where('sender_id', $currentUser->id)->where('receiver_id', $targetUser->id);
                            })->orWhere(function ($query) use ($currentUser, $targetUser) {
                                $query->where('sender_id', $targetUser->id)->where('receiver_id', $currentUser->id);
                            })->first();

                if ($friendship) {
                    if ($friendship->is_active == 1) {
                        $isFriend = true;
                        $friendActionType = 'remove';
                    } else {
                        if ($friendship->sender_id == $currentUser->id) {
                            $friendActionType = 'cancel';
                        } else {
                            $friendActionType = 'confirm';
                        }
                    }
                }

                // فحص الحظر
                if (class_exists(\App\Models\Block::class)) {
                    $blockedByMe = \App\Models\Block::where('blocker_id', $currentUser->id)->where('blocked_id', $targetUser->id)->exists() ? 1 : 0;
                    $blockedMe = \App\Models\Block::where('blocker_id', $targetUser->id)->where('blocked_id', $currentUser->id)->exists() ? 1 : 0;
                    if ($blockedByMe || $blockedMe) {
                        $canInteract = 0;
                    }
                }
            }
        }

        $postCount = \App\Models\Post::where('user_id', $targetUser->id)->where('is_active', 1)->count();
        $friendCount = Friendship::where('is_active', 1)
            ->where(function($q) use ($targetUser) {
                $q->where('sender_id', $targetUser->id)
                  ->orWhere('receiver_id', $targetUser->id);
            })->count();

        // رتبة الحكيم وحساب التقدم
        $allRankings = \App\Models\Ranking::orderBy('rank_order', 'asc')->get();
        $userPoints = (int)($targetUser->points ?? 0);
        $currentRank = $targetUser->rank;
        $nextRank = $targetUser->next_rank;

        $rankName = null;
        $rankIcon = null;
        if ($currentRank) {
            $rankName = $currentRank->rank_name;
            if (!empty($currentRank->photo)) {
                $rankIcon = asset('upload/rankings/' . $currentRank->photo);
            }
        } elseif ($targetUser->role == 'admin') {
            $rankName = 'مدير المنصة';
        } elseif ($targetUser->role == 'owner') {
            $rankName = 'مالك المنصة';
        } else {
            $rankName = 'مستشار تقني';
        }

        $rankProgress = [
            'current_points'        => $userPoints,
            'current_rank_id'       => $currentRank ? $currentRank->id : null,
            'current_rank_name'     => $currentRank ? $currentRank->rank_name : ($rankName ?: 'حكيم مستوى أول'),
            'current_rank_desc'     => $currentRank ? ($currentRank->rank_description ?? '') : '',
            'current_rank_icon'     => $rankIcon,
            'current_rank_order'    => $currentRank ? (int)$currentRank->rank_order : 1,
            'current_rank_start'    => $currentRank ? (int)$currentRank->rank_start_point : 1,
            'current_rank_end'      => $currentRank && $currentRank->rank_end_point !== null ? (int)$currentRank->rank_end_point : null,
            'is_max_rank'           => $nextRank ? false : ($currentRank && $currentRank->is_last ? true : false),
            'next_rank_id'          => $nextRank ? $nextRank->id : null,
            'next_rank_name'        => $nextRank ? $nextRank->rank_name : null,
            'next_rank_desc'        => $nextRank ? ($nextRank->rank_description ?? '') : null,
            'next_rank_icon'        => $nextRank && !empty($nextRank->photo) ? asset('upload/rankings/' . $nextRank->photo) : null,
            'next_rank_start'       => $nextRank ? (int)$nextRank->rank_start_point : null,
            'points_remaining'      => $nextRank ? max(0, (int)$nextRank->rank_start_point - $userPoints) : 0,
            'progress_percentage'   => 0.0,
            'encouragement_message' => '',
        ];

        $isEn = ($request->input('lang') == 'en' || $request->header('Accept-Language') == 'en');
        if ($currentRank && $nextRank) {
            $range = (int)$nextRank->rank_start_point - (int)$currentRank->rank_start_point;
            $earned = max(0, $userPoints - (int)$currentRank->rank_start_point);
            $pct = $range > 0 ? min(1.0, round($earned / $range, 3)) : 1.0;
            $rankProgress['progress_percentage'] = $pct;
            $remaining = $rankProgress['points_remaining'];
            $rankProgress['encouragement_message'] = $isEn
                ? "You are only {$remaining} points away from advancing to {$nextRank->rank_name}! Keep sharing your valuable thoughts."
                : "أنت على بُعد {$remaining} نقطة فقط من الارتقاء إلى {$nextRank->rank_name}! استمر في مشاركة أفكارك القيمة.";
        } elseif ($currentRank && $currentRank->is_last) {
            $rankProgress['progress_percentage'] = 1.0;
            $rankProgress['encouragement_message'] = $isEn
                ? "Congratulations! You have reached the apex rank of wisdom and profound knowledge."
                : "مبارك! لقد بلغت أعلى رتب الحكمة في المنصة ورسوخ المعرفة.";
        } else {
            $rankProgress['progress_percentage'] = 0.0;
            $rankProgress['encouragement_message'] = $isEn
                ? "Start writing and sharing your valuable thoughts to advance through the wisdom ranks."
                : "ابدأ بكتابة ومشاركة أفكارك القيمة للارتقاء في رتب الحكمة.";
        }

        $allRankingsList = $allRankings->map(function($r) use ($userPoints, $currentRank) {
            $isCurrent = $currentRank && $currentRank->id === $r->id;
            $isUnlocked = $userPoints >= (int)$r->rank_start_point;
            return [
                'id'                  => (int)$r->id,
                'rank_name'           => $r->rank_name,
                'rank_description'    => $r->rank_description ?? '',
                'rank_order'          => (int)$r->rank_order,
                'rank_start_point'    => (int)$r->rank_start_point,
                'rank_end_point'      => $r->rank_end_point !== null ? (int)$r->rank_end_point : null,
                'level_reward_amount' => (int)($r->level_reward_amount ?? 0),
                'is_last'             => (bool)$r->is_last,
                'photo'               => !empty($r->photo) ? asset('upload/rankings/' . $r->photo) : null,
                'is_current'          => $isCurrent,
                'is_unlocked'         => $isUnlocked,
                'points_needed'       => max(0, (int)$r->rank_start_point - $userPoints),
            ];
        });

        // متوسط تقييم الحكماء وعدد المواضيع المقيمة
        $wiseRatingAvg = \App\Models\Post::where('user_id', $targetUser->id)
            ->where('is_active', 1)
            ->whereNotNull('wise_rating')
            ->where('wise_rating', '>', 0)
            ->avg('wise_rating');

        $wiseRatedPostsCount = \App\Models\Post::where('user_id', $targetUser->id)
            ->where('is_active', 1)
            ->whereNotNull('wise_rating')
            ->where('wise_rating', '>', 0)
            ->count();

        $coverPicture = (!empty($targetUser->cover_picture) && $targetUser->cover_picture != 'non')
            ? (filter_var($targetUser->cover_picture, FILTER_VALIDATE_URL) ? $targetUser->cover_picture : asset('new_wiselook/uploads/' . $targetUser->cover_picture))
            : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?q=80&w=1964&auto=format&fit=crop';

        $profilePicture = (!empty($targetUser->profile_picture) && $targetUser->profile_picture != 'non')
            ? (filter_var($targetUser->profile_picture, FILTER_VALIDATE_URL) ? $targetUser->profile_picture : asset('new_wiselook/uploads/' . $targetUser->profile_picture))
            : asset('images/default_profile.png');

        return response()->json([
            'user' => [
                [
                    'id'                  => (int)$targetUser->id,
                    'first_name'          => $targetUser->first_name ?? '',
                    'last_name'           => $targetUser->last_name ?? '',
                    'email'               => $targetUser->email ?? '',
                    'profile_picture'     => $profilePicture,
                    'cover_picture'       => $coverPicture,
                    'is_friend'           => $isFriend,
                    'type'                => $friendActionType,
                    'bio'                 => $targetUser->bio ?? '',
                    'date_joined'         => $targetUser->created_at ? $targetUser->created_at->format('Y-m-d') : 'N/A',
                    'points'              => (int)($targetUser->points ?? 0),
                    'user_points'         => (int)($targetUser->points ?? 0),
                    'rank_name'           => $rankName,
                    'user_rank_name'      => $rankName,
                    'rank_icon'           => $rankIcon,
                    'user_rank_icon'      => $rankIcon,
                    'rank_progress'       => $rankProgress,
                    'all_rankings'        => $allRankingsList,
                    'role'                => $targetUser->role ?? 'user',
                    'post_count'          => (int)$postCount,
                    'friend_count'        => (int)$friendCount,
                    'followers_count'     => (int)$friendCount,
                    'can_interact'        => $canInteract,
                    'blocked_by_me'       => $blockedByMe,
                    'blocked_me'          => $blockedMe,
                    'token'               => $targetUser->fcm_token ?? $targetUser->token ?? '',
                    'wise_rating_avg'     => $wiseRatingAvg ? round((float)$wiseRatingAvg, 1) : null,
                    'wise_rated_count'    => (int)$wiseRatedPostsCount,
                ]
            ]
        ]);
    }

    /**
     * 1.10 إرسال رمز استعادة كلمة المرور عبر البريد الإلكتروني أو الواتساب
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

        $target = trim(strtolower($request->email));
        $method = $request->method; // 'email' or 'phone'
        $otpCode = (string) rand(100000, 999999);
        $verificationId = Str::uuid()->toString();

        if ($method === 'email') {
            $user = User::where('email', $target)
                ->orWhereRaw('LOWER(email) = ?', [$target])
                ->first();

            // في حال لم يكن الإيميل موجوداً، ينشئ حساباً لتمكينه من الاستعادة وتجربة المنصة كما في الموقع
            if (!$user) {
                Log::info("API Password reset: Email {$target} not found in DB. Creating temporary record for reset testing.");
                $user = User::create([
                    'email'         => $target,
                    'first_name'    => 'مستخدم',
                    'last_name'     => 'جديد',
                    'password'      => Hash::make(Str::random(16)),
                    'password_hash' => md5(Str::random(16)),
                    'status'        => 1,
                    'is_active'     => 1,
                ]);
            }

            // حفظ كود الاستعادة للمستخدم
            $user->reset_code = $otpCode;
            $user->save();

            // تحديث جدول password_reset_tokens
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'email'      => $user->email,
                    'token'      => Hash::make($otpCode),
                    'created_at' => now(),
                ]
            );

            // حفظ الجلسة في phone_verifications
            PhoneVerification::where('phone_number', $target)->where('used', 0)->delete();
            PhoneVerification::create([
                'verification_id' => $verificationId,
                'phone_number'    => $target,
                'otp_code'        => $otpCode,
                'expires_at'      => now()->addMinutes(15),
                'used'            => 0,
                'verified'        => 0
            ]);

            $resetUrl = url('/reset-password?email=' . urlencode($user->email) . '&token=' . $otpCode);

            Log::info("API Attempting to send Brevo reset OTP code {$otpCode} to email: {$user->email}");

            try {
                BrevoMailService::sendResetCodeMail($user, $otpCode, $resetUrl);
                Log::info("API Brevo reset OTP mail sent successfully to {$user->email}");
            } catch (\Exception $e) {
                Log::error("API Failed to send Brevo password reset mail to {$user->email}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء إرسال البريد الإلكتروني: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success'         => true,
                'verification_id' => $verificationId,
                'message'         => 'تم إرسال كود التحقق بنجاح إلى بريدك الإلكتروني.'
            ]);
        } else {
            // طريقة الهاتف / الواتساب
            $phone = $target;
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }
            $cleanPhone = ltrim($phone, '+');

            $user = User::where('phone_number', $phone)
                ->orWhere('phone_number', $cleanPhone)
                ->orWhere('phone_number', '+' . $cleanPhone)
                ->first();

            if (!$user) {
                $msg = ($request->lang === 'en') ? 'Phone number not found' : 'رقم الهاتف غير مسجل لدينا في المنصة.';
                return response()->json(['success' => false, 'message' => $msg], 404);
            }

            PhoneVerification::where(function ($q) use ($phone, $cleanPhone) {
                $q->where('phone_number', $phone)
                  ->orWhere('phone_number', $cleanPhone)
                  ->orWhere('phone_number', '+' . $cleanPhone);
            })->where('used', 0)->delete();

            PhoneVerification::create([
                'verification_id' => $verificationId,
                'phone_number'    => $phone,
                'otp_code'        => $otpCode,
                'expires_at'      => now()->addMinutes(15),
                'used'            => 0,
                'verified'        => 0
            ]);

            try {
                $apiUrl = config('services.textmebot.url', 'http://api.textmebot.com/send.php');
                $apiKey = config('services.textmebot.api_key', 'zh9d51Rp9csh');

                Http::timeout(15)->get($apiUrl, [
                    'recipient' => $phone,
                    'apikey'    => $apiKey,
                    'text'      => 'رمز استعادة كلمة المرور الخاص بك في حكماء العالم هو : ' . $otpCode,
                ]);

                return response()->json([
                    'success'         => true,
                    'verification_id' => $verificationId,
                    'message'         => 'تم إرسال رمز استعادة كلمة المرور عبر الواتساب بنجاح.'
                ]);
            } catch (\Throwable $e) {
                Log::error('TextMeBot error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'تعذر إرسال رمز التحقق عبر الواتساب.'
                ], 500);
            }
        }
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
            'verification_id' => 'nullable|string',
            'method'          => 'required|string|in:email,phone'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $target = trim(strtolower($request->email));
        $code = trim($request->reset_code);
        $method = $request->method;

        if ($method === 'email') {
            $user = User::where('email', $target)
                ->orWhereRaw('LOWER(email) = ?', [$target])
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
            }

            // التحقق من الجلسة في phone_verifications أو الكود المباشر في reset_code
            $verification = PhoneVerification::where('phone_number', $target)
                ->where('otp_code', $code)
                ->where('used', 0)
                ->where('expires_at', '>', now())
                ->first();

            $isCodeValid = ($verification !== null) || (!empty($user->reset_code) && $user->reset_code === $code);

            if (!$isCodeValid) {
                return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400);
            }

            // تحديث كلمة المرور للحقلين
            $user->update([
                'password'      => Hash::make($request->new_password),
                'password_hash' => md5($request->new_password),
                'reset_code'    => null,
            ]);

            if ($verification) {
                $verification->update([
                    'used'        => 1,
                    'verified'    => 1,
                    'verified_at' => now()
                ]);
            }

            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            return response()->json([
                'success' => true,
                'message' => ($request->lang === 'en') ? 'Password has been reset successfully' : 'تم إعادة تعيين كلمة المرور بنجاح'
            ]);
        } else {
            // الهاتف
            $phone = $target;
            if (!str_starts_with($phone, '+')) {
                $phone = '+' . $phone;
            }
            $cleanPhone = ltrim($phone, '+');

            $verification = PhoneVerification::where(function ($q) use ($phone, $cleanPhone) {
                $q->where('phone_number', $phone)
                  ->orWhere('phone_number', $cleanPhone)
                  ->orWhere('phone_number', '+' . $cleanPhone);
            })
            ->where('otp_code', $code)
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

            if (!$verification) {
                return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية'], 400);
            }

            $user = User::where('phone_number', $phone)
                ->orWhere('phone_number', $cleanPhone)
                ->orWhere('phone_number', '+' . $cleanPhone)
                ->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
            }

            $user->update([
                'password'      => Hash::make($request->new_password),
                'password_hash' => md5($request->new_password)
            ]);

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

    /**
     * جلب سجل نقاط التقييم التفصيلي للمستخدم لتعريضه في الـ Popup
     */
    public function getPointsDetails(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('id');
        if (!$userId) {
            $user = $request->user() ?? auth('sanctum')->user();
            $userId = $user ? $user->id : null;
        }

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User ID is required'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $logs = \App\Models\WisePointLog::where('recipient_user_id', $userId)
            ->with(['wiseUser', 'post'])
            ->latest()
            ->get();

        $formattedLogs = $logs->map(function($log) {
            return [
                'id' => (int)$log->id,
                'points_given' => (int)$log->points_given,
                'wise_name' => $log->wiseUser ? trim($log->wiseUser->first_name . ' ' . $log->wiseUser->last_name) : 'حكيم منصة',
                'note' => $log->note ?? 'لا توجد ملاحظات إضافية.',
                'post_id' => $log->post_id ? (int)$log->post_id : null,
                'post_snippet' => $log->post ? \Illuminate\Support\Str::limit(strip_tags($log->post->content), 80) : null,
                'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i') : '',
                'diff' => $log->created_at ? $log->created_at->diffForHumans() : ''
            ];
        });

        return response()->json([
            'success' => true,
            'user_name' => trim($user->first_name . ' ' . $user->last_name),
            'total_points' => (int)($user->points ?? 0),
            'logs' => $formattedLogs
        ]);
    }

    /**
     * 1.15 استرجاع جميع رتب الحكمة المتاحة مع التقدم
     */
    public function getAllRankings(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('id');
        $user = null;
        if ($userId) {
            $user = User::find($userId);
        } else {
            $user = $request->user() ?? auth('sanctum')->user();
        }

        $userPoints = $user ? (int)($user->points ?? 0) : 0;
        $currentRank = $user ? $user->rank : null;
        $nextRank = $user ? $user->next_rank : null;

        $rankings = \App\Models\Ranking::orderBy('rank_order', 'asc')->get();

        $rankProgress = [
            'current_points'        => $userPoints,
            'current_rank_id'       => $currentRank ? $currentRank->id : ($rankings->first() ? $rankings->first()->id : null),
            'current_rank_name'     => $currentRank ? $currentRank->rank_name : ($rankings->first() ? $rankings->first()->rank_name : 'حكيم مستوى أول'),
            'current_rank_desc'     => $currentRank ? ($currentRank->rank_description ?? '') : ($rankings->first() ? ($rankings->first()->rank_description ?? '') : ''),
            'current_rank_icon'     => $currentRank && !empty($currentRank->photo) ? asset('upload/rankings/' . $currentRank->photo) : ($rankings->first() && !empty($rankings->first()->photo) ? asset('upload/rankings/' . $rankings->first()->photo) : null),
            'current_rank_order'    => $currentRank ? (int)$currentRank->rank_order : 1,
            'current_rank_start'    => $currentRank ? (int)$currentRank->rank_start_point : 1,
            'current_rank_end'      => $currentRank && $currentRank->rank_end_point !== null ? (int)$currentRank->rank_end_point : null,
            'is_max_rank'           => $nextRank ? false : ($currentRank && $currentRank->is_last ? true : false),
            'next_rank_id'          => $nextRank ? $nextRank->id : null,
            'next_rank_name'        => $nextRank ? $nextRank->rank_name : null,
            'next_rank_desc'        => $nextRank ? ($nextRank->rank_description ?? '') : null,
            'next_rank_icon'        => $nextRank && !empty($nextRank->photo) ? asset('upload/rankings/' . $nextRank->photo) : null,
            'next_rank_start'       => $nextRank ? (int)$nextRank->rank_start_point : null,
            'points_remaining'      => $nextRank ? max(0, (int)$nextRank->rank_start_point - $userPoints) : 0,
            'progress_percentage'   => 0.0,
            'encouragement_message' => '',
        ];

        $isEn = ($request->input('lang') == 'en' || $request->header('Accept-Language') == 'en');
        if ($currentRank && $nextRank) {
            $range = (int)$nextRank->rank_start_point - (int)$currentRank->rank_start_point;
            $earned = max(0, $userPoints - (int)$currentRank->rank_start_point);
            $pct = $range > 0 ? min(1.0, round($earned / $range, 3)) : 1.0;
            $rankProgress['progress_percentage'] = $pct;
            $remaining = $rankProgress['points_remaining'];
            $rankProgress['encouragement_message'] = $isEn
                ? "You are only {$remaining} points away from advancing to {$nextRank->rank_name}! Keep sharing your valuable thoughts."
                : "أنت على بُعد {$remaining} نقطة فقط من الارتقاء إلى {$nextRank->rank_name}! استمر في مشاركة أفكارك القيمة.";
        } elseif ($currentRank && $currentRank->is_last) {
            $rankProgress['progress_percentage'] = 1.0;
            $rankProgress['encouragement_message'] = $isEn
                ? "Congratulations! You have reached the apex rank of wisdom and profound knowledge."
                : "مبارك! لقد بلغت أعلى رتب الحكمة في المنصة ورسوخ المعرفة.";
        } else {
            $rankProgress['progress_percentage'] = 0.0;
            $rankProgress['encouragement_message'] = $isEn
                ? "Start writing and sharing your valuable thoughts to advance through the wisdom ranks."
                : "ابدأ بكتابة ومشاركة أفكارك القيمة للارتقاء في رتب الحكمة.";
        }

        $formattedRankings = $rankings->map(function ($r) use ($userPoints, $currentRank) {
            $isCurrent = $currentRank && $currentRank->id === $r->id;
            $isUnlocked = $userPoints >= (int)$r->rank_start_point;
            return [
                'id'                  => (int)$r->id,
                'rank_name'           => $r->rank_name,
                'rank_description'    => $r->rank_description ?? '',
                'rank_order'          => (int)$r->rank_order,
                'rank_start_point'    => (int)$r->rank_start_point,
                'rank_end_point'      => $r->rank_end_point !== null ? (int)$r->rank_end_point : null,
                'level_reward_amount' => (int)($r->level_reward_amount ?? 0),
                'is_last'             => (bool)$r->is_last,
                'photo'               => !empty($r->photo) ? asset('upload/rankings/' . $r->photo) : null,
                'is_current'          => $isCurrent,
                'is_unlocked'         => $isUnlocked,
                'points_needed'       => max(0, (int)$r->rank_start_point - $userPoints),
            ];
        });

        return response()->json([
            'success'       => true,
            'rank_progress' => $rankProgress,
            'rankings'      => $formattedRankings,
        ]);
    }

}