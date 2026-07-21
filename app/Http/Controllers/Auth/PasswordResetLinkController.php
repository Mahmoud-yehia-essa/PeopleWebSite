<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BrevoMailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('frontend.wiselook.pages.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'يرجى كتابة البريد الإلكتروني.',
            'email.email'    => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        $email = trim(strtolower($request->email));
        $user = User::where('email', $email)->first();

        // في حال لم يكن الإيميل موجوداً في قاعدة البيانات، ينشئ حساب تجريبي مؤقت فوراً لتمكين المستخدم من التجربة
        if (!$user) {
            Log::info("Email {$email} not found in DB. Creating temporary record for password reset testing.");
            $user = User::create([
                'email'         => $email,
                'first_name'    => 'مستخدم',
                'last_name'     => 'جديد',
                'password'      => Hash::make(Str::random(16)),
                'password_hash' => md5(Str::random(16)),
                'status'        => 1,
                'is_active'     => 1,
            ]);
        }

        // توليد كود عشوائي مكون من 6 أرقام
        $code = (string) rand(100000, 999999);

        // حفظ الكود للمستخدم في قاعدة البيانات
        $user->reset_code = $code;
        $user->save();

        // تحديث جدول password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email'      => $user->email,
                'token'      => Hash::make($code),
                'created_at' => now(),
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $code,
            'email' => $user->email
        ]);

        Log::info("Attempting to send Brevo reset OTP code {$code} to email: {$user->email}");

        try {
            BrevoMailService::sendResetCodeMail($user, $code, $resetUrl);
            Log::info("Brevo reset OTP mail processed successfully for {$user->email}");
        } catch (\Exception $e) {
            Log::error("Failed to send Brevo password reset mail to {$user->email}: " . $e->getMessage());
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'حدث خطأ أثناء الإرسال: ' . $e->getMessage()]);
        }

        // تخزين بيانات إعادة التعيين في Session للانتقال لاحقاً
        session([
            'reset_pending_email' => $user->email,
            'reset_pending_code'  => $code,
        ]);

        $notification = [
            'message'    => "✅ تم إرسال كود التحقق بنجاح إلى {$user->email} — يرجى مراجعة بريدك الإلكتروني وإدخال الكود.",
            'alert-type' => 'success'
        ];

        return back()
            ->withInput($request->only('email'))
            ->with($notification)
            ->with('status', "تم إرسال كود التحقق (6 أرقام) إلى بريدك الإلكتروني.")
            ->with('reset_email', $user->email)
            ->with('reset_code', $code)
            ->with('code_sent', true);
    }
}
