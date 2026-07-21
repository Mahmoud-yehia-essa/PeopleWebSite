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
if (!isset($input['sender_id']) || empty(trim($input['sender_id'])) || 
    !isset($input['receiver_id']) || empty(trim($input['receiver_id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Both sender_id and receiver_id are required'
    ]);
    exit;
}

$sender_id = (int) $input['sender_id'];
$receiver_id = (int) $input['receiver_id'];

// Check if users are trying to add themselves
if ($sender_id === $receiver_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot send friend request to yourself'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Check if friendship already exists - now including receiver_id in SELECT
$checkStmt = $conn->prepare("
    SELECT id, is_active, sender_id, receiver_id
    FROM friendships 
    WHERE (sender_id = :sender_id AND receiver_id = :receiver_id)
       OR (sender_id = :receiver_id AND receiver_id = :sender_id)
");
$checkStmt->bindParam(':sender_id', $sender_id);
$checkStmt->bindParam(':receiver_id', $receiver_id);
$checkStmt->execute();
$existingFriendship = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($existingFriendship) {
    // If the exact same request exists (same sender and receiver), delete it (cancel/remove)
    if ($existingFriendship['sender_id'] == $sender_id && $existingFriendship['receiver_id'] == $receiver_id) {
        $deleteStmt = $conn->prepare("
            DELETE FROM friendships 
            WHERE id = :friendship_id
        ");
        $deleteStmt->bindParam(':friendship_id', $existingFriendship['id']);
        $deleteStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Friend request removed successfully',
            'action' => 'removed'
        ]);
        exit;
    }
    // If pending request exists where receiver is now the sender, accept it
    elseif ($existingFriendship['is_active'] == 0 && $existingFriendship['sender_id'] == $receiver_id) {
        $updateStmt = $conn->prepare("
            UPDATE friendships 
            SET is_active = 1 
            WHERE id = :friendship_id
        ");
        $updateStmt->bindParam(':friendship_id', $existingFriendship['id']);
        $updateStmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Friend request accepted and friendship established',
            'action' => 'accepted'
        ]);
        exit;
    }
    // If active friendship exists
    elseif ($existingFriendship['is_active'] == 1) {
        echo json_encode([
            'success' => false,
            'message' => 'Friendship already exists',
            'action' => 'exists'
        ]);
        exit;
    }
    // If pending request exists in same direction
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Friend request already pending',
            'action' => 'pending'
        ]);
        exit;
    }
}

// Insert new friend request if no existing relationship
try {
    $insertStmt = $conn->prepare("
        INSERT INTO friendships (sender_id, receiver_id, is_active, created_at)
        VALUES (:sender_id, :receiver_id, 0, NOW())
    ");
    $insertStmt->bindParam(':sender_id', $sender_id);
    $insertStmt->bindParam(':receiver_id', $receiver_id);
    $insertStmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Friend request sent successfully',
        'action' => 'sent'
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send friend request',
        'error' => $e->getMessage()
    ]);
}

exit;