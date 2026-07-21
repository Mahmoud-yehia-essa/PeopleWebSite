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
if (!isset($input['id']) || empty(trim($input['id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Sanitize inputs
$user_id = $db->sanitize($input['id']);
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
            token,
            friend_count, 
            DATE_FORMAT(created_at, '%D %M, %Y') AS date_joined 
          FROM `users` 
          WHERE id = :target_user_id AND is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':target_user_id', $target_user_id);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Initialize blocking status variables
    $blocked_by_me = false;
    $blocked_me = false;
    $mutual_block = false;
    
    // Check blocking status if viewing another user's profile
    if ($profile_id && $user_id != $profile_id) {
        $mutual_block_query = "SELECT blocker_id, blocked_id FROM block 
            WHERE (blocker_id = :user_id AND blocked_id = :profile_id) 
               OR (blocker_id = :profile_id AND blocked_id = :user_id)";
        $mutual_block_stmt = $conn->prepare($mutual_block_query);
        $mutual_block_stmt->bindParam(':user_id', $user_id);
        $mutual_block_stmt->bindParam(':profile_id', $profile_id);
        $mutual_block_stmt->execute();
        $blocks = $mutual_block_stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($blocks as $block) {
            if ($block['blocker_id'] == $user_id) {
                $blocked_by_me = true;
            }
            if ($block['blocker_id'] == $profile_id) {
                $blocked_me = true;
            }
        }
        
        $mutual_block = $blocked_by_me && $blocked_me;

        // If I blocked them OR they blocked me, return restricted profile
        if ($blocked_by_me || $blocked_me) {
            $user['profile_picture'] = $uploadsPath . "default.jpeg";
            $user['cover_picture'] = $uploadsPath . "default-cover.jpeg";
            $user['first_name'] = $user["first_name"];
            $user['last_name'] = $user["last_name"];
            $user['bio'] = "";
            $user['post_count'] = 0;
            $user['friend_count'] = 0;
            $user['blocked_by_me'] = $blocked_by_me ? 1 : 0;
            $user['blocked_me'] = $blocked_me ? 1 : 0;
            $user['mutual_block'] = $mutual_block ? 1 : 0;
            $user['can_interact'] = 0; // Cannot perform any actions
            $user['show_unblock_only'] = $blocked_by_me ? 1 : 0; // Show unblock button only if I blocked them
            
            echo json_encode([
                'success' => true,
                'message' => 'User profile is private',
                'user' => $user
            ]);
            exit;
        }
    }

    // Add full path to profile and cover pictures if they exist
    $user['profile_picture'] = !empty($user['profile_picture']) ? $uploadsPath . $user['profile_picture'] : $uploadsPath . "default.jpeg";
    $user['cover_picture'] = !empty($user['cover_picture']) ? $uploadsPath . $user['cover_picture'] : $uploadsPath . "default-cover.jpeg";

    // Add blocking status to response
    $user['blocked_by_me'] = $blocked_by_me ? 1 : 0;
    $user['blocked_me'] = $blocked_me ? 1 : 0;
    $user['mutual_block'] = $mutual_block ? 1 : 0;
    $user['can_interact'] = 1; // Can perform normal actions
    $user['show_unblock_only'] = 0;

    $response = [
        'success' => true,
        'message' => 'User profile retrieved successfully',
        'user' => $user
    ];

    // Check friendship status if profile_id is provided and it's different from user_id
    if ($profile_id && $user_id != $profile_id && !$blocked_by_me && !$blocked_me) {
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
            $response['user']['is_friend'] = (bool)$friendship['is_active'];

            if ($friendship['is_active']) {
                $response['user']['type'] = 'remove';
            } else {
                if ($friendship['sender_id'] == $user_id) {
                    $response['user']['type'] = 'cancel';
                } else {
                    $response['user']['type'] = 'confirm';
                }
            }
        } else {
            $response['user']['is_friend'] = false;
            $response['user']['type'] = 'add';
        }
    } else if ($profile_id && $user_id != $profile_id) {
        // If there's blocking, no friendship actions available
        $response['user']['is_friend'] = false;
        $response['user']['type'] = 'none';
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
?>