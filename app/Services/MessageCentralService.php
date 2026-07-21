<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MessageCentralService
{
    protected string $baseUrl;
    protected string $authToken;
    protected string $senderId;
    protected string $templateName;
    protected string $language;

    public function __construct()
    {
        $this->baseUrl = env('MESSAGECENTRAL_BASE_URL', 'https://cpaas.messagecentral.com');
        $this->authToken = env('MESSAGECENTRAL_AUTH_TOKEN', '');
        $this->senderId = env('MESSAGECENTRAL_SENDER_ID', '');
        $this->templateName = env('MESSAGECENTRAL_WHATSAPP_TEMPLATE', 'signup_demo');
        $this->language = env('MESSAGECENTRAL_WHATSAPP_LANG', 'en');
    }

    /**
     * Send OTP via WhatsApp CPaaS API.
     */
    public function sendOtp(string $countryCode, string $mobileNumber): array
    {
        if (empty($this->authToken)) {
            Log::error('MessageCentral authToken is not configured in .env file.');
            return [
                'success' => false,
                'message' => 'Configuration missing (authToken).'
            ];
        }

        try {
            // Generate a 6-digit OTP code
            $otpCode = (string) rand(100000, 999999);
            // Unique local verification ID
            $verificationId = (string) \Illuminate\Support\Str::uuid();

            // Clean phone numbers
            $cleanCountryCode = ltrim($countryCode, '+');
            $cleanMobileNumber = ltrim($mobileNumber, '0');

            // The official endpoint is /verification/v3/send
            $url = rtrim($this->baseUrl, '/') . '/verification/v3/send';

            // Construct variables parameter based on template name
            // For signup_demo: body_1 is User, body_2 is OTP code
            if ($this->templateName === 'signup_demo') {
                $variables = 'User,' . $otpCode;
            } else {
                $variables = $otpCode;
            }

            // Construct query parameters as specified in the WhatsApp Now API guide
            $queryParams = [
                'flowType' => 'WHATSAPP',
                'type' => 'BROADCAST',
                'mobileNumber' => $cleanMobileNumber,
                'countryCode' => $cleanCountryCode,
                'senderId' => $this->senderId,
                'langId' => $this->language,
                'templateName' => $this->templateName,
                'variables' => $variables
            ];

            // Send POST request with query params and authToken in headers
            $response = Http::withHeaders([
                'authToken' => $this->authToken,
                'accept' => '*/*',
            ])->post($url . '?' . http_build_query($queryParams));

            if ($response->successful()) {
                $data = $response->json();
                $responseCode = $data['responseCode'] ?? null;
                $messageStatus = $data['message'] ?? '';

                if ($responseCode == 200 || $messageStatus === 'SUCCESS') {
                    return [
                        'success' => true,
                        'verification_id' => $verificationId,
                        'otp' => $otpCode
                    ];
                }
            }

            Log::error('MessageCentral Send OTP failed: ' . $response->body());
            
            $detailedMessage = $response->json('message') ?? $response->json('error.message') ?? $response->body();
            
            return [
                'success' => false,
                'message' => $detailedMessage ?: 'Failed to send OTP via WhatsApp.'
            ];

        } catch (\Exception $e) {
            Log::error('Exception in MessageCentral sendOtp: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while sending OTP: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP code locally from database.
     */
    public function verifyOtp(string $verificationId, string $otpCode): bool
    {
        try {
            $verification = \App\Models\PhoneVerification::where('verification_id', $verificationId)
                ->where('otp_code', $otpCode)
                ->where('used', 0)
                ->where('expires_at', '>', now())
                ->first();

            return $verification !== null;
        } catch (\Exception $e) {
            Log::error('Exception in MessageCentral verifyOtp: ' . $e->getMessage());
            return false;
        }
    }
}
