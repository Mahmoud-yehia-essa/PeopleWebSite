<?php
// post/add_comment.php
// Allow from any origin
header("Access-Control-Allow-Origin: *");
// Allow specific methods
header("Access-Control-Allow-Methods: POST, OPTIONS");
// Allow these headers to be sent by the client
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return 200 OK for OPTIONS requests
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once '../notifications/notification_class.php';
$notification_class = new NotificationClass();

$response = ['success' => false, 'message' => '', 'data' => null];

// Get input data (works for both JSON and form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$content = isset($input['content']) ? trim($input['content']) : null;
$parent_id = isset($input['comment_id']) ? (int)$input['comment_id'] : null;

// Validate inputs
if (!$user_id || !$post_id || !$content) {
    $missing = [];
    if (!$user_id) $missing[] = 'user_id';
    if (!$post_id) $missing[] = 'post_id';
    if (!$content) $missing[] = 'content';
    
    $response['message'] = 'Missing parameters: ' . implode(', ', $missing);
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check if user exists
$db->checkUserExists($user_id);

// Check if post exists
$db->checkPostExists($post_id);

// Check if parent comment exists if provided
if ($parent_id) {
    $db->checkCommentExists($parent_id);
}

// Sanitize content (prevent XSS)
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

try {
    $conn->beginTransaction();

    // Insert the comment
    $insert_query = "INSERT INTO comments 
                    (user_id, post_id, parent_id, content, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_query);
    $stmt->execute([$user_id, $post_id, $parent_id, $content]);
    $comment_id = $conn->lastInsertId();

    // Update comment count on the post
    $update_query = "UPDATE posts SET comment_count = comment_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->execute([$post_id]);

    // If it's a reply, update parent comment's reply count
    if ($parent_id) {
        $update_reply_query = "UPDATE comments SET reply_count = reply_count + 1 WHERE id = ?";
        $stmt = $conn->prepare($update_reply_query);
        $stmt->execute([$parent_id]);
    }

    // Get the full comment data for response
    $comment_query = "SELECT 
                        c.id, 
                        c.user_id, 
                        c.post_id, 
                        c.parent_id, 
                        c.content, 
                        c.created_at,
                        u.first_name,
                        u.last_name,
                        u.profile_picture
                      FROM comments c
                      JOIN users u ON u.id = c.user_id
                      WHERE c.id = ?";
    $stmt = $conn->prepare($comment_query);
    $stmt->execute([$comment_id]);
    $comment_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $conn->commit();

    // Get commenter's name
    $commenter_name = $comment_data['first_name'] . ' ' . $comment_data['last_name'];

    // Notification Logic
    if ($parent_id) {
        // REPLY TO COMMENT - notify original comment author
        $target_query = "SELECT 
                            u.id as owner_id, 
                            u.token, 
                            CONCAT(u.first_name, ' ', u.last_name) as owner_name
                         FROM comments c
                         JOIN users u ON u.id = c.user_id
                         WHERE c.id = ?";
        $stmt = $conn->prepare($target_query);
        $stmt->execute([$parent_id]);
        $notification_target = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $notification_message = "$commenter_name replied to your comment";
        $notification_type = 'comment_reply';
    } else {
        // NEW COMMENT ON POST - notify post owner
        $target_query = "SELECT 
                            u.id as owner_id, 
                            u.token, 
                            CONCAT(u.first_name, ' ', u.last_name) as owner_name
                         FROM posts p
                         JOIN users u ON u.id = p.user_id
                         WHERE p.id = ?";
        $stmt = $conn->prepare($target_query);
        $stmt->execute([$post_id]);
        $notification_target = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $notification_message = "$commenter_name commented on your post";
        $notification_type = 'comment';
    }

    // Send notification if:
    // 1. Target exists
    // 2. Not commenting on own content
    // 3. Target has FCM token
    if ($notification_target && 
        $notification_target['owner_id'] != $user_id && 
        !empty($notification_target['token'])) {
        
        $notification_class->sendStaticNotification(
            $notification_message,
            $notification_target['token'],
            $notification_type,
            $post_id
        );
    }

    $response = [
        'success' => true,
        'message' => $parent_id ? 'Reply added successfully' : 'Comment added successfully',
        'data' => $comment_data
    ];

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;