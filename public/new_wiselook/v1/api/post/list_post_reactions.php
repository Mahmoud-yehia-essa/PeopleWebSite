<?php
// post/list_post_reactions.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

$post_id = isset($input['post_id']) ? (int)$input['post_id'] : 0;
$offset  = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit   = isset($input['limit']) ? (int)$input['limit'] : 20;

if ($post_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid post_id'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch users who reacted to this post
$sql = "SELECT 
            r.id AS reaction_id,
            r.reaction_type_id,
            rt.type AS reaction_name,
            u.id AS user_id,
            u.first_name,
            u.last_name,
            u.profile_picture,
            r.created_at
        FROM reactions r
        JOIN users u ON r.user_id = u.id
        JOIN reaction_type rt ON r.reaction_type_id = rt.id
        WHERE r.content_type_id = 1  -- 1 = post
        AND r.content_id = :post_id
        AND r.is_active = 1
        ORDER BY r.created_at DESC
        LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepend uploads path to profile_picture if available
foreach ($users as &$user) {
    if (!empty($user['profile_picture'])) {
        $user['profile_picture'] = $uploadsPath . $user['profile_picture'];
    }
    $user['time_ago'] = $db->timeAgo($user['created_at']);
}

echo json_encode([
    'success' => true,
    'message' => 'Post reactions fetched successfully',
    'data' => $users
]);
exit;
?>
