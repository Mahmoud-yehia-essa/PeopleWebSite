<?php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
// Allow specific methods
header("Access-Control-Allow-Methods: POST, OPTIONS");
// Allow these headers to be sent by the client
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return 200 OK for OPTIONS requests
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once '../notifications/notification_class.php';
$notification_class = new NotificationClass();

$response = ['success' => false, 'message' => '', 'data' => null];

// Get input data (from JSON or form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$content_id = isset($input['content_id']) ? (int)$input['content_id'] : null;
$content_type = isset($input['content_type']) ? trim($input['content_type']) : 'post';
$reaction_type = isset($input['reaction_type']) ? trim($input['reaction_type']) : 'like';

// Validate inputs
if (!$user_id || !$content_id) {
    $response['message'] = 'Missing required parameters';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check if user exists
$db->checkUserExists($user_id);

// Check if content exists based on type
switch ($content_type) {
    case 'post':
        $db->checkPostExists($content_id);
        $table = 'posts';
        $count_field = 'like_count';
        break;
    case 'comment':
        $db->checkCommentExists($content_id);
        $table = 'comments';
        $count_field = 'reaction_count';
        break;
    case 'story':
        $db->checkStoryExists($content_id);
        $table = 'stories';
        $count_field = 'reaction_count';
        break;
    default:
        $response['message'] = 'Invalid content type';
        echo json_encode($response);
        exit;
}

try {
    $conn->beginTransaction();

    if ($reaction_type === 'remove') {
        // Remove reaction if it exists
        $delete_query = "DELETE FROM reactions 
                         WHERE user_id = ? 
                         AND content_id = ? 
                         AND content_type_id = (SELECT id FROM content_type WHERE type = ?)";
        $stmt = $conn->prepare($delete_query);
        $stmt->execute([$user_id, $content_id, $content_type]);

        // Decrement count (minimum 0)
        $update_query = "UPDATE $table 
                         SET $count_field = GREATEST(0, $count_field - 1) 
                         WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->execute([$content_id]);

        $response['success'] = true;
        $response['message'] = 'Reaction removed successfully';
    } else {
        // Check if reaction already exists
        $check_query = "SELECT id FROM reactions 
                        WHERE user_id = ? 
                        AND content_id = ? 
                        AND content_type_id = (SELECT id FROM content_type WHERE type = ?)";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([$user_id, $content_id, $content_type]);

        if ($stmt->rowCount() > 0) {
            $response['message'] = 'Reaction already exists';
        } else {
            // Insert new reaction
            $insert_query = "INSERT INTO reactions 
                             (user_id, content_id, content_type_id, reaction_type_id) 
                             VALUES (?, ?, 
                             (SELECT id FROM content_type WHERE type = ?), 
                             (SELECT id FROM reaction_type WHERE type = ?))";
            $stmt = $conn->prepare($insert_query);
            $stmt->execute([$user_id, $content_id, $content_type, $reaction_type]);

            // Increment count
            $update_query = "UPDATE $table 
                             SET $count_field = $count_field + 1 
                             WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->execute([$content_id]);

            $response['success'] = true;
            $response['message'] = 'Reaction added successfully';
            


            // Get user who performed the action
            $query_user_action = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users 
                                WHERE id = ?";
            $stmt = $conn->prepare($query_user_action);
            $stmt->execute([$user_id]);
            $user_action = $stmt->fetch(PDO::FETCH_ASSOC);
            $actor_name = $user_action['full_name'] ?? 'Someone';
            
            // Get content owner's FCM token
            
            $query_user_receiver_action = "";
            switch ($content_type) {
                case 'post':
                    $query_user_receiver_action = "SELECT u.id , u.token FROM users u 
                                                JOIN posts p ON p.user_id = u.id 
                                                WHERE p.id = ?";
                    break;
                case 'comment':
                    $query_user_receiver_action = "SELECT u.id , u.token FROM users u 
                                                JOIN comments c ON c.user_id = u.id 
                                                WHERE c.id = ?";
                    break;
                case 'story':
                    $query_user_receiver_action = "SELECT u.id , u.token FROM users u 
                                                JOIN stories s ON s.user_id = u.id 
                                                WHERE s.id = ?";
                    break;
            }
            $stmt = $conn->prepare($query_user_receiver_action);
            $stmt->execute([$content_id]);
            $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
            $token = $receiver['token'] ?? null;
            $user_id_receiver = $receiver['id'];
            // Prepare notification message based on content type
            $message = "";
            switch ($content_type) {
                case 'post':
                    $message = "$actor_name liked your post";
                    break;
                case 'comment':
                    $message = "$actor_name liked your comment";
                    break;
                case 'story':
                    $message = "$actor_name liked your story";
                    break;
            }
            
            // Send notification if token exists
            if ($token && !empty($message) &&  ($user_id_receiver != $user_id)) {
                $notification_class->sendStaticNotification($message, $token, $content_type , $content_id);
            }


        }
    }

    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;