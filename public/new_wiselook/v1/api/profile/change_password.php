<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate required inputs
if (
    !isset($input['id']) || !isset($input['old_password']) || !isset($input['new_password']) ||
    empty(trim($input['id'])) || empty(trim($input['old_password'])) || empty(trim($input['new_password']))
) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing credentials'
    ]);
    exit;
}

$client_id = (int)$input['id'];
$old_password = trim($input['old_password']);
$new_password = trim($input['new_password']);

// Check that old and new passwords are different
if ($old_password === $new_password) {
    echo json_encode([
        'success' => false,
        'message' => 'Old password should not be the same as new password'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    // Fetch the current password for the user
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :client_id");
    $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit;
    }

    // Compare old password (not hashed in your case)
    if ($old_password != $user['password']) {
        echo json_encode([
            'success' => false,
            'message' => 'Old password is incorrect'
        ]);
        exit;
    }

    // Update the password
    $date_now = date('Y-m-d H:i:s');
    $stmtUpdate = $conn->prepare("UPDATE users SET password = :new_password, updated_at = :updated_at WHERE id = :client_id");
    $stmtUpdate->bindParam(':new_password', $new_password);
    $stmtUpdate->bindParam(':updated_at', $date_now);
    $stmtUpdate->bindParam(':client_id', $client_id, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed: ' . $e->getMessage()
    ]);
    exit;
}
