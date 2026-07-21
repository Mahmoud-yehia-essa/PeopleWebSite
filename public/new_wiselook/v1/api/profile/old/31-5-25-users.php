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

// Check if user exists and is active
$db->checkUserExists($user_id);

// Fetch user profile information
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
          WHERE id = :user_id AND is_active = 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    // Add full path to profile and cover pictures if they exist
    if (!empty($user['profile_picture'])) {
        $user['profile_picture'] = $uploadsPath . $user['profile_picture'];
    }
    
    if (!empty($user['cover_picture'])) {
        $user['cover_picture'] = $uploadsPath . $user['cover_picture'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User profile retrieved successfully',
        'user' => $user
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve user profile'
    ]);
    exit;
}