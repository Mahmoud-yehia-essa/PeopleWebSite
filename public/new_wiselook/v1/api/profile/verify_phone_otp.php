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

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['verification_id'], $input['otp_code'], $input['type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Verification ID, OTP code, and type are required'
    ]);
    exit;
}

$type = $input['type']; // phone أو email

$db = new Database();
$conn = $db->getConnection();

$verification_id = $db->sanitize($input['verification_id']);
$otp_code = $db->sanitize($input['otp_code']);

try {
    
    if ($type === 'phone') {
    $countryCode = $input['country_code'] ?? '971';
    $mobileNumber = $input['mobile_number'] ?? '';
    $verificationId = $input['verification_id'] ?? '';
        $authToken = "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJDLUM0REM0QkI1NjU2RjRBNiIsImlhdCI6MTc2MzYyNDQxMywiZXhwIjoxOTIxMzA0NDEzfQ.xiXFizaoKPrJji7WOSaMtde40tUeUoFqXgWEl3ifnWhALlHKSNyGvl038sqTBjy21R6SIl_2jSGwC90XRWAU8Q";
        $customerId = "C-C4DC4BB5656F4A6";

    $url = "https://cpaas.messagecentral.com/verification/v3/validateOtp?countryCode=$countryCode&mobileNumber=$mobileNumber&verificationId=$verificationId&customerId=$customerId&code=$otp_code";

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
    // Get OTP record
    $stmtOtp = $conn->prepare("SELECT id, phone_number, otp_code, expires_at, verified 
                               FROM phone_verifications 
                               WHERE verification_id = :verification_id 
                               AND used = 0 
                               ORDER BY created_at DESC LIMIT 1");
    $stmtOtp->bindParam(':verification_id', $verification_id);
    $stmtOtp->execute();
    $otpRecord = $stmtOtp->fetch(PDO::FETCH_ASSOC);

    if (!$otpRecord) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid verification ID'
        ]);
        exit;
    }

    if ($otpRecord['verified']) {
        echo json_encode([
            'success' => false,
            'message' => $type === 'email' ? 'Email already verified' : 'Phone number already verified'
        ]);
        exit;
    }

    if (strtotime($otpRecord['expires_at']) < time()) {
        echo json_encode([
            'success' => false,
            'message' => 'OTP has expired'
        ]);
        exit;
    }

    if ($otpRecord['otp_code'] !== $otp_code) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid OTP code'
        ]);
        exit;
    }

    // Mark OTP as verified
    $stmtUpdate = $conn->prepare("UPDATE phone_verifications 
                                  SET verified = 1, verified_at = NOW() 
                                  WHERE id = :id");
    $stmtUpdate->bindParam(':id', $otpRecord['id']);
    $stmtUpdate->execute();
}
    // Response message based on type
$verifiedField = $type === 'email' ? 'email' : 'phone_number';
$successMessage = $type === 'email' ? 'Email verified successfully' : 'Phone number verified successfully';

echo json_encode([
    'success' => true,
    'message' => $successMessage,
    'data' => [
        $verifiedField => $otpRecord[$verifiedField] ?? null
    ]
]);


} catch (PDOException $e) {
    error_log("Verify OTP error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Verification failed. Please try again.'
    ]);
}
?>
