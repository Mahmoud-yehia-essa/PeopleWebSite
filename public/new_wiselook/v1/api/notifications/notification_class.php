<?php

include(__DIR__ . '/generate_token.php');

class NotificationClass
{
    private const PROJECT_ID = "wiselook-f161f";

    // ✅ STATIC NOTIFICATIONS (Like, Comment, Follow, etc.)
    public static function sendStaticNotification($message, $token_receiver, $content_type, $content_id)
    {
        $token = getAccessToken();
        if (empty($token)) {
            error_log("Failed to get access token");
            return false;
        }
        $fcmEndpoint = "https://fcm.googleapis.com/v1/projects/" . self::PROJECT_ID . "/messages:send";
        
        $notificationData = [
            "message" => [
                "token" => $token_receiver,
                // ✅ KEEP notification field for when app is terminated
                "notification" => [
                    "title" => self::getNotificationTitle($content_type),
                    "body" => $message
                ],
                "data" => [
                    "path" => $content_type,
                    "content_id" => (string)$content_id,
                    "type" => $content_type,
                    "title" => self::getNotificationTitle($content_type), // Keep in data too
                    "message" => $message
                ],
                "android" => [
                    "priority" => "high",
                    "notification" => [
                        "sound" => "notification",
                        "channel_id" => "regular_channel_v2"
                    ]
                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10",
                        "apns-push-type" => "alert"
                    ],
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => self::getNotificationTitle($content_type),
                                "body" => $message
                            ],
                            "sound" => "default",
                            "badge" => 1,
                            "thread-id" => "static_$content_type"
                        ]
                    ]
                ]
            ]
        ];

        $ch = curl_init($fcmEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($notificationData)
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log("cURL error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        $responseBody = json_decode($response, true);
        
        if (isset($responseBody["error"])) {
            error_log("FCM error: " . $responseBody["error"]["message"]);
            return false;
        }
        
        return true;
    }
    
    private static function getNotificationTitle(string $content_type): string
    {
        $titles = [
            'post' => 'New post interaction',
            'comment' => 'New comment reaction',
            'story' => 'New story reaction',
            'chat' => 'New message',
            'group' => 'Group Update',
            'call' => 'Incoming Call',
            'like' => 'New like',
            'follow' => 'New follower',
            'mention' => 'You were mentioned',
            'default' => 'New notification'
        ];

        return $titles[$content_type] ?? $titles['default'];
    }

    // ✅ CHAT MESSAGES NOTIFICATIONS
    public static function sendChatNotification(
        $recipientToken, 
        $senderId, 
        $senderName, 
        $sender_fcm_token,
        $messageText, 
        $chatId,
        $profile_pic,
        $type, 
        $callData = null
    ) {
        $token = getAccessToken();
        if (empty($token)) {
            error_log("Access token missing.");
            return false;
        }

        $fcmEndpoint = "https://fcm.googleapis.com/v1/projects/" . self::PROJECT_ID . "/messages:send";

        // Data payload for app navigation
        $dataPayload = [
            "type" => $type,
            "path" => $type,
            "sender_id" => (string)$senderId,
            "sender_name" => $senderName,
            "sender_fcm_token" => $sender_fcm_token,
            "chat_id" => $chatId,
            "sender_profile_pic" => $profile_pic,
            "message" => trim($messageText) !== '' ? $messageText : 'New message',
        ];

        // ✅ CALL NOTIFICATION - Special handling for calls
        if ($type === 'call' && !empty($callData) && is_array($callData)) {
            $dataPayload['call_data'] = json_encode($callData);
            
            $notificationData = [
                "message" => [
                    "token" => $recipientToken,
                    // ✅ KEEP notification field for calls (full-screen intent)
                    "notification" => [
                        "title" => "📞 Incoming Call",
                        "body" => "$senderName is calling you..."
                    ],
                    "data" => $dataPayload,
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "sound" => "call_sound",
                            "channel_id" => "call_channel"
                        ]
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "10",
                            "apns-push-type" => "alert"
                        ],
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                    "title" => "📞 Incoming Call",
                                    "body" => "$senderName is calling you..."
                                ],
                                "sound" => "call_sound.aiff",
                                "badge" => 1,
                                "category" => "CALL_CATEGORY",
                                "interruption-level" => "time-sensitive",
                                "thread-id" => "voice_calls"
                            ]
                        ]
                    ]
                ]
            ];
        } 
        // ✅ REGULAR CHAT MESSAGE NOTIFICATION
        else {
            $notificationData = [
                "message" => [
                    "token" => $recipientToken,
                    // ✅ KEEP notification field for chat messages too
                    "notification" => [
                        "title" => $senderName,
                        "body" => trim($messageText) !== '' ? $messageText : 'New message'
                    ],
                    "data" => $dataPayload,
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "sound" => "notification",
                            "channel_id" => "regular_channel_v2"
                        ]
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "10",
                            "apns-push-type" => "alert"
                        ],
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                    "title" => $senderName,
                                    "body" => trim($messageText) !== '' 
                                        ? $messageText 
                                        : 'New message'
                                ],
                                "sound" => "default",
                                "badge" => 1,
                                "thread-id" => "chat_$chatId"
                            ]
                        ]
                    ]
                ]
            ];
        }

        // Send request
        $ch = curl_init($fcmEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json",
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($notificationData),
        ]);

        $response = curl_exec($ch);

        error_log("FCM Request: " . json_encode($notificationData));
        error_log("FCM Response: " . $response);

        if (curl_errno($ch)) {
            error_log("cURL error: " . curl_error($ch));
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $responseBody = json_decode($response, true);

        if (isset($responseBody["error"])) {
            error_log("FCM error: " . json_encode($responseBody["error"]));
            return false;
        }

        return true;
    }

    // ✅ GROUP MESSAGES NOTIFICATIONS
    public static function sendGroupNotification($groupId, $senderName, $messageText, $senderId, $type = 'group')
    {
        include_once '../db.php';
        $db = new Database();
        $conn = $db->getConnection();

        // Get group name
        $groupQuery = "SELECT name FROM groups WHERE id = :group_id LIMIT 1";
        $stmtGroup = $conn->prepare($groupQuery);
        $stmtGroup->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmtGroup->execute();
        $group = $stmtGroup->fetch(PDO::FETCH_ASSOC);
        $groupName = $group ? $group['name'] : 'Unknown Group';

        // Get all group members except sender
        $query = "SELECT u.token, u.id
                  FROM group_member gm
                  JOIN users u ON gm.user_id = u.id
                  WHERE gm.group_id = :group_id 
                    AND u.is_active = 1 
                    AND gm.is_active = 1
                    AND u.id != :sender_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':group_id', $groupId, PDO::PARAM_INT);
        $stmt->bindParam(':sender_id', $senderId, PDO::PARAM_INT);
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($members)) {
            error_log("No active members found in group $groupId (excluding sender $senderId)");
            return false;
        }

        $token = getAccessToken();
        if (empty($token)) {
            error_log("Failed to get access token");
            return false;
        }

        $fcmEndpoint = "https://fcm.googleapis.com/v1/projects/" . self::PROJECT_ID . "/messages:send";

        // Send notification to each member
        foreach ($members as $member) {
            $recipientToken = $member['token'];
            if (empty($recipientToken)) continue;

            $dataPayload = [
                "type" => $type,
                "path" => "group_chat",
                "group_id" => (string)$groupId,
                "group_name" => $groupName,
                "sender_id" => (string)$senderId,
                "sender_name" => $senderName,
                "message" => trim($messageText) !== '' ? $messageText : 'New message',
            ];

            $notificationData = [
                "message" => [
                    "token" => $recipientToken,
                    // ✅ KEEP notification field for group messages
                    "notification" => [
                        "title" => $groupName,
                        "body" => trim($messageText) !== '' 
                            ? $senderName . ': ' . $messageText 
                            : $senderName . ' sent a message'
                    ],
                    "data" => $dataPayload,
                    "android" => [
                        "priority" => "high",
                        "notification" => [
                            "sound" => "notification",
                            "channel_id" => "regular_channel_v2"
                        ]
                    ],
                    "apns" => [
                        "headers" => [
                            "apns-priority" => "10",
                            "apns-push-type" => "alert"
                        ],
                        "payload" => [
                            "aps" => [
                                "alert" => [
                                    "title" => $groupName,
                                    "body" => trim($messageText) !== ''
                                        ? $senderName . ': ' . $messageText
                                        : $senderName . ' sent a message'
                                ],
                                "sound" => "default",
                                "badge" => 1,
                                "thread-id" => "group_$groupId"
                            ]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($fcmEndpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $token,
                    "Content-Type: application/json",
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode($notificationData),
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            error_log("GroupNotif to user {$member['id']} (Group: $groupName): " . $response);
        }

        return true;
    }
}

?>