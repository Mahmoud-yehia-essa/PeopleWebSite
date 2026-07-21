<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../db.php";
require_once "../config.php";

$db = new Database();
$conn = $db->getConnection();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get input (works with JSON or POST)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$group_id = isset($input['group_id']) ? (int)$input['group_id'] : null;

if (!$group_id) {
    echo json_encode(['success' => false, 'message' => 'group_id is required']);
    exit;
}

try {
    // Fetch group info and members with joined_at and left_at
    $stmt = $conn->prepare("
        SELECT 
            g.id AS group_id, 
            g.name AS group_name,
            CONCAT(:uploadsPath, IFNULL(g.image, 'default.jpeg')) AS group_image,
            g.descriptions, 
            g.created_by_user_id, 
            g.date_created,
            gm.user_id, 
            gm.role_id,
            gm.is_active,
            gm.joined_at,
            gm.left_at, -- 👈 Add left_at field
            u.first_name, 
            u.last_name,
            CONCAT(:uploadsPath, IFNULL(u.profile_picture, 'default.jpeg')) AS profile_picture
        FROM groups g
        JOIN group_member gm ON g.id = gm.group_id
        JOIN users u ON gm.user_id = u.id
        WHERE g.id = :group_id
        ORDER BY gm.role_id DESC, u.first_name ASC
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':uploadsPath' => $uploadsPath
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo json_encode(['success' => false, 'message' => 'Group not found']);
        exit;
    }

    // Prepare response
    $groupData = [
        'id' => $rows[0]['group_id'],
        'name' => $rows[0]['group_name'],
        'image' => $rows[0]['group_image'],
        'descriptions' => $rows[0]['descriptions'],
        'created_by_user_id' => $rows[0]['created_by_user_id'],
        'date_created' => $rows[0]['date_created'],
        'members' => []
    ];

    foreach ($rows as $row) {
        $groupData['members'][] = [
            'id' => $row['user_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'role_id' => $row['role_id'],
            'is_active' => (int)$row['is_active'],
            'joined_at' => $row['joined_at'],
            'left_at' => $row['left_at'], // 👈 Add left_at to response
            'profile_picture' => $row['profile_picture']
        ];
    }

    echo json_encode([
        'success' => true,
        'group' => $groupData
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>