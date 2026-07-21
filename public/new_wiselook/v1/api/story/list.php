<?php
// story/list.php

ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

$offset = isset($input['offset']) ? (int) $input['offset'] : 0;
$limit = isset($input['limit']) ? (int) $input['limit'] : 20;
$lang = "";
$db = new Database();
$conn = $db->getConnection();

if (isset($input['user_id']) && !empty(trim($input['user_id']))) {
    $user_id = (int) $input['user_id'];

    // === Get All Active Stories (Not Expired) in Chronological Order excluding blocked users ===
    $queryStories = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture,
               CASE WHEN ss.story_id IS NULL THEN 0 ELSE 1 END as is_seen
        FROM stories s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN story_seen ss ON ss.story_id = s.id AND ss.user_id = :user_id
        WHERE s.is_active = 1 
          AND s.expires_at > NOW()
          AND (
                s.user_id = :user_id 
                OR EXISTS (
                    SELECT 1 FROM friendships f 
                    WHERE f.is_active = 1 
                      AND (
                          (f.sender_id = :user_id AND f.receiver_id = s.user_id) 
                          OR 
                          (f.receiver_id = :user_id AND f.sender_id = s.user_id)
                      )
                )
          )
          AND s.user_id NOT IN (
              SELECT blocked_id FROM block WHERE blocker_id = :user_id_block
          )
          AND s.user_id NOT IN (
              SELECT blocker_id FROM block WHERE blocked_id = :user_id_block
          )
        ORDER BY s.created_at ASC
        LIMIT :offset, :limit
    ";

    $stmt = $conn->prepare($queryStories);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id_block', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // === All Active + Not Expired Stories (No user_id given) ===
    $query = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture, 0 as is_seen
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1 
          AND s.expires_at > NOW()
        ORDER BY s.created_at ASC
        LIMIT :offset, :limit
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === Group Stories by User ===
$groupedStories = [];
foreach ($stories as $story) {
         

    $userId = $story['user_id'];
    //  $created_at_ago =    timeago();
    if (!isset($groupedStories[$userId])) {
        $groupedStories[$userId] = [
            'user_id' => $userId,
            'first_name' => $story['first_name'],
            'last_name' => $story['last_name'],
            'profile_picture' => !empty($story['profile_picture']) 
                ? $uploadsPath . $story['profile_picture'] 
                : $uploadsPath . 'default-profile.jpg',
            'stories' => []
        ];
    }

    // Determine media type and path
    $media = null;
    $type = null;
    
    if (!empty($story['image'])) {
        $media = $uploadsPath . $story['image'];
        $type = 'image';
    } elseif (!empty($story['video'])) {
        $media = $uploadsPath . $story['video'];
        $type = 'video';
    }

    $groupedStories[$userId]['stories'][] = [
        'id' => $story['id'],
        'media' => $media,
        'type' => $type,
        'created_at' => $story['created_at'],
        'is_active' => $story['is_active'],
        'is_seen' => $story['is_seen'],
        'view_count' => $story['view_count'],
        'time_ago' => $db->timeAgo($story['created_at'] , $lang),
    ];
}

// === Make Sure User's Section Is First Even If Empty ===
if (isset($user_id)) {
    if (!isset($groupedStories[$user_id])) {
        $queryUser = "SELECT first_name, last_name, profile_picture FROM users WHERE id = :user_id";
        $stmtUser = $conn->prepare($queryUser);
        $stmtUser->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtUser->execute();
        $userInfo = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if ($userInfo) {
            $groupedStories[$user_id] = [
                'user_id' => $user_id,
                'first_name' => $userInfo['first_name'],
                'last_name' => $userInfo['last_name'],
                'profile_picture' => !empty($userInfo['profile_picture']) 
                    ? $uploadsPath . $userInfo['profile_picture'] 
                    : $uploadsPath . 'default-profile.jpg',
                'stories' => []
            ];
        }
    }

    $userStories = isset($groupedStories[$user_id]) ? [$user_id => $groupedStories[$user_id]] : [];
    unset($groupedStories[$user_id]);
    $groupedStories = $userStories + $groupedStories;
}

$result = array_values($groupedStories);

echo json_encode([
    'success' => true,
    'message' => 'Stories fetched successfully',
    'data' => $result
]);
exit;
