<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use App\Models\User;
use App\Models\NotificationForApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmNotificationService
{
    /**
     * إرسال إشعار FCM لعدة توكنات مع ضبط عدد البادج والبيانات الإضافية
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = [], int $badgeCount = 1): array
    {
        $validTokens = array_values(array_unique(array_filter($tokens, function ($token) {
            return !empty($token) && is_string($token);
        })));

        if (empty($validTokens)) {
            return [
                'success_count' => 0,
                'failure_count' => 0,
                'errors' => ['لا توجد رموز توكن صالحة للإرسال.']
            ];
        }

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        $badge = isset($data['badge']) ? (int)$data['badge'] : $badgeCount;
        if ($badge < 1) {
            $badge = 1;
        }

        try {
            $messaging = Firebase::messaging();

            $notification = Notification::create($title, $body);

            $stringData = array_map(function ($val) {
                return (string) $val;
            }, array_merge([
                'title'        => $title,
                'body'         => $body,
                'message'      => $body,
                'description'  => $body,
                'type'         => 'general',
                'path'         => 'general',
                'route'        => 'general',
                'badge'        => (string)$badge,
                'unread_count' => (string)$badge,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                'date'         => now()->toDateTimeString(),
            ], $data));

            $messageTemplate = CloudMessage::new()
                ->withNotification($notification)
                ->withData($stringData)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'channel_id' => 'regular_channel_v2',
                        'sound' => 'default',
                        'notification_count' => $badge,
                    ],
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => $badge,
                        ],
                    ],
                ]));

            $chunks = array_chunk($validTokens, 500);

            foreach ($chunks as $chunk) {
                try {
                    $report = $messaging->sendMulticast($messageTemplate, $chunk);
                    $successCount += $report->successes()->count();
                    $failureCount += $report->failures()->count();

                    if ($report->hasFailures()) {
                        foreach ($report->failures()->getItems() as $failure) {
                            $errors[] = $failure->error()->getMessage();
                        }
                    }
                } catch (Throwable $chunkException) {
                    $failureCount += count($chunk);
                    $errors[] = $chunkException->getMessage();
                    Log::warning('FCM chunk send error: ' . $chunkException->getMessage());
                }
            }
        } catch (Throwable $e) {
            Log::error('FCM Service General Error: ' . $e->getMessage());
            return [
                'success_count' => 0,
                'failure_count' => count($validTokens),
                'errors' => [$e->getMessage()]
            ];
        }

        return [
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'errors' => array_unique($errors)
        ];
    }

    /**
     * إرسال إشعار لمستخدم فردي مع احتساب البادج
     */
    public function sendToUser($userOrId, string $title, string $body, array $data = []): array
    {
        $user = $userOrId instanceof User ? $userOrId : User::find($userOrId);
        if (!$user || empty($user->token)) {
            return ['success_count' => 0, 'failure_count' => 1, 'errors' => ['User token not found']];
        }

        $userUnreadCount = $this->calculateUserUnreadCount($user->id);
        $data['badge'] = (string)$userUnreadCount;
        $data['unread_count'] = (string)$userUnreadCount;

        return $this->sendToTokens([$user->token], $title, $body, $data, $userUnreadCount);
    }

    /**
     * إرسال إشعار تعليق جديد على منشور
     */
    public function sendCommentNotification($targetUserId, string $senderName, string $postTitle, int $postId, int $senderId): array
    {
        $title = 'تعليق جديد';
        $body = "قام {$senderName} بالتعليق على موضوعك: \"{$postTitle}\"";
        $data = [
            'type'        => 'comment',
            'path'        => 'comment',
            'route'       => 'comment',
            'post_id'     => (string)$postId,
            'content_id'  => (string)$postId,
            'sender_id'   => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار إعجاب جديد على منشور
     */
    public function sendLikeNotification($targetUserId, string $senderName, string $postTitle, int $postId, int $senderId): array
    {
        $title = 'إعجاب جديد';
        $body = "قام {$senderName} بالإعجاب بموضوعك: \"{$postTitle}\"";
        $data = [
            'type'        => 'like',
            'path'        => 'post',
            'route'       => 'post',
            'post_id'     => (string)$postId,
            'content_id'  => (string)$postId,
            'sender_id'   => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار رد جديد على تعليق
     */
    public function sendReplyNotification($targetUserId, string $senderName, string $postTitle, int $postId, int $senderId): array
    {
        $title = 'رد جديد على تعليقك';
        $body = "قام {$senderName} بالرد على تعليقك في موضوع: \"{$postTitle}\"";
        $data = [
            'type'        => 'comment_reply',
            'path'        => 'comment',
            'route'       => 'comment',
            'post_id'     => (string)$postId,
            'content_id'  => (string)$postId,
            'sender_id'   => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار إشارة (Mention)
     */
    public function sendMentionNotification($targetUserId, string $senderName, string $contextSnippet, int $postId, int $senderId): array
    {
        $title = 'إشارة إليك';
        $body = "قام {$senderName} بالإشارة إليك في: \"{$contextSnippet}\"";
        $data = [
            'type'        => 'mention',
            'path'        => 'comment',
            'route'       => 'comment',
            'post_id'     => (string)$postId,
            'content_id'  => (string)$postId,
            'sender_id'   => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار طلب صداقة جديد
     */
    public function sendFriendRequestNotification($targetUserId, string $senderName, int $senderId): array
    {
        $title = 'طلب صداقة جديد';
        $body = "قام {$senderName} بإرسال طلب صداقة إليك.";
        $data = [
            'type'        => 'friend_request',
            'path'        => 'profile',
            'route'       => 'profile',
            'user_id'     => (string)$senderId,
            'sender_id'   => (string)$senderId,
            'profile_id'  => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار قبول طلب صداقة
     */
    public function sendFriendAcceptNotification($targetUserId, string $senderName, int $senderId): array
    {
        $title = 'قبول طلب صداقة';
        $body = "وافق {$senderName} على طلب الصداقة الخاص بك.";
        $data = [
            'type'        => 'friend_accept',
            'path'        => 'profile',
            'route'       => 'profile',
            'user_id'     => (string)$senderId,
            'sender_id'   => (string)$senderId,
            'profile_id'  => (string)$senderId,
            'sender_name' => $senderName,
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار رسالة خاصة جديدة في الشات (1-on-1 Chat)
     */
    public function sendChatNotification($targetUserId, string $senderName, string $messageText, int $senderId, ?string $senderPic = null, ?string $senderToken = null): array
    {
        $title = $senderName;
        $body = $messageText;
        $data = [
            'type'                => 'chat',
            'path'                => 'chat',
            'route'               => 'chat',
            'sender_id'           => (string)$senderId,
            'user_id'             => (string)$senderId,
            'sender_name'         => $senderName,
            'recipient_name'      => $senderName,
            'sender_profile_pic'  => $senderPic ? (filter_var($senderPic, FILTER_VALIDATE_URL) ? $senderPic : asset('new_wiselook/uploads/' . $senderPic)) : '',
            'sender_fcm_token'    => $senderToken ?? '',
        ];
        return $this->sendToUser($targetUserId, $title, $body, $data);
    }

    /**
     * إرسال إشعار رسالة في شات جماعي لجميع أعضاء المجموعة
     */
    public function sendGroupChatNotification(array $targetUserIds, string $groupName, string $senderName, string $messageText, int $groupId, ?string $groupImage = null, int $senderId = 0): array
    {
        $title = $groupName;
        $body = "{$senderName}: {$messageText}";
        $data = [
            'type'        => 'group_chat',
            'path'        => 'group_chat',
            'route'       => 'group_chat',
            'group_id'    => (string)$groupId,
            'groupId'     => (string)$groupId,
            'group_name'  => $groupName,
            'groupName'   => $groupName,
            'group_image' => $groupImage ? (filter_var($groupImage, FILTER_VALIDATE_URL) ? $groupImage : asset('new_wiselook/uploads/' . $groupImage)) : '',
            'sender_id'   => (string)$senderId,
            'sender_name' => $senderName,
        ];

        $users = User::whereIn('id', $targetUserIds)
            ->whereNotNull('token')
            ->where('token', '!=', '')
            ->get(['id', 'token']);

        $tokens = $users->pluck('token')->toArray();
        return $this->sendToTokens($tokens, $title, $body, $data, 1);
    }

    /**
     * إرسال إشعار إنهاء مكالمة (Call Ended) بدون صوت تنبيهي لتحديث الواجهة وإغلاق الشاشة فوراً
     */
    public function sendCallEndNotification($targetUserId, string $channelName, int $senderId = 0): array
    {
        $user = is_object($targetUserId) ? $targetUserId : User::find($targetUserId);
        if (!$user || empty($user->token)) {
            return ['success_count' => 0, 'failure_count' => 0];
        }

        $stringData = [
            'type'         => 'call_ended',
            'channel_name' => (string)$channelName,
            'sender_id'    => (string)$senderId,
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];

        try {
            $messaging = Firebase::messaging();
            $message = CloudMessage::withTarget('token', $user->token)
                ->withData($stringData)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'headers' => [
                        'apns-priority' => '10',
                    ],
                    'payload' => [
                        'aps' => [
                            'content-available' => 1,
                        ],
                    ],
                ]));

            $messaging->send($message);
            return ['success_count' => 1, 'failure_count' => 0];
        } catch (\Throwable $e) {
            return ['success_count' => 0, 'failure_count' => 1, 'error' => $e->getMessage()];
        }
    }

    /**
     * حساب إجمالي الإشعارات غير المقروءة لمستخدم
     */
    public function calculateUserUnreadCount(int $userId): int
    {
        $unreadAdminCount = NotificationForApp::where(function($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id')->orWhere('user_id', 0);
        })->where(function($q) {
            $q->where('user_view', 'no')->orWhere('user_view', '0')->orWhereNull('user_view');
        })->count();

        $unreadDbCount = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();

        $total = $unreadAdminCount + $unreadDbCount;
        return $total < 1 ? 1 : $total;
    }
}
