<?php
// chat/send.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

include_once __DIR__ . '/../db.php';

$db = new Database();
$conn = $db->getConnection();

// Get form data
$sender_id = isset($_POST['sender_id']) ? $db->sanitize($_POST['sender_id']) : null;
$receiver_id = isset($_POST['receiver_id']) ? $db->sanitize($_POST['receiver_id']) : null;
$message = isset($_POST['message']) ? $db->sanitize($_POST['message']) : null;

// Basic validation
if (!$sender_id || !$receiver_id) {
    echo json_encode([
        'success' => false,
        'message' => 'Both sender and receiver are required'
    ]);
    exit;
}

if (!$message && !isset($_FILES['media'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Either message text or media file is required'
    ]);
    exit;
}

// Check user exists
$db->checkUserExists($sender_id);
$db->checkUserExists($receiver_id);

try {
    // Insert message into database
    $query = "INSERT INTO chats (sender_id, receiver_id, message, media) 
              VALUES (:sender_id, :receiver_id, :message, :media)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':sender_id', $sender_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':media', $media_path);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'message_id' => $conn->lastInsertId()
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send message'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}