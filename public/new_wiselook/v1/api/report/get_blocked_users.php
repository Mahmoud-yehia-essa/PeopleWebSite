<?php
// block/get_blocked_users.php

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
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

if (empty($input['blocker_id'])) {
    echo json_encode(['success' => false, 'message' => 'blocker_id is required']);
    exit;
}

$blocker_id = (int) $input['blocker_id'];

// تحديد اللغة، الافتراضي إنجليزي
$lang = isset($input['lang']) && $input['lang'] === 'ar' ? 'ar' : 'en';

$db = new Database();
$conn = $db->getConnection();

$sql = "
    SELECT u.id, u.first_name, u.last_name, u.profile_picture
    FROM block b
    JOIN users u ON u.id = b.blocked_id
    WHERE b.blocker_id = :blocker_id
";

$stmt = $conn->prepare($sql);
$stmt->execute([':blocker_id' => $blocker_id]);
$blocked_users_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// بناء رابط الصورة الكامل لكل مستخدم
$blocked_users = [];
foreach ($blocked_users_raw as $user) {
    $profile_picture = $user['profile_picture'];
    if ($profile_picture && $profile_picture !== '') {
        $full_link = rtrim($uploadsPath, '/') . '/' . ltrim($profile_picture, '/');
    } else {
        $full_link = null;
    }

    $blocked_users[] = [
        'id' => $user['id'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'profile_picture' => $full_link,
    ];
}

if (count($blocked_users) > 0) {
    echo json_encode([
        'success' => true,
        'data' => $blocked_users,
        'message' => $lang === 'ar' ? 'تم العثور على مستخدمين محجوبين' : 'Blocked users found'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'data' => [],
        'message' => $lang === 'ar' ? 'لا يوجد مستخدمين محجوبين' : 'No blocked users found'
    ]);
}
exit;
