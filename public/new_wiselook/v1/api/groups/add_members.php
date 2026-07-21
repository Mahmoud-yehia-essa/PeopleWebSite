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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['group_id']) || empty($input['member_ids']) || !is_array($input['member_ids'])) {
        throw new Exception("Missing required fields: group_id and member_ids array");
    }

    $group_id = (int)$input['group_id'];
    $member_ids = array_map('intval', $input['member_ids']);
    $added_by_user_id = isset($input['added_by_user_id']) ? (int)$input['added_by_user_id'] : null;

    // Verify group exists
    $stmt = $conn->prepare("SELECT id FROM groups WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Group not found");
    }

    // Verify members exist
    if (!empty($member_ids)) {
        $member_ids_str = implode(',', $member_ids);

        // Check if users exist
        $stmt = $conn->prepare("SELECT id FROM users WHERE id IN ($member_ids_str)");
        $stmt->execute();
        $existingMembers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $invalidMembers = array_diff($member_ids, $existingMembers);
        if (!empty($invalidMembers)) {
            throw new Exception("Invalid member IDs: " . implode(', ', $invalidMembers));
        }
    }

    // Start transaction
    $conn->beginTransaction();

    // Prepare statements
    $insertStmt = $conn->prepare("
        INSERT INTO group_member (group_id, user_id, role_id, joined_at, added_by_user_id)
        VALUES (:group_id, :user_id, 0, NOW(), :added_by_user_id)
    ");

    $updateStmt = $conn->prepare("
        UPDATE group_member
        SET is_active = 1, joined_at = NOW(), left_at = NULL, added_by_user_id = :added_by_user_id , role_id = 3
        WHERE group_id = :group_id AND user_id = :user_id
    ");

    $addedCount = 0;

    foreach ($member_ids as $member_id) {
        // Check if user exists in group_member
        $stmt = $conn->prepare("SELECT is_active FROM group_member WHERE group_id = :group_id AND user_id = :user_id");
        $stmt->execute([
            ':group_id' => $group_id,
            ':user_id' => $member_id
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ((int)$existing['is_active'] === 0) {
                // Reactivate user
                $updateStmt->execute([
                    ':group_id' => $group_id,
                    ':user_id' => $member_id,
                    ':added_by_user_id' => $added_by_user_id
                ]);
                $addedCount++;
            }
            // Already active, do nothing
        } else {
            // Insert new member
            $insertStmt->execute([
                ':group_id' => $group_id,
                ':user_id' => $member_id,
                ':added_by_user_id' => $added_by_user_id
            ]);
            $addedCount++;
        }
    }

    // Update member count in groups table
    if ($addedCount > 0) {
        $stmt = $conn->prepare("
            UPDATE groups 
            SET member_count = member_count + :added_count 
            WHERE id = :group_id
        ");
        $stmt->execute([
            ':added_count' => $addedCount,
            ':group_id' => $group_id
        ]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Members added successfully",
        'added_count' => $addedCount
    ]);

} catch (Exception $e) {
    // Rollback if error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Add members error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
