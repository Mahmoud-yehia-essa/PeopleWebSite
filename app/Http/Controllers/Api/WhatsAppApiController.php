<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppApiController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * التحقق من مفتاح الـ API
     */
    protected function authenticateApiKey(Request $request): ?WhatsAppSetting
    {
        $settings = $this->whatsappService->getSettings();

        $providedKey = $request->input('apikey')
            ?? $request->input('api_key')
            ?? $request->header('x-api-key')
            ?? $request->bearerToken();

        if (empty($providedKey) || !hash_equals((string)$settings->api_key, (string)$providedKey)) {
            return null;
        }

        return $settings;
    }

    /**
     * إرسال رسالة واتساب عبر الـ API (يدعم كلاً من GET و POST تماماً مثل TextMeBot)
     *
     * GET  /api/whatsapp/send?apikey={KEY}&phone={PHONE}&text={TEXT}
     * POST /api/whatsapp/send
     */
    public function send(Request $request)
    {
        $settings = $this->authenticateApiKey($request);

        if (!$settings) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو غير مصرح به (Invalid or missing API key).',
            ], 401);
        }

        $phone = $request->input('phone') 
            ?? $request->input('recipient') 
            ?? $request->input('to');

        $message = $request->input('text') 
            ?? $request->input('message') 
            ?? $request->input('body');

        if (empty($phone)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'رقم المستلم مطلوب (phone / recipient is required).',
            ], 422);
        }

        if (empty($message)) {
            return response()->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'نص الرسالة مطلوب (text / message is required).',
            ], 422);
        }

        $result = $this->whatsappService->sendMessage($phone, $message, 'api');

        if ($result['success']) {
            return response()->json([
                'success'    => true,
                'status'     => 'sent',
                'message'    => 'تم إرسال الرسالة بنجاح عبر الواتساب.',
                'recipient'  => $result['recipient'] ?? $phone,
                'message_id' => $result['message_id'] ?? null,
                'timestamp'  => now()->toIso8601String(),
            ], 200);
        }

        return response()->json([
            'success'   => false,
            'status'    => 'failed',
            'message'   => $result['message'],
            'recipient' => $phone,
            'timestamp' => now()->toIso8601String(),
        ], 400);
    }

    /**
     * الاستعلام عن حالة اتصال الواتساب برمجياً عبر الـ API
     *
     * GET /api/whatsapp/status?apikey={KEY}
     */
    public function status(Request $request)
    {
        $settings = $this->authenticateApiKey($request);

        if (!$settings) {
            return response()->json([
                'success' => false,
                'status'  => 'unauthorized',
                'message' => 'مفتاح API غير صالح أو مفقود.',
            ], 401);
        }

        $liveStatus = $this->whatsappService->getStatus();

        return response()->json([
            'success'         => true,
            'is_connected'    => (bool)($liveStatus['is_connected'] ?? false),
            'status'          => $liveStatus['status'] ?? 'unknown',
            'connected_phone' => $liveStatus['phone'] ?? $settings->connected_phone,
            'connected_name'  => $liveStatus['name'] ?? $settings->connected_name,
            'engine'          => $settings->engine,
            'message'         => $liveStatus['message'] ?? '',
            'timestamp'       => now()->toIso8601String(),
        ]);
    }
}
