<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../db.php";
require_once "../config.php";
require_once "../notifications/notification_class.php";

$db = new Database();
$conn = $db->getConnection();
$notification_class = new NotificationClass();

/* ================= PREFLIGHT ================= */
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
    /* ================= INPUT ================= */
    $input = json_decode(file_get_contents('php://input'), true);

    if (
        empty($input['group_id']) ||
        empty($input['member_ids']) ||
        !is_array($input['member_ids'])
    ) {
        throw new Exception("Missing required fields");
    }

    $group_id = (int)$input['group_id'];
    $member_ids = array_map('intval', $input['member_ids']);
    $added_by_user_id = !empty($input['added_by_user_id'])
        ? (int)$input['added_by_user_id']
        : null;

    /* ================= GROUP ================= */
    $stmt = $conn->prepare("SELECT id, name FROM groups WHERE id = :id");
    $stmt->execute([':id' => $group_id]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        throw new Exception("Group not found");
    }

    $group_name = $group['name'];

    /* ================= INVITER ================= */
    $added_by_name = "Someone";
    $inviter_token = null;

    if ($added_by_user_id) {
        $stmt = $conn->prepare("
            SELECT CONCAT(first_name,' ',last_name) AS full_name, token
            FROM users
            WHERE id = :id
        ");
        $stmt->execute([':id' => $added_by_user_id]);
        $inviter = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($inviter) {
            $added_by_name = $inviter['full_name'] ?: $added_by_name;
            $inviter_token = $inviter['token'] ?? null;
        }
    }

    /* ================= USERS ================= */
    $ids = implode(',', $member_ids);
    $stmt = $conn->prepare("
        SELECT id,
               CONCAT(first_name,' ',last_name) AS full_name,
               token
        FROM users
        WHERE id IN ($ids)
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) !== count($member_ids)) {
        throw new Exception("Some users do not exist");
    }

    $memberTokens = [];

    foreach ($users as $u) {
        $memberTokens[$u['id']] = $u['token'];
    }

    /* ================= TRANSACTION ================= */
    $conn->beginTransaction();

    $insert = $conn->prepare("
        INSERT INTO group_member (group_id, user_id, role_id, joined_at, added_by_user_id)
        VALUES (:group_id, :user_id, 3, NOW(), :added_by)
    ");

    $reactivate = $conn->prepare("
        UPDATE group_member
        SET is_active = 1,
            joined_at = NOW(),
            left_at = NULL,
            role_id = 3,
            added_by_user_id = :added_by
        WHERE group_id = :group_id AND user_id = :user_id
    ");

    $check = $conn->prepare("
        SELECT is_active FROM group_member
        WHERE group_id = :group_id AND user_id = :user_id
    ");

    $addedUsers = [];
    $addedCount = 0;

    foreach ($member_ids as $uid) {
        $check->execute([
            ':group_id' => $group_id,
            ':user_id'  => $uid
        ]);

        $row = $check->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if ((int)$row['is_active'] === 0) {
                $reactivate->execute([
                    ':group_id' => $group_id,
                    ':user_id'  => $uid,
                    ':added_by' => $added_by_user_id
                ]);
                $addedUsers[] = $uid;
                $addedCount++;
            }
        } else {
            $insert->execute([
                ':group_id' => $group_id,
                ':user_id'  => $uid,
                ':added_by' => $added_by_user_id
            ]);
            $addedUsers[] = $uid;
            $addedCount++;
        }
    }

    if ($addedCount > 0) {
        $stmt = $conn->prepare("
            UPDATE groups
            SET member_count = member_count + :cnt
            WHERE id = :id
        ");
        $stmt->execute([
            ':cnt' => $addedCount,
            ':id'  => $group_id
        ]);
    }

    $conn->commit();

    /* ================= NOTIFICATIONS ================= */

    // Notify ADDED USERS
    foreach ($addedUsers as $uid) {
        if (!empty($memberTokens[$uid])) {
            $msg = "$added_by_name added you to '$group_name'";

            $notification_class->sendStaticNotification(
                $msg,
                $memberTokens[$uid],
                'group',
                $group_id
            );
        }
    }

    // Notify INVITER (FIXED MESSAGE)
    if ($inviter_token && $addedCount > 0) {
        $msg = "You successfully added $addedCount member" .
               ($addedCount > 1 ? "s" : "") .
               " to '$group_name'";

        $notification_class->sendGroupNotification(
            $msg,
            $inviter_token,
            'group',
            $group_id , 'group'
        );
    }

    echo json_encode([
        'success' => true,
        'added_count' => $addedCount
    ]);

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log("Add members error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
