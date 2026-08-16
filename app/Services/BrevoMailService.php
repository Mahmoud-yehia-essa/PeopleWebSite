<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordCodeMail;

class BrevoMailService
{
    /**
     * إرسال بريد استعادة كلمة المرور يحتوي على كود التحقق
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
        $htmlContent = view('emails.reset_code', [
            'user' => $user,
            'code' => $code,
            'resetUrl' => $resetUrl
        ])->render();

        // 2. تجهيز محتوى نصي عادي (Plain Text) لضمان اجتياز فلاتر البريد (Multipart Standard)
        $textContent = "مرحباً {$userName}،

"
            . "لقد تلقينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في منصة مجلس الحكماء (Wiselook).

"
            . "رمز التحقق الخاص بك هو: {$code}

"
            . "أو يمكنك إعادة تعيين كلمة المرور عبر الرابط التالي:
{$resetUrl}

"
            . "تنبيه أمني: هذا الرمز صالح للاستخدام لمرة واحدة فقط. إذا لم تكن أنت من طلب ذلك، يرجى تجاهل هذه الرسالة.

"
            . "مجلس الحكماء - Wiselook
https://worldwisepeople.net";

        $subject = 'كود التحقق لإعادة تعيين كلمة المرور - مجلس الحكماء';

        if (!empty($apiKey)) {
            Log::info("Sending email via Brevo REST API to: {$user->email}");

            try {
                $response = Http::withHeaders([
                    'api-key'      => $apiKey,
                    'accept'       => 'application/json',
                    'content-type' => 'application/json',
                ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', [
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
                    Log::error("Brevo REST API Email failed with status {$response->status()}: " . $response->body());
                }
            } catch (\Throwable $e) {
                Log::error("Brevo REST API Exception for {$user->email}: " . $e->getMessage());
            }
        }

        // في حال تعذر الإرسال عبر API، نقوم بالمحاولة عبر Laravel Mailer القياسي كإجراء بديل
        Log::info("Falling back to Laravel Standard Mailer for {$user->email}");
        Mail::to($user->email)->send(new ResetPasswordCodeMail($user, $code, $resetUrl));
        return true;
    }
}
