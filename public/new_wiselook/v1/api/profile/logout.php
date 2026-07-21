<?php
// profile/logout.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['user_id']) || empty(trim($input['user_id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing user'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = $db->sanitize($input['user_id']);

// Update the user's token to null
$updateStmt = $conn->prepare("UPDATE users SET token = NULL WHERE id = :user_id");
$updateStmt->bindParam(':user_id', $user_id);
$updateStmt->execute();

if ($updateStmt->rowCount() > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Logout successful'
    ]);
} else {
    // حتى لو ما تغير شي (token كان NULL) بدنا نتحقق إذا اليوزر موجود
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE id = :user_id");
    $checkStmt->bindParam(':user_id', $user_id);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Already logged out'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
}
exit;
