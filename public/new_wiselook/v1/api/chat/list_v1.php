<?php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$db = new Database();
$conn = $db->getConnection();

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$user_id   = isset($input['user_id']) ? $db->sanitize($input['user_id']) : null;
$user_ids  = isset($input['user_ids']) ? $input['user_ids'] : [];
$group_ids = isset($input['group_ids']) ? $input['group_ids'] : [];
$limit     = isset($input['limit']) ? (int)$input['limit'] : null;
$offset    = isset($input['offset']) ? (int)$input['offset'] : 0;
$search    = isset($input['search']) ? trim($input['search']) : '';
$type      = isset($input['type']) ? trim($input['type']) : ''; // only_friends, users, groups, all
$without_in      = isset($input['without_in']) ? trim($input['without_in']) : ''; // only_friends, users, 
$member      = isset($input['member']) ? trim($input['member']) : ''; // only_friends, users, groups, all



// Validation
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit;
}

$user_ids  = array_filter($user_ids, fn($v) => $v !== 'null' && $v !== null);
$group_ids = array_filter($group_ids, fn($v) => $v !== 'null' && $v !== null);

try {
    $result = [];





  

    // ---------------------------
    // NEW: Fetch ALL Users (for search functionality)
    // ---------------------------
    if ($type === "all_users") {
        $params = [];

        $sql = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.token,
                    CONCAT(?, IFNULL(u.profile_picture, 'default.jpeg')) AS profile_picture,
                    'user' AS type
                FROM users u
                WHERE u.id != ?";

        // uploadsPath parameter
        $params[] = rtrim($uploadsPath, '/') . '/';
        
        // Exclude current user
        $params[] = $user_id;

        // Search by name
        if ($search !== '') {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Order by name
        $sql .= " ORDER BY u.first_name ASC, u.last_name ASC";

        // Pagination
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array_merge($result, $users);
    }

    // ---------------------------
    // Fetch Friends ONLY if type = "only_friends"
    // ---------------------------
    if ($type === "only_friends") {
        $params = [];

        $sql = "SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.token,
                    CONCAT(?, IFNULL(u.profile_picture, 'default.jpeg')) AS profile_picture,
                    'user' AS type
                FROM users u
                INNER JOIN friendships f 
                    ON (f.sender_id = ? OR f.receiver_id = ?)
                   AND f.is_active = 1
                   AND (u.id = f.sender_id OR u.id = f.receiver_id)
                WHERE u.id != ?";

        // uploadsPath parameter first
        $params[] = rtrim($uploadsPath, '/') . '/';

        // friendship bindings
        $params[] = $user_id; // f.sender_id = ?
        $params[] = $user_id; // f.receiver_id = ?
        $params[] = $user_id; // u.id != ?

        // exclude specific IDs
        if (!empty($without_in)) {
            $withoutIds = array_filter(explode(',', $without_in), fn($v) => trim($v) !== '');
            if (!empty($withoutIds)) {
                $placeholders = str_repeat('?,', count($withoutIds) - 1) . '?';
                $sql .= " AND u.id NOT IN ($placeholders)";
                foreach ($withoutIds as $id) {
                    $params[] = trim($id);
                }
            }
        } elseif (!empty($user_ids)) {
            $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
            $sql .= " AND u.id NOT IN ($placeholders)";
            foreach ($user_ids as $id) {
                $params[] = $id;
            }
        }

        // search
        if ($search !== '') {
            $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // pagination
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = array_merge($result, $friends);
    }
    
    
    
    
    // ---------------------------
//     // Fetch Friends ONLY if type = "only_friends"
// if ($type === "only_friends") {
//     $params = [];

//     $sql = "SELECT 
//                 u.id,
//                 u.first_name,
//                 u.last_name,
//                 u.token,
//                 CONCAT(?, IFNULL(u.profile_picture, 'default.jpeg')) AS profile_picture,
//                 'user' AS type
//             FROM users u
//             INNER JOIN friendships f 
//                 ON (f.sender_id = ? OR f.receiver_id = ?)
//               AND f.is_active = 1
//               AND (u.id = f.sender_id OR u.id = f.receiver_id)
//             WHERE u.id != ?";

//     // uploadsPath parameter first
//     $params[] = rtrim($uploadsPath, '/') . '/';

//     // friendship bindings
//     $params[] = $user_id; // f.sender_id = ?
//     $params[] = $user_id; // f.receiver_id = ?
//     $params[] = $user_id; // u.id != ?

//     // exclude specific IDs
//     if (!empty($without_in)) {
//         $withoutIds = array_filter(explode(',', $without_in), fn($v) => trim($v) !== '');
//         if (!empty($withoutIds)) {
//             $placeholders = str_repeat('?,', count($withoutIds) - 1) . '?';
//             $sql .= " AND u.id NOT IN ($placeholders)";
//             foreach ($withoutIds as $id) {
//                 $params[] = trim($id);
//             }
//         }
//     } elseif (!empty($user_ids)) {
//         $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
//         $sql .= " AND u.id NOT IN ($placeholders)";
//         foreach ($user_ids as $id) {
//             $params[] = $id;
//         }
//     }

//     // search
//     if ($search !== '') {
//         $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
//         $params[] = "%$search%";
//         $params[] = "%$search%";
//     }

//     // pagination
//     if ($limit !== null) {
//         $sql .= " LIMIT ? OFFSET ?";
//         $params[] = (int)$limit;
//         $params[] = (int)$offset;
//     }

//     $stmt = $conn->prepare($sql);
//     $stmt->execute($params);
//     $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $result = array_merge($result, $friends);
// }


    // ---------------------------
    // Fetch Users by IDs (if type != only_friends)
if (($type === "users" || $type === "all") && !empty($user_ids)) {
    $in  = str_repeat('?,', count($user_ids) - 1) . '?';

    $sql = "SELECT 
                u.id,
                u.first_name,
                u.last_name,
                u.token,
                CONCAT(?, IFNULL(u.profile_picture, 'default.jpeg')) AS profile_picture,
                'user' AS type,

                CASE WHEN b1.id IS NOT NULL THEN 1 ELSE 0 END AS i_blocked_user,
                CASE WHEN b2.id IS NOT NULL THEN 1 ELSE 0 END AS blocked_me

            FROM users u

            LEFT JOIN block b1 
                ON b1.blocker_id = ? AND b1.blocked_id = u.id

            LEFT JOIN block b2
                ON b2.blocker_id = u.id AND b2.blocked_id = ?

            WHERE u.id IN ($in)";

    // $params = array_merge([$uploadsPath, $currentUserId, $currentUserId], $user_ids);
    
    $params = array_merge([$uploadsPath, $user_id, $user_id], $user_ids);


    if ($search !== '') {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    if ($limit !== null) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = array_merge($result, $users);
}



if (($type === "groups" || $type === "all") && !empty($group_ids)) {
    $in  = str_repeat('?,', count($group_ids) - 1) . '?';
    $sql = "SELECT 
                g.id,
                g.name,
                CONCAT(?, IFNULL(g.image, 'default.png')) AS image,
                g.descriptions,
                g.created_by_user_id,
                g.date_created,
                g.member_count,
                'group' AS type,
                gm.is_active
            FROM groups g
            JOIN group_member gm 
                ON gm.group_id = g.id 
                AND gm.user_id = ? 
                AND gm.is_active = 1  -- only active members
            WHERE g.id IN ($in)";

    // uploads path first
    $params = [rtrim($uploadsPath, '/') . '/'];

    // user_id second (for JOIN condition)
    $params[] = $user_id;

    // then the group IDs for IN (...)
    $params = array_merge($params, $group_ids);

    // search
    if ($search !== '') {
        $sql .= " AND g.name LIKE ?";
        $params[] = "%$search%";
    }

    // pagination
    if ($limit !== null) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = array_merge($result, $groups);
}


    echo json_encode([
        'success' => true,
        'count' => count($result),
        'data' => $result
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
