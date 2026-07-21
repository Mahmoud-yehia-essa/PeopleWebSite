<?php
// post/list.php
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

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$profile_id = isset($input['profile_id']) ? (int)$input['profile_id'] : null;
$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = isset($input['limit']) ? (int)$input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Main posts query
$sql = "
    SELECT 
        p.*,
        u.first_name, u.last_name, u.profile_picture,
        op.id AS original_post_id, op.content AS original_content, op.image AS original_image,
        op.video AS original_video, op.post_type_id AS original_post_type_id,
        ou.first_name AS original_first_name, ou.last_name AS original_last_name, ou.profile_picture AS original_profile_picture
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN posts op ON op.id = p.parent_id
    LEFT JOIN users ou ON ou.id = op.user_id
    WHERE p.is_active = 1
";

if ($post_id !== null) {
    // Fetch specific post
    $sql .= " AND p.id = :post_id";
} elseif ($profile_id !== null) {
    // Fetch posts for specific profile
    $sql .= " AND p.user_id = :profile_id";
}

if ($post_id === null) {
    // Only apply limit/offset when not fetching a single post
    $sql .= " ORDER BY p.id DESC LIMIT :offset, :limit";
}

$stmt = $conn->prepare($sql);

if ($post_id !== null) {
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
} elseif ($profile_id !== null) {
    $stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
}

if ($post_id === null) {
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
}

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($post_id !== null && empty($posts)) {
    echo json_encode([
        'success' => false,
        'message' => 'Post not found or not active'
    ]);
    exit;
}

// Collect post IDs for reactions and polls
$post_ids = [];
foreach ($posts as $post) {
    $post_ids[] = $post['id'];
    if (!empty($post['original_post_id'])) {
        $post_ids[] = $post['original_post_id'];
    }
}

// Fetch user saved posts
$user_saved_posts = [];
if ($user_id !== null && !empty($post_ids)) {
    $saved_sql = "SELECT post_id FROM saved_posts 
                 WHERE user_id = ? AND post_id IN (" . implode(',', array_fill(0, count($post_ids), '?')) . ")";
    $saved_stmt = $conn->prepare($saved_sql);
    $saved_stmt->execute(array_merge([$user_id], $post_ids));
    $user_saved_posts = $saved_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch user reactions
$user_reactions = [];
if ($user_id !== null && !empty($post_ids)) {
    $reaction_sql = "SELECT content_id FROM reactions 
                     WHERE user_id = ? AND content_type_id = 1 AND is_active = 1 
                     AND content_id IN (" . implode(',', array_fill(0, count($post_ids), '?')) . ")";
    $reaction_stmt = $conn->prepare($reaction_sql);
    $reaction_stmt->execute(array_merge([$user_id], $post_ids));
    $user_reactions = $reaction_stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch poll data
$poll_post_ids = [];
foreach ($posts as $post) {
    if ($post['post_type_id'] == 2) {
        $poll_post_ids[] = $post['id'];
    }
    if (!empty($post['original_post_id']) && $post['original_post_type_id'] == 2) {
        $poll_post_ids[] = $post['original_post_id'];
    }
}

$poll_data = [];
if (!empty($poll_post_ids)) {
    $poll_sql = "SELECT * FROM polls WHERE post_id IN (" . implode(',', array_fill(0, count($poll_post_ids), '?')) . ")";
    $poll_stmt = $conn->prepare($poll_sql);
    $poll_stmt->execute($poll_post_ids);
    $polls = $poll_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($polls)) {
        $poll_ids = array_column($polls, 'id');
        $poll_option_sql = "
            SELECT po.*, 
                (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id) AS vote_count,
                (SELECT COUNT(*) FROM poll_responses pr WHERE pr.poll_option_id = po.id AND pr.user_id = ?) AS is_selected
            FROM poll_options po
            WHERE po.poll_id IN (" . implode(',', array_fill(0, count($poll_ids), '?')) . ")";
        $poll_option_stmt = $conn->prepare($poll_option_sql);
        $poll_option_stmt->execute(array_merge([$user_id], $poll_ids));
        $poll_options = $poll_option_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($polls as $poll) {
            $poll_data[$poll['post_id']] = [
                'question' => $poll['question'],
                'expires_at' => $poll['expires_at'],
                'total_votes' => (int)$poll['total_votes'],
                'options' => []
            ];
        }

        foreach ($poll_options as $option) {
            foreach ($polls as $poll) {
                if ($poll['id'] == $option['poll_id']) {
                    $poll_data[$poll['post_id']]['options'][] = [
                        'id' => $option['id'],
                        'content' => $option['content'],
                        'vote_count' => (int)$option['vote_count'],
                        'is_selected' => (int)$option['is_selected']
                    ];
                    break;
                }
            }
        }
    }
}

// Prepare posts output
$processed_posts = [];
foreach ($posts as $post) {
    $processed_post = [
        'post_id' => $post['id'],
        'user_id' => $post['user_id'],
        'content' => $post['content'],
        'image' => $post['image'] ? $uploadsPath . $post['image'] : null,
        'video' => $post['video'] ? $uploadsPath . $post['video'] : null,
        'like_count' => $post['like_count'],
        'comment_count' => $post['comment_count'],
        'share_count' => $post['share_count'],
        'parent_id' => $post['parent_id'],
        'post_type_id' => $post['post_type_id'],
        'created_at' => $post['created_at'],
        'first_name' => $post['first_name'],
        'last_name' => $post['last_name'],
        'profile_picture' => $post['profile_picture'] ? $uploadsPath . $post['profile_picture'] : null,
        'is_reacted' => in_array($post['id'], $user_reactions),
        'is_saved' => in_array($post['id'], $user_saved_posts) ? true : false,
        'time_ago' => $db->timeAgo($post['created_at']),
        'question' => null,
        'expires_at' => null,
        'total_votes' => 0,
        'options' => []
    ];

    // Flatten poll data for main post
    if ($post['post_type_id'] == 2 && isset($poll_data[$post['id']])) {
        $processed_post['question'] = $poll_data[$post['id']]['question'];
        $processed_post['expires_at'] = $poll_data[$post['id']]['expires_at'];
        $processed_post['total_votes'] = $poll_data[$post['id']]['total_votes'];
        $processed_post['options'] = $poll_data[$post['id']]['options'];
    }

    // Handle original post
    if (!empty($post['parent_id'])) {
        $original = [
            'post_id' => $post['original_post_id'],
            'content' => $post['original_content'],
            'image' => $post['original_image'] ? $uploadsPath . $post['original_image'] : null,
            'video' => $post['original_video'] ? $uploadsPath . $post['original_video'] : null,
            'post_type_id' => $post['original_post_type_id'],
            'first_name' => $post['original_first_name'],
            'last_name' => $post['original_last_name'],
            'profile_picture' => $post['original_profile_picture'] ? $uploadsPath . $post['original_profile_picture'] : null,
            'question' => null,
            'expires_at' => null,
            'total_votes' => 0,
            'options' => []
        ];

        // Flatten poll data for original post
        if ($post['original_post_type_id'] == 2 && isset($poll_data[$post['original_post_id']])) {
            $original['question'] = $poll_data[$post['original_post_id']]['question'];
            $original['expires_at'] = $poll_data[$post['original_post_id']]['expires_at'];
            $original['total_votes'] = $poll_data[$post['original_post_id']]['total_votes'];
            $original['options'] = $poll_data[$post['original_post_id']]['options'];
        }

        $processed_post['original_post'] = $original;
    }

    $processed_posts[] = $processed_post;
}

// Output
echo json_encode([
    'success' => true,
    'message' => $post_id !== null ? 'Post fetched successfully' : 'Posts fetched successfully',
    'data' => $post_id !== null ? ($processed_posts[0] ?? null) : $processed_posts
]);
exit;