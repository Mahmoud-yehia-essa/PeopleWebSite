<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Exception;

class GoogleController extends Controller
{
    /**
     * إعادة توجيه المستخدم إلى صفحة مصادقة Google.
     */
    public function redirectToGoogle(Request $request)
    {
        if ($request->has('app') || $request->has('mobile')) {
            session(['google_auth_source' => 'app']);
        }

        return Socialite::driver('google')
            ->redirectUrl(url('/auth/google/callback'))
            ->redirect();
    }

    /**
     * استقبال استجابة Google وتسجيل دخول المستخدم أو إنشائه.
     */
    public function handleGoogleCallback(Request $request)
    {
        $isApp = session('google_auth_source') === 'app' || $request->has('app') || $request->has('mobile');
        session()->forget('google_auth_source');

        try {
            try {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl(url('/auth/google/callback'))
                    ->user();
            } catch (\Exception $e) {
                $googleUser = Socialite::driver('google')
                    ->redirectUrl(url('/auth/google/callback'))
                    ->stateless()
                    ->user();
            }

            if (!$googleUser || !$googleUser->getEmail()) {
                if ($isApp) {
                    return redirect()->route('auth.google.app_callback', [
                        'status' => 'error',
                        'message' => 'لم نتمكن من الحصول على بيانات حساب جوجل الخاص بك.'
                    ]);
                }
                return redirect()->route('user.login')->with([
                    'message' => 'لم نتمكن من الحصول على بيانات حساب جوجل الخاص بك.',
                    'alert-type' => 'error'
                ]);
            }
            
            // البحث عن المستخدم باستخدام google_id أو البريد الإلكتروني
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();
            
            if ($user) {
                // إذا وجدنا المستخدم مسبقاً، نقوم بتحديث الـ google_id والـ provider إن لم يكونا موجودين
                if (empty($user->google_id)) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'provider' => 'google'
                    ]);
                }
            } else {
                // استخراج الاسم الأول والاسم الأخير
                $nameParts = explode(' ', $googleUser->getName() ?? 'مستخدم جوجل', 2);
                $firstName = $nameParts[0] ?? 'مستخدم';
                $lastName = $nameParts[1] ?? 'جوجل';

                // إنشاء مستخدم جديد
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'provider' => 'google',
                    'password' => Hash::make(Str::random(24)),
                    'password_hash' => md5(Str::random(24)),
                    'status' => 1,
                    'is_active' => 1,
                    'profile_picture' => $googleUser->getAvatar() ?? null,
                ]);
            }

            Auth::login($user, true);

            // معالجة مكافأة الإحالة وزيادة نقاط المسوق
            \App\Http\Controllers\AffiliateController::processReferralReward($user, session('affiliate_ref'), request()->ip());

            if ($isApp) {
                $token = $user->createToken('WiselookMobileToken')->plainTextToken;
                
                $userData = [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'username' => $user->username ?? $user->email,
                    'profile_picture' => $user->profile_picture,
                    'status' => $user->status,
                ];

                return redirect()->route('auth.google.app_callback', [
                    'status' => 'success',
                    'token' => $token,
                    'user' => json_encode($userData)
                ]);
            }

            $targetRoute = ($user->role === 'admin' || $user->role === 'owner') 
                ? route('dashboard') 
                : route('frontend.home');

            $notification = [
                'message' => 'تم تسجيل الدخول بنجاح عبر جوجل! أهلاً بك، ' . $user->first_name,
                'alert-type' => 'success'
            ];

            return redirect()->intended($targetRoute)->with($notification);

        } catch (Exception $e) {
            if ($isApp) {
                return redirect()->route('auth.google.app_callback', [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);
            }
            return redirect()->route('user.login')->with([
                'message' => 'حدث خطأ أثناء تسجيل الدخول باستخدام جوجل: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }

    /**
     * صفحة إتمام المصادقة لتطبيق الهاتف (App Callback).
     */
    public function appCallback(Request $request)
    {
        return response()->view('auth.google_app_callback', [
            'status' => $request->get('status', 'success'),
            'token' => $request->get('token', ''),
            'user' => $request->get('user', '{}'),
            'message' => $request->get('message', '')
        ]);
    }
}
