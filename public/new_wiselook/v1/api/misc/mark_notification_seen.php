<?php
// misc/mark_notification_seen.php
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

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$notification_id = isset($input['notification_id']) ? $input['notification_id'] : null;
$notification_type = isset($input['notification_type']) ? $input['notification_type'] : null;
$mark_all = isset($input['mark_all']) ? (bool)$input['mark_all'] : false;
$last_user_id = isset($input['last_user_id']) ? $input['last_user_id'] : null;

// Validate input
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

if (!$mark_all && (!$notification_id || !$notification_type)) {
    echo json_encode(['success' => false, 'message' => 'Notification ID and type are required when not marking all']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    if ($mark_all) {
        // Mark all notifications as seen for this user
        // First, get all notification IDs that should be marked as seen
        $notifications_to_mark = [];
        
        // Friend requests
        $sql = "SELECT CONCAT('friend_request_', f.id) as notification_id, 'friend_request' as notification_type
                FROM friendships f
                WHERE f.receiver_id = :user_id AND f.is_active = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications_to_mark[] = $row;
        }
        
        // Post likes - include user ID in notification ID
        $sql = "SELECT CONCAT('like_', p.id, '_', r.user_id) as notification_id, 'post_like' as notification_type
                FROM posts p
                JOIN reactions r ON r.content_id = p.id AND r.content_type_id = 1
                WHERE p.user_id = :user_id AND r.user_id != :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications_to_mark[] = $row;
        }
        
        // Post comments - include user ID in notification ID
        $sql = "SELECT CONCAT('comment_', p.id, '_', c.user_id) as notification_id, 'post_comment' as notification_type
                FROM posts p
                JOIN comments c ON c.post_id = p.id
                WHERE p.user_id = :user_id AND c.user_id != :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications_to_mark[] = $row;
        }
        
        // Post shares - include user ID in notification ID
        $sql = "SELECT CONCAT('share_', p.id, '_', s.user_id) as notification_id, 'post_share' as notification_type
                FROM posts p
                JOIN posts s ON s.parent_id = p.id
                WHERE p.user_id = :user_id AND s.user_id != :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications_to_mark[] = $row;
        }
        
        // Insert all notifications as seen (using INSERT IGNORE to avoid duplicates)
        $insert_sql = "INSERT IGNORE INTO seen (user_id, notification_id, notification_type, seen_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insert_sql);
        
        $marked_count = 0;
        foreach ($notifications_to_mark as $notification) {
            $stmt->execute([$user_id, $notification['notification_id'], $notification['notification_type']]);
            $marked_count += $stmt->rowCount();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'All notifications marked as seen',
            'marked_count' => $marked_count
        ]);
        
    } else {
        // For single notification marking, handle the case where we need to include user ID
        $final_notification_id = $notification_id;
        
        // If it's a post-related notification and we have a last_user_id, append it
        if (in_array($notification_type, ['post_like', 'post_comment', 'post_share']) && $last_user_id) {
            // Check if the user ID is already included in the notification ID
            $parts = explode('_', $notification_id);
            if (count($parts) == 2) { // Format is like "like_57"
                $final_notification_id = $notification_id . '_' . $last_user_id;
            }
        }
        
        // Check if already marked as seen
        $check_sql = "SELECT id FROM seen WHERE user_id = :user_id AND notification_id = :notification_id AND notification_type = :notification_type";
        $stmt = $conn->prepare($check_sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':notification_id', $final_notification_id, PDO::PARAM_STR);
        $stmt->bindParam(':notification_type', $notification_type, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification already marked as seen'
            ]);
        } else {
            // Insert new seen record
            $insert_sql = "INSERT INTO seen (user_id, notification_id, notification_type, seen_at) VALUES (:user_id, :notification_id, :notification_type, NOW())";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':notification_id', $final_notification_id, PDO::PARAM_STR);
            $stmt->bindParam(':notification_type', $notification_type, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notification marked as seen'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to mark notification as seen'
                ]);
            }
        }
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>