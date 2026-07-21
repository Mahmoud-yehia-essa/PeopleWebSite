<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Exception;

class FacebookController extends Controller
{
    /**
     * إعادة توجيه المستخدم إلى صفحة مصادقة فيسبوك.
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->redirectUrl(url('/auth/facebook/callback'))
            ->redirect();
    }

    /**
     * استقبال استجابة فيسبوك وتسجيل دخول المستخدم أو إنشائه.
     */
    public function handleFacebookCallback()
    {
        try {
            try {
                $facebookUser = Socialite::driver('facebook')
                    ->redirectUrl(url('/auth/facebook/callback'))
                    ->user();
            } catch (\Exception $e) {
                $facebookUser = Socialite::driver('facebook')
                    ->redirectUrl(url('/auth/facebook/callback'))
                    ->stateless()
                    ->user();
            }

            if (!$facebookUser || !$facebookUser->getId()) {
                return redirect()->route('user.login')->with([
                    'message' => 'لم نتمكن من الحصول على بيانات حساب فيسبوك الخاص بك.',
                    'alert-type' => 'error'
                ]);
            }

            $email = $facebookUser->getEmail() ?? ($facebookUser->getId() . '@facebook.com');

            // البحث عن المستخدم باستخدام facebook_id أو البريد الإلكتروني
            $user = User::where('facebook_id', $facebookUser->getId())
                        ->orWhere('email', $email)
                        ->first();

            if ($user) {
                // إذا وجدنا المستخدم مسبقاً، نقوم بتحديث الـ facebook_id والـ provider إن لم يكونا موجودين
                if (empty($user->facebook_id)) {
                    $user->update([
                        'facebook_id' => $facebookUser->getId(),
                        'provider' => 'facebook'
                    ]);
                }
            } else {
                // استخراج الاسم الأول والاسم الأخير
                $nameParts = explode(' ', $facebookUser->getName() ?? 'مستخدم فيسبوك', 2);
                $firstName = $nameParts[0] ?? 'مستخدم';
                $lastName = $nameParts[1] ?? 'فيسبوك';

                // إنشاء مستخدم جديد
                $user = User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'facebook_id' => $facebookUser->getId(),
                    'provider' => 'facebook',
                    'password' => Hash::make(Str::random(24)),
                    'password_hash' => md5(Str::random(24)),
                    'status' => 1,
                    'is_active' => 1,
                    'profile_picture' => $facebookUser->getAvatar() ?? null,
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
                'message' => 'تم تسجيل الدخول بنجاح عبر فيسبوك! أهلاً بك، ' . $user->first_name,
                'alert-type' => 'success'
            ];

            return redirect()->intended($targetRoute)->with($notification);

        } catch (Exception $e) {
            return redirect()->route('user.login')->with([
                'message' => 'حدث خطأ أثناء تسجيل الدخول باستخدام فيسبوك: ' . $e->getMessage(),
                'alert-type' => 'error'
            ]);
        }
    }
}
