<?php
//story/list.php
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

$db = new Database();
$conn = $db->getConnection();

if (isset($input['user_id']) && !empty(trim($input['user_id']))) {
    $user_id = (int) $input['user_id'];
    
    // Initialize empty array for user's stories
    $userStories = [];
    
    // First, get the user's own active stories (if any)
    $queryUserStories = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1 AND s.user_id = :user_id
        ORDER BY s.created_at DESC
    ";
    
    $stmtUser = $conn->prepare($queryUserStories);
    $stmtUser->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtUser->execute();
    $userStories = $stmtUser->fetchAll(PDO::FETCH_ASSOC);
    
    // Then get active stories from friends
    $queryFriendStories = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1
        AND s.user_id != :user_id 
        AND EXISTS (
            SELECT 1 FROM friendships f 
            WHERE f.is_active = 1 
            AND (
                (f.sender_id = :user_id AND f.receiver_id = s.user_id) 
                OR 
                (f.receiver_id = :user_id AND f.sender_id = s.user_id)
            )
        )
        ORDER BY s.created_at DESC
        LIMIT :offset, :limit
    ";
    
    $stmtFriends = $conn->prepare($queryFriendStories);
    $stmtFriends->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtFriends->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmtFriends->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmtFriends->execute();
    $friendStories = $stmtFriends->fetchAll(PDO::FETCH_ASSOC);
    
    // Combine results - user's stories first (even if empty)
    $stories = array_merge($userStories, $friendStories);
} else {
    // Fetch all active stories if no user_id is provided
    $query = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1
        ORDER BY s.created_at DESC
        LIMIT :offset, :limit
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Group stories by user and format the response
$groupedStories = [];
foreach ($stories as $story) {
    $userId = $story['user_id'];
    
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
    
    $groupedStories[$userId]['stories'][] = [
        'id' => $story['id'],
        'image' => !empty($story['image']) ? $uploadsPath . $story['image'] : null,
        'created_at' => $story['created_at'],
        'is_active' => $story['is_active']
    ];
}

// If user_id was provided, ensure user's stories appear first even if empty
if (isset($user_id)) {
    // Create empty entry for user if they have no stories
    if (!isset($groupedStories[$user_id])) {
        // Get user info
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
    
    // Reorder array to put user's stories first
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