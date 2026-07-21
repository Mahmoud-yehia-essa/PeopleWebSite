<?php
//profile/send_code.php
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
include_once __DIR__ . '/../mailer/php_mailer.php';
$php_mailer = new php_mailer();

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
// $lang = isset($input['lang']) ? $db->sanitize($input['lang']) : "ar";


if (empty($input) || !isset($input['email']) || empty($input['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$method = isset($input['method']) ? $db->sanitize($input['method']) : "email";
$email = $db->sanitize($input['email']);

try {
    
    if ($method === 'phone') {
    $full_number = preg_replace('/\s+/', '', $email); // full number sent from frontend, e.g., +96171887115

    // Validate full number format
    if (!preg_match('/^\+?[1-9]\d{1,14}$/', $full_number)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
        exit();
    }

    // Extract country code
    preg_match('/^\+(\d{1,3})/', $full_number, $matches);
    $code = $matches[1] ?? '';
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Invalid country code']);
        exit();
    }

    // Extract local phone number without country code
    $phone_number = preg_replace('/^\+' . $code . '/', '', $full_number);

    // Check if full number exists in DB
    $stmtPhone = $conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
    $stmtPhone->bindParam(':phone_number', $full_number);
    $stmtPhone->execute();

    if (!$stmtPhone->fetch(PDO::FETCH_ASSOC)) {
        // Phone number not found → stop execution
        echo json_encode([
            'success' => false,
            'message' => 'Invalid phone number'
        ]);
        exit();
    }

    // Send OTP via API
  $authToken = "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJDLUM0REM0QkI1NjU2RjRBNiIsImlhdCI6MTc2MzYyNDQxMywiZXhwIjoxOTIxMzA0NDEzfQ.xiXFizaoKPrJji7WOSaMtde40tUeUoFqXgWEl3ifnWhALlHKSNyGvl038sqTBjy21R6SIl_2jSGwC90XRWAU8Q";
$customerId = "C-C4DC4BB5656F4A6";

    $url = "https://cpaas.messagecentral.com/verification/v3/send?countryCode=$code&customerId=$customerId&flowType=WHATSAPP&mobileNumber=$phone_number";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "authToken: $authToken",
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => '{}'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code >= 400) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP',
            'url' => $url,
            'error' => $curl_error ?: $response
        ]);
        exit();
    }

    $apiResponse = json_decode($response, true);

    // Save verification in DB
    $verification_id = $apiResponse['verificationId'] ?? uniqid('msg_', true);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $otp_code = "111111"; // For testing, replace with real OTP generator

    $stmtOtp = $conn->prepare("
        INSERT INTO phone_verifications 
        (verification_id, phone_number, otp_code, expires_at, created_at) 
        VALUES (:verification_id, :phone_number, :otp_code, :expires_at, NOW())
    ");
    $stmtOtp->bindParam(':verification_id', $verification_id);
    $stmtOtp->bindParam(':phone_number', $full_number); // save full number
    $stmtOtp->bindParam(':otp_code', $otp_code);
    $stmtOtp->bindParam(':expires_at', $expires_at);
    $stmtOtp->execute();

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully via MessageCentral',
        'data' => [
            'full_number' => $full_number,       // full number with country code
            'phone_number' => $phone_number,     // local number without country code
            'verification_id' => $verification_id,
            'messagecentral_response' => $apiResponse
        ]
    ], JSON_UNESCAPED_UNICODE);
}

    
    else{
    // Check if email exists and user is active
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND is_active = 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No active account found with this email'
        ]);
        exit;
    }
    
    // Generate 6-digit random code
    $reset_code = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Update the user's reset_code in database
    $updateStmt = $conn->prepare("UPDATE users SET reset_code = :reset_code WHERE email = :email");
    $updateStmt->bindParam(':reset_code', $reset_code);
    $updateStmt->bindParam(':email', $email);
    $updateStmt->execute();
    
    // Send the code via email
    $subject = "Your Password Reset Code";
    $message = "Your password reset code is: <strong>$reset_code</strong><br><br>";
    // $message .= "This code will expire in 15 minutes.";
    
    $mailSent = $php_mailer->sendMail($email, $subject, $message);
    
    if (!$mailSent) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send reset code email'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Reset code generated and sent successfully',
        'reset_code' => $reset_code // For testing purposes, you might want to remove this in production
    ]);
    
}
    
}

catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
    exit;
}