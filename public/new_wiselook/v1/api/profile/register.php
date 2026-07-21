<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../mailer/php_mailer.php';

$input = json_decode(file_get_contents('php://input'), true);

// Define required fields for user registration
$required_fields = [
    'first_name', 'last_name', 'email', 'password', 'phone_number'
];

// Check required fields
foreach ($required_fields as $field) {
    if (!isset($input[$field])) {
        echo json_encode([
            'success' => false,
            'message' => "Missing required field: {$field}"
        ]);
        exit;
    }
    
    if (empty(trim($input[$field]))) {
        echo json_encode([
            'success' => false,
            'message' => "Field cannot be empty: {$field}"
        ]);
        exit;
    }
}

$db = new Database();
$conn = $db->getConnection();

// Sanitize input data
$user_data = [];
foreach ($required_fields as $field) {
    $user_data[$field] = $db->sanitize($input[$field]);
}

// Sanitize optional fields
$user_data['birth_date'] = isset($input['birth_date']) ? $db->sanitize($input['birth_date']) : null;
$user_data['gender'] = isset($input['gender']) ? $db->sanitize($input['gender']) : null;
$user_data['bio'] = isset($input['bio']) ? $db->sanitize($input['bio']) : null;
$user_data['token'] = isset($input['token']) ? $db->sanitize($input['token']) : null;

// Validate email format
if (!filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// Check if email already exists
$stmtEmail = $conn->prepare("SELECT id FROM users WHERE email = :email");
$stmtEmail->bindParam(':email', $user_data['email']);
$stmtEmail->execute();
if ($stmtEmail->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is already in use.'
    ]);
    exit;
}

// Check if phone_number already exists
$stmtPhone = $conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
$stmtPhone->bindParam(':phone_number', $user_data['phone_number']);
$stmtPhone->execute();
if ($stmtPhone->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        'success' => false,
        'message' => 'Phone Number is already in use.'
    ]);
    exit;
}

try {
    $password = $user_data['password'];

    // Prepare the SQL query with token field
    $query = "INSERT INTO users 
        (email, password, first_name, last_name, phone_number, birth_date, gender, bio, token, created_at, updated_at) 
        VALUES 
        (:email, :password, :first_name, :last_name, :phone_number, :birth_date, :gender, :bio, :token, NOW(), NOW())";
    
    $stmt = $conn->prepare($query);
    
    $stmt->bindParam(':email', $user_data['email']);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':first_name', $user_data['first_name']);
    $stmt->bindParam(':last_name', $user_data['last_name']);
    $stmt->bindParam(':phone_number', $user_data['phone_number']);
    $stmt->bindParam(':birth_date', $user_data['birth_date']);
    $stmt->bindParam(':gender', $user_data['gender']);
    $stmt->bindParam(':bio', $user_data['bio']);
    $stmt->bindParam(':token', $user_data['token']);
    
    $stmt->execute();

    $user_id = $conn->lastInsertId();

    // Fetch the newly created user data including token
    $stmtUser = $conn->prepare("SELECT id, first_name, last_name, email, phone_number, birth_date, gender, bio, token, created_at FROM users WHERE id = :id");
    $stmtUser->bindParam(':id', $user_id);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Combine first and last name into a full name
    $user['name'] = $user['first_name'] . ' ' . $user['last_name'];

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful.',
        'data' => $user
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.',
        // 'error' => $e->getMessage()
    ]);
    exit;
}