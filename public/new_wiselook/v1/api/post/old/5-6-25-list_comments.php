<?php
// post/list_comments.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$offset  = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit   = isset($input['limit']) ? (int)$input['limit'] : 20;

if (!$post_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing post_id'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT 
            c.id AS comment_id,
            c.post_id,
            c.user_id,
            c.content,
            c.reaction_count,
            c.created_at,
            u.id AS user_id,
            u.first_name,
            u.last_name,
            u.profile_picture
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = :post_id AND c.is_active = 1
        ORDER BY c.created_at DESC
        LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add time_ago
foreach ($comments as &$comment) {
    $comment['time_ago'] = $db->timeAgo($comment['created_at']);
}

echo json_encode([
    'success' => true,
    'message' => 'Comments fetched successfully',
    'data' => $comments
]);
exit;
