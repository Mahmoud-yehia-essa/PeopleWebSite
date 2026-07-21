<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents("php://input"), true);

$lang = isset($input['lang']) && $input['lang'] === 'en' ? 'en' : 'ar';

if (!isset($input['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 'user_id مطلوب' : 'user_id is required'
    ]);
    exit;
}

$user_id = intval($input['user_id']);

$db = new Database();
$conn = $db->getConnection();

$sql = "UPDATE users SET status = 1 WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $user_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => $lang === 'ar'
            ? 'تم جدولة حذف الحساب بعد 30 يوماً'
            : 'Account deletion scheduled in 30 days'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar'
            ? 'فشل في جدولة الحذف'
            : 'Failed to schedule deletion'
    ]);
}
