<?php
// post/get_voters.php
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

$poll_option_id = isset($input['poll_option_id']) ? (int)$input['poll_option_id'] : null;
$user_id = isset($input['user_id']) ? (int)$input['user_id'] : null;

if (!$poll_option_id) {
    echo json_encode(['success' => false, 'message' => 'Poll option ID is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// جلب المصوتين لهذا الخيار
$sql = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        pr.created_at as voted_at
    FROM poll_responses pr
    JOIN users u ON u.id = pr.user_id
    WHERE pr.poll_option_id = :poll_option_id
    AND u.is_active = 1
";

// منع عرض المستخدمين المحظورين
if ($user_id !== null) {
    $sql .= " AND u.id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id) ";
    $sql .= " AND u.id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id) ";
}

$sql .= " ORDER BY pr.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':poll_option_id', $poll_option_id, PDO::PARAM_INT);

if ($user_id !== null) {
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
}

$stmt->execute();
$voters = $stmt->fetchAll(PDO::FETCH_ASSOC);

// تنسيق البيانات
$formatted_voters = [];
foreach ($voters as $voter) {
    $formatted_voters[] = [
        'id' => $voter['id'],
        'first_name' => $voter['first_name'],
        'last_name' => $voter['last_name'],
        'full_name' => $voter['first_name'] . ' ' . $voter['last_name'],
        'profile_picture' => $voter['profile_picture'] ? $uploadsPath . $voter['profile_picture'] : null,
        'voted_at' => $voter['voted_at'],
        'time_ago' => $db->timeAgo($voter['voted_at'], $input['lang'] ?? 'ar')
    ];
}

echo json_encode([
    'success' => true,
    'data' => $formatted_voters,
    'total_count' => count($formatted_voters)
]);
exit;
?>