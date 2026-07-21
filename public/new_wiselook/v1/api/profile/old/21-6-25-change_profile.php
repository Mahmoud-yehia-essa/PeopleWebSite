<?php
//profile/change_profile.php
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
include_once __DIR__ . '/../upload_function.php'; // Include our upload function

// Since we're handling file uploads, we need to use $_POST and $_FILES instead of php://input
$input = $_POST;
$files = $_FILES;

// For testing JSON input (you can remove this in production)
if (empty($_POST) && !empty(file_get_contents('php://input'))) {
    $input = json_decode(file_get_contents('php://input'), true);
}

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

// Handle file uploads for profile and cover pictures
$fileFields = [
    'profile_picture' => null,
    'cover_picture' => null
];

foreach (array_keys($fileFields) as $field) {
    if (!empty($files[$field]['name'])) {
        $uploadResult = uploadToExternalServer($files[$field], [
            'user_id' => $user_id,
            'purpose' => $field
        ]);
        
        if ($uploadResult['success']) {
            $mediaName = $uploadResult['media_name'][0] ?? null;
            if ($mediaName) {
                $updateValues[$field] = $mediaName;
                $updateSQL[] = "$field = :$field";
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Failed to upload $field",
                'upload_errors' => $uploadResult['errors'] ?? []
            ]);
            exit;
        }
    } elseif (isset($input[$field])) {
        // Allow clearing the picture by sending null or empty string
        $updateValues[$field] = $input[$field] ? $db->sanitize($input[$field]) : null;
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

// Add updated_at
$updateSQL[] = "updated_at = :updated_at";
$updateValues['updated_at'] = date('Y-m-d H:i:s');

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

    // Get updated user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'User profile updated successfully',
        'user' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'profile_picture' => $user['profile_picture'],
            'cover_picture' => $user['cover_picture'],
            'bio' => $user['bio'],
        ]
    ]);
    exit;
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Update failed',
        'error' => $e->getMessage()
    ]);
    exit;
}