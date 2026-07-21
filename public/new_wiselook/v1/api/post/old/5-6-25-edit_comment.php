<?php
// post/edit_comment.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';

$response = ['success' => false, 'message' => '', 'data' => null];

// Get input data (works for both JSON and form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$comment_id = isset($input['comment_id']) ? (int)$input['comment_id'] : null;
$content = isset($input['content']) ? trim($input['content']) : null;

// Validate inputs
if (!$user_id || !$comment_id || !$content) {
    $missing = [];
    if (!$user_id) $missing[] = 'user_id';
    if (!$comment_id) $missing[] = 'comment_id';
    if (!$content) $missing[] = 'content';
    
    $response['message'] = 'Missing parameters: ' . implode(', ', $missing);
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check if user exists
$db->checkUserExists($user_id);

// Sanitize content (prevent XSS)
$content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

try {
    // First verify the comment belongs to this user
    $check_query = "SELECT id FROM comments WHERE id = :comment_id AND user_id = :user_id AND is_active = 1";
    $stmt = $conn->prepare($check_query);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'Comment not found or you do not have permission to edit it';
        http_response_code(403);
        echo json_encode($response);
        exit;
    }

    $conn->beginTransaction();
    
    // Update the comment
    $update_query = "UPDATE comments 
                    SET content = :content, 
                        updated_at = NOW() 
                    WHERE id = :comment_id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();

    // Get the updated comment data for response
    $comment_query = "SELECT 
                        c.id, 
                        c.user_id, 
                        c.post_id, 
                        c.parent_id, 
                        c.content, 
                        c.created_at,
                        c.updated_at,
                        u.first_name,
                        u.last_name,
                        u.profile_picture
                      FROM comments c
                      JOIN users u ON u.id = c.user_id
                      WHERE c.id = :comment_id";
    $stmt = $conn->prepare($comment_query);
    $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
    $stmt->execute();
    $comment_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $conn->commit();

    $response = [
        'success' => true,
        'message' => 'Comment updated successfully',
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