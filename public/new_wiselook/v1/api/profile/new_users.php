<?php
// profile/users.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['user_id']) || empty(trim($input['user_id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Sanitize inputs
$user_id = $db->sanitize($input['user_id']);
$profile_id = isset($input['profile_id']) ? $db->sanitize($input['profile_id']) : null;

// Check if logged-in user exists and is active
$db->checkUserExists($user_id);

// Determine which user's profile to fetch
$target_user_id = $profile_id ? $profile_id : $user_id;

// Fetch target user profile information
$query = "SELECT 
            id, 
            email, 
            phone_number, 
            first_name, 
            last_name, 
            profile_picture, 
            cover_picture, 
            birth_date, 
            gender, 
            address, 
            bio, 
            post_count, 
            friend_count, 
            DATE_FORMAT(created_at, '%D %M, %Y') AS date_joined 
          FROM `users` 
          WHERE id = :target_user_id AND is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':target_user_id', $target_user_id);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Add full path to profile and cover pictures if they exist
    if (!empty($user['profile_picture'])) {
        $user['profile_picture'] = $uploadsPath . $user['profile_picture'];
    } else {
        $user['profile_picture'] = $uploadsPath . "default.jpeg";
    }
    
    if (!empty($user['cover_picture'])) {
        $user['cover_picture'] = $uploadsPath . $user['cover_picture'];
    } else {
        $user['cover_picture'] = $uploadsPath . "default-cover.jpeg";
    }
    
    $response = [
        'success' => true,
        'message' => 'User profile retrieved successfully',
        'user' => $user
    ];
    
    // Check friendship status if profile_id is provided and it's different from user_id
    if ($profile_id && $user_id != $profile_id) {
        // Check friendship status between logged-in user (user_id) and profile user (profile_id)
        $friendship_query = "SELECT 
                                sender_id, 
                                receiver_id, 
                                is_active 
                            FROM `friendships` 
                            WHERE (sender_id = :user_id AND receiver_id = :profile_id) 
                               OR (sender_id = :profile_id AND receiver_id = :user_id)";
        $friendship_stmt = $conn->prepare($friendship_query);
        $friendship_stmt->bindParam(':user_id', $user_id);
        $friendship_stmt->bindParam(':profile_id', $profile_id);
        $friendship_stmt->execute();
        $friendship = $friendship_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($friendship) {
            $response['is_friend'] = (bool)$friendship['is_active'];
            
            if ($friendship['is_active']) {
                $response['type'] = 'remove';
            } else {
                if ($friendship['sender_id'] == $user_id) {
                    $response['type'] = 'cancel';
                } else {
                    $response['type'] = 'confirm';
                }
            }
        } else {
            $response['is_friend'] = false;
            $response['type'] = 'add';
        }
    }
    
    echo json_encode($response);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve user profile'
    ]);
    exit;
}