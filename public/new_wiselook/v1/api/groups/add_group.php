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
    // استلام POST + FILES
    $input = $_POST;
    $files = $_FILES;
    
    // التحقق من الحقول الأساسية
    if (empty($input['name']) || empty($input['created_by_user_id'])) {
        throw new Exception("Missing required fields: name and created_by_user_id");
    }

    $name = $db->sanitize(trim($input['name']));
    $descriptions = isset($input['descriptions']) ? $db->sanitize(trim($input['descriptions'])) : null;
    $created_by_user_id = (int)$input['created_by_user_id'];

    // members جاي كـ JSON string
    $members = [];
    if (!empty($input['members'])) {
        $decoded = json_decode($input['members'], true);
        if (is_array($decoded)) {
            $members = array_map('intval', $decoded);
        }
    }

    // التحقق من وجود الكرياتور في قاعدة البيانات
    $stmt = $conn->prepare("SELECT users.id FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $created_by_user_id]);
    if (!$stmt->fetch()) {
        throw new Exception("Creator user not found");
    }

    // التحقق من وجود الأعضاء
    if (!empty($members)) {
        $placeholders = implode(',', array_fill(0, count($members), '?'));
        $stmt = $conn->prepare("SELECT id FROM users WHERE id IN ($placeholders)");
        $stmt->execute($members);
        $existingMembers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $invalidMembers = array_diff($members, $existingMembers);
        if (!empty($invalidMembers)) {
            throw new Exception("Invalid member IDs: " . implode(', ', $invalidMembers));
        }
    }

    // رفع صورة الجروب - FIXED: Check for $_FILES['image'] properly
    $image = null;
    if (isset($_FILES['image']) && !empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Use the upload function with proper file structure
        $uploadResult = uploadToLocalServer($_FILES['image'], [
            'user_id' => $created_by_user_id,
            'purpose' => 'group_image'
        ]);

        if ($uploadResult['success'] && !empty($uploadResult['media_name'])) {
            $image = $uploadResult['media_name'][0] ?? null;
        } else {
            // Log the upload error but don't fail the group creation
            error_log("Group image upload failed: " . ($uploadResult['message'] ?? 'Unknown error'));
            // Optionally, you can uncomment the next line if you want to fail group creation on image upload failure
            // throw new Exception("Failed to upload group image: " . ($uploadResult['message'] ?? 'Unknown error'));
        }
    }

    // ابدأ transaction
    $conn->beginTransaction();

    // إدخال الجروب
    $stmt = $conn->prepare("
        INSERT INTO groups (name, image, descriptions, created_by_user_id, date_created, member_count) 
        VALUES (:name, :image, :descriptions, :created_by_user_id, NOW(), :member_count)
    ");
    $memberCount = count($members) + 1; // creator + members
    $stmt->execute([
        ':name' => $name,
        ':image' => $image,
        ':descriptions' => $descriptions,
        ':created_by_user_id' => $created_by_user_id,
        ':member_count' => $memberCount
    ]);

    $group_id = $conn->lastInsertId();
    if (!$group_id) {
        throw new Exception("Failed to create group");
    }

    // أضف الكرياتور كأدمن
    $stmt = $conn->prepare("
        INSERT INTO group_member (group_id, user_id, role_id, joined_at, added_by_user_id)
        VALUES (:group_id, :user_id, 1, NOW(), :added_by_user_id)
    ");
    $stmt->execute([
        ':group_id' => $group_id,
        ':user_id' => $created_by_user_id,
        ':added_by_user_id' => $created_by_user_id
    ]);

    // أضف باقي الأعضاء
    if (!empty($members)) {
        $stmt = $conn->prepare("
            INSERT INTO group_member (group_id, user_id, role_id, joined_at, added_by_user_id)
            VALUES (:group_id, :user_id, 0, NOW(), :added_by_user_id)
        ");

        foreach ($members as $member_id) {
            if ($member_id === $created_by_user_id) continue; // skip creator
            $stmt->execute([
                ':group_id' => $group_id,
                ':user_id' => $member_id,
                ':added_by_user_id' => $created_by_user_id
            ]);
        }
    }

    // commit transaction فقط إذا كل شيء تمام
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => "Group created successfully",
        'group_id' => $group_id,
        'image' => $image,
        'member_count' => $memberCount
    ]);

} catch (Exception $e) {
    // rollback إذا حدث خطأ
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Group creation error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>