<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Events\CallInitiated;
use App\Events\CallAccepted;
use App\Events\CallDeclined;
use App\Events\CallEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Peterujah\Agora\Agora;
use Peterujah\Agora\User as AgoraUser;
use Peterujah\Agora\Roles;
use Peterujah\Agora\Builders\RtcToken;

class CallController extends Controller
{
    /**
     * Initiate a call request.
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $callerId = Auth::id();
        $receiverId = (int) $request->receiver_id;

        if ($callerId === $receiverId) {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك الاتصال بنفسك.'], 400);
        }

        $caller = Auth::user();
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

            // Generate token for Caller (User A) using caller's ID as the Agora UID
            $callerAgoraUser = (new AgoraUser($callerId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $callerToken = RtcToken::buildTokenWithUid($client, $callerAgoraUser);

            // Generate token for Receiver (User B) using receiver's ID as the Agora UID
            $receiverAgoraUser = (new AgoraUser($receiverId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $receiverToken = RtcToken::buildTokenWithUid($client, $receiverAgoraUser);

            // Broadcast the call initiation event to the receiver
            $callerName = $caller->first_name . ' ' . $caller->last_name;
            $callerAvatar = $caller->avatar_url;

            broadcast(new CallInitiated(
                $callerId,
                $callerName,
                $callerAvatar,
                $receiverId,
                $channelName,
                $receiverToken
            ))->toOthers();

            return response()->json([
                'status' => 'success',
                'channel_name' => $channelName,
                'token' => $callerToken,
                'caller_id' => $callerId,
                'receiver_id' => $receiverId,
                'receiver_name' => $receiver->first_name . ' ' . $receiver->last_name,
                'receiver_avatar' => $receiver->avatar_url,
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
        $request->validate([
            'caller_id' => 'required|exists:users,id',
            'channel_name' => 'required|string',
        ]);

        $callerId = (int) $request->caller_id;
        $receiverId = Auth::id();
        $channelName = $request->channel_name;

        // Broadcast to the caller that the call is accepted
        broadcast(new CallAccepted($callerId, $receiverId, $channelName))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * Decline an incoming call.
     */
    public function declineCall(Request $request)
    {
        $request->validate([
            'caller_id' => 'required|exists:users,id',
        ]);

        $callerId = (int) $request->caller_id;
        $receiverId = Auth::id();

        // Broadcast to the caller that the call was declined
        broadcast(new CallDeclined($callerId, $receiverId))->toOthers();

        return response()->json(['status' => 'success']);
    }

    /**
     * End or cancel a call.
     */
    public function endCall(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'channel_name' => 'required|string',
        ]);

        $targetUserId = (int) $request->target_user_id;
        $channelName = $request->channel_name;

        // Broadcast to the other user that the call has ended
        broadcast(new CallEnded($targetUserId, $channelName))->toOthers();

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

        $callerId = Auth::id();
        $groupId = (int) $request->group_id;

        $caller = Auth::user();
        $group = \App\Models\Group::with(['members' => function($q) {
            $q->where('is_active', 1);
        }])->find($groupId);

        if (!$group) {
            return response()->json(['status' => 'error', 'message' => 'المجموعة غير موجودة.'], 404);
        }

        // Check if caller is member
        $isMember = $group->members->contains('user_id', $callerId);
        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالاتصال في هذه المجموعة.'], 403);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        // Unique channel name for the group call
        $channelName = 'group_call_' . $groupId . '_' . time();

        try {
            $expireTime = time() + 3600; // 1 hour expiration

            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            // Generate token for Caller (uid matches callerId)
            $callerAgoraUser = (new AgoraUser($callerId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $callerToken = RtcToken::buildTokenWithUid($client, $callerAgoraUser);

            $callerName = $caller->first_name . ' ' . $caller->last_name;
            $callerAvatar = $caller->avatar_url;

            // Broadcast to all other active group members
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
                    ))->toOthers();
                }
            }

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

        $userId = Auth::id();
        $groupId = (int) $request->group_id;
        $channelName = $request->channel_name;

        // Verify membership
        $isMember = \App\Models\GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->exists();

        if (!$isMember) {
            return response()->json(['status' => 'error', 'message' => 'غير مسموح لك بالانضمام لهذه المكالمة.'], 403);
        }

        $appId = env('AGORA_APP_ID');
        $appCertificate = env('AGORA_APP_CERTIFICATE');

        if (!$appId || !$appCertificate) {
            return response()->json(['status' => 'error', 'message' => 'لم يتم إعداد مفاتيح Agora بشكل صحيح في الخادم.'], 500);
        }

        try {
            $expireTime = time() + 3600;

            $client = new Agora($appId, $appCertificate);
            $client->setExpiration($expireTime);

            // Generate token for UID = userId
            $agoraUser = (new AgoraUser($userId))
                ->setChannel($channelName)
                ->setRole(Roles::RTC_PUBLISHER)
                ->setPrivilegeExpire($expireTime);
            $token = RtcToken::buildTokenWithUid($client, $agoraUser);

            return response()->json([
                'status' => 'success',
                'token' => $token,
                'agora_app_id' => $appId,
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'فشل الانضمام للمكالمة: ' . $e->getMessage()], 500);
        }
    }
}
