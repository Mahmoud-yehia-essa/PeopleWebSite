<?php
// post/delete_comment.php
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
$comment_id = isset($input['comment_id']) ? (int)$input['comment_id'] : null;

// Validate required fields
if (!$user_id || !$comment_id) {
    $response['message'] = 'Missing required fields (user_id and comment_id are required)';
    echo json_encode($response); 
    exit;
}

// Check if user exists and is active
$db->checkUserExists($user_id);

try {
    // Check if comment exists and get its details
    $stmt = $conn->prepare("SELECT id, parent_id FROM comments WHERE id = :comment_id AND is_active = 1");
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comment) {
        $response['message'] = 'Comment not found or already deleted';
        echo json_encode($response);
        exit;
    }

    $conn->beginTransaction();
    $now = date('Y-m-d H:i:s');

    // Soft delete the main comment
    $stmt = $conn->prepare("UPDATE comments SET is_active = 0, deleted_at = :now WHERE id = :comment_id");
    $stmt->bindParam(':now', $now);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();

    // If this is a parent comment (no parent_id), delete its children
    if (empty($comment['parent_id'])) {
        $stmt = $conn->prepare("UPDATE comments SET is_active = 0, deleted_at = :now WHERE parent_id = :comment_id AND is_active = 1");
        $stmt->bindParam(':now', $now);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $childCount = $stmt->rowCount();
        $response['message'] = $childCount > 0 
            ? "Comment and its $childCount replies deleted successfully" 
            : "Comment deleted successfully";
    } else {
        $response['message'] = "Reply comment deleted successfully";
    }

    $conn->commit();
    $response['success'] = true;
    $response['data'] = [
        'comment_id' => $comment_id,
        'deleted_at' => $now,
        'had_replies' => !empty($comment['parent_id']) ? false : ($childCount ?? 0) > 0
    ];
    
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;