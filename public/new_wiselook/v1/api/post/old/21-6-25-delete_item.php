<?php
// post/delete_item.php
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
include_once __DIR__ . '/../config.php';

$response = ['success' => false, 'message' => '', 'data' => null];

// Get input data (from JSON or form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$content_id = isset($input['content_id']) ? (int)$input['content_id'] : null;
$content_type = isset($input['content_type']) ? trim($input['content_type']) : null;

// Validate required fields
if (!$user_id || !$content_id || !$content_type) {
    $response['message'] = 'Missing required fields.';
    echo json_encode($response); 
    exit;
}

// Check if user exists and is active
$db->checkUserExists($user_id);

// Prepare deletion logic
$table = '';
$id_field = '';
switch ($content_type) {
    case 'post':
        $table = 'posts';
        $id_field = 'id';
        break;
    case 'comment':
        $table = 'comments';
        $id_field = 'id';
        break;
    default:
        $response['message'] = 'Invalid content type.';
        echo json_encode($response);
        exit;
}

$now = date('Y-m-d H:i:s');

// Verify ownership and soft delete the main record
$stmt = $conn->prepare("SELECT * FROM $table WHERE $id_field = :content_id AND user_id = :user_id AND is_active = 1");
$stmt->bindParam(':content_id', $content_id, PDO::PARAM_INT);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    $response['message'] = 'Content not found or already deleted.';
    echo json_encode($response);
    exit;
}

// Start transaction
$conn->beginTransaction();

try {
    // Soft delete the main content
    $update = $conn->prepare("UPDATE $table SET is_active = 0, deleted_at = :now WHERE $id_field = :content_id");
    $update->bindParam(':now', $now);
    $update->bindParam(':content_id', $content_id, PDO::PARAM_INT);
    $update->execute();

    // Soft delete children with parent_id = this content_id
    $childUpdate = $conn->prepare("UPDATE $table SET is_active = 0, deleted_at = :now WHERE parent_id = :content_id");
    $childUpdate->bindParam(':now', $now);
    $childUpdate->bindParam(':content_id', $content_id, PDO::PARAM_INT);
    $childUpdate->execute();

    // Update counters based on content type
    if ($content_type === 'post') {
        // Decrement user's post_count
        $decrementPost = $conn->prepare("UPDATE users SET post_count = post_count - 1 WHERE id = :user_id");
        $decrementPost->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $decrementPost->execute();
    } elseif ($content_type === 'comment') {
        // Get the post_id this comment belongs to
        $getPostId = $conn->prepare("SELECT post_id FROM comments WHERE id = :comment_id");
        $getPostId->bindParam(':comment_id', $content_id, PDO::PARAM_INT);
        $getPostId->execute();
        $post = $getPostId->fetch(PDO::FETCH_ASSOC);
        
        if ($post && isset($post['post_id'])) {
            // Decrement the post's comment_count
            $decrementComment = $conn->prepare("UPDATE posts SET comment_count = comment_count - 1 WHERE id = :post_id");
            $decrementComment->bindParam(':post_id', $post['post_id'], PDO::PARAM_INT);
            $decrementComment->execute();
        }
    }

    $conn->commit();
    $response['success'] = true;
    $response['message'] = ucfirst($content_type) . " and its replies soft deleted successfully.";
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = 'Error deleting content: ' . $e->getMessage();
}

echo json_encode($response);
exit;