<?php


ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once './notification_class.php';
$notification_class = new NotificationClass();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    $recipientToken = $input['recipient_token'] ?? '';
    $senderId = $input['sender_id'] ?? '';
    $senderName = $input['sender_name'] ?? '';
    $messageText = $input['message'] ?? 'Tst';
    $chatId = $input['chat_id'] ?? '';
    $type = $input['type'];
    $profile_pic =$input['profile_pic']?? '';
    $call_data = $input['call_data'] ?? [];
    $sender_fcm_token = $input["sender_fcm_token"] ?? '';
    

    if (!$recipientToken || !$senderId || !$senderName || !$messageText || !$chatId) {
        http_response_code(400);
        echo json_encode(["status" => false, "error" => "Missing fields"]);
        exit;
    }

    // Pass call_data as an array for call notifications
    if ($type === 'call') {
        $success = NotificationClass::sendChatNotification(
            $recipientToken, 
            $senderId, 
            $senderName, 
            $sender_fcm_token,
            $messageText, 
            $chatId,
            $profile_pic,
            $type,
            $call_data
        );
    } else {
        $success = NotificationClass::sendChatNotification(
            $recipientToken, 
            $senderId, 
            $senderName, 
            $sender_fcm_token,
            $messageText, 
            $chatId,
            $profile_pic,
            $type
        );
    }

    echo json_encode(["status" => $success]);
} else {
    http_response_code(405);
    echo json_encode(["status" => false, "error" => "Method not allowed"]);
}
