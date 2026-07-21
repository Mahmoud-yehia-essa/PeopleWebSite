<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['id']) || empty(trim($input['id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing user'
    ]);
    exit;
}

$user_id = (int) $input['id'];
$offset = isset($input['offset']) ? (int) $input['offset'] : 0;
$limit = isset($input['limit']) ? (int) $input['limit'] : 20;

$db = new Database();
$conn = $db->getConnection();

// Updated query
$query = "
    SELECT 
        u.id, 
        u.first_name, 
        u.last_name, 
        u.profile_picture, 
        (
            SELECT CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM friendships 
                    WHERE sender_id = :user_id 
                    AND receiver_id = u.id 
                    AND is_active = 0
                ) THEN 'cancel' 
                WHEN EXISTS (
                    SELECT 1 
                    FROM friendships 
                    WHERE sender_id = u.id 
                    AND receiver_id = :user_id 
                    AND is_active = 0
                ) THEN 'confirm' 
                ELSE 'add' 
            END
        ) AS type 
    FROM users u 
    WHERE u.id != :user_id 
    AND NOT EXISTS (
        SELECT 1 
        FROM friendships f 
        WHERE is_active = 1 
        AND (
            (f.sender_id = :user_id AND f.receiver_id = u.id) 
            OR 
            (f.sender_id = u.id AND f.receiver_id = :user_id)
        )
    )
    ORDER BY u.id DESC
    LIMIT :offset, :limit
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$response = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'List success',
    'data' => $response
]);
exit;
