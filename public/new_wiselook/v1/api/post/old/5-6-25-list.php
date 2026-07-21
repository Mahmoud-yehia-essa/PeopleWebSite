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

// Get and validate JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit;
}

$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$profile_id = isset($input['profile_id']) ? (int)$input['profile_id'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit  = isset($input['limit']) ? (int)$input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Main SQL with repost (parent) joins
$sql = "
    SELECT DISTINCT 
        p.id AS post_id,
        p.user_id,
        p.content,
        p.image,
        p.video,    
        p.like_count,
        p.comment_count,
        p.share_count,
        p.parent_id,
        p.post_type_id,
        p.created_at,
        u.first_name,
        u.last_name,
        u.profile_picture,
        
        -- Original post (reposted) info
        op.id AS original_post_id,
        op.content AS original_content,
        op.image AS original_image,
        op.video AS original_video,
        op.post_type_id AS original_post_type_id,
        ou.first_name AS original_first_name,
        ou.last_name AS original_last_name,
        ou.profile_picture AS original_profile_picture
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN posts op ON op.id = p.parent_id
    LEFT JOIN users ou ON ou.id = op.user_id
    WHERE p.is_active = 1 
";

// Filter by profile_id if provided
if ($profile_id !== null) {
    $sql .= " AND p.user_id = :profile_id ";
}

$sql .= " GROUP BY p.id ORDER BY p.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);

// Bind parameters
if ($profile_id !== null) {
    $stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check user reactions if user_id is provided
if ($user_id !== null && !empty($posts)) {
    $post_ids = array_column($posts, 'post_id');
    $placeholders = implode(',', array_fill(0, count($post_ids), '?'));

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

    foreach ($posts as &$post) {
        $post['is_reacted'] = in_array($post['post_id'], $liked_posts);
    }
}

// Get poll data for posts that are polls
$poll_post_ids = [];
foreach ($posts as $post) {
    if ($post['post_type_id'] == 2) {
        $poll_post_ids[] = $post['post_id'];
    }
}

if (!empty($poll_post_ids)) {
    // Get poll questions and total_votes
    $poll_sql = "SELECT id, post_id, question, created_at, expires_at, total_votes 
                 FROM polls 
                 WHERE post_id IN (" . implode(',', array_fill(0, count($poll_post_ids), '?')) . ")";
    $poll_stmt = $conn->prepare($poll_sql);
    $poll_stmt->execute($poll_post_ids);
    $polls = $poll_stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($polls)) {
        $poll_ids = array_column($polls, 'id');

        // Get poll options with vote_count
        $poll_options_sql = "SELECT po.id, po.poll_id, po.content, po.vote_count, 
                     CASE WHEN pr.user_id IS NOT NULL THEN 1 ELSE 0 END as is_selected
                     FROM poll_options po
                     LEFT JOIN poll_responses pr ON pr.poll_option_id = po.id AND pr.user_id = ?
                     WHERE po.poll_id IN (" . implode(',', array_fill(0, count($poll_ids), '?')) . ")";

        // Prepare the parameters - user_id first, then poll_ids
        $poll_options_params = array_merge([$user_id], $poll_ids);

        $poll_options_stmt = $conn->prepare($poll_options_sql);
        $poll_options_stmt->execute($poll_options_params);
        $poll_options = $poll_options_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Organize options by poll_id
        $options_by_poll = [];
        foreach ($poll_options as $option) {
            $options_by_poll[$option['poll_id']][] = [
                'id' => $option['id'],
                'content' => $option['content'],
                'vote_count' => (int)$option['vote_count'],
                // 'is_selected' => (bool)$option['is_selected']
                'is_selected' => (int)$option['is_selected']
            ];
        }

        // Create a map of post_id to poll data
        $poll_data_by_post = [];
        foreach ($polls as $poll) {
            $poll_data_by_post[$poll['post_id']] = [
                'poll_id' => $poll['id'], // Include poll_id for vote checking
                'question' => $poll['question'],
                'expires_at' => $poll['expires_at'],
                'total_votes' => (int)$poll['total_votes'],
                'options' => $options_by_poll[$poll['id']] ?? []
            ];
        }
    }
}

// Process media paths and add time_ago for each post
foreach ($posts as &$post) {
    // Process media paths
    if (!empty($post['image'])) {
        $post['image'] = $uploadsPath . $post['image'];
    }
    if (!empty($post['video'])) {
        $post['video'] = $uploadsPath . $post['video'];
    }

    if (!empty($post['profile_picture'])) {
        $post['profile_picture'] = $uploadsPath . $post['profile_picture'];
    }

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

    // Add poll data if this is a poll post
    if ($post['post_type_id'] == 2 && isset($poll_data_by_post[$post['post_id']])) {
        $poll_data = $poll_data_by_post[$post['post_id']];
        $post['question'] = $poll_data['question'];
        $post['expires_at'] = $poll_data['expires_at'];
        $post['total_votes'] = $poll_data['total_votes'];
        $post['options'] = $poll_data['options'];

        // Add user's vote if user_id is provided
        if ($user_id !== null) {
            $vote_stmt = $conn->prepare("
                SELECT poll_option_id 
                FROM poll_responses 
                WHERE user_id = ? 
                AND poll_option_id IN (
                    SELECT id FROM poll_options WHERE poll_id = ?
                )
            ");
            $vote_stmt->execute([$user_id, $poll_data['poll_id']]);
            $user_vote = $vote_stmt->fetchColumn();

            if ($user_vote) {
                $post['user_vote'] = (int)$user_vote;
            }
        }
    }

    // If this is a repost of a poll, add original poll data
    if (!empty($post['parent_id']) && $post['original_post_type_id'] == 2 && isset($poll_data_by_post[$post['original_post_id']])) {
        $original_poll_data = $poll_data_by_post[$post['original_post_id']];
        $post['original_question'] = $original_poll_data['question'];
        $post['original_expires_at'] = $original_poll_data['expires_at'];
        $post['original_total_votes'] = $original_poll_data['total_votes'];
        $post['original_options'] = $original_poll_data['options'];

        // Add user's vote for original poll if user_id is provided
        if ($user_id !== null) {
            $vote_stmt = $conn->prepare("
                SELECT poll_option_id 
                FROM poll_responses 
                WHERE user_id = ? 
                AND poll_option_id IN (
                    SELECT id FROM poll_options WHERE poll_id = ?
                )
            ");
            $vote_stmt->execute([$user_id, $original_poll_data['poll_id']]);
            $user_vote = $vote_stmt->fetchColumn();

            if ($user_vote) {
                $post['original_user_vote'] = (int)$user_vote;
            }
        }
    }
}

// Return result
echo json_encode([
    'success' => true,
    'message' => 'Posts fetched successfully',
    'data' => $posts
]);
exit;
