<?php
// chat/list.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$db = new Database();
$conn = $db->getConnection();

// Get input data (works for both JSON and form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Get parameters
$user_id = isset($input['user_id']) ? $db->sanitize($input['user_id']) : null;

// Validate user_id
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit;
}

try {
    // Query to fetch friends list
    $query = "SELECT 
                u.id as person_id,
                u.first_name,
                u.last_name,
                u.token,
                CONCAT(:uploadsPath, IFNULL(u.profile_picture, 'default.jpeg')) as profile_picture 
              FROM users u, friendships f 
              WHERE 
                (f.sender_id = :user_id OR f.receiver_id = :user_id) AND 
                f.is_active = 1 AND
                (u.id = f.sender_id OR u.id = f.receiver_id) AND
                u.id != :user_id
              ORDER BY u.first_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':uploadsPath', $uploadsPath);
    $stmt->execute();
    
    $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($friends),
        'data' => $friends
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>