<?php

namespace App\Services;

use App\Models\WhatsAppSetting;
use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WhatsAppService
{
    /**
     * جلب إعدادات الواتساب الحالية
     */
    public function getSettings(): WhatsAppSetting
    {
        return WhatsAppSetting::getSettings();
    }

    /**
     * فحص الحالة الحية وجلب رمز QR أو بيانات الاتصال
     */
    public function getStatus(): array
    {
        $settings = $this->getSettings();

        if ($settings->engine === 'textmebot') {
            return $this->getTextMeBotStatus($settings);
        }

        return $this->getLocalStatus($settings);
    }

    /**
     * فحص حالة السيرفر الداخلي (Baileys)
     */
    protected function getLocalStatus(WhatsAppSetting $settings): array
    {
        $baseUrl = rtrim($settings->local_server_url ?: 'http://127.0.0.1:3001', '/');

        try {
            $response = Http::timeout(2)->get("{$baseUrl}/status");

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = (bool)($data['is_connected'] ?? false);
                $phone = $data['phone'] ?? null;
                $name = $data['name'] ?? null;

                // تحديث حالة الاتصال في قاعدة البيانات إذا تغيرت
                if ($settings->is_connected !== $isConnected || ($phone && $settings->connected_phone !== $phone)) {
                    $settings->update([
                        'is_connected'      => $isConnected,
                        'connected_phone'   => $phone ?: $settings->connected_phone,
                        'connected_name'    => $name ?: $settings->connected_name,
                        'last_connected_at' => $isConnected ? now() : $settings->last_connected_at,
                    ]);
                }

                return [
                    'online'             => true,
                    'is_connected'       => $isConnected,
                    'has_session'        => (bool)($data['has_session'] ?? false),
                    'status'             => $data['status'] ?? 'unknown',
                    'phone'              => $phone ?: $settings->connected_phone,
                    'name'               => $name ?: $settings->connected_name,
                    'qr'                 => $data['qr'] ?? null,
                    'pairing_code'       => $data['pairing_code'] ?? null,
                    'reconnect_attempts' => $data['reconnect_attempts'] ?? 0,
                    'engine'             => 'local',
                    'message'            => $isConnected ? 'متصل بنجاح وجاهز للإرسال' : ($data['status'] === 'reconnecting' ? 'جاري استعادة الاتصال بالواتساب تلقائياً...' : 'بانتظار مسح رمز QR من تطبيق الواتساب'),
                ];
            }
        } catch (Throwable $e) {
            // محاولة التشغيل التلقائي إذا كان الخادم المحلي متوقفاً
            $this->attemptAutoStartService();
        }

        return [
            'online'       => false,
            'is_connected' => false,
            'has_session'  => !empty($settings->connected_phone),
            'status'       => 'service_offline',
            'phone'        => $settings->connected_phone,
            'name'         => $settings->connected_name,
            'qr'           => null,
            'pairing_code' => null,
            'engine'       => 'local',
            'message'      => 'محرك الواتساب الداخلي متوقف حالياً. يمكنك تشغيله فوراً بالضغط على زر تشغيل السيرفر أدناه.',
        ];
    }

    /**
     * محاولة تشغيل خادم الواتساب تلقائياً في الخلفية
     */
    public function attemptAutoStartService(): void
    {
        // تجنب تكرار محاولات التشغيل المتزامنة باستخدام cache lock
        if (\Illuminate\Support\Facades\Cache::has('wa_auto_starting')) {
            return;
        }
        \Illuminate\Support\Facades\Cache::put('wa_auto_starting', true, 10);

        try {
            $serviceDir = base_path('whatsapp-service');
            $logPath = storage_path('logs/whatsapp.log');
            if (is_dir($serviceDir)) {
                $cmd = "cd " . escapeshellarg($serviceDir) . " && (nohup node server.js >> " . escapeshellarg($logPath) . " 2>&1 &)";
                @exec($cmd);
            }
        } catch (Throwable $e) {}
    }

    /**
     * تشغيل خادم الواتساب يدوياً والتحقق من عمله
     */
    public function startLocalService(): array
    {
        $serviceDir = base_path('whatsapp-service');
        $logPath = storage_path('logs/whatsapp.log');

        try {
            $res = Http::timeout(1)->get('http://127.0.0.1:3001/status');
            if ($res->successful()) {
                return ['success' => true, 'message' => 'خادم الواتساب الداخلي يعمل بالفعل على المنفذ 3001.'];
            }
        } catch (Throwable $e) {}

        if (!is_dir($serviceDir)) {
            return ['success' => false, 'message' => 'مجلد whatsapp-service غير موجود على السيرفر.'];
        }

        $cmd = "cd " . escapeshellarg($serviceDir) . " && (nohup node server.js >> " . escapeshellarg($logPath) . " 2>&1 &)";
        @exec($cmd);

        usleep(1500000); // الانتظار 1.5 ثانية للتشغيل

        try {
            $res = Http::timeout(2)->get('http://127.0.0.1:3001/status');
            if ($res->successful()) {
                return ['success' => true, 'message' => 'تم تشغيل خادم الواتساب بنجاح وجاري الاتصال!'];
            }
        } catch (Throwable $e) {}

        return [
            'success' => true,
            'message' => 'تم إرسال أمر تشغيل خادم الواتساب في الخلفية بنجاح، وسيبدأ خلال ثوانٍ.',
        ];
    }

    /**
     * فحص حالة بوابة TextMeBot
     */
    protected function getTextMeBotStatus(WhatsAppSetting $settings): array
    {
        $apiKey = $settings->textmebot_api_key;

        if (empty($apiKey)) {
            return [
                'online'       => false,
                'is_connected' => false,
                'status'       => 'missing_api_key',
                'phone'        => $settings->textmebot_phone,
                'name'         => 'TextMeBot Gateway',
                'qr'           => null,
                'engine'       => 'textmebot',
                'message'      => 'يرجى إدخال مفتاح API الخاص بـ TextMeBot أولاً',
            ];
        }

        try {
            $response = Http::timeout(5)->get("https://api.textmebot.com/status.php", [
                'apikey' => $apiKey,
                'json'   => 'yes',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $isConnected = ($data['status'] ?? '') === 'connected' || ($data['connected'] ?? false) === true;

                return [
                    'online'       => true,
                    'is_connected' => $isConnected,
                    'status'       => $data['status'] ?? ($isConnected ? 'connected' : 'waiting'),
                    'phone'        => $settings->textmebot_phone,
                    'name'         => 'TextMeBot Connected',
                    'qr'           => $data['qr'] ?? null,
                    'pairing_code' => $data['pairing_code'] ?? null,
                    'engine'       => 'textmebot',
                    'message'      => $data['message'] ?? ($isConnected ? 'متصل بنجاح عبر TextMeBot' : 'بانتظار الربط في TextMeBot'),
                ];
            }
        } catch (Throwable $e) {
            Log::warning('TextMeBot status error: ' . $e->getMessage());
        }

        return [
            'online'       => false,
            'is_connected' => false,
            'status'       => 'error',
            'phone'        => $settings->textmebot_phone,
            'name'         => 'TextMeBot Gateway',
            'qr'           => null,
            'engine'       => 'textmebot',
            'message'      => 'تعذر التحقق من حالة TextMeBot، يرجى مراجعة الاتصال والمفتاح.',
        ];
    }

    /**
     * طلب كود الاقتران عبر رقم الهاتف (Pairing Code)
     */
    public function requestPairingCode(string $phone): array
    {
        $settings = $this->getSettings();
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleanPhone, '00')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if ($settings->engine === 'local') {
            $baseUrl = rtrim($settings->local_server_url ?: 'http://127.0.0.1:3001', '/');
            try {
                $response = Http::timeout(8)->post("{$baseUrl}/pairing-code", [
                    'phone' => $cleanPhone
                ]);

                if ($response->successful()) {
                    return $response->json();
                }
                return ['success' => false, 'message' => $response->json('message') ?? 'فشل طلب كود الاقتران'];
            } catch (Throwable $e) {
                return ['success' => false, 'message' => 'تعذر الاتصال بمحرك الواتساب: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'خاصية كود الاقتران متاحة للمحرك الداخلي المباشر'];
    }

    /**
     * إرسال رسالة واتساب وتسجيلها في قاعدة البيانات
     */
    public function sendMessage(string $phone, string $message, string $senderType = 'admin'): array
    {
        $settings = $this->getSettings();

        // تنظيف رقم الهاتف وإزالة أي فراغات أو علامات زائد
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '00')) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if (strlen($cleanPhone) < 7) {
            return [
                'success' => false,
                'message' => 'رقم الهاتف غير صالح. يجب أن يحتوي على كود الدولة وألا يقل عن 7 أرقام.',
            ];
        }

        if (empty(trim($message))) {
            return [
                'success' => false,
                'message' => 'نص الرسالة مطلوب ولا يمكن أن يكون فارغاً.',
            ];
        }

        // إنشاء سجل الرسالة في قاعدة البيانات بحالة pending
        $logRecord = WhatsAppMessage::create([
            'recipient_phone' => $cleanPhone,
            'message'         => $message,
            'status'          => 'pending',
            'engine_used'     => $settings->engine,
            'sender_type'     => $senderType,
        ]);

        if ($settings->engine === 'textmebot') {
            return $this->sendViaTextMeBot($settings, $cleanPhone, $message, $logRecord);
        }

        return $this->sendViaLocal($settings, $cleanPhone, $message, $logRecord);
    }

    /**
     * إرسال عبر السيرفر الداخلي
     */
    protected function sendViaLocal(WhatsAppSetting $settings, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $baseUrl = rtrim($settings->local_server_url ?: 'http://127.0.0.1:3001', '/');

        try {
            $response = Http::timeout(10)->post("{$baseUrl}/send", [
                'phone'   => $phone,
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['success'] ?? false) === true) {
                $logRecord->update([
                    'status'        => 'sent',
                    'message_id'    => $data['message_id'] ?? null,
                    'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);

                return [
                    'success'    => true,
                    'message'    => 'تم إرسال الرسالة بنجاح عبر الواتساب.',
                    'message_id' => $data['message_id'] ?? null,
                    'recipient'  => $phone,
                ];
            }

            $errorMessage = $data['message'] ?? 'فشل إرسال الرسالة من خلال السيرفر الداخلي.';
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
                'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (Throwable $e) {
            $errorMessage = 'تعذر الاتصال بالسيرفر الداخلي: ' . $e->getMessage();
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        }
    }

    /**
     * إرسال عبر TextMeBot API
     */
    protected function sendViaTextMeBot(WhatsAppSetting $settings, string $phone, string $message, WhatsAppMessage $logRecord): array
    {
        $apiKey = $settings->textmebot_api_key;

        if (empty($apiKey)) {
            $err = 'مفتاح TextMeBot API غير مضبوط في الإعدادات.';
            $logRecord->update(['status' => 'failed', 'error_message' => $err]);
            return ['success' => false, 'message' => $err];
        }

        try {
            $response = Http::timeout(12)->get("https://api.textmebot.com/send.php", [
                'recipient' => $phone,
                'apikey'    => $apiKey,
                'text'      => $message,
                'json'      => 'yes',
            ]);

            $body = $response->body();
            $isJson = str_starts_with(trim($body), '{');
            $data = $isJson ? $response->json() : ['raw_response' => $body];

            if ($response->successful()) {
                $logRecord->update([
                    'status'        => 'sent',
                    'message_id'    => $data['message_id'] ?? $data['id'] ?? null,
                    'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                ]);

                return [
                    'success'   => true,
                    'message'   => 'تم إرسال الرسالة بنجاح عبر بوابة TextMeBot.',
                    'recipient' => $phone,
                ];
            }

            $err = $data['message'] ?? $body ?: 'خطأ في استجابة TextMeBot';
            $logRecord->update([
                'status'        => 'failed',
                'error_message' => $err,
                'response_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            ]);

            return ['success' => false, 'message' => $err];
        } catch (Throwable $e) {
            $err = 'تعذر الاتصال بـ TextMeBot: ' . $e->getMessage();
            $logRecord->update(['status' => 'failed', 'error_message' => $err]);
            return ['success' => false, 'message' => $err];
        }
    }

    /**
     * قطع الاتصال وفك الارتباط
     */
    public function disconnect(): array
    {
        $settings = $this->getSettings();

        if ($settings->engine === 'local') {
            $baseUrl = rtrim($settings->local_server_url ?: 'http://127.0.0.1:3001', '/');
            try {
                Http::timeout(5)->post("{$baseUrl}/logout");
            } catch (Throwable $e) {
                Log::warning('Logout request failed: ' . $e->getMessage());
            }
        }

        $settings->update([
            'is_connected'    => false,
            'connected_phone' => null,
            'connected_name'  => null,
        ]);

        return [
            'success' => true,
            'message' => 'تم قطع الاتصال بنجاح وإعادة ضبط الجلسة.',
        ];
    }

    /**
     * إعادة توليد مفتاح API جديد
     */
    public function regenerateApiKey(): string
    {
        $settings = $this->getSettings();
        $newKey = 'wl_wa_' . \Illuminate\Support\Str::random(36);
        $settings->update(['api_key' => $newKey]);
        return $newKey;
    }
}
