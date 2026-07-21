<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;

class FirebaseAuthController extends Controller
{
    /**
     * Show the Firebase Phone Login page.
     *
     * GET /login/phone/new
     */
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.firebase_phone_login');
    }

    /**
     * Show the Firebase Phone Register page.
     *
     * GET /register/phone/new
     */
    public function showRegisterForm(): \Illuminate\View\View
    {
        return view('auth.firebase_phone_register');
    }

    /**
     * Check if phone number is available for new user registration.
     *
     * POST /check-phone-register-new
     */
    public function checkPhoneAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone      = $request->input('phone');
        $cleanPhone = ltrim($phone, '+');

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

        return response()->json([
            'success' => true,
            'message' => 'رقم الهاتف متاح للتسجيل.',
        ]);
    }

    /**
     * Verify the Firebase idToken sent by the frontend and log the user in.
     *
     * POST /verify-firebase-token-new
     *
     * Request body (JSON):
     *   - token       : string  (Firebase idToken)
     *   - phone       : string  (e.g. "+96555123456")
     *   - is_register : boolean (optional)
     *   - first_name  : string  (optional)
     *   - last_name   : string  (optional)
     */
    public function verifyToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'phone' => 'required|string',
        ]);

        $idToken    = $request->input('token');
        $phone      = $request->input('phone');
        $isRegister = $request->boolean('is_register');

        // ------------------------------------------------------------------
        // 1. Verify idToken with Firebase Admin SDK
        // ------------------------------------------------------------------
        try {
            $auth          = Firebase::auth();
            $verifiedToken = $auth->verifyIdToken($idToken);

            // Extra safety: make sure the phone claim matches what the client sent
            $tokenPhone = $verifiedToken->claims()->get('phone_number');

            if ($tokenPhone && $tokenPhone !== $phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف في التوكن لا يتطابق مع ما أُرسل.',
                ], 422);
            }

        } catch (FailedToVerifyToken $e) {
            Log::warning('FirebaseAuthController: Invalid idToken', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صالح. يرجى المحاولة من جديد.',
            ], 401);
        } catch (RevokedIdToken $e) {
            Log::warning('FirebaseAuthController: Revoked idToken', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'انتهت صلاحية الجلسة. يرجى إعادة تسجيل الدخول.',
            ], 401);
        } catch (\Throwable $e) {
            Log::error('FirebaseAuthController: Unexpected error during token verification', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التحقق من الهوية.',
            ], 500);
        }

        // ------------------------------------------------------------------
        // 2. Find or create the user in the database
        // ------------------------------------------------------------------
        try {
            $cleanPhone = ltrim($phone, '+');
            $user = User::where('phone_number', $phone)
                ->orWhere('phone_number', $cleanPhone)
                ->orWhere('phone_number', '+' . $cleanPhone)
                ->first();

            // Block registration if user already exists
            if ($isRegister && $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'رقم الهاتف هذا مسجل بالفعل. يرجى تسجيل الدخول.',
                ], 400);
            }

            $firstName = $request->input('first_name') ?: ($request->input('fname') ?: 'مستخدم');
            $lastName  = $request->input('last_name')  ?: ($request->input('lname')  ?: 'جديد');

            if (!$user) {
                // Create a new account for this phone number
                $randomPassword = Str::random(20);

                $user = User::create([
                    'first_name'   => $firstName,
                    'last_name'    => $lastName,
                    'email'        => null,
                    'password'     => Hash::make($randomPassword),
                    'phone_number' => $phone,
                    'status'       => 1,
                    'is_active'    => 1,
                    'is_verified'  => 1,
                ]);

                Log::info('FirebaseAuthController: New user created via Firebase Phone Auth', [
                    'phone' => $phone,
                    'user_id' => $user->id,
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('FirebaseAuthController: Error finding/creating user', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة حسابك.',
            ], 500);
        }

        // ------------------------------------------------------------------
        // 3. Log the user in via Laravel Auth
        // ------------------------------------------------------------------
        Auth::login($user, true);
        $request->session()->regenerate();

        Log::info('FirebaseAuthController: User logged in successfully', [
            'user_id' => $user->id,
            'phone'   => $phone,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'تم تسجيل الدخول بنجاح!',
            'redirect' => session()->pull('url.intended', '/'),
        ]);
    }

    /**
     * Send OTP via WhatsApp API (TextMeBot)
     *
     * POST /send-whatsapp-otp-new
     */
    public function sendWhatsappOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone      = $request->input('phone'); // e.g. "+96555123456"
        $isRegister = $request->boolean('is_register');

        if ($isRegister) {
            $cleanPhone = ltrim($phone, '+');
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

        $otp = (string) rand(100000, 999999);

        // Store OTP in session
        session([
            'whatsapp_otp_' . $phone => [
                'code'       => $otp,
                'expires_at' => now()->addMinutes(10),
            ]
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::get('http://api.textmebot.com/send.php', [
                'recipient' => $phone,
                'apikey'    => 'zh9d51Rp9csh',
                'text'      => 'رمز التحقق الخاص بك في حكماء العالم هو : ' . $otp,
            ]);

            Log::info('WhatsApp OTP sent via TextMeBot', [
                'phone'  => $phone,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رمز التحقق عبر الواتساب بنجاح.',
            ]);
        } catch (\Throwable $e) {
            Log::error('TextMeBot API error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'تعذر إرسال رسالة الواتساب. يرجى المحاولة لاحقاً.',
            ], 500);
        }
    }

    /**
     * Verify WhatsApp OTP
     *
     * POST /verify-whatsapp-otp-new
     */
    public function verifyWhatsappOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string|size:6',
        ]);

        $phone      = $request->input('phone');
        $code       = $request->input('code');
        $isRegister = $request->boolean('is_register');

        $sessionData = session('whatsapp_otp_' . $phone);

        if (!$sessionData || now()->isAfter($sessionData['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'جلسة التحقق منتهية الصلاحية أو غير موجودة.',
            ], 400);
        }

        if ($sessionData['code'] !== $code) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح.',
            ], 400);
        }

        // Clear session OTP
        session()->forget('whatsapp_otp_' . $phone);

        // Find or create user
        $cleanPhone = ltrim($phone, '+');
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
            $randomPassword = Str::random(20);
            $user = User::create([
                'first_name'   => $firstName,
                'last_name'    => $lastName,
                'email'        => null,
                'password'     => Hash::make($randomPassword),
                'phone_number' => $phone,
                'status'       => 1,
                'is_active'    => 1,
                'is_verified'  => 1,
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'message'  => 'تم تسجيل الدخول بنجاح!',
            'redirect' => session()->pull('url.intended', '/'),
        ]);
    }
}

