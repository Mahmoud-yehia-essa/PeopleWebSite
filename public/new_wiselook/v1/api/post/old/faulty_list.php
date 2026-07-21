<?php
// post/list.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit  = isset($input['limit']) ? (int)$input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    SELECT 
        p.id AS post_id,
        p.user_id,
        p.content,
        p.privacy_level_id,
        p.like_count,
        p.comment_count,
        p.share_count,
        p.parent_id,
        p.post_type_id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        
        -- Original post info
        op.id AS original_post_id,
        op.content AS original_content,
        ou.first_name AS original_first_name,
        ou.last_name AS original_last_name,
        ou.profile_picture AS original_profile_picture,
        
        -- Current post media
        pm.image AS post_image,
        pm.video AS post_video,
        
        -- Original post media
        opm.image AS original_post_image,
        opm.video AS original_post_video
        
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN posts op ON op.id = p.parent_id
    LEFT JOIN users ou ON ou.id = op.user_id
    LEFT JOIN post_media pm ON pm.post_id = p.id AND pm.is_active = 1
    LEFT JOIN post_media opm ON opm.post_id = op.id AND opm.is_active = 1
    WHERE p.is_active = 1
";

if ($user_id !== null) {
    $sql .= " AND p.user_id != :user_id";
}

$sql .= " ORDER BY p.id DESC, pm.id ASC, opm.id ASC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);

if ($user_id !== null) {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process rows to group media
$posts = [];
$current_post_id = null;

foreach ($rows as $row) {
    if ($row['post_id'] !== $current_post_id) {
        // New post found
        $current_post_id = $row['post_id'];
        
        $post = [
            'post_id' => $row['post_id'],
            'user_id' => $row['user_id'],
            'content' => $row['content'],
            'privacy_level_id' => $row['privacy_level_id'],
            'like_count' => $row['like_count'],
            'comment_count' => $row['comment_count'],
            'share_count' => $row['share_count'],
            'parent_id' => $row['parent_id'],
            'post_type_id' => $row['post_type_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'profile_picture' => $row['profile_picture'],
            'images' => [],
            'videos' => [],
            'original_post_id' => $row['original_post_id'],
            'original_content' => $row['original_content'],
            'original_first_name' => $row['original_first_name'],
            'original_last_name' => $row['original_last_name'],
            'original_profile_picture' => $row['original_profile_picture'],
            'original_images' => [],
            'original_videos' => []
        ];
        
        $posts[] = &$post;
    } else {
        // Continue with same post
        $post = &$posts[count($posts) - 1];
    }
    
    // Add media to current post
    if (!empty($row['post_image'])) {
        $post['images'][] = $row['post_image'];
    }
    if (!empty($row['post_video'])) {
        $post['videos'][] = $row['post_video'];
    }
    
    // Add media to original post
    if (!empty($row['original_post_image'])) {
        $post['original_images'][] = $row['original_post_image'];
    }
    if (!empty($row['original_post_video'])) {
        $post['original_videos'][] = $row['original_post_video'];
    }
}

// Remove duplicates (in case JOIN created them)
foreach ($posts as &$post) {
    $post['images'] = array_values(array_unique($post['images']));
    $post['videos'] = array_values(array_unique($post['videos']));
    $post['original_images'] = array_values(array_unique($post['original_images']));
    $post['original_videos'] = array_values(array_unique($post['original_videos']));
}

echo json_encode([
    'success' => true,
    'message' => 'Posts fetched successfully',
    'data' => $posts
]);
exit;