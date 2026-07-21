<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpRequest;
use App\Services\VerifyNowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;

class LoginController extends Controller
{
    protected VerifyNowService $verifyNowService;

    public function __construct(VerifyNowService $verifyNowService)
    {
        $this->verifyNowService = $verifyNowService;
    }

    /**
     * Display the OTP Login Page View.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('frontend.home');
        }

        return view('auth.otp_login');
    }

    /**
     * Handle OTP Request (Step 1 of Login Flow).
     *
     * Validates input, calls VerifyNowService, logs request to DB, and returns JSON.
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['required', 'string', 'max:10'],
            'phone' => ['required', 'string', 'min:6', 'max:20'],
            'flow_type' => ['nullable', 'string', 'in:SMS,WHATSAPP'],
        ], [
            'country_code.required' => 'رمز الدولة مطلوب.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.min' => 'رقم الهاتف غير صحيح.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $countryCode = trim($request->input('country_code'));
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . $countryCode;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $flowType = strtoupper($request->input('flow_type', 'SMS'));

        // Rate limiting check: max 5 requests per 10 minutes per phone
        $recentAttempts = OtpRequest::where('country_code', $countryCode)
            ->where('phone_number', $cleanPhone)
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentAttempts >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'لقد تجاوزت الحد الأقصى للمحاولات. يرجى الانتظار 10 دقائق قبل إعادة المحاولة.'
            ], 429);
        }

        try {
            // Call VerifyNowService sendOtp
            $verificationId = $this->verifyNowService->sendOtp($countryCode, $cleanPhone, $flowType);

            // Store in otp_requests table
            OtpRequest::create([
                'country_code' => $countryCode,
                'phone_number' => $cleanPhone,
                'verification_id' => $verificationId,
                'flow_type' => $flowType,
                'status' => 'PENDING',
                'ip_address' => $request->ip(),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]);

            $channelText = $flowType === 'WHATSAPP' ? 'الواتس اب' : 'SMS';

            return response()->json([
                'success' => true,
                'message' => "تم إرسال رمز التحقق بنجاح عبر {$channelText}.",
                'verification_id' => $verificationId,
                'phone' => $cleanPhone,
                'country_code' => $countryCode,
                'flow_type' => $flowType,
            ], 200);

        } catch (Exception $e) {
            Log::error('LoginController@requestOtp Failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'فشل إرسال رمز التحقق: ' . $e->getMessage(),
                'debug'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ]
            ], 400);
        }
    }

    /**
     * Handle OTP Verification and User Authentication (Step 2 of Login Flow).
     *
     * Validates code via VerifyNowService, logs in user / generates Sanctum token, returns JSON.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'min:4', 'max:8'],
            'verification_id' => ['nullable', 'string'],
        ], [
            'code.required' => 'رمز التحقق مطلوب.',
            'code.min' => 'رمز التحقق يجب أن يتكون من 4 أرقام على الأقل.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $countryCode = trim($request->input('country_code'));
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . $countryCode;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->input('phone'));
        $otpCode = trim($request->input('code'));

        // Retrieve verification record
        $otpRecord = OtpRequest::where('country_code', $countryCode)
            ->where('phone_number', $cleanPhone)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        $verificationId = $request->input('verification_id') ?? ($otpRecord ? $otpRecord->verification_id : null);

        if (!$verificationId) {
            return response()->json([
                'success' => false,
                'message' => 'جلسة التحقق غير صالحة أو منتهية الصلاحية. يرجى طلب رمز جديد.'
            ], 400);
        }

        // Check verification status
        $isValid = false;
        if (str_starts_with($verificationId, 'LOCAL_DEV_')) {
            $isValid = ($otpCode === '123456');
        } else {
            // Validate via Message Central VerifyNow API
            $isValid = $this->verifyNowService->validateOtp($verificationId, $otpCode);
        }

        if (!$isValid) {
            if ($otpRecord) {
                $otpRecord->increment('attempts');
            }

            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.'
            ], 400);
        }

        // Mark OTP record as verified
        if ($otpRecord) {
            $otpRecord->update([
                'status' => 'VERIFIED',
                'verified_at' => now(),
            ]);
        }

        // Find existing user or create a new user record
        $fullPhoneNumber = $countryCode . $cleanPhone;
        $user = User::where(function ($query) use ($countryCode, $cleanPhone, $fullPhoneNumber) {
            $query->where(function ($q) use ($countryCode, $cleanPhone) {
                $q->where('country_code', $countryCode)
                  ->where('phone_number', $cleanPhone);
            })
            ->orWhere('phone_number', $cleanPhone)
            ->orWhere('phone_number', $fullPhoneNumber)
            ->orWhere('phone_number', '+' . $fullPhoneNumber);
        })->first();

        if (!$user) {
            // Create user for first-time OTP sign in
            $randomPassword = Str::random(16);
            $user = User::create([
                'first_name' => 'مستخدم',
                'last_name' => substr($cleanPhone, -4),
                'email' => null,
                'country_code' => $countryCode,
                'phone_number' => $cleanPhone,
                'password' => Hash::make($randomPassword),
                'password_hash' => md5($randomPassword),
                'status' => 1,
                'is_active' => 1,
                'is_verified' => 1,
            ]);
        } else {
            // Update user country_code if missing
            if (empty($user->country_code)) {
                $user->update(['country_code' => $countryCode]);
            }
        }

        // Log user into web guard if session available
        Auth::login($user, true);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        // Generate Sanctum Bearer Token
        $token = $user->createToken('verifynow_otp_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح!',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone_number,
                'country_code' => $user->country_code,
                'avatar_url' => $user->avatar_url,
            ],
            'redirect' => session()->pull('url.intended', route('frontend.home')),
        ], 200);
    }
}
