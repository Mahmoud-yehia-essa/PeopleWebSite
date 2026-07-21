<?php
// post/add_post.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return 200 OK for OPTIONS requests
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../upload_function.php';

$response = ['success' => false, 'message' => '', 'data' => null];

$db = new Database();
$conn = $db->getConnection();

// Sanitize input
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$privacy_level_id = isset($_POST['privacy_level_id']) ? (int)$_POST['privacy_level_id'] : 1;
$post_type_id = isset($_POST['post_type_id']) ? (int)$_POST['post_type_id'] : 1;
$hasMedia = !empty($_FILES['media']);

// Validate required fields
if (!$user_id || (empty($content) && !$hasMedia)) {
    $response['message'] = 'Missing required fields: need either content or media (or both), but not both empty.';
    echo json_encode($response); 
    exit;
}

// Check if user exists and is active
$db->checkUserExists($user_id);

$uploadedMedia = [];
if ($hasMedia) {
    // Prepare to upload files
    $mediaFiles = $_FILES['media'];

    // Normalize multiple file uploads
    $mediaCount = is_array($mediaFiles['name']) ? count($mediaFiles['name']) : 1;

    // If single file, convert to array format for consistent processing
    if (!is_array($mediaFiles['name'])) {
        $mediaFiles = [
            'name' => [$mediaFiles['name']],
            'type' => [$mediaFiles['type']],
            'tmp_name' => [$mediaFiles['tmp_name']],
            'error' => [$mediaFiles['error']],
            'size' => [$mediaFiles['size']]
        ];
    }

    for ($i = 0; $i < $mediaCount; $i++) {
        if ($mediaFiles['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // Skip failed uploads
        }

        $_FILES['single_media'] = [
            'name' => $mediaFiles['name'][$i],
            'type' => $mediaFiles['type'][$i],
            'tmp_name' => $mediaFiles['tmp_name'][$i],
            'error' => $mediaFiles['error'][$i],
            'size' => $mediaFiles['size'][$i],
        ];

        $upload = uploadImage('single_media', '/posts');

        if ($upload['status'] === 'success') {
            $filePath = 'uploads/posts/' . basename($upload['file_name']);
            $ext = strtolower(pathinfo($upload['file_name'], PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';

            $uploadedMedia[] = [
                'path' => $filePath,
                'type' => $mediaType
            ];
        }
    }
}

// Insert post (with or without media)
try {
    $image = null;
    $video = null;
    
    if (count($uploadedMedia) > 0) {
        $mainMedia = $uploadedMedia[0];
        $image = $mainMedia['type'] === 'image' ? $mainMedia['path'] : null;
        $video = $mainMedia['type'] === 'video' ? $mainMedia['path'] : null;
    }
    
    // Fixed the duplicate privacy_level_id in the SQL query
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, video, privacy_level_id, post_type_id, created_at) 
                           VALUES (:user_id, :content, :image, :video, :privacy_level_id, :post_type_id, NOW())");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':video', $video);
    $stmt->bindParam(':privacy_level_id', $privacy_level_id);
    $stmt->bindParam(':post_type_id', $post_type_id);

    if ($stmt->execute()) {
        $post_id = $conn->lastInsertId();

        // Insert remaining media (if any) into post_media
        if (count($uploadedMedia) > 1) {
            for ($i = 1; $i < count($uploadedMedia); $i++) {
                $media = $uploadedMedia[$i];
                
                $image = $media['type'] === 'image' ? $media['path'] : null;
                $video = $media['type'] === 'video' ? $media['path'] : null;
                
                $stmtMedia = $conn->prepare("INSERT INTO post_media (post_id, image, video, created_at) 
                                            VALUES (:post_id, :image, :video, NOW())");
                $stmtMedia->bindParam(':post_id', $post_id);
                $stmtMedia->bindParam(':image', $image);
                $stmtMedia->bindParam(':video', $video);
                $stmtMedia->execute();
            }
        }

        $response['success'] = true;
        $response['message'] = 'Post created successfully.';
        $response['data'] = ['post_id' => $post_id];
    } else {
        $response['message'] = 'Failed to insert post.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;