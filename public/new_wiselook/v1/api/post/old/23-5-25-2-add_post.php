<?php
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
if (!$user_id || !$privacy_level_id || (empty($content) && !$hasMedia)) {
    $response['message'] = 'Missing required fields: need either content or media (or both)';
    echo json_encode($response); 
    exit;
}

// Check if user exists and is active
try {
    $db->checkUserExists($user_id);
} catch (Exception $e) {
    $response['message'] = 'User error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

$uploadedMedia = [];
if ($hasMedia) {
    $mediaFiles = $_FILES['media'];
    $mediaCount = is_array($mediaFiles['name']) ? count($mediaFiles['name']) : 1;

    // Handle single file (normalize to array)
    if ($mediaCount === 1 && !is_array($mediaFiles['name'])) {
        $_FILES['single_media'] = $mediaFiles;
        $upload = uploadImage('single_media', __DIR__ . '/../uploads');
        
        if ($upload['status'] === 'success') {
            $ext = strtolower(pathinfo($upload['file_name'], PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
            $uploadedMedia[] = [
                'path' => '/app/wise/uploads/' . $upload['file_name'],
                'type' => $mediaType
            ];
        }
    } else {
        // Handle multiple files
        for ($i = 0; $i < $mediaCount; $i++) {
            $_FILES['single_media'] = [
                'name' => $mediaFiles['name'][$i],
                'type' => $mediaFiles['type'][$i],
                'tmp_name' => $mediaFiles['tmp_name'][$i],
                'error' => $mediaFiles['error'][$i],
                'size' => $mediaFiles['size'][$i],
            ];

            $upload = uploadImage('single_media', __DIR__ . '/../uploads');

            if ($upload['status'] === 'success') {
                $ext = strtolower(pathinfo($upload['file_name'], PATHINFO_EXTENSION));
                $mediaType = in_array($ext, ['mp4', 'mov', 'avi']) ? 'video' : 'image';
                $uploadedMedia[] = [
                    'path' => 'uploads/' . $upload['file_name'],
                    'type' => $mediaType
                ];
            }
        }
    }
}

// Insert post
try {
    $image = null;
    $video = null;
    
    if (count($uploadedMedia) > 0) {
        $mainMedia = $uploadedMedia[0];
        $image = $mainMedia['type'] === 'image' ? $mainMedia['path'] : null;
        $video = $mainMedia['type'] === 'video' ? $mainMedia['path'] : null;
    }
    
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

        // Insert remaining media into post_media
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
        $response['message'] = 'Post created successfully';
        $response['data'] = ['post_id' => $post_id];
    } else {
        $response['message'] = 'Failed to insert post';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;