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

// التحقق من نوع التحقق
$verification_type = $input['verification_type'] ?? 'email'; // default email

// تحديد الحقول المطلوبة حسب نوع التحقق
$required_fields = ['first_name', 'last_name', 'password'];
if ($verification_type === 'email') {
    $required_fields[] = 'email';
} elseif ($verification_type === 'phone') {
    $required_fields[] = 'phone_number';
}

// التحقق من الحقول المطلوبة
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


// $lang = $db->sanitize($input['lang']);
$lang = isset($input['lang']) ? $db->sanitize($input['lang']) : "ar";


// تجميع بيانات المستخدم
$user_data = [];
foreach ($required_fields as $field) {
    $user_data[$field] = $db->sanitize($input[$field]);
}

// الحقول الاختيارية
$user_data['birth_date'] = isset($input['birth_date']) ? $db->sanitize($input['birth_date']) : null;
$user_data['gender'] = isset($input['gender']) ? $db->sanitize($input['gender']) : null;
$user_data['bio'] = isset($input['bio']) ? $db->sanitize($input['bio']) : null;
$user_data['token'] = isset($input['token']) ? $db->sanitize($input['token']) : null;

// التحقق من البريد الإلكتروني فقط إذا موجود
if ($verification_type === 'email' && !filter_var($user_data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email format'
    ]);
    exit;
}

// تحقق من وجود البريد أو رقم الهاتف في القاعدة
if ($verification_type === 'email') {
    $stmtEmail = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmtEmail->bindParam(':email', $user_data['email']);
    $stmtEmail->execute();
    if ($stmtEmail->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
        exit;
    }
} elseif ($verification_type === 'phone') {
    $stmtPhone = $conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
    $stmtPhone->bindParam(':phone_number', $user_data['phone_number']);
    $stmtPhone->execute();
    if ($stmtPhone->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'Phone Number is already in use.']);
        exit;
    }
}

// إدراج المستخدم
try {
    $password = $user_data['password'];
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

    // جلب بيانات المستخدم الجديد
    $stmtUser = $conn->prepare("SELECT id, first_name, last_name, email, phone_number, birth_date, gender, bio, token, created_at FROM users WHERE id = :id");
    $stmtUser->bindParam(':id', $user_id);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $user['name'] = $user['first_name'] . ' ' . $user['last_name'];

$message = ($lang == "ar")
    ? "تم التسجيل بنجاح."
    : "Registration successful.";

echo json_encode([
    'success' => true,
    'message' => $message,
    'data' => $user
]);
exit;


} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Registration failed. Please try again later.'
    ]);
    exit;
}
