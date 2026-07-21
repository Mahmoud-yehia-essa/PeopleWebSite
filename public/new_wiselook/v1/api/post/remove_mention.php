<?php
// mention/remove_mention.php
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

$response = ['success' => false, 'message' => '', 'data' => null];

// Get input
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;
$content_id = isset($input['content_id']) ? (int)$input['content_id'] : null;
$content_type = isset($input['content_type']) ? trim($input['content_type']) : null;

// Validate required fields
if (!$user_id || !$content_id || !$content_type) {
    $response['message'] = 'Missing required fields.';
    echo json_encode($response);
    exit;
}

// Only support 'post' mentions for now
if ($content_type !== 'post') {
    $response['message'] = 'Invalid content type.';
    echo json_encode($response);
    exit;
}

try {
    // Check if the mention exists
    $check = $conn->prepare("SELECT * FROM mentions WHERE user_id = :user_id AND content_id = :content_id AND content_type_id = 1");
    $check->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $check->bindParam(':content_id', $content_id, PDO::PARAM_INT);
    $check->execute();

    if ($check->rowCount() === 0) {
        $response['message'] = 'Mention not found.';
        echo json_encode($response);
        exit;
    }

    // Delete the mention
    $delete = $conn->prepare("DELETE FROM mentions WHERE user_id = :user_id AND content_id = :content_id AND content_type_id = 1");
    $delete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $delete->bindParam(':content_id', $content_id, PDO::PARAM_INT);
    $delete->execute();

    $response['success'] = true;
    $response['message'] = 'Mention removed successfully.';
} catch (Exception $e) {
    $response['message'] = 'Error removing mention: ' . $e->getMessage();
}

echo json_encode($response);
exit;
