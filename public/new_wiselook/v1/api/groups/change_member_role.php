<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
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

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['group_id']) || empty($input['member_id']) || !isset($input['new_role'])) {
        throw new Exception("Missing required fields: group_id, member_id, and new_role");
    }

    $group_id = (int)$input['group_id'];
    $member_id = (int)$input['member_id'];
    $new_role = (int)$input['new_role'];



    // Verify group exists
    $stmt = $conn->prepare("SELECT id FROM groups WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Group not found");
    }

    // Verify member exists in group
    $stmt = $conn->prepare("
        SELECT id, role_id FROM group_member 
        WHERE group_id = :group_id AND user_id = :member_id
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':member_id' => $member_id
    ]);
    
    $memberData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$memberData) {
        throw new Exception("Member not found in this group");
    }

    // Check if trying to change creator's role (prevent this)
    $stmt = $conn->prepare("
        SELECT created_by_user_id FROM groups WHERE id = :group_id
    ");
    $stmt->execute([':group_id' => $group_id]);
    $creator_id = $stmt->fetchColumn();
    
    if ($member_id === $creator_id) {
        throw new Exception("Cannot change role of group creator");
    }

    // Check if role is already the same
    if ($memberData['role_id'] === $new_role) {
        throw new Exception("Member already has this role");
    }

    // Update member role
    $stmt = $conn->prepare("
        UPDATE group_member 
        SET role_id = :new_role 
        WHERE group_id = :group_id AND user_id = :member_id
    ");
    $stmt->execute([
        ':new_role' => $new_role,
        ':group_id' => $group_id,
        ':member_id' => $member_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => "Member role updated successfully",
        'new_role' => $new_role
    ]);

} catch (Exception $e) {
    error_log("Change member role error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>