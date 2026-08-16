<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Peterujah\Agora\Agora;
use Peterujah\Agora\User as AgoraUser;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class AgoraCallApiController extends Controller
{
    /**
     * Generate standard Agora RTC Token for calls.
     */
    public function generateToken(Request $request)
    {
        $channelName = $request->input('channelName') ?? $request->input('channel_name');
        if (!$channelName) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'channelName is required'], 400);
        }

        $userId = (int) ($request->input('uid') ?? Auth::id() ?? 0);

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'Agora keys not configured on server'], 500);
        }

        try {
            $expireTime = time() + 3600;

            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            $agoraUser = (new AgoraUser($userId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);

            $token = RtcToken::buildTokenWithUid($client, $agoraUser);

            return response()->json([
                'success'      => true,
                'status'       => 'success',
                'token'        => $token,
                'uid'          => $userId,
                'agora_app_id' => $appId
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'status' => 'error', 'message' => 'Failed to generate token: ' . $e->getMessage()], 500);
        }
    }
}