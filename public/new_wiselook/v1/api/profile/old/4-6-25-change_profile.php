<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS'); // PATCH/PUT simulated via POST
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['user_id']) || empty($input['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing user_id'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$user_id = (int)$db->sanitize($input['user_id']);

// Define allowed fields to update
$updatableFields = ['first_name', 'last_name', 'birth_date', 'gender', 'bio'];
$updateValues = [];
$updateSQL = [];

foreach ($updatableFields as $field) {
    if (isset($input[$field])) {
        $updateValues[$field] = $db->sanitize($input[$field]);
        $updateSQL[] = "$field = :$field";
    }
}

// Ensure at least one field is being updated
if (empty($updateSQL)) {
    echo json_encode([
        'success' => false,
        'message' => 'No updatable fields provided'
    ]);
    exit;
}

// Add last_modified
$updateSQL[] = "last_modified = :last_modified";
$updateValues['last_modified'] = date('Y-m-d H:i:s');

// Build SQL
$sql = "UPDATE users SET " . implode(', ', $updateSQL) . " WHERE id = :user_id";

try {
    $stmt = $conn->prepare($sql);

    // Bind values
    foreach ($updateValues as $field => $value) {
        $stmt->bindValue(":$field", $value);
    }
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'User profile updated successfully'
    ]);
    exit;
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed',
        'reason'  =>  'reason: '. $e->getMessage(),
    ]);
    exit;
}
