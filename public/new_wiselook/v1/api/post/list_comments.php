<?php
// post/list_comments.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

$post_id = isset($input['post_id']) ? (int)$input['post_id'] : null;
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$lang = isset($input['lang']) ? $input['lang'] : 'ar';

// Remove offset and limit parameters
// $offset  = isset($input['offset']) ? (int)$input['offset'] : 0;
// $limit   = isset($input['limit']) ? (int)$input['limit'] : 20;

if (!$post_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing post_id'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Check if user exists and is active if user_id is provided
if ($user_id) {
    $db->checkUserExists($user_id);
}

// First fetch all top-level comments (parent_id is NULL or 0)
// Removed LIMIT clause
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
        AND (c.parent_id IS NULL OR c.parent_id = 0)
        ORDER BY c.created_at DESC";  // Removed LIMIT :offset, :limit

$stmt = $conn->prepare($sql);
$stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
// Removed offset and limit bindings
// $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
// $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all comment IDs for fetching reactions
$commentIds = array_column($comments, 'comment_id');
$userReactions = [];

// Fetch user reactions if user_id is provided
if ($user_id && !empty($commentIds)) {
    $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
    
    $reactionSql = "SELECT 
                    r.content_id AS comment_id,
                    r.reaction_type_id,
                    rt.type AS reaction_name
                FROM reactions r
                JOIN reaction_type rt ON r.reaction_type_id = rt.id
                WHERE r.user_id = ? 
                AND r.content_id IN ($placeholders)
                AND r.content_type_id = 2 
                AND r.is_active = 1";
    
    $reactionStmt = $conn->prepare($reactionSql);
    $reactionStmt->execute(array_merge([$user_id], $commentIds));
    
    while ($row = $reactionStmt->fetch(PDO::FETCH_ASSOC)) {
        $userReactions[$row['comment_id']] = [
            'is_reacted' => 1,
            'reaction_type_id' => $row['reaction_type_id'],
            'reaction_name' => $row['reaction_name']
        ];
    }
}

// Now fetch all replies for these comments
if (!empty($comments)) {
    $placeholders = implode(',', array_fill(0, count($commentIds), '?'));
    
    $repliesSql = "SELECT 
                    c.id AS comment_id,
                    c.post_id,
                    c.parent_id,
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
                WHERE c.parent_id IN ($placeholders) AND c.is_active = 1
                ORDER BY c.created_at ASC";
    
    $repliesStmt = $conn->prepare($repliesSql);
    $repliesStmt->execute($commentIds);
    $allReplies = $repliesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get all reply IDs for fetching reactions
    $replyIds = array_column($allReplies, 'comment_id');
    $userReplyReactions = [];
    
    // Fetch user reactions for replies if user_id is provided
    if ($user_id && !empty($replyIds)) {
        $placeholders = implode(',', array_fill(0, count($replyIds), '?'));
        
        $reactionSql = "SELECT 
                        r.content_id AS comment_id,
                        r.reaction_type_id,
                        rt.type AS reaction_name
                    FROM reactions r
                    JOIN reaction_type rt ON r.reaction_type_id = rt.id
                    WHERE r.user_id = ? 
                    AND r.content_id IN ($placeholders)
                    AND r.content_type_id = 2 
                    AND r.is_active = 1";
        
        $reactionStmt = $conn->prepare($reactionSql);
        $reactionStmt->execute(array_merge([$user_id], $replyIds));
        
        while ($row = $reactionStmt->fetch(PDO::FETCH_ASSOC)) {
            $userReplyReactions[$row['comment_id']] = [
                'is_reacted' => 1,
                'reaction_type_id' => $row['reaction_type_id'],
                'reaction_name' => $row['reaction_name']
            ];
        }
    }
    
    // Organize replies by parent_id
    $repliesByParent = [];
    foreach ($allReplies as $reply) {
        $repliesByParent[$reply['parent_id']][] = $reply;
    }
    
    // Merge replies with their parent comments
    foreach ($comments as &$comment) {
        $comment['time_ago'] = $db->timeAgo($comment['created_at'] , $lang);
        
        // Prepend uploads path to profile picture if it's not empty
        if (!empty($comment['profile_picture'])) {
            $comment['profile_picture'] = $uploadsPath . $comment['profile_picture'];
        }
        
        // Add reaction info if user_id is provided
        if ($user_id) {
            $comment['is_reacted'] = $userReactions[$comment['comment_id']]['is_reacted'] ?? 0;
            if ($comment['is_reacted']) {
                $comment['reaction_type_id'] = $userReactions[$comment['comment_id']]['reaction_type_id'];
                $comment['reaction_name'] = $userReactions[$comment['comment_id']]['reaction_name'];
            }
        }
        
        // Add replies if they exist
        $comment['replies'] = [];
        if (isset($repliesByParent[$comment['comment_id']])) {
            foreach ($repliesByParent[$comment['comment_id']] as &$reply) {
                $reply['time_ago'] = $db->timeAgo($reply['created_at'], $lang); // Added lang parameter
                if (!empty($reply['profile_picture'])) {
                    $reply['profile_picture'] = $uploadsPath . $reply['profile_picture'];
                }
                
                // Add reaction info for replies if user_id is provided
                if ($user_id) {
                    $reply['is_reacted'] = $userReplyReactions[$reply['comment_id']]['is_reacted'] ?? 0;
                    if ($reply['is_reacted']) {
                        $reply['reaction_type_id'] = $userReplyReactions[$reply['comment_id']]['reaction_type_id'];
                        $reply['reaction_name'] = $userReplyReactions[$reply['comment_id']]['reaction_name'];
                    }
                }
            }
            $comment['replies'] = $repliesByParent[$comment['comment_id']];
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Comments fetched successfully',
    'data' => $comments
]);
exit;
?>