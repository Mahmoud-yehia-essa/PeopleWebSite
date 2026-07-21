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

        // تجهيز محتوى البريد الإلكتروني بحسب التنسيق الاحترافي
        $htmlContent = view('emails.reset_code', [
            'user' => $user,
            'code' => $code,
            'resetUrl' => $resetUrl
        ])->render();

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
                            'name'  => $user->first_name ? ($user->first_name . ' ' . $user->last_name) : 'المستخدم'
                        ]
                    ],
                    'subject'     => 'رمز إستعادة كلمة المرور | مجلس الحكماء - Wiselook',
                    'htmlContent' => $htmlContent
                ]);

                if ($response->successful()) {
                    Log::info("Brevo REST API Email sent successfully to {$user->email}. MessageId: " . ($response->json()['messageId'] ?? 'N/A'));
                    return true;
                } else {
                    Log::error("Brevo REST API Email failed with status {$response->status()}: " . $response->body());
                }
            } catch (\Exception $e) {
                Log::error("Brevo REST API Exception for {$user->email}: " . $e->getMessage());
            }
        }

        // في حال تعذر الإرسال عبر API، نقوم بالمحاولة عبر Laravel Mailer القياسي كإجراء بديل
        Log::info("Falling back to Laravel Standard Mailer for {$user->email}");
        Mail::to($user->email)->send(new ResetPasswordCodeMail($user, $code, $resetUrl));
        return true;
    }
}
