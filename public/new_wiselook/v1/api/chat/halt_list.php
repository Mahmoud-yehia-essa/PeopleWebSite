<?php
// chat/list.php
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

// Get input data (works for both JSON and form-data)
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

// Get parameters
$user_id = isset($input['user_id']) ? $db->sanitize($input['user_id']) : null;
$person_id = isset($input['person_id']) ? $db->sanitize($input['person_id']) : null;
$limit = isset($input['limit']) ? intval($input['limit']) : 20;
$offset = isset($input['offset']) ? intval($input['offset']) : 0;

// Validate user_id
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit;
}

try {
    if ($person_id) {
        // Case 1: Get conversation between user_id and person_id
        $query = "SELECT 
            c.*,
            u.id as person_id,
            u.first_name,
            u.last_name,
            u.token as person_token,
            (SELECT token FROM users WHERE id = :user_id) as user_token,
            CONCAT(:uploadsPath, IFNULL(u.profile_picture, 'default.jpeg')) as profile_picture,
            CASE 
                WHEN c.id = (
                    SELECT MAX(id) FROM chats 
                    WHERE (sender_id = :user_id AND receiver_id = :person_id) 
                    OR (sender_id = :person_id AND receiver_id = :user_id)
                ) THEN TRUE 
                ELSE FALSE 
            END as is_last
          FROM chats c
          JOIN users u ON (
              u.id = CASE 
                  WHEN c.sender_id = :user_id THEN c.receiver_id 
                  ELSE c.sender_id 
              END
          )
          WHERE 
              (c.sender_id = :user_id AND c.receiver_id = :person_id)
              OR 
              (c.sender_id = :person_id AND c.receiver_id = :user_id)
          ORDER BY c.created_at DESC
          LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':person_id', $person_id);
    } else {
        // Case 2: Get last message from each conversation
        $query = "SELECT 
                    latest_messages.*,
                    u.first_name,
                    u.last_name,
                    u.token as person_token,
                    (SELECT token FROM users WHERE id = :user_id) as user_token,
                    CONCAT(:uploadsPath, IFNULL(u.profile_picture, 'default.jpeg')) as profile_picture,
                    TRUE as is_last
                  FROM (
                      SELECT 
                          c1.*,
                          CASE 
                              WHEN c1.sender_id = :user_id THEN c1.receiver_id 
                              ELSE c1.sender_id 
                          END as person_id
                      FROM chats c1
                      INNER JOIN (
                          SELECT 
                              CASE 
                                  WHEN sender_id = :user_id THEN receiver_id 
                                  ELSE sender_id 
                              END as partner_id,
                              MAX(id) as max_id
                          FROM chats
                          WHERE sender_id = :user_id OR receiver_id = :user_id
                          GROUP BY partner_id
                      ) c2 ON c1.id = c2.max_id
                      WHERE c1.sender_id = :user_id OR c1.receiver_id = :user_id
                  ) latest_messages
                  JOIN users u ON latest_messages.person_id = u.id
                  ORDER BY latest_messages.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
    }
    
    $stmt->bindParam(':uploadsPath', $uploadsPath);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($messages),
        'data' => $messages
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}