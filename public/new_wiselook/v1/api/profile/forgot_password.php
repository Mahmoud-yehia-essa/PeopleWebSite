<?php
//profile/forgot_password.php
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($input) || 
    !isset($input['email']) || empty($input['email']) ||
    !isset($input['reset_code']) || empty($input['reset_code']) ||
    !isset($input['new_password']) || empty($input['new_password']) ||
    !isset($input['confirm_password']) || empty($input['confirm_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Credentials Required'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$email = $db->sanitize($input['email']);
$reset_code = $db->sanitize($input['reset_code']);
$new_password = $input['new_password'];
$confirm_password = $input['confirm_password'];

$verification_id = $db->sanitize($input['verification_id']);
// $method = $db->sanitize($input['method']);
$method = isset($input['method']) ? $db->sanitize($input['method']) : '';

// Check if passwords match
if ($new_password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match'
    ]);
    exit;
}

try {
    
        
    if ($method === 'phone') {
    $countryCode = $input['country_code'] ?? '971';
    $mobileNumber = $input['mobile_number'] ?? '';
    $verificationId = $input['verification_id'] ?? '';
        $authToken = "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJDLUM0REM0QkI1NjU2RjRBNiIsImlhdCI6MTc2MzYyNDQxMywiZXhwIjoxOTIxMzA0NDEzfQ.xiXFizaoKPrJji7WOSaMtde40tUeUoFqXgWEl3ifnWhALlHKSNyGvl038sqTBjy21R6SIl_2jSGwC90XRWAU8Q";
        $customerId = "C-C4DC4BB5656F4A6";

    $url = "https://cpaas.messagecentral.com/verification/v3/validateOtp?countryCode=$countryCode&mobileNumber=$mobileNumber&verificationId=$verificationId&customerId=$customerId&code=$reset_code";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["authToken: $authToken"],
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $http_code >= 400) {
        echo json_encode(['success' => false, 'message' => 'فشل التحقق', 'error' => $curl_error ?: $response, 'status' => $http_code]);
        exit;
    }

    $body = json_decode($response, true);
    if (isset($body['responseCode']) && $body['responseCode'] === 200) {
        $stmtVerify = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = :user_id");
        $stmtVerify->bindParam(':user_id', $user_id);
        $stmtVerify->execute();
        echo json_encode(['success' => true, 'message' => 'رقم الهاتف تم التحقق منه بنجاح']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'OTP غير صحيح أو verificationId خاطئ', 'response' => $body]);
        exit;
    }
}

else{
    // Check if reset code exists and is valid (maybe expiry for later)
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = :email AND reset_code = :reset_code");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':reset_code', $reset_code);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired reset code'
        ]);
        exit;
    }

    // Update password
    $updateStmt = $conn->prepare("UPDATE users SET password = :password, reset_code = NULL WHERE email = :email");
    $updateStmt->bindParam(':password', $new_password);
    $updateStmt->bindParam(':email', $email);
    $updateStmt->execute();
    
    
    
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully',
        'email' => $email
    ]);
}   
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
    exit;
}