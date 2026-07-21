<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('frontend.wiselook.pages.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'token.required'     => 'كود التحقق مطلوب.',
            'email.required'     => 'البريد الإلكتروني مطلوب.',
            'email.email'        => 'صيغة البريد الإلكتروني غير صحيحة.',
            'password.required'  => 'كلمة المرور الجديدة مطلوبة.',
            'password.min'       => 'يجب أن لا تقل كلمة المرور عن 6 أحرف.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'البريد الإلكتروني غير مسجل.']);
        }

        // التحقق من كود التحقق
        $isValid = false;
        if ($user->reset_code && $user->reset_code === trim($request->token)) {
            $isValid = true;
        } else {
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();
            if ($tokenRecord && Hash::check($request->token, $tokenRecord->token)) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return back()->withInput($request->only('email', 'token'))
                ->withErrors(['token' => 'كود التحقق المدخل غير صحيح. يرجى التأكد وإعادة المحاولة.']);
        }

        // تحديث كلمة المرور في قاعدة البيانات
        $user->password = Hash::make($request->password);
        $user->password_hash = md5($request->password);
        $user->reset_code = null;
        $user->remember_token = Str::random(60);
        $user->save();

        // حذف التوكن من الجدول المؤقت
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        event(new PasswordReset($user));

        $notification = [
            'message'    => 'تم تعيين كلمة المرور الجديدة بنجاح! يمكنك الآن تسجيل الدخول.',
            'alert-type' => 'success'
        ];

        return redirect()->route('user.login')->with($notification);
    }
}
