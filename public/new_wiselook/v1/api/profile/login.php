<?php
// profile/login.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php'; // Include the config file to get uploads path

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
$lang = isset($input['lang']) ? $db->sanitize($input['lang']) : "ar";
$token = isset($input['token']) ? $db->sanitize($input['token']) : null;

// First check if username exists as either email or phone number
$checkStmt = $conn->prepare("SELECT id FROM users WHERE status = 0 AND (email = :username OR phone_number = :username) LIMIT 1");
$checkStmt->bindParam(':username', $username);
$checkStmt->execute();

if ($checkStmt->rowCount() === 0) {

    if($lang == "ar"){
        $message = "اسم المستخدم أو كلمة المرور غير صحيحة";
    } else {
        $message = "Username or password is incorrect";
    }

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}


// Determine if username is email or phone number for the login attempt
$isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
$field = $isEmail ? 'email' : 'phone_number';

// Prepare and execute the login query
$stmt = $conn->prepare("SELECT id, email, phone_number, first_name, last_name, profile_picture, cover_picture, birth_date, gender, bio, token 
                        FROM users 
                        WHERE $field = :username 
                          AND password = :password 
                          AND is_active = 1");
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password', $password);
$stmt->execute();

$userData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($userData) {
    // Update last_login timestamp and token if provided
    $updateQuery = "UPDATE users SET last_login = NOW()";
    $params = [':id' => $userData['id']];
    
    if ($token) {
        $updateQuery .= ", token = :token";
        $params[':token'] = $token;
    }
    
    $updateQuery .= " WHERE id = :id";
    
    $updateStmt = $conn->prepare($updateQuery);
    foreach ($params as $key => &$val) {
        $updateStmt->bindParam($key, $val);
    }
    $updateStmt->execute();

    // If token was updated, include it in the response
    if ($token) {
        $userData['token'] = $token;
    }

    // Add full path to profile and cover pictures if they exist
    if (!empty($userData['profile_picture'])) {
        $userData['profile_picture'] = $uploadsPath . $userData['profile_picture'];
    } else {
        $userData['profile_picture'] = $uploadsPath . "default.jpeg";
    }
    
    if (!empty($userData['cover_picture'])) {
        $userData['cover_picture'] = $uploadsPath . $userData['cover_picture'];
    } else {
        $userData['cover_picture'] = $uploadsPath . "default-cover.jpeg";
    }

    echo json_encode([
        'success' => true,
        'message' => ($lang == "ar" ? 'تم تسجيل الدخول بنجاح' : 'Login successful'),
        'data' => $userData
    ]);
    exit;
} else {

    $message = ($lang == "ar")
        ? "اسم المستخدم أو كلمة المرور غير صحيحة"
        : "Username or password is incorrect";

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}