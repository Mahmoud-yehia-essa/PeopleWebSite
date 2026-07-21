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

    $groupId = $input['group_id'] ?? '';
    $senderName = $input['sender_name'] ?? '';
    $senderId = $input['sender_id'] ?? '';
    $messageText = $input['message'] ?? 'New message';

    if (!$groupId || !$senderName || !$messageText || !$senderId) {
        http_response_code(400);
        echo json_encode(["status" => false, "error" => "Missing fields"]);
        exit;
    }

    $success = NotificationClass::sendGroupNotification($groupId, $senderName, $messageText, $senderId);

    echo json_encode(["status" => $success]);
} else {
    http_response_code(405);
    echo json_encode(["status" => false, "error" => "Method not allowed"]);
}
