<?php
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once __DIR__ . '/../db.php';

$data = json_decode(file_get_contents('php://input'), true);

// ✅ Validate input
if (
    !isset($data['user_id']) || empty($data['user_id']) ||
    !isset($data['token']) || empty($data['token'])
) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$db   = new Database();
$conn = $db->getConnection();

$user_id = (int) $data['user_id'];
$token   = $db->sanitize($data['token']);

try {

    $stmt = $conn->prepare("
        UPDATE users 
        SET token = :token 
        WHERE id = :id
    ");

    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Token updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update token'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error'   => $e->getMessage()
    ]);
}
