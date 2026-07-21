<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Optional: If you want posts for a specific user
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit  = isset($input['limit']) ? (int)$input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Base SQL
$sql = "
    SELECT 
        p.id AS post_id,
        p.user_id,
        p.content,
        p.image,
        p.video,
        p.privacy_level_id,
        p.like_count,
        p.comment_count,
        p.share_count,
        p.is_active,
        p.parent_id,
        p.post_type_id,
        u.first_name,
        u.last_name,
        u.profile_picture
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.is_active = 1
";

// Optional filter
if ($user_id !== null) {
    $sql .= " AND p.user_id = :user_id";
}

$sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);

// Bind parameters
if ($user_id !== null) {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return result
echo json_encode([
    'success' => true,
    'message' => 'Posts fetched successfully',
    'data' => $posts
]);
exit;
