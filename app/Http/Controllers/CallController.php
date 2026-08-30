<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallDeclined;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Peterujah\Agora\Agora;
use Peterujah\Agora\User as AgoraUser;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class CallController extends Controller
{
    /**
     * Resolve authenticated user from Session, Sanctum, Bearer token, or request ID
     */
    private function resolveCaller(Request $request)
    {
        $user = $request->user() ?: auth('sanctum')->user() ?: Auth::user();
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($accessToken && $accessToken->tokenable) {
                    $user = $accessToken->tokenable;
                }
            }
        }
        if (!$user && $request->filled('caller_id')) {
            $user = User::find($request->caller_id);
        }
        if (!$user && $request->filled('user_id')) {
            $user = User::find($request->user_id);
        }
        if ($user) {
            auth()->setUser($user);
            auth('sanctum')->setUser($user);
        }
        return $user;
    }

    /**
     * Initiate a call request.
     */
    public function initiateCall(Request $request)
    {
        $receiverId = (int) ($request->receiver_id ?? $request->receiverId ?? $request->input('receiver_id') ?? $request->input('receiverId'));

        if (!$receiverId || !User::where('id', $receiverId)->exists()) {
            return response()->json(['status' => 'error', 'message' => 'receiver_id is invalid or missing'], 422);
        }

        $caller = $this->resolveCaller($request);
        if (!$caller) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }

        $callerId = (int)$caller->id;

        if ($callerId === $receiverId) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك الاتصال بنفسك.'], 400);
        }

        // 1. Check if Caller is currently in an active call
        $callerActive = Cache::get("active_call_user_{$callerId}");
        if ($callerActive && !empty($callerActive['channel_name'])) {
            return response()->json([
                'status' => 'busy',
                'busy' => true,
                'message' => 'أنت في مكالمة أخرى حالياً.'
            ], 409);
        }

        // 2. Check if Receiver is currently in an active call
        $receiverActive = Cache::get("active_call_user_{$receiverId}");
        if ($receiverActive && !empty($receiverActive['channel_name'])) {
            return response()->json([
                'status' => 'busy',
                'busy' => true,
                'message' => 'المستخدم في مكالمة أخرى حالياً.'
            ], 409);
        }

        $receiver = User::find($receiverId);

        if (!$receiver || !$receiver->is_active) {
            return response()->json(['status' => 'error', 'message' => 'المستخدم الآخر غير نشط حالياً.'], 404);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        // Generate a unique channel name using current timestamp to avoid collisions
        $channelName = 'call_' . min($callerId, $receiverId) . '_' . max($callerId, $receiverId) . '_' . time();

        try {
            $expireTime = time() + 3600; // 1 hour expiration

            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            // Generate token for Caller (User A) using caller ID as the Agora UID
            $callerAgoraUser = (new AgoraUser($callerId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $callerToken = RtcToken::buildTokenWithUid($client, $callerAgoraUser);

            // Generate token for Receiver (User B) using receiver ID as the Agora UID
            $receiverAgoraUser = (new AgoraUser($receiverId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $receiverToken = RtcToken::buildTokenWithUid($client, $receiverAgoraUser);

            $callerName = trim(($caller->first_name ?? '') . ' ' . ($caller->last_name ?? ''));
            if (empty($callerName)) $callerName = $caller->name ?? 'مستخدم';
            $callerAvatar = $caller->avatar_url ?? $caller->profile_picture ?? null;

            // Send FCM Call Notification to Receiver
            try {
                app(\App\Services\FcmNotificationService::class)->sendChatNotification(
                    $receiverId,
                    $callerName,
                    '📞 مكالمة صوتية واردة...',
                    (int)$callerId,
                    $callerAvatar,
                    $caller->token ?? null
                );
            } catch (\Throwable $e) {}

            // Broadcast the call initiation event to the receiver
            broadcast(new CallInitiated(
                $callerId,
                $callerName,
                $callerAvatar,
                $receiverId,
                $channelName,
                $receiverToken
            ));

            // Record active call for both users
            $callData = [
                'channel_name' => $channelName,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'status' => 'ringing',
                'timestamp' => time(),
            ];
            Cache::put("active_call_user_{$callerId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_user_{$receiverId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_channel_{$channelName}", $callData, now()->addMinutes(60));

            return response()->json([
                'status' => 'success',
                'channel_name' => $channelName,
                'token' => $callerToken,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'receiver_name' => trim(($receiver->first_name ?? '') . ' ' . ($receiver->last_name ?? '')),
                'receiver_avatar' => $receiver->avatar_url ?? $receiver->profile_picture ?? null,
                'agora_app_id' => $appId
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الاتصال: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Accept an incoming call.
     */
    public function acceptCall(Request $request)
    {
        $caller = $this->resolveCaller($request);
        $callerId = (int) ($request->caller_id ?? $request->callerId ?? $request->input('caller_id') ?? $request->input('callerId'));
        $channelName = $request->channel_name ?? $request->channelName ?? $request->input('channel_name');
        $receiverId = $caller ? (int)$caller->id : (auth('sanctum')->id() ?? Auth::id());

        if ($callerId && $channelName) {
            $callData = Cache::get("active_call_channel_{$channelName}") ?? [
                'channel_name' => $channelName,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
            ];
            $callData['status'] = 'in_call';
            $callData['accepted_at'] = time();

            Cache::put("active_call_user_{$callerId}", $callData, now()->addHours(2));
            Cache::put("active_call_user_{$receiverId}", $callData, now()->addHours(2));
            Cache::put("active_call_channel_{$channelName}", $callData, now()->addHours(2));

            broadcast(new CallAccepted($callerId, $receiverId, $channelName));
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Decline an incoming call.
     */
    public function declineCall(Request $request)
    {
        $caller = $this->resolveCaller($request);
        $callerId = (int) ($request->caller_id ?? $request->callerId ?? $request->input('caller_id') ?? $request->input('callerId'));
        $receiverId = $caller ? (int)$caller->id : (auth('sanctum')->id() ?? Auth::id());
        $channelName = $request->channel_name ?? $request->channelName ?? $request->input('channel_name');

        if ($callerId) {
            Cache::forget("active_call_user_{$callerId}");
            broadcast(new CallDeclined($callerId, $receiverId));
        }
        if ($receiverId) {
            Cache::forget("active_call_user_{$receiverId}");
        }
        if ($channelName) {
            Cache::forget("active_call_channel_{$channelName}");
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * End or cancel a call.
     */
    public function endCall(Request $request)
    {
        $caller = $this->resolveCaller($request);
        $targetUserId = (int) ($request->target_user_id ?? $request->targetUserId ?? $request->input('target_user_id') ?? $request->input('targetUserId'));
        $channelName = $request->channel_name ?? $request->channelName ?? $request->input('channel_name') ?? '';
        $currentUserId = $caller ? (int)$caller->id : (auth('sanctum')->id() ?? Auth::id());

        if (!$targetUserId && $channelName) {
            $parts = explode('_', $channelName);
            if (count($parts) >= 3) {
                $id1 = (int) $parts[1];
                $id2 = (int) $parts[2];
                if ($currentUserId) {
                    $targetUserId = ($id1 === (int) $currentUserId) ? $id2 : $id1;
                } else {
                    $targetUserId = $id1 ?: $id2;
                }
            }
        }

        if ($channelName) {
            $callData = Cache::get("active_call_channel_{$channelName}");
            if ($callData) {
                if (!empty($callData['caller_id'])) Cache::forget("active_call_user_{$callData['caller_id']}");
                if (!empty($callData['receiver_id'])) Cache::forget("active_call_user_{$callData['receiver_id']}");
            }
            Cache::forget("active_call_channel_{$channelName}");
        }

        if ($targetUserId) {
            Cache::forget("active_call_user_{$targetUserId}");
        }
        if ($currentUserId) {
            Cache::forget("active_call_user_{$currentUserId}");
        }

        if ($targetUserId && $channelName) {
            broadcast(new CallEnded($targetUserId, $channelName));
            if ($currentUserId && (int)$currentUserId !== (int)$targetUserId) {
                broadcast(new CallEnded($currentUserId, $channelName));
            }
            try {
                app(\App\Services\FcmNotificationService::class)->sendCallEndNotification(
                    $targetUserId,
                    $channelName,
                    (int)$currentUserId
                );
            } catch (\Throwable $e) {}
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Initiate a group call request.
     */
    public function initiateGroupCall(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $caller = $this->resolveCaller($request);
        if (!$caller) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }
        $callerId = (int)$caller->id;
        $groupId = (int) $request->group_id;

        $group = \App\Models\Group::with(['members' => function($q) {
            $q->where('is_active', 1);
        }])->find($groupId);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        $isMember = $group->members->contains('user_id', $callerId);
        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالاتصال في هذه المجموعة.'], 403);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        $channelName = 'group_call_' . $groupId . '_' . time();

        try {
            $expireTime = time() + 3600;

            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            $callerAgoraUser = (new AgoraUser($callerId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $callerToken = RtcToken::buildTokenWithUid($client, $callerAgoraUser);

            $callerName = trim(($caller->first_name ?? '') . ' ' . ($caller->last_name ?? ''));
            if (empty($callerName)) $callerName = $caller->name ?? 'مستخدم';
            $callerAvatar = $caller->avatar_url ?? $caller->profile_picture ?? null;

            foreach ($group->members as $member) {
                if ((int)$member->user_id !== (int)$callerId) {
                    broadcast(new \App\Events\GroupCallInitiated(
                        $callerId,
                        $callerName,
                        $callerAvatar,
                        $groupId,
                        $group->name,
                        $channelName,
                        (int)$member->user_id
                    ));
                }
            }

            // Record active call for both users
            $callData = [
                'channel_name' => $channelName,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'status' => 'ringing',
                'timestamp' => time(),
            ];
            Cache::put("active_call_user_{$callerId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_user_{$receiverId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_channel_{$channelName}", $callData, now()->addMinutes(60));

            return response()->json([
                'status' => 'success',
                'channel_name' => $channelName,
                'token' => $callerToken,
                'caller_id' => $callerId,
                'group_id' => $groupId,
                'group_name' => $group->name,
                'agora_app_id' => $appId
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الاتصال الجماعي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Join an active group call (generate a token for the channel).
     */
    public function joinGroupCall(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'channel_name' => 'required|string',
        ]);

        $caller = $this->resolveCaller($request);
        if (!$caller) {
            return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
        }
        $callerId = (int)$caller->id;
        $groupId = (int) $request->group_id;
        $channelName = $request->channel_name;

        $group = \App\Models\Group::with(['members' => function($q) {
            $q->where('is_active', 1);
        }])->find($groupId);

        if (!$group || !$group->members->contains('user_id', $callerId)) {
            return response()->json(['status' => 'error', 'message' => 'غير مصرح لك بالانضمام لهذه المكالمة.'], 403);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        try {
            $expireTime = time() + 3600;
            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            $agoraUser = (new AgoraUser($callerId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $token = RtcToken::buildTokenWithUid($client, $agoraUser);

            // Record active call for both users
            $callData = [
                'channel_name' => $channelName,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'status' => 'ringing',
                'timestamp' => time(),
            ];
            Cache::put("active_call_user_{$callerId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_user_{$receiverId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_channel_{$channelName}", $callData, now()->addMinutes(60));

            return response()->json([
                'status' => 'success',
                'channel_name' => $channelName,
                'token' => $token,
                'caller_id' => $callerId,
                'group_id' => $groupId,
                'group_name' => $group->name,
                'agora_app_id' => $appId
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز الانضمام للمكالمة: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate Agora Token dynamically for any channel.
     */
    public function generateToken(Request $request)
    {
        $caller = $this->resolveCaller($request);
        $userId = $caller ? (int)$caller->id : (int) ($request->user_id ?? $request->userId ?? 0);
        $channelName = $request->channel_name ?? $request->channelName ?? $request->input('channel_name') ?? 'wiselook_call';

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
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

            // Record active call for both users
            $callData = [
                'channel_name' => $channelName,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'status' => 'ringing',
                'timestamp' => time(),
            ];
            Cache::put("active_call_user_{$callerId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_user_{$receiverId}", $callData, now()->addMinutes(30));
            Cache::put("active_call_channel_{$channelName}", $callData, now()->addMinutes(60));

            return response()->json([
                'status' => 'success',
                'channel_name' => $channelName,
                'token' => $token,
                'user_id' => $userId,
                'agora_app_id' => $appId
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'فشل توليد رمز Agora: ' . $e->getMessage()], 500);
        }
    }
}
