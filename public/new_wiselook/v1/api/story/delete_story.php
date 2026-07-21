<?php
// store/delete_story.php
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

$response = [
    'success' => false,
    'message' => '',
];

$db = new Database();
$conn = $db->getConnection();





$input = json_decode(file_get_contents('php://input'), true);

$story_id = isset($input['story_id']) ? (int)$input['story_id'] : null;
$user_id  = isset($input['user_id']) ? (int)$input['user_id'] : null;




if (!$story_id || !$user_id) {
    $response['message'] = 'story_id and user_id are required';
    echo json_encode($response);
    exit;
}

try {
    // Check if story exists and belongs to user
    $stmt = $conn->prepare("SELECT * FROM stories WHERE id = :story_id AND user_id = :user_id");
    $stmt->bindParam(':story_id', $story_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $story = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$story) {
        $response['message'] = 'Story not found or not authorized';
        echo json_encode($response);
        exit;
    }


    // Delete story from DB
    $deleteStmt = $conn->prepare("DELETE FROM stories WHERE id = :story_id");
    $deleteStmt->bindParam(':story_id', $story_id);

    if ($deleteStmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Story deleted successfully';
    } else {
        $response['message'] = 'Failed to delete story';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
