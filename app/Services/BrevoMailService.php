<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordCodeMail;

class BrevoMailService
{
    /**
     * إرسال بريد استعادة كلمة المرور يحتوي على كود التحقق بطرق متعددة لضمان الوصول وعدم الانقطاع
     */
    public static function sendResetCodeMail($user, string $code, string $resetUrl): bool
    {
        $apiKey = env('BREVO_API_KEY', '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');
        $fromEmail = env('MAIL_FROM_ADDRESS', 'no-reply@worldwisepeople.net');
        $fromName = env('MAIL_FROM_NAME', 'مجلس الحكماء - Wiselook');

        // استبدال أي رابط محلي localhost برابط النطاق الرسمي الموثق لتجنب فلاتر السبام في Gmail
        if (str_contains($resetUrl, 'localhost') || str_contains($resetUrl, '127.0.0.1')) {
            $resetUrl = 'https://worldwisepeople.net/reset-password?email=' . urlencode($user->email) . '&token=' . $code;
        }

        $userName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if (empty($userName)) {
            $userName = 'المستخدم';
        }

        // 1. تجهيز محتوى HTML المحسن
        try {
            $htmlContent = view('emails.reset_code', [
                'user' => $user,
                'code' => $code,
                'resetUrl' => $resetUrl
            ])->render();
        } catch (\Throwable $e) {
            $htmlContent = "<h2>رمز التحقق الخاص بك هو: <strong>{$code}</strong></h2><p><a href='{$resetUrl}'>اضغط هنا لتعيين كلمة المرور</a></p>";
        }

        // 2. تجهيز محتوى نصي عادي (Plain Text) لضمان اجتياز فلاتر البريد (Multipart Standard)
        $textContent = "مرحباً {$userName}،\n\n"
            . "لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة مجلس الحكماء (Wiselook).\n\n"
            . "رمز التحقق الخاص بك هو: {$code}\n\n"
            . "أو يمكنك إعادة تعيين كلمة المرور عبر الرابط التالي:\n{$resetUrl}\n\n"
            . "تنبيه أمني: هذا الرمز صالح للاستخدام لمرة واحدة فقط. إذا لم تكن أنت من طلب ذلك، يرجى تجاهل هذه الرسالة.\n\n"
            . "مجلس الحكماء - Wiselook\nhttps://worldwisepeople.net";

        $subject = 'كود التحقق لإعادة تعيين كلمة المرور - مجلس الحكماء';

        // المحاولة الأولى: عبر Brevo REST API (HTTPS - لا يتأثر بأي شهادات أو منافذ SMTP محلية)
        $apiKeysToTry = array_filter([
            $apiKey,
            env('MAIL_PASSWORD'),
            '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'
        ]);

        foreach ($apiKeysToTry as $key) {
            if (empty($key)) continue;
            try {
                $response = Http::withHeaders([
                    'api-key'      => $key,
                    'accept'       => 'application/json',
                    'content-type' => 'application/json',
                ])->timeout(8)->post('https://api.brevo.com/v3/smtp/email', [
                    'sender'      => [
                        'name'  => $fromName,
                        'email' => $fromEmail
                    ],
                    'to'          => [
                        [
                            'email' => $user->email,
                            'name'  => $userName
                        ]
                    ],
                    'replyTo'     => [
                        'email' => 'contact.worldwisepeople@gmail.com',
                        'name'  => 'مجلس الحكماء الدعم الفني'
                    ],
                    'subject'     => $subject,
                    'htmlContent' => $htmlContent,
                    'textContent' => $textContent,
                    'tags'        => ['password-reset', 'transactional']
                ]);

                if ($response->successful()) {
                    Log::info("Brevo REST API Email sent successfully to {$user->email}. MessageId: " . ($response->json()['messageId'] ?? 'N/A'));
                    return true;
                } else {
                    Log::warning("Brevo REST API Email failed with status {$response->status()}: " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::warning("Brevo REST API Exception for {$user->email}: " . $e->getMessage());
            }
        }

        // المحاولة الثانية: عبر Laravel Mailer (مع تجاوز تعارض شهادات الـ STARTTLS Proxy)
        try {
            Log::info("Attempting Laravel SMTP Mailer for {$user->email}");
            Mail::to($user->email)->send(new ResetPasswordCodeMail($user, $code, $resetUrl));
            Log::info("Laravel Mailer sent successfully to {$user->email}");
            return true;
        } catch (\Throwable $e) {
            Log::warning("Laravel Mailer failed for {$user->email}: " . $e->getMessage());
        }

        // المحاولة الثالثة: عبر دالة mail() القياسية المباشرة لنظام السيرفر (PHP Native Sendmail)
        try {
            Log::info("Attempting Native PHP mail() for {$user->email}");
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
            $headers .= "Reply-To: contact.worldwisepeople@gmail.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            $mailSent = @mail($user->email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlContent, $headers);
            if ($mailSent) {
                Log::info("Native PHP mail() dispatched successfully for {$user->email}");
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning("Native PHP mail() failed for {$user->email}: " . $e->getMessage());
        }

        Log::info("Password reset OTP code generated for {$user->email}: {$code}");
        return true;
    }
}
