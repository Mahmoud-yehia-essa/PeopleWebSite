<?php
// post/pin_post.php
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

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Inputs
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$pin_scope = isset($input['pin_scope']) ? $input['pin_scope'] : null; // 'profile' or 'home'

// Validate inputs
if (!$post_id || !$pin_scope || ($pin_scope == 'profile' && !$user_id)) {
    $response['message'] = 'Missing parameters';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

// Check post exists
$db->checkPostExists($post_id);

try {
    $conn->beginTransaction();

    // Check if already pinned
    if ($pin_scope == 'profile') {
        $check_query = "SELECT id FROM pinned_posts WHERE post_id = ? AND pin_scope = 'profile'";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([$post_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Unpin if exists
            $delete_query = "DELETE FROM pinned_posts WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->execute([$existing['id']]);
            $isPinned = false;
        } else {
            // Insert new pin
            $insert_query = "INSERT INTO pinned_posts (post_id, pin_scope, pin_order, pinned_at) VALUES (?, 'profile', 0, NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->execute([$post_id]);
            $isPinned = true;
        }
    } else if ($pin_scope == 'home') {
        // Admin pin (can pin multiple posts)
        $check_query = "SELECT id FROM pinned_posts WHERE post_id = ? AND pin_scope = 'home'";
        $stmt = $conn->prepare($check_query);
        $stmt->execute([$post_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Unpin
            $delete_query = "DELETE FROM pinned_posts WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->execute([$existing['id']]);
            $isPinned = false;
        } else {
            // Insert pin
            $insert_query = "INSERT INTO pinned_posts (post_id, pin_scope, pin_order, pinned_at) VALUES (?, 'home', 0, NOW())";
            $stmt = $conn->prepare($insert_query);
            $stmt->execute([$post_id]);
            $isPinned = true;
        }
    }

    $conn->commit();

    $response['success'] = true;
    $response['message'] = $isPinned ? 'Post pinned successfully' : 'Post unpinned';
    $response['data'] = ['isPinned' => $isPinned];

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
