<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['username']) || empty(trim($input['username']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing input: email or phone number'
    ]);
    exit;
}

if (!isset($input['password']) || empty(trim($input['password']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing input: password'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Sanitize inputs
$username = $db->sanitize($input['username']);
$password = $db->sanitize($input['password']);

// First check if username exists as either email or phone number
$checkStmt = $conn->prepare("SELECT id FROM users WHERE email = :username OR phone_number = :username LIMIT 1");
$checkStmt->bindParam(':username', $username);
$checkStmt->execute();

if ($checkStmt->rowCount() === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'User does not exist'
    ]);
    exit;
}

// Determine if username is email or phone number for the login attempt
$isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
$field = $isEmail ? 'email' : 'phone_number';

// Prepare and execute the login query
$stmt = $conn->prepare("SELECT id, email, phone_number, first_name, last_name, profile_picture, cover_picture, birth_date, gender, bio 
                        FROM users 
                        WHERE $field = :username 
                          AND password = :password 
                          AND is_active = 1");
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password);
$stmt->execute();

$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    // Update last_login timestamp
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
    $updateStmt->bindParam(':id', $userData['id']);
    $updateStmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => $userData
    ]);
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid password'
    ]);
    exit;
}