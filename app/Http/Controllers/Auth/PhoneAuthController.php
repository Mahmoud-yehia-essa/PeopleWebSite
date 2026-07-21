<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PhoneVerification;
use App\Services\MessageCentralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhoneAuthController extends Controller
{
    protected MessageCentralService $messageCentral;

    public function __construct(MessageCentralService $messageCentral)
    {
        $this->messageCentral = $messageCentral;
    }

    /**
     * Send OTP to user phone number.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'phone_number' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $countryCode = $request->code;
        $mobileNumber = $request->phone_number;

        $result = $this->messageCentral->sendOtp($countryCode, $mobileNumber);

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        $verificationId = $result['verification_id'];
        $fullPhoneNumber = ltrim($countryCode, '+') . ltrim($mobileNumber, '0');

        // Save session details locally
        PhoneVerification::create([
            'verification_id' => $verificationId,
            'phone_number' => $fullPhoneNumber,
            'otp_code' => $result['otp'] ?? '000000',
            'expires_at' => now()->addMinutes(10),
            'used' => 0,
            'verified' => 0
        ]);

        return response()->json([
            'success' => true,
            'verification_id' => $verificationId,
            'message' => 'تم إرسال رمز التحقق بنجاح عبر الواتس آب.'
        ]);
    }

    /**
     * Verify OTP and Login the user.
     */
    public function loginVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_id' => 'required|string',
            'otp_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $verification = PhoneVerification::where('verification_id', $request->verification_id)
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'جلسة التحقق غير صالحة أو منتهية الصلاحية.'], 400);
        }

        // Verify with Message Central
        $isVerified = $this->messageCentral->verifyOtp($request->verification_id, $request->otp_code);

        if (!$isVerified) {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.'], 400);
        }

        // Find user by phone number
        $phone = $verification->phone_number;
        $user = User::where('phone_number', $phone)
            ->orWhere('phone_number', '+' . $phone)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الرقم غير مسجل. يرجى الانتقال إلى تبويب إنشاء حساب جديد.'
            ], 404);
        }

        // Mark verification session as verified/used
        $verification->update([
            'used' => 1,
            'verified' => 1,
            'verified_at' => now()
        ]);

        // Login the user
        Auth::login($user, $request->boolean('remember', true));
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => session()->pull('url.intended', route('dashboard'))
        ]);
    }

    /**
     * Verify OTP and Register/Login the user.
     */
    public function registerVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required|string|max:50',
            'lname' => 'required|string|max:50',
            'verification_id' => 'required|string',
            'otp_code' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $verification = PhoneVerification::where('verification_id', $request->verification_id)
            ->where('used', 0)
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'جلسة التحقق غير صالحة أو منتهية الصلاحية.'], 400);
        }

        // Verify with Message Central
        $isVerified = $this->messageCentral->verifyOtp($request->verification_id, $request->otp_code);

        if (!$isVerified) {
            return response()->json(['success' => false, 'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.'], 400);
        }

        // Check if phone number is already registered
        $phone = $verification->phone_number;
        $exists = User::where('phone_number', $phone)
            ->orWhere('phone_number', '+' . $phone)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'رقم الهاتف هذا مسجل بالفعل. يرجى تسجيل الدخول.'
            ], 400);
        }

        // Mark verification session as verified/used
        $verification->update([
            'used' => 1,
            'verified' => 1,
            'verified_at' => now()
        ]);

        // Create new user
        $passwordRandom = Str::random(16);
        $user = User::create([
            'first_name' => $request->fname,
            'last_name' => $request->lname,
            'email' => null,
            'password' => Hash::make($passwordRandom),
            'password_hash' => md5($passwordRandom),
            'phone_number' => $phone,
            'status' => 1,
            'is_active' => 1,
            'is_verified' => 1
        ]);

        // Login the user
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard')
        ]);
    }
}
