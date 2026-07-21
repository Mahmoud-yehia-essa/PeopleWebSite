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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['group_id']) || empty($input['user_id'])) {
        throw new Exception("Missing required fields: group_id and user_id");
    }

    $group_id = (int)$input['group_id'];
    $user_id  = (int)$input['user_id'];

    // Check if group exists
    $stmt = $conn->prepare("SELECT created_by_user_id FROM groups WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        throw new Exception("Group not found");
    }

    $creator_id = (int)$group['created_by_user_id'];

    // Check if user is an active member
    $stmt = $conn->prepare("SELECT role_id, is_active 
                              FROM group_member 
                             WHERE group_id = :group_id 
                               AND user_id = :user_id");
    $stmt->execute([':group_id' => $group_id, ':user_id' => $user_id]);
    $membership = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$membership || (int)$membership['is_active'] === 0) {
        throw new Exception("You are not an active member of this group");
    }

    $role_id = (int)$membership['role_id'];

    // If user is the creator, check for other admins
    if ($user_id === $creator_id) {
        $stmt = $conn->prepare("SELECT COUNT(*) 
                                  FROM group_member 
                                 WHERE group_id = :group_id 
                                   AND user_id != :user_id 
                                   AND role_id = 1 
                                   AND is_active = 1");
        $stmt->execute([':group_id' => $group_id, ':user_id' => $user_id]);
        $otherAdmins = $stmt->fetchColumn();

        if ($otherAdmins == 0) {
            throw new Exception("You must assign another owner before leaving the group");
        }
    }

    // Deactivate membership instead of deleting
    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE group_member 
                               SET is_active = 0 ,
                                left_at = NOW()
                             WHERE group_id = :group_id 
                               AND user_id = :user_id");
    $stmt->execute([':group_id' => $group_id, ':user_id' => $user_id]);

    // Update member count (count only active members)
    $stmt = $conn->prepare("UPDATE groups 
                               SET member_count = (
                                   SELECT COUNT(*) FROM group_member 
                                    WHERE group_id = :group_id AND is_active = 1
                               )
                             WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "You left the group successfully"
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
