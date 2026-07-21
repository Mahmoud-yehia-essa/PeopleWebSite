<?php
// store/add_store.php
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
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../upload_function.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

$db = new Database();
$conn = $db->getConnection();

// Get input
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$hasMedia = !empty($_FILES['media']);

// Validate
if (!$user_id || empty($content)) {
    $response['message'] = 'user_id and content are required';
    echo json_encode($response);
    exit;
}

// Check user exists
$db->checkUserExists($user_id);

// Handle media upload
$image = null;
$video = null;

if ($hasMedia) {
    $uploadResult = uploadToExternalServer($_FILES['media'], ['user_id' => $user_id]);
    
    if (!$uploadResult['success']) {
        $response['message'] = 'Upload failed: ' . ($uploadResult['message'] ?? '');
        echo json_encode($response);
        exit;
    }

    $mediaName = $uploadResult['media_name'][0] ?? null;
    if ($mediaName) {
        $ext = strtolower(pathinfo($mediaName, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $image = $mediaName;
        } elseif (in_array($ext, ['mp4', 'mov', 'avi', 'mkv', 'webm'])) {
            $video = $mediaName;
        } else {
            $response['message'] = 'Unsupported file type';
            echo json_encode($response);
            exit;
        }
    }
}

// Insert story
try {
    $stmt = $conn->prepare("INSERT INTO stories 
                          (user_id, content, image, video) 
                          VALUES (:user_id, :content, :image, :video)");
    
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':video', $video);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Story created successfully';
        $response['data'] = [
            'story_id' => $conn->lastInsertId(),
            'user_id' => $user_id,
            'content' => $content,
            'image' => $image,
            'video' => $video
        ];
    } else {
        $response['message'] = 'Failed to create story';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;