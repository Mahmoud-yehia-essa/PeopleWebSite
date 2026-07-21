<?php
// block/block_action.php

ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$required_fields = ['blocker_id', 'blocked_id', 'type'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

$blocker_id = (int) $input['blocker_id'];
$blocked_id = (int) $input['blocked_id'];
$type = strtolower(trim($input['type']));
$lang = isset($input['lang']) && $input['lang'] === 'ar' ? 'ar' : 'en';

if ($blocker_id === $blocked_id) {
    echo json_encode(['success' => false, 'message' => $lang === 'ar' ? 'لا يمكنك حظر نفسك' : 'You cannot block yourself']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

if ($type === 'block') {
    // Check if already blocked
    $check_sql = "SELECT id FROM block WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([
        ':blocker_id' => $blocker_id,
        ':blocked_id' => $blocked_id
    ]);

    if ($check_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => $lang === 'ar' ? 'تم الحظر مسبقاً' : 'User already blocked'
        ]);
        exit;
    }

    // Check if friendship exists before deleting
    $check_friendship_sql = "
        SELECT id FROM friendships
        WHERE (sender_id = :blocker AND receiver_id = :blocked)
           OR (sender_id = :blocked AND receiver_id = :blocker)
        LIMIT 1
    ";
    $check_friendship_stmt = $conn->prepare($check_friendship_sql);
    $check_friendship_stmt->execute([
        ':blocker' => $blocker_id,
        ':blocked' => $blocked_id
    ]);

    if ($check_friendship_stmt->rowCount() > 0) {
        // Friendship exists → delete it
        $delete_friendship_sql = "
            DELETE FROM friendships 
            WHERE (sender_id = :blocker AND receiver_id = :blocked)
               OR (sender_id = :blocked AND receiver_id = :blocker)
        ";
        $delete_stmt = $conn->prepare($delete_friendship_sql);
        $delete_stmt->execute([
            ':blocker' => $blocker_id,
            ':blocked' => $blocked_id
        ]);
        
        
        // Update friend_count for both users
$update_friend_count_sql = "
    UPDATE users 
    SET friend_count = GREATEST(friend_count - 1, 0)
    WHERE id IN (:blocker_id, :blocked_id)
";
$update_stmt = $conn->prepare($update_friend_count_sql);
$update_stmt->execute([
    ':blocker_id' => $blocker_id,
    ':blocked_id' => $blocked_id
]);



    }

    // Insert block
    $insert_sql = "INSERT INTO block (blocker_id, blocked_id) VALUES (:blocker_id, :blocked_id)";
    $insert_stmt = $conn->prepare($insert_sql);
    $result = $insert_stmt->execute([
        ':blocker_id' => $blocker_id,
        ':blocked_id' => $blocked_id
    ]);

    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $lang === 'ar' ? 'تم الحظر بنجاح' : 'User blocked successfully',
            'block_id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $lang === 'ar' ? 'فشل في عملية الحظر' : 'Failed to block user'
        ]);
    }
} elseif ($type === 'unblock') {
    // Delete block
    $delete_block_sql = "DELETE FROM block WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id";
    $delete_stmt = $conn->prepare($delete_block_sql);
    $result = $delete_stmt->execute([
        ':blocker_id' => $blocker_id,
        ':blocked_id' => $blocked_id
    ]);

    if ($result && $delete_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => $lang === 'ar' ? 'تم إلغاء الحظر بنجاح' : 'User unblocked successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $lang === 'ar' ? 'لم يتم العثور على الحظر لإزالته' : 'No block found to remove'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 'نوع العملية غير صالح' : 'Invalid action type'
    ]);
}

exit;
