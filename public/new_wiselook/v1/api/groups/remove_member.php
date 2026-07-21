<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['group_id']) || empty($input['member_id'])) {
        throw new Exception("Missing required fields: group_id and member_id");
    }

    $group_id = (int)$input['group_id'];
    $member_id = (int)$input['member_id'];

    // Verify group exists
    $stmt = $conn->prepare("SELECT id FROM groups WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Group not found");
    }

    // Verify member exists in group
    $stmt = $conn->prepare("
        SELECT id FROM group_member 
        WHERE group_id = :group_id AND user_id = :member_id
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':member_id' => $member_id
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception("Member not found in this group");
    }

    // Check if member is the creator (prevent removing creator)
    $stmt = $conn->prepare("
        SELECT created_by_user_id FROM groups WHERE id = :group_id
    ");
    $stmt->execute([':group_id' => $group_id]);
    $creator_id = $stmt->fetchColumn();
    
    if ($member_id === $creator_id) {
        throw new Exception("Cannot remove group creator");
    }

    // Start transaction
    $conn->beginTransaction();

    // Remove member from group
    $stmt = $conn->prepare("
        DELETE FROM group_member 
        WHERE group_id = :group_id AND user_id = :member_id
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':member_id' => $member_id
    ]);

    // Update member count in groups table
    $stmt = $conn->prepare("
        UPDATE groups 
        SET member_count = member_count - 1 
        WHERE id = :group_id
    ");
    $stmt->execute([':group_id' => $group_id]);

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Member removed successfully"
    ]);

} catch (Exception $e) {
    // Rollback if error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Remove member error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>