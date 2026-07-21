<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../db.php";
require_once "../config.php";
require_once __DIR__ . '/../../../upload_function.php';

$db = new Database();
$conn = $db->getConnection();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $input = $_POST;

    // Required fields
    if (empty($input['group_id'])) {
        throw new Exception("Missing required fields: group_id");
    }

    if (empty($input['updated_by'])) {
        throw new Exception("Missing required fields: updated_by");
    }

    $group_id = (int)$input['group_id'];
    $updated_by_user_id = (int)$input['updated_by'];

    $name = isset($input['name']) ? $db->sanitize(trim($input['name'])) : null;
    $descriptions = isset($input['descriptions']) ? $db->sanitize(trim($input['descriptions'])) : null;

    // Check group exists
    $stmt = $conn->prepare("SELECT * FROM groups WHERE id = :group_id");
    $stmt->execute([':group_id' => $group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$group) {
        throw new Exception("Group not found");
    }

    // Check if user has permission to edit (is owner or admin)
    $stmt = $conn->prepare("
        SELECT role_id FROM group_member 
        WHERE group_id = :group_id AND user_id = :user_id AND is_active = 1
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':user_id' => $updated_by_user_id
    ]);
    $member = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$member) {
        throw new Exception("You are not a member of this group");
    }
    
    // Only owners (role_id=1) and admins (role_id=2) can edit group info
    if ($member['role_id'] > 2) {
        throw new Exception("You don't have permission to edit this group");
    }

    // Handle image upload if exists
    $image = $group['image']; // keep existing if no new upload
    if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = uploadToLocalServer($_FILES['image'], [
            'user_id' => $updated_by_user_id,
            'purpose' => 'group_image'
        ]);

        if ($uploadResult['success'] && !empty($uploadResult['media_name'])) {
            if (is_array($uploadResult['media_name'])) {
                $image = $uploadResult['media_name'][0];
            } else {
                $image = $uploadResult['media_name'];
            }
            
            // Delete old image if it exists
            if (!empty($group['image']) && file_exists(__DIR__ . '/../../../uploads/' . $group['image'])) {
                @unlink(__DIR__ . '/../../../uploads/' . $group['image']);
            }
        } else {
            error_log("Group image upload failed: " . ($uploadResult['message'] ?? 'Unknown error'));
        }
    }

    // Build update query dynamically - WITHOUT updated_at
    $updateFields = [];
    $params = [':group_id' => $group_id];
    
    if ($name !== null && $name !== $group['name']) {
        $updateFields[] = "name = :name";
        $params[':name'] = $name;
    }
    
    if ($descriptions !== null && $descriptions !== $group['descriptions']) {
        $updateFields[] = "descriptions = :descriptions";
        $params[':descriptions'] = $descriptions;
    }
    
    if ($image !== $group['image']) {
        $updateFields[] = "image = :image";
        $params[':image'] = $image;
    }
    
    // Remove updated_at line completely
    
    if (empty($updateFields)) {
        echo json_encode([
            'success' => true,
            'message' => "No changes detected",
            'group_id' => $group_id,
            'image' => $image,
            'name' => $group['name'],
            'descriptions' => $group['descriptions']
        ]);
        exit;
    }

    // Update group
    $sql = "UPDATE groups SET " . implode(", ", $updateFields) . " WHERE id = :group_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo json_encode([
        'success' => true,
        'message' => "Group updated successfully",
        'group_id' => $group_id,
        'image' => $image,
        'name' => $name ?? $group['name'],
        'descriptions' => $descriptions ?? $group['descriptions']
    ]);

} catch (Exception $e) {
    error_log("Group update error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>