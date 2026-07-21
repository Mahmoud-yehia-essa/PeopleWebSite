<?php
//post/list.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit  = isset($input['limit']) ? (int)$input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Main SQL with repost (parent) joins
$sql = "
    SELECT 
        p.id AS post_id,
        p.user_id,
        p.content,
        p.image,
        p.video,
        p.like_count,
        p.comment_count,
        p.share_count,
        p.parent_id,
        p.created_at,
        u.first_name,
        u.last_name,
        u.profile_picture,
        
        -- Original post (reposted) info
        op.id AS original_post_id,
        op.content AS original_content,
        op.image AS original_image,
        op.video AS original_video,
        ou.first_name AS original_first_name,
        ou.last_name AS original_last_name,
        ou.profile_picture AS original_profile_picture
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN posts op ON op.id = p.parent_id
    LEFT JOIN users ou ON ou.id = op.user_id
    WHERE p.is_active = 1 
    AND p.post_type_id = 1 
";

$sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check user reactions if user_id is provided
if ($user_id !== null && !empty($posts)) {
    // Get all post IDs
    $post_ids = array_column($posts, 'post_id');
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));

    // Query to check which posts the user has liked
    $reaction_sql = "
        SELECT content_id 
        FROM reactions 
        WHERE user_id = ? 
        AND content_type_id = 1 
        AND is_active = 1 
        AND content_id IN ($placeholders)
    ";

    $reaction_stmt = $conn->prepare($reaction_sql);
    $reaction_stmt->execute(array_merge([$user_id], $post_ids));
    $liked_posts = $reaction_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Add is_liked flag to each post
    foreach ($posts as &$post) {
        $post['is_reacted'] = in_array($post['post_id'], $liked_posts);
    }
}

// Process media paths and add time_ago for each post
foreach ($posts as &$post) {
    // Process main post media
    if (!empty($post['image'])) {
        $post['image'] = $uploadsPath . $post['image'];
    }
    if (!empty($post['video'])) {
        $post['video'] = $uploadsPath . $post['video'];
    }
    
    // Process profile picture
    if (!empty($post['profile_picture'])) {
        $post['profile_picture'] = $uploadsPath . $post['profile_picture'];
    }
    
    // Process original post media (for reposts)
    if (!empty($post['original_image'])) {
        $post['original_image'] = $uploadsPath . $post['original_image'];
    }
    if (!empty($post['original_video'])) {
        $post['original_video'] = $uploadsPath . $post['original_video'];
    }
    if (!empty($post['original_profile_picture'])) {
        $post['original_profile_picture'] = $uploadsPath . $post['original_profile_picture'];
    }
    
    $post['time_ago'] = $db->timeAgo($post['created_at']);
}

// Return result
echo json_encode([
    'success' => true,
    'message' => 'Posts fetched successfully',
    'data' => $posts
]);
exit;