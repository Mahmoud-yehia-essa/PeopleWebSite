<?php
// misc/notifications.php
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
$offset = isset($input['offset']) ? (int)$input['offset'] : 0;
$limit = isset($input['limit']) ? (int)$input['limit'] : 20;

// Validate input
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Helper function to generate Facebook-style grouped messages
function generateGroupedMessage($names, $others_count, $action) {
    $first_two = array_slice($names, 0, 2);
    $message = implode(' and ', $first_two);
    
    if ($others_count > 0) {
        $message .= ' and ' . $others_count . ' other' . ($others_count > 1 ? 's' : '');
    }
    
    $message .= ' ' . $action;
    return $message;
}

function isNotificationSeen($conn, $user_id, $notification_id, $notification_type, $user_ids) {
    // For friend requests (no user_ids array)
    if ($user_ids === null) {
        $sql = "SELECT id FROM seen 
                WHERE user_id = :user_id 
                AND notification_id = :notification_id 
                AND notification_type = :notification_type
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':notification_id', $notification_id, PDO::PARAM_STR);
        $stmt->bindParam(':notification_type', $notification_type, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    // For grouped notifications (with user_ids array)
    if (!empty($user_ids)) {
        $first_user_id = $user_ids[0]; // Get the first user in the array
        
        // Create a unique notification ID by combining with first user ID
        $unique_notification_id = $notification_id . '_' . $first_user_id;
        
        $sql = "SELECT id FROM seen 
                WHERE user_id = :user_id 
                AND notification_id = :notification_id 
                AND notification_type = :notification_type
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':notification_id', $unique_notification_id, PDO::PARAM_STR);
        $stmt->bindParam(':notification_type', $notification_type, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    return false; // Default to unseen if no user_ids provided
}
try {
    $notifications = [];
    
    // 1. Friend requests (individual notifications)
    $friend_requests_sql = "SELECT 
                            f.id as request_id,
                            u.id as user_id,
                            u.first_name,
                            u.last_name,
                            u.profile_picture,
                            f.created_at,
                            'friend_request' as type
                        FROM friendships f
                        JOIN users u ON u.id = f.sender_id
                        WHERE f.receiver_id = :user_id 
                        AND f.is_active = 0
                        ORDER BY f.created_at DESC
                        LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($friend_requests_sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($request = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notification_id = 'friend_request_'.$request['request_id'];
        $is_seen = isNotificationSeen($conn, $user_id, $notification_id, 'friend_request' , Null);
        
        $notifications[] = [
            'id' => $notification_id,
            'type' => $request['type'],
            'user_id' => $request['user_id'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'profile_picture' => $request['profile_picture'] ? $uploadsPath . $request['profile_picture'] : null,
            'created_at' => $request['created_at'],
            'time_ago' => $db->timeAgo($request['created_at']),
            'message' => $request['first_name'].' '.$request['last_name'].' sent you a friend request',
            'is_seen' => $is_seen
        ];
    }

    // 2. Post likes (grouped notifications)
    $post_likes_sql = "SELECT 
                        p.id as post_id,
                        p.content as post_content,
                        p.like_count,
                        GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) ORDER BY r.created_at DESC SEPARATOR '|') as likers_names,
                        GROUP_CONCAT(DISTINCT u.id ORDER BY r.created_at DESC SEPARATOR '|') as likers_ids,
                        MAX(r.created_at) as created_at,
                        'post_like' as type
                    FROM posts p
                    JOIN reactions r ON r.content_id = p.id AND r.content_type_id = 1
                    JOIN users u ON u.id = r.user_id
                    WHERE p.user_id = :user_id
                    AND r.user_id != :user_id
                    GROUP BY p.id
                    ORDER BY created_at DESC
                    LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($post_likes_sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($like = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $likers_names = explode('|', $like['likers_names']);
        $likers_ids = explode('|', $like['likers_ids']);
        $total_likes = (int)$like['like_count'];
        $others_count = max(0, $total_likes - count($likers_ids));
        
        $notification_id = 'like_'.$like['post_id'];
        $is_seen = isNotificationSeen($conn, $user_id, $notification_id, 'post_like' , $likers_ids);
        
        $notifications[] = [
            'id' => $notification_id,
            'type' => $like['type'],
            'user_ids' => $likers_ids,
            'post_id' => $like['post_id'],
            'post_content' => $like['post_content'],
            'created_at' => $like['created_at'],
            'time_ago' => $db->timeAgo($like['created_at']),
            'message' => generateGroupedMessage($likers_names, $others_count, 'liked your post'),
            'total_count' => $total_likes,
            'others_count' => $others_count,
            'is_seen' => $is_seen
        ];
    }

    // 3. Post comments (grouped notifications)
    $post_comments_sql = "SELECT 
                            p.id as post_id,
                            p.content as post_content,
                            p.comment_count,
                            GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) ORDER BY c.created_at DESC SEPARATOR '|') as commenters_names,
                            GROUP_CONCAT(DISTINCT u.id ORDER BY c.created_at DESC SEPARATOR '|') as commenters_ids,
                            MAX(c.created_at) as created_at,
                            'post_comment' as type
                        FROM posts p
                        JOIN comments c ON c.post_id = p.id
                        JOIN users u ON u.id = c.user_id
                        WHERE p.user_id = :user_id
                        AND c.user_id != :user_id
                        GROUP BY p.id
                        ORDER BY created_at DESC
                        LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($post_comments_sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($comment = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $commenters_names = explode('|', $comment['commenters_names']);
        $commenters_ids = explode('|', $comment['commenters_ids']);
        $total_comments = (int)$comment['comment_count'];
        $others_count = max(0, $total_comments - count($commenters_ids));
        
        $notification_id = 'comment_'.$comment['post_id'];
        // $is_seen = isNotificationSeen($conn, $user_id, $notification_id, 'post_comment' $commenters_ids);
        $is_seen = isNotificationSeen($conn, $user_id, 'comment_'.$comment['post_id'], 'post_comment', $commenters_ids);
        $notifications[] = [
            'id' => $notification_id,
            'type' => $comment['type'],
            'user_ids' => $commenters_ids,
            'post_id' => $comment['post_id'],
            'post_content' => $comment['post_content'],
            'created_at' => $comment['created_at'],
            'time_ago' => $db->timeAgo($comment['created_at']),
            'message' => generateGroupedMessage($commenters_names, $others_count, 'commented on your post'),
            'total_count' => $total_comments,
            'others_count' => $others_count,
            'is_seen' => $is_seen
        ];
    }

    // 4. Post shares (grouped notifications)
    $post_shares_sql = "SELECT 
                        p.id as original_post_id,
                        p.content as original_post_content,
                        p.share_count,
                        GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) ORDER BY s.created_at DESC SEPARATOR '|') as sharers_names,
                        GROUP_CONCAT(DISTINCT u.id ORDER BY s.created_at DESC SEPARATOR '|') as sharers_ids,
                        MAX(s.created_at) as created_at,
                        'post_share' as type
                    FROM posts p
                    JOIN posts s ON s.parent_id = p.id
                    JOIN users u ON u.id = s.user_id
                    WHERE p.user_id = :user_id
                    AND s.user_id != :user_id
                    GROUP BY p.id
                    ORDER BY created_at DESC
                    LIMIT :offset, :limit";
    
    $stmt = $conn->prepare($post_shares_sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    while ($share = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sharers_names = explode('|', $share['sharers_names']);
        $sharers_ids = explode('|', $share['sharers_ids']);
        $total_shares = (int)$share['share_count'];
        $others_count = max(0, $total_shares - count($sharers_ids));
        
        $notification_id = 'share_'.$share['original_post_id'];
        // $is_seen = isNotificationSeen($conn, $user_id, $notification_id, 'post_share' $sharers_ids);
        $is_seen = isNotificationSeen($conn, $user_id, 'share_'.$share['original_post_id'], 'post_share', $sharers_ids);
        
        $notifications[] = [
            'id' => $notification_id,
            'type' => $share['type'],
            'user_ids' => $sharers_ids,
            'post_id' => $share['original_post_id'],
            'post_content' => $share['original_post_content'],
            'created_at' => $share['created_at'],
            'time_ago' => $db->timeAgo($share['created_at']),
            'message' => generateGroupedMessage($sharers_names, $others_count, 'shared your post'),
            'total_count' => $total_shares,
            'others_count' => $others_count,
            'is_seen' => $is_seen
        ];
    }

    // Sort all notifications by created_at DESC
    usort($notifications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Apply pagination after sorting
    $paginated_notifications = array_slice($notifications, $offset, $limit);
    
    // Count unseen notifications
    $unseen_count = count(array_filter($notifications, function($n) { return !$n['is_seen']; }));
    
    echo json_encode([
        'success' => true,
        'notifications' => $paginated_notifications,
        'total' => count($notifications),
        'unseen_count' => $unseen_count
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>