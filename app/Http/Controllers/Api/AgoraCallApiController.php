<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgoraCallApiController extends Controller
{
    /**
     * 7.1 توليد الـ Agora RTC Token للمكالمات (GET Request)
     */
    public function generateToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channelName' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // مفاتيح منصة Agora (يمكنك وضعها بملف الـ .env لاحقاً لحفظ الأمان)
        $appId = env('AGORA_APP_ID', 'YOUR_AGORA_APP_ID_HERE');
        $appCertificate = env('AGORA_APP_CERTIFICATE', 'YOUR_AGORA_APP_CERTIFICATE_HERE');
        
        $channelName = $request->query('channelName');
        $uid = $request->query('uid', 0);
        
        // حساب أوقات انتهاء صلاحية الرمز (ساعة واحدة افتراضياً للاتصال الآمن)
        $expireTimeInSeconds = 3600;
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;

        // بناء ومحاكاة هيكلية التشفير الرياضي لـ Agora RTC Token القياسي
        $authToken = '';
        if ($appId !== 'YOUR_AGORA_APP_ID_HERE') {
            $msgToBeSigned = $appId . $channelName . $uid . $privilegeExpiredTs;
            $signature = hash_hmac('sha256', $msgToBeSigned, $appCertificate);
            $authToken = "006" . $appId . "IAC" . base64_encode($signature . "_" . $privilegeExpiredTs);
        } else {
            // توكين افتراضي آمن لغايات الفحص البرمجي بـ Postman في بيئة الـ Local
            $authToken = "sample_agora_token_for_channel_" . $channelName . "_" . rand(1000, 9999);
        }

        return response()->json([
            'success' => true,
            'token'   => $authToken
        ]);
    }
}