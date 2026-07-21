<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->redirectUrl(url('/auth/google/callback'))
            ->redirect();
    }

    /**
     * استقبال استجابة Google وتسجيل دخول المستخدم أو إنشائه.
     */
    public function handleGoogleCallback()
    {
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

            // التحقق من تتبع الإحالة إن وجد بالجلسة
            if (session()->has('affiliate_ref')) {
                $code = session('affiliate_ref');
                $link = \App\Models\AffiliateLink::where('code', $code)->where('is_active', true)->first();
                if ($link) {
                    $exists = \App\Models\AffiliateTracking::where('affiliate_link_id', $link->id)
                        ->where('registered_user_id', $user->id)
                        ->exists();
                    if (!$exists) {
                        \App\Models\AffiliateTracking::create([
                            'affiliate_link_id' => $link->id,
                            'registered_user_id' => $user->id,
                            'ip_address' => request()->ip(),
                        ]);
                    }
                }
                session()->forget('affiliate_ref');
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
            return redirect()->route('user.login')->with([
                'message' => 'حدث خطأ أثناء تسجيل الدخول باستخدام جوجل: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}
