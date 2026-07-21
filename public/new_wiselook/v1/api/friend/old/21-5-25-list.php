<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['id']) || empty(trim($input['id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing user ID'
    ]);
    exit;
}

$user_id = (int) $input['id'];
$start = isset($input['start']) ? (int) $input['start'] : 0;
$limit = isset($input['limit']) ? (int) $input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Pending friend requests
$pendingStmt = $conn->prepare("
    SELECT 
        f.id AS friendship_id,
        u.id, u.first_name, u.last_name, u.profile_picture,
        CASE 
            WHEN f.sender_id = :user_id THEN 'cancel'
            WHEN f.receiver_id = :user_id THEN 'confirm'
            ELSE NULL
        END AS action
    FROM friendships f
    JOIN users u ON (u.id = IF(f.sender_id = :user_id, f.receiver_id, f.sender_id))
    WHERE (f.sender_id = :user_id OR f.receiver_id = :user_id)
      AND f.is_active = 0
");
$pendingStmt->bindParam(':user_id', $user_id);
$pendingStmt->execute();
$pendingList = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

// IDs in pending or confirmed friendships
$blockListStmt = $conn->prepare("
    SELECT 
        CASE 
            WHEN sender_id = :user_id THEN receiver_id
            WHEN receiver_id = :user_id THEN sender_id
        END AS friend_id
    FROM friendships
    WHERE (sender_id = :user_id OR receiver_id = :user_id)
");
$blockListStmt->bindParam(':user_id', $user_id);
$blockListStmt->execute();
$blockedIds = array_column($blockListStmt->fetchAll(PDO::FETCH_ASSOC), 'friend_id');
$blockedIds[] = $user_id; // Exclude self

$placeholders = rtrim(str_repeat('?,', count($blockedIds)), ',');
$addQuery = "
    SELECT id, first_name, last_name, profile_picture, 'add' as action
    FROM users
    WHERE id NOT IN ($placeholders)
    LIMIT ?, ?
";

$addStmt = $conn->prepare($addQuery);
foreach ($blockedIds as $index => $blockedId) {
    $addStmt->bindValue($index + 1, $blockedId, PDO::PARAM_INT);
}
$addStmt->bindValue(count($blockedIds) + 1, $start, PDO::PARAM_INT);
$addStmt->bindValue(count($blockedIds) + 2, $limit, PDO::PARAM_INT);
$addStmt->execute();
$addList = $addStmt->fetchAll(PDO::FETCH_ASSOC);

// Merge and respond
$response = array_merge($pendingList, $addList);

echo json_encode([
    'success' => true,
    'message' => 'List success',
    'data' => $response
]);
exit;
