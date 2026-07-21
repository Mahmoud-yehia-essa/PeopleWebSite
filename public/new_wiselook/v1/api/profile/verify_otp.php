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
require './vendor/vendor/autoload.php';

// use HTTP_Request2;
// $request = new HTTP_Request2();
$input = json_decode(file_get_contents('php://input'), true);

if (!isset( $input['otp_code'], $input['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = $input['user_id'];
$otp_code = $input['otp_code'];
// $type = $input['type'];
$type = isset($input['type']) ? $input['type'] : '';


$db = new Database();
$conn = $db->getConnection();

try {
    if ($type === 'email') {
        // ✅ Verify email-based OTP
        $stmtOtp = $conn->prepare("SELECT id, used, expires_at FROM otp_codes WHERE user_id = :user_id AND code = :code LIMIT 1");
        $stmtOtp->bindParam(':user_id', $user_id);
        $stmtOtp->bindParam(':code', $otp_code);
        $stmtOtp->execute();
        $otp = $stmtOtp->fetch(PDO::FETCH_ASSOC);

        if (!$otp) {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP code']);
            exit;
        }

        if ($otp['used']) {
            echo json_encode(['success' => false, 'message' => 'OTP already used']);
            exit;
        }

        if (strtotime($otp['expires_at']) < time()) {
            echo json_encode(['success' => false, 'message' => 'OTP expired']);
            exit;
        }

        $stmtVerify = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = :user_id");
        $stmtVerify->bindParam(':user_id', $user_id);
        $stmtVerify->execute();

        $stmtUpdateOtp = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE id = :id");
        $stmtUpdateOtp->bindParam(':id', $otp['id']);
        $stmtUpdateOtp->execute();

        echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
        exit;
    }

    // if ($type === 'phone') {
    //     // ✅ Verify with MessageCentral
    //     $countryCode = $input['country_code'] ?? '971';
    //     $mobileNumber = $input['mobile_number'] ?? '';
    //     $verificationId = $input['verification_id'] ?? '';
    //     $authToken = "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiJDLUNDREJCOUQ1NDg2RTQyNiIsImlhdCI6MTc2Mjg0MjQwOSwiZXhwIjoxOTIwNTIyNDA5fQ.Adsm9BkqqXC9tJpioMEvTlZQVEE8f_cZE4304zW9uNDfzFSVVySTk8fXT9xYxSX86iVla2J00SqFalBDhBJ5mA";
    //     $customerId = "C-CCDBB9D5486E426";

     

    //     $url = "https://cpaas.messagecentral.com/verification/v3/validateOtp?countryCode=$countryCode&mobileNumber=$mobileNumber&verificationId=$verificationId&customerId=$customerId&code=$otp_code";
    //     $request->setUrl($url);
    //     $request->setMethod(HTTP_Request2::METHOD_GET);
    //     $request->setHeader(['authToken' => $authToken]);

    //     $response = $request->send();

    //     if ($response->getStatus() === 200) {
    //         $body = json_decode($response->getBody(), true);

    //         if (isset($body['responseCode']) && $body['responseCode'] === 200) {
    //             // ✅ Verified successfully
    //             $stmtVerify = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = :user_id");
    //             $stmtVerify->bindParam(':user_id', $user_id);
    //             $stmtVerify->execute();

    //             echo json_encode(['success' => true, 'message' => 'Phone verified successfully']);
    //             exit;
    //         } else {
    //             echo json_encode(['success' => false, 'message' => 'Invalid OTP or verificationId']);
    //             exit;
    //         }
    //     } else {
    //         echo json_encode(['success' => false, 'message' => 'Verification request failed', 'status' => $response->getStatus()]);
    //         exit;
    //     }
    // }


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

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
