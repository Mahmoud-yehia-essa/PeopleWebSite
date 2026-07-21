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
// include_once __DIR__ . '/../config.php';

// $php_mailer = new php_mailer();

// session_start();

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

// Check if username already exists
// $stmtUsername = $conn->prepare("SELECT id FROM users WHERE username = :username");
// $stmtUsername->bindParam(':username', $user_data['username']);
// $stmtUsername->execute();
// if ($stmtUsername->fetch(PDO::FETCH_ASSOC)) {
//     echo json_encode([
//         'success' => false,
//         'message' => 'Username is already taken.'
//     ]);
//     exit;
// }

try {
    $password = $user_data['password'];

    // Insert into the `users` table
    $stmt = $conn->prepare("INSERT INTO users 
        ( email, password, first_name, last_name, phone_number, birth_date, gender, bio, created_at, updated_at) 
        VALUES 
        ( :email, :password, :first_name, :last_name, :phone_number, :birth_date, :gender, :bio, NOW(), NOW())");
    
    // $stmt->bindParam(':username', $user_data['username']);
    $stmt->bindParam(':email', $user_data['email']);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':first_name', $user_data['first_name']);
    $stmt->bindParam(':last_name', $user_data['last_name']);
    $stmt->bindParam(':phone_number', $user_data['phone_number']);
    $stmt->bindParam(':birth_date', $user_data['birth_date']);
    $stmt->bindParam(':gender', $user_data['gender']);
    $stmt->bindParam(':bio', $user_data['bio']);
    
    $stmt->execute();

    $user_id = $conn->lastInsertId();

    // Send welcome email with verification code
    // $subject = "Welcome to $business_name!";
    // $body = "Welcome to $business_name!<br><br>" .
    //         "Your account has been successfully created.<br>" .
    //         "Username: {$user_data['username']}<br>";
    // $php_mailer->sendMail($user_data['email'], $subject, $body);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful.'
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
