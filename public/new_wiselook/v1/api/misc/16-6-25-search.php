<?php
// misc/search.php
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
$search_term = isset($input['search_term']) ? trim($input['search_term']) : null;
$search_type = isset($input['search_type']) ? $input['search_type'] : null;
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = isset($input['limit']) ? (int)$input['limit'] : 20;

// Validate input
if (!$search_term || strlen($search_term) < 2) {
  echo json_encode(['success' => false, 'message' => 'Search term must be at least 2 characters']);
  exit;
}

if (!in_array($search_type, ['posts', 'users'])) {
  echo json_encode(['success' => false, 'message' => 'Invalid search type. Must be "posts" or "users"']);
  exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
  if ($search_type === 'posts') {
    // Search for posts
    $search_like = '%' . $search_term . '%';

    // Search both posts content and poll questions
    $sql = "SELECT 
                p.*,
                u.first_name, u.last_name, u.profile_picture,
                CASE WHEN sp.post_id IS NOT NULL THEN 1 ELSE 0 END AS is_saved
            FROM posts p
            JOIN users u ON u.id = p.user_id
            LEFT JOIN saved_posts sp ON sp.post_id = p.id AND sp.user_id = :user_id
            LEFT JOIN polls pl ON pl.post_id = p.id
            WHERE p.is_active = 1 AND 
                (p.content LIKE :search_term OR 
                (p.post_type_id = 2 AND pl.question LIKE :search_term))
            ORDER BY p.id DESC
            LIMIT :offset, :limit";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':search_term', $search_like);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Collect post IDs for reactions, polls, etc.
    $post_ids = [];
    foreach ($posts as $post) {
        $post_ids[] = $post['id'];
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

    // Identify which posts are polls
    $poll_post_ids = [];
    foreach ($posts as $post) {
        if ($post['post_type_id'] == 2) { // Assuming 2 is the type ID for polls
            $poll_post_ids[] = $post['id'];
        }
    }

    // Fetch poll data
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

    // Process posts
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
            'post_type_id' => $post['post_type_id'],
            'created_at' => $post['created_at'],
            'first_name' => $post['first_name'],
            'last_name' => $post['last_name'],
            'profile_picture' => $post['profile_picture'] ? $uploadsPath . $post['profile_picture'] : null,
            'is_reacted' => in_array($post['id'], $user_reactions),
            'is_saved' => in_array($post['id'], $user_saved_posts),
            'time_ago' => $db->timeAgo($post['created_at'])
        ];

        // Add poll data if this is a poll
        if ($post['post_type_id'] == 2 && isset($poll_data[$post['id']])) {
            $processed_post['question'] = $poll_data[$post['id']]['question'];
            $processed_post['expires_at'] = $poll_data[$post['id']]['expires_at'];
            $processed_post['total_votes'] = $poll_data[$post['id']]['total_votes'];
            $processed_post['options'] = $poll_data[$post['id']]['options'];
        }

        $processed_posts[] = $processed_post;
    }

    echo json_encode([
        'success' => true,
        'type' => 'posts',
        'data' => $processed_posts,
        'count' => count($processed_posts)
    ]);
} else {
    // Search for users
    $search_like = '%' . $search_term . '%';

    $sql = "SELECT 
                u.id, 
                u.first_name, 
                u.last_name, 
                u.profile_picture, 
                u.bio,
                COALESCE(
                    (SELECT 
                        CASE 
                            WHEN f.is_active = 1 THEN 'friends'
                            WHEN f.sender_id = :user_id THEN 'cancel'
                            ELSE 'confirm'
                        END
                    FROM friendships f
                    WHERE (f.sender_id = :user_id AND f.receiver_id = u.id)
                       OR (f.receiver_id = :user_id AND f.sender_id = u.id)
                    LIMIT 1),
                    'add'
                ) AS friendship_type
            FROM users u
            WHERE u.is_active = 1 AND 
                (u.first_name LIKE :search_term OR u.last_name LIKE :search_term)
            ORDER BY u.id DESC
            LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':search_term', $search_like);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process users
    $processed_users = [];
    foreach ($users as $user) {
      $processed_users[] = [
        'user_id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'profile_picture' => $user['profile_picture'] ? $uploadsPath . $user['profile_picture'] : null,
        'bio' => $user['bio'],
        'friendship_type' => $user['friendship_type'],
        'is_friend' => ($user['friendship_type'] === 'friends')
      ];
    }

    echo json_encode([
      'success' => true,
      'type' => 'users',
      'data' => $processed_users,
      'count' => count($processed_users)
    ]);
  }
} catch (PDOException $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage()
  ]);
}
