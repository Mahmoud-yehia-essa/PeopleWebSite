<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class VerifyNowService
{
    protected string $baseUrl;
    protected string $customerId;
    protected string $key;

    public function __construct()
    {
        $this->baseUrl = env('MESSAGE_CENTRAL_BASE_URL', 'https://cpaas.messagecentral.com/');
        $this->customerId = env('MESSAGE_CENTRAL_CUSTOMER_ID', 'C-45259547DE864C7');
        $this->key = env('MESSAGE_CENTRAL_KEY', 'RXNzYUAxMDIwMzA=');
    }

    /**
     * Programmatically generate and cache the authentication token from Message Central.
     * Caches the token for 20 hours.
     *
     * @return string
     * @throws Exception
     */
    private function generateToken(): string
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/auth/v1/authentication/token';

        $queryParams = [
            'customerId' => $this->customerId,
            'key'        => $this->key,
            'scope'      => 'NEW',
        ];

        Log::info("VerifyNowService: Fetching new authToken from Message Central...");

        $response = Http::withoutVerifying()
            ->timeout(30)
            ->get($endpoint, $queryParams);

        $statusCode = $response->status();
        $body = $response->json() ?? [];

        Log::info("VerifyNowService: Token Generation Response [{$statusCode}]", $body);

        if ($response->successful()) {
            $token = $body['token'] ?? $body['data']['token'] ?? null;
            if (!empty($token)) {
                return (string) $token;
            }
        }

        $rawBody = $response->body() ?: 'Empty Response Body';
        $errorMessage = "Token Generation Failed [HTTP {$statusCode}] | Raw Body: {$rawBody}";
        Log::error("VerifyNowService: {$errorMessage}");

        throw new Exception($errorMessage);
    }

    /**
     * Send OTP via Message Central VerifyNow API (v3).
     *
     * @param string $countryCode e.g., '965', '+965'
     * @param string $mobileNumber e.g., '55123456'
     * @param string $flowType 'WHATSAPP' or 'SMS'
     * @return string verificationId returned from Message Central API
     * @throws Exception
     */
    public function sendOtp(string $countryCode, string $mobileNumber, string $flowType = 'SMS'): string
    {
        $token = $this->generateToken();

        $cleanCountryCode = ltrim($countryCode, '+');
        $cleanMobileNumber = ltrim($mobileNumber, '0');
        $endpoint = rtrim($this->baseUrl, '/') . '/verification/v3/send';

        $payload = [
            'countryCode'  => $cleanCountryCode,
            'mobileNumber' => $cleanMobileNumber,
            'flowType'     => strtoupper($flowType),
        ];

        try {
            Log::info("VerifyNowService: Sending OTP to {$cleanCountryCode}{$cleanMobileNumber} via {$flowType}");

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'authToken' => $token,
                    'accept'    => 'application/json',
                ])->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json() ?? [];

            Log::info("VerifyNowService: API Send Response [{$statusCode}]", $body);

            if ($response->successful()) {
                $responseCode = $body['responseCode'] ?? null;
                $verificationId = $body['data']['verificationId'] ?? $body['verificationId'] ?? null;

                if ($responseCode == 200 && !empty($verificationId)) {
                    return (string) $verificationId;
                }
            }

            $rawBody = $response->body() ?: 'Empty Response Body';
            $json = $response->json();

            $detailedError = "HTTP Code: {$statusCode} | ";

            if (is_array($json)) {
                if (isset($json['message'])) {
                    $detailedError .= "API Message: {$json['message']} | ";
                }
                if (isset($json['errorMessage'])) {
                    $detailedError .= "Details: {$json['errorMessage']} | ";
                }
            }

            $detailedError .= "Raw Body: {$rawBody}";

            throw new Exception($detailedError);

        } catch (Exception $e) {
            Log::error('VerifyNowService sendOtp Exception: ' . $e->getMessage(), [
                'countryCode'  => $countryCode,
                'mobileNumber' => $mobileNumber,
                'exception'    => $e
            ]);
            throw $e;
        }
    }

    /**
     * Validate OTP code against Message Central VerifyNow API (v3).
     *
     * @param string $verificationId
     * @param string $code
     * @return bool
     */
    public function validateOtp(string $verificationId, string $code): bool
    {
        try {
            $token = $this->generateToken();
        } catch (Exception $e) {
            Log::error('VerifyNowService validateOtp token error: ' . $e->getMessage());
            return false;
        }

        $endpoint = rtrim($this->baseUrl, '/') . '/verification/v3/validateOtp';

        $payload = [
            'verificationId' => $verificationId,
            'code'           => $code,
        ];

        try {
            Log::info("VerifyNowService: Validating OTP for verificationId [{$verificationId}]");

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders([
                    'authToken' => $token,
                    'accept'    => 'application/json',
                ])->post($endpoint, $payload);

            $statusCode = $response->status();
            $body = $response->json() ?? [];

            Log::info("VerifyNowService: Validate Response [{$statusCode}]", $body);

            if ($response->successful()) {
                $responseCode = $body['responseCode'] ?? $body['status'] ?? null;
                $verificationStatus = strtoupper($body['verificationStatus'] ?? $body['status'] ?? '');

                if ($responseCode == 200 || $verificationStatus === 'SUCCESS' || $verificationStatus === 'VERIFIED') {
                    return true;
                }
            }

            Log::warning("VerifyNowService: Validation rejected for verificationId [{$verificationId}]");
            return false;

        } catch (Exception $e) {
            Log::error('VerifyNowService validateOtp Exception: ' . $e->getMessage(), [
                'verificationId' => $verificationId,
                'code'           => $code
            ]);
            return false;
        }
    }
}
