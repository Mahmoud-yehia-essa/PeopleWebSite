<?php
// Comprehensive CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token, Accept, Origin, Cache-Control, X-Requested-With");
header("Access-Control-Expose-Headers: Content-Length, X-JSON");
header("Content-Type: application/json; charset=utf-8");


ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once './vendor/vendor/autoload.php';

use Twilio\Rest\Client;

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// Debug information
error_log("=== SEND OTP ENDPOINT ACCESSED ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);

// Include database connection
include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../mailer/php_mailer.php';
$php_mailer = new php_mailer();
// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.',
        'debug' => [
            'method' => $_SERVER['REQUEST_METHOD']
        ]
    ]);
    exit();
}

// Get input data
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);
error_log("Raw input: " . $raw_input);

// Validate JSON
if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input'
    ]);
    exit();
}

// Get and clean inputs safely
$type = isset($input['type']) ? trim((string)$input['type']) : null;
// $code = isset($input['code']) ? trim((string)$input['code']) : null;
$code = isset($input['code']) ? ltrim(trim($input['code']), '+') : null;

$phone_number = isset($input['phone_number']) ? trim((string)$input['phone_number']) : null;
$email = isset($input['email']) ? trim((string)$input['email']) : null;

// التحقق إنو في شي واحد على الأقل
if (empty($phone_number) && empty($email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Phone number or email is required',
        'received_data' => $input
    ]);
    exit();
}

// تحقق حسب النوع
if ($type === 'phone' && empty($phone_number)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Phone number is required for phone verification']);
    exit();
}

if ($type === 'email' && empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required for email verification']);
    exit();
}

// Initialize database
$db = new Database();
$conn = $db->getConnection();

// Sanitize inputs safely (avoid null trim warning)
$phone_number = $phone_number ? $db->sanitize($phone_number) : null;
$email = $email ? $db->sanitize($email) : null;

error_log("Processing OTP request for phone: $phone_number, email: $email");

try {
    // لو التحقق عبر الإيميل فقط


if ($type === 'email') {
    // تحقق من صحة الإيميل
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }

    // تحقق من عدم استخدام الإيميل مسبقًا
    $stmtEmail = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmtEmail->bindParam(':email', $email);
    $stmtEmail->execute();

    if ($stmtEmail->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
        exit();
    }

    // إنشاء OTP
    $otp_code = rand(100000, 999999);
    $verification_id = uniqid('email_', true);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // حفظ OTP في قاعدة البيانات
    $stmtOtp = $conn->prepare("
        INSERT INTO phone_verifications 
        (verification_id, phone_number, otp_code, expires_at, created_at) 
        VALUES (:verification_id, :email, :otp_code, :expires_at, NOW())
    ");
    $stmtOtp->bindParam(':verification_id', $verification_id);
    $stmtOtp->bindParam(':email', $email);
    $stmtOtp->bindParam(':otp_code', $otp_code);
    $stmtOtp->bindParam(':expires_at', $expires_at);
    $stmtOtp->execute();

    // **أرسل الرد فورًا لتجنب Timeout**
    echo json_encode([
        'success' => true,
        'message' => 'OTP generated successfully',
        'data' => ['verification_id' => $verification_id, 'email' => $email]
    ]);

    // إرسال الإيميل في الخلفية
    ignore_user_abort(true);
    set_time_limit(0); // تعطيل timeout للسكريبت
    $php_mailer->send_otpcode($email, $otp_code);

    exit();
}




    // تحقق من رقم الهاتف وصيغته
//     if ($type === 'phone') {
//         if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone_number)) {
//             echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
//             exit();
//         }

//         // تحقق إنو الرقم مش مستخدم
//         $stmtPhone = $conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
//         $stmtPhone->bindParam(':phone_number', $phone_number);
//         $stmtPhone->execute();

//         if ($stmtPhone->fetch(PDO::FETCH_ASSOC)) {
//             echo json_encode(['success' => false, 'message' => 'Phone number is already in use.']);
//             exit();
//         }

//         // إنشاء OTP للأرقام (حروف)
//         function generateLetterOTP($length = 6) {
//             $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
//             $otp = '';
//             for ($i = 0; $i < $length; $i++) {
//                 $otp .= $letters[rand(0, strlen($letters) - 1)];
//             }
//             return $otp;
//         }

//         $otp_code = generateLetterOTP(6);
//         $verification_id = uniqid('phone_', true);
//         $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

//         $stmtOtp = $conn->prepare("INSERT INTO phone_verifications 
//             (verification_id, phone_number, otp_code, expires_at, created_at) 
//             VALUES (:verification_id, :phone_number, :otp_code, :expires_at, NOW())");

//         $stmtOtp->bindParam(':verification_id', $verification_id);
//         $stmtOtp->bindParam(':phone_number', $phone_number);
//         $stmtOtp->bindParam(':otp_code', $otp_code);
//         $stmtOtp->bindParam(':expires_at', $expires_at);
//         $stmtOtp->execute();

//       $sid = "ACd57e784f4fb98b6252f5ade0474f930f";
//         $token = "63f573a437194ce53f63f473e447e89c";
//         $messagingServiceSid = "MGf6390c26c1807075c7115cdb6392ff3a"; 
//         $twilio_from = '+12175668785';
//         $sms_message = "Your verification code is: $otp_code. Valid for 10 minutes.";
//         $twilio = new Client($sid, $token);
     
        
//         // $ch = curl_init();
//         // curl_setopt_array($ch, [
//         //     CURLOPT_URL => "https://api.twilio.com/2010-04-01/Accounts/$twilio_sid/Messages.json", // Account SID هنا
//         //     CURLOPT_RETURNTRANSFER => true,
//         //     CURLOPT_POST => true,
//         //     CURLOPT_USERPWD => "$twilio_sid:$twilio_token",
//         //     CURLOPT_POSTFIELDS => http_build_query([
//         //         'MessagingServiceSid' => $messagingServiceSid,
//         //         'To' => $phone_number,
//         //         'Body' => $sms_message
//         //     ]),
//         //     CURLOPT_TIMEOUT => 30,
//         //     CURLOPT_SSL_VERIFYPEER => true
//         // ]);
//          $message = $twilio->messages->create(
//          $phone_number,
//         [
//             "messagingServiceSid" => $messagingServiceSid,
//             "body" =>  $sms_message,
//         ]
//     );
   
//   echo json_encode([
//             'success' => true,
//             'message' => 'OTP sent successfully via SMS',
//             'data' => [
//                 'verification_id' => $verification_id,
//                 'phone_number' => $phone_number
//             ]
//         ], JSON_UNESCAPED_UNICODE);
//         // $sms_response = curl_exec($ch);
//         // $curl_error = curl_error($ch);
//         // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//         // curl_close($ch);
        
//         // if ($sms_response === false) {
//         //     throw new Exception("cURL error: " . $curl_error);
//         // }
        
//         // $response_data = json_decode($sms_response, true);
//         // if ($http_code >= 400 || (isset($response_data['code']) && $response_data['code'] >= 400)) {
//         //     throw new Exception("SMS API error: " . $sms_response);
//         // }


//         // echo json_encode([
//         //     'success' => true,
//         //     'message' => 'OTP sent successfully via SMS',
//         //     'data' => [
//         //         'verification_id' => $verification_id,
//         //         'phone_number' => $phone_number
//         //     ]
//         // ]);
//     }



if ($type === 'phone') {
    if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone_number)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number format']);
        exit();
    }
    $full_number = "+".$code."".$phone_number;

    // Check if phone already exists
    $stmtPhone = $conn->prepare("SELECT id FROM users WHERE phone_number = :phone_number");
    $stmtPhone->bindParam(':phone_number', $full_number);
    $stmtPhone->execute();
    if ($stmtPhone->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode(['success' => false, 'message' => 'Phone number already in use']);
        exit();
    }

    // Combine code + number (if separate)



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
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP', 'error' => $curl_error ?: $response]);
        exit();
    }

    $apiResponse = json_decode($response, true);

    // Save verification to database
    $verification_id = $apiResponse['verificationId'] ?? uniqid('msg_', true);
    $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));



    // $stmtOtp = $conn->prepare("
    //     INSERT INTO phone_verifications ( verification_id, otp_code, expires_at, used)
    //     VALUES (NULL, :verification_id, NULL, :expires_at, 0)
    // ");
    // $stmtOtp->bindParam(':verification_id', $verification_id);
    // $stmtOtp->bindParam(':expires_at', $expires_at);
    // $stmtOtp->execute();




$otp_code = "111111"; // or generated OTP
$stmtOtp = $conn->prepare("
    INSERT INTO phone_verifications 
    (verification_id, phone_number, otp_code, expires_at, created_at) 
    VALUES (:verification_id, :phone_number, :otp_code, :expires_at, NOW())
");
$stmtOtp->bindParam(':verification_id', $verification_id);
$stmtOtp->bindParam(':phone_number', $phone_number);
$stmtOtp->bindParam(':otp_code', $otp_code);
$stmtOtp->bindParam(':expires_at', $expires_at);
$stmtOtp->execute();




    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully via MessageCentral',
        'url' => $url,
        'data' => [
            'phone_number' => $phone_number,
            'verification_id' => $verification_id,
            'messagecentral_response' => $apiResponse
        ]
    ], JSON_UNESCAPED_UNICODE);
}





} catch (PDOException $e) {
    error_log("Database error in send_otp: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error. Please try again.', 'error' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error in send_otp: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.', 'error' => $e->getMessage()]);
}

error_log("=== SEND OTP PROCESS COMPLETED ===");
?>
