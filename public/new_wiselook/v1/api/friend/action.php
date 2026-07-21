<?php
// friend/action.php
// ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../notifications/notification_class.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (
    !isset($input['sender_id']) || empty(trim($input['sender_id'])) ||
    !isset($input['receiver_id']) || empty(trim($input['receiver_id'])) ||
    !isset($input['type']) || empty(trim($input['type']))
) {
    echo json_encode([
        'success' => false,
        'message' => 'sender_id, receiver_id, and type are required'
    ]);
    exit;
}

$sender_id = (int) $input['sender_id'];
$receiver_id = (int) $input['receiver_id'];
$type = trim($input['type']);

if ($sender_id === $receiver_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot add yourself'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$notification_class = new NotificationClass();

// Check for existing relationship
$checkStmt = $conn->prepare("SELECT id, is_active, sender_id, receiver_id FROM friendships 
    WHERE (sender_id = :sender_id AND receiver_id = :receiver_id) 
       OR (sender_id = :receiver_id AND receiver_id = :sender_id)");
$checkStmt->bindParam(':sender_id', $sender_id);
$checkStmt->bindParam(':receiver_id', $receiver_id);
$checkStmt->execute();
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

switch ($type) {
    case 'add':
        if ($existing) {
            echo json_encode([
                'success' => false,
                'message' => 'Friendship or request already exists',
                'action' => 'exists'
            ]);
            exit;
        }
        
        $conn->beginTransaction();
        try {
            $insertStmt = $conn->prepare("INSERT INTO friendships (sender_id, receiver_id, is_active, created_at) 
                                        VALUES (:sender_id, :receiver_id, 0, NOW())");
            $insertStmt->bindParam(':sender_id', $sender_id);
            $insertStmt->bindParam(':receiver_id', $receiver_id);
            $insertStmt->execute();

            // Get sender's name for notification
            $senderQuery = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = ?");
            $senderQuery->execute([$sender_id]);
            $sender = $senderQuery->fetch(PDO::FETCH_ASSOC);
            $sender_name = $sender['full_name'] ?? 'Someone';

            // Get receiver's FCM token
            $receiverQuery = $conn->prepare("SELECT token FROM users WHERE id = ?");
            $receiverQuery->execute([$receiver_id]);
            $receiver = $receiverQuery->fetch(PDO::FETCH_ASSOC);
            $receiver_token = $receiver['token'] ?? null;

            // Send notification if token exists
            if ($receiver_token) {
                $notification_class->sendStaticNotification(
    "$sender_name sent you a friend request",
    $receiver_token,
    "add_friend", // ✅ Correct spelling
    $sender_id
);
            }

            $conn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Friend request sent',
                'action' => 'sent'
            ]);
        } catch (PDOException $e) {
            $conn->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
            exit;
        }
        break;

    case 'remove':
    case 'cancel':
        if ($existing) {
            // If friendship was active, decrement both users' friend counts
            if ((int)$existing['is_active'] === 1) {
                $decrementStmt = $conn->prepare("UPDATE users 
                    SET friend_count = GREATEST(friend_count - 1, 0) 
                    WHERE id IN (:user1, :user2)");
                $decrementStmt->bindParam(':user1', $existing['sender_id']);
                $decrementStmt->bindParam(':user2', $existing['receiver_id']);
                $decrementStmt->execute();
            }

            $deleteStmt = $conn->prepare("DELETE FROM friendships WHERE id = :id");
            $deleteStmt->bindParam(':id', $existing['id']);
            $deleteStmt->execute();

            echo json_encode([
                'success' => true,
                'message' => 'Friend request/friendship removed',
                'action' => 'removed'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No existing relationship to remove',
                'action' => 'not_found'
            ]);
        }
        break;

    case 'confirm':
        if ($existing && $existing['is_active'] == 0 && $existing['receiver_id'] == $sender_id) {
            $conn->beginTransaction();
            try {
                $updateStmt = $conn->prepare("UPDATE friendships SET is_active = 1 WHERE id = :id");
                $updateStmt->bindParam(':id', $existing['id']);
                $updateStmt->execute();

                // Increment friend_count for both users
                $incrementStmt = $conn->prepare("UPDATE users 
                    SET friend_count = friend_count + 1 
                    WHERE id IN (:user1, :user2)");
                $incrementStmt->bindParam(':user1', $sender_id);
                $incrementStmt->bindParam(':user2', $receiver_id);
                $incrementStmt->execute();

                // Get receiver's name (who is now confirming the request)
                $receiverQuery = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = ?");
                $receiverQuery->execute([$sender_id]);
                $receiver = $receiverQuery->fetch(PDO::FETCH_ASSOC);
                $receiver_name = $receiver['full_name'] ?? 'Someone';

                // Get original sender's FCM token
                $senderQuery = $conn->prepare("SELECT token FROM users WHERE id = ?");
                $senderQuery->execute([$receiver_id]);
                $sender = $senderQuery->fetch(PDO::FETCH_ASSOC);
                $sender_token = $sender['token'] ?? null;

                // Send notification if token exists
                if ($sender_token) {
                    // $notification_class->sendStaticNotification(
                    //     "$receiver_name accepted your friend request",
                    //     $sender_token,
                    //     'friend_accept',
                    //     [
                    //         'sender_id' => $receiver_id, // original sender
                    //         'receiver_id' => $sender_id  // who accepted
                    //     ]
                    // );
                    
                                 $notification_class->sendStaticNotification(
    "$receiver_name accepted your friend request",
    $sender_token,
    "friend_accept", // ✅ Correct spelling
    $sender_id
);


                }

                $conn->commit();

                echo json_encode([
                    'success' => true,
                    'message' => 'Friend request confirmed',
                    'action' => 'confirmed'
                ]);
            } catch (PDOException $e) {
                $conn->rollBack();
                echo json_encode([
                    'success' => false,
                    'message' => 'Database error: ' . $e->getMessage()
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot confirm: no pending request from receiver to sender',
                'action' => 'invalid_confirm'
            ]);
        }
        break;

    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action type',
            'action' => 'invalid_type'
        ]);
}

exit;