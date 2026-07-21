<?php
// post/add_post.php on easy Server
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
$db->checkUserExists($user_id);

// Process media uploads if any
$uploadedMedia = [];

if ($hasMedia) {
    // Prepare files for upload to external server
    $files = [];
    
    if (is_array($_FILES['media']['name'])) {
        // Multiple files
        foreach ($_FILES['media']['name'] as $key => $name) {
            $files[] = [
                'name' => 'media[]',
                'filename' => $_FILES['media']['name'][$key],
                'type' => $_FILES['media']['type'][$key],
                'tmp_name' => $_FILES['media']['tmp_name'][$key]
            ];
        }
    } else {
        // Single file
        $files[] = [
            'name' => 'media',
            'filename' => $_FILES['media']['name'],
            'type' => $_FILES['media']['type'],
            'tmp_name' => $_FILES['media']['tmp_name']
        ];
    }
    
    // Initialize cURL for external upload
    $ch = curl_init();
    $postFields = [
        'user_id' => $user_id,
        'content' => $content,
        'privacy_level_id' => $privacy_level_id,
        'post_type_id' => $post_type_id
    ];
    
    // Add files to post fields
    foreach ($files as $file) {
        $postFields[$file['name']] = new CURLFile(
            $file['tmp_name'],
            $file['type'],
            $file['filename']
        );
    }
    
    curl_setopt($ch, CURLOPT_URL, $uploadFunction);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $uploadResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        $response['message'] = 'Failed to upload media to external server';
        echo json_encode($response);
        exit;
    }
    
    $uploadData = json_decode($uploadResponse, true);
    
    if (!$uploadData || !$uploadData['success']) {
        $response['message'] = $uploadData['message'] ?? 'Media upload failed';
        if (isset($uploadData['errors'])) {
            $response['errors'] = $uploadData['errors'];
        }
        echo json_encode($response);
        exit;
    }
    
    // Process uploaded media names and determine their types
    foreach ($uploadData['media_name'] as $mediaName) {
        $extension = strtolower(pathinfo($mediaName, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm']);
        
        if ($isImage || $isVideo) {
            $uploadedMedia[] = [
                'name' => $mediaName,
                'type' => $isImage ? 'image' : 'video'
            ];
        }
    }
}

// Insert post
try {
    $image = null;
    $video = null;
    
    if (count($uploadedMedia) > 0) {
        $mainMedia = $uploadedMedia[0];
        if ($mainMedia['type'] === 'image') {
            $image = $mainMedia['name'];
        } else {
            $video = $mainMedia['name'];
        }
    }
    
    $conn->beginTransaction();
    
    // Insert the main post
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
                $image = $media['type'] === 'image' ? $media['name'] : null;
                $video = $media['type'] === 'video' ? $media['name'] : null;
                
                $stmtMedia = $conn->prepare("INSERT INTO post_media (post_id, image, video, created_at) 
                                            VALUES (:post_id, :image, :video, NOW())");
                $stmtMedia->bindParam(':post_id', $post_id);
                $stmtMedia->bindParam(':image', $image);
                $stmtMedia->bindParam(':video', $video);
                $stmtMedia->execute();
            }
        }

        // Increment post_count in users table
        $stmtUpdateUser = $conn->prepare("UPDATE users SET post_count = post_count + 1 WHERE id = :user_id");
        $stmtUpdateUser->bindParam(':user_id', $user_id);
        $stmtUpdateUser->execute();

        $conn->commit();

        $response['success'] = true;
        $response['message'] = 'Post created successfully';
        $response['data'] = [
            'post_id' => $post_id,
            'media_uploaded' => count($uploadedMedia),
            'media_info' => $uploadedMedia
        ];
    } else {
        $conn->rollBack();
        $response['message'] = 'Failed to insert post';
    }
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;