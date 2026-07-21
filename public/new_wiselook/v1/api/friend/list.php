<?php
// ini_set('display_errors', 1);
error_reporting(E_ALL);

// CORS Headers - Full Configuration
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
header('Access-Control-Max-Age: 86400');  // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 204 No Content');
    exit;
}

// Content Type
header('Content-Type: application/json; charset=utf-8');

// Security Headers (Recommended)
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');  // Enable if using HTTPS

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

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
$person_id = isset($input['person_id']) && !empty(trim($input['person_id'])) ? (int) $input['person_id'] : null;
$is_active = (isset($input['is_active']) && (int)$input['is_active'] === 1) ? 1 : 0;
$offset = isset($input['offset']) ? (int) $input['offset'] : 0;
$limit = isset($input['limit']) ? (int) $input['limit'] : 20;
// $filter_type = isset($input['filter_type']) ?? '';
$filter_type = $input['filter_type'] ?? '';
$db = new Database();
$conn = $db->getConnection();

if ($is_active === 0) {
    
if ($filter_type === 'requests') {
        $query = "
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        'confirm' AS type
    FROM users u
    INNER JOIN friendships f 
        ON f.sender_id = u.id
    WHERE 
        f.receiver_id = :user_id
        AND f.is_active = 0
        AND u.id NOT IN (
            SELECT blocked_id FROM block WHERE blocker_id = :user_id
        )
        AND u.id NOT IN (
            SELECT blocker_id FROM block WHERE blocked_id = :user_id
        )
    ORDER BY u.id DESC
    LIMIT :offset, :limit
";
    }
    else{
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
        AND u.id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id)
        AND u.id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id)
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
    }
} else {
    if ($person_id) {
        // Fetch person's friends and exclude blocked users, show relationship with main user_id
        $query = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.profile_picture,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM friendships 
                        WHERE is_active = 1 
                        AND (
                            (sender_id = :user_id AND receiver_id = u.id) 
                            OR 
                            (sender_id = u.id AND receiver_id = :user_id)
                        )
                    ) THEN 'friend'
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
                END AS type
            FROM users u
            INNER JOIN friendships f ON (
                (f.sender_id = :person_id AND f.receiver_id = u.id) OR 
                (f.receiver_id = :person_id AND f.sender_id = u.id)
            )
            WHERE f.is_active = 1
            AND u.id != :user_id
            AND u.id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id)
            AND u.id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id)
            ORDER BY u.id DESC
            LIMIT :offset, :limit
        ";
    } else {
        // Fetch user's own friends (is_active = 1), exclude blocked users
        $query = "
            SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.profile_picture,
                'friend' AS type
            FROM users u
            INNER JOIN friendships f ON (
                (f.sender_id = :user_id AND f.receiver_id = u.id) OR 
                (f.receiver_id = :user_id AND f.sender_id = u.id)
            )
            WHERE f.is_active = 1
            AND u.id NOT IN (SELECT blocked_id FROM block WHERE blocker_id = :user_id)
            AND u.id NOT IN (SELECT blocker_id FROM block WHERE blocked_id = :user_id)
            ORDER BY u.id DESC
            LIMIT :offset, :limit
        ";
    }
}

$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
if ($person_id) {
    $stmt->bindParam(':person_id', $person_id, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add uploads path to profile pictures
foreach ($users as &$user) {
    if (!empty($user['profile_picture'])) {
        $user['profile_picture'] = $uploadsPath . $user['profile_picture'];
    } else {
        $user['profile_picture'] = $uploadsPath . 'default.jpeg';
    }
}

echo json_encode([
    'success' => true,
    'message' => 'List success',
    'data' => $users
]);
exit;
