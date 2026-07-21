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
    
    // Fetch stories from friends (is_active = 1 in friendships)
    $query = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1
        AND (
            EXISTS (
                SELECT 1 FROM friendships f 
                WHERE f.is_active = 1 
                AND (
                    (f.sender_id = :user_id AND f.receiver_id = s.user_id) 
                    OR 
                    (f.receiver_id = :user_id AND f.sender_id = s.user_id)
                )
            )
            OR s.user_id = :user_id  -- Include user's own stories
        )
        ORDER BY s.user_id, s.created_at DESC
        LIMIT :offset, :limit
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
} else {
    // Fetch all active stories if no user_id is provided
    $query = "
        SELECT s.*, u.first_name, u.last_name, u.profile_picture
        FROM stories s
        JOIN users u ON s.user_id = u.id
        WHERE s.is_active = 1
        ORDER BY s.user_id, s.created_at DESC
        LIMIT :offset, :limit
    ";
    
    $stmt = $conn->prepare($query);
}

$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

$result = array_values($groupedStories);

echo json_encode([
    'success' => true,
    'message' => 'Stories fetched successfully',
    'data' => $result
]);
exit;