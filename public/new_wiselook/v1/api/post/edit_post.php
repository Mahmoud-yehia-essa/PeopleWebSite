<?php
// post/edit_post.php
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
include_once __DIR__ . '/../../../upload_function.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

$db = new Database();
$conn = $db->getConnection();

// Get input data
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : null;
$privacy_level_id = isset($_POST['privacy_level_id']) ? (int)$_POST['privacy_level_id'] : null;
$media = !empty($_FILES['media']) ? $_FILES['media'] : null;

// Validate required fields
if (!$user_id || !$post_id) {
    $response['message'] = 'Missing required fields (user_id and post_id)';
    echo json_encode($response);
    exit;
}

// Check if user exists and is active
$db->checkUserExists($user_id);

try {
    // Verify post belongs to user
    $stmt = $conn->prepare("SELECT id FROM posts WHERE id = :post_id AND user_id = :user_id");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$post) {
        $response['message'] = 'Post not found or you do not have permission to edit it';
        http_response_code(403);
        echo json_encode($response);
        exit;
    }

    $conn->beginTransaction();
    
    // Process media upload if provided
    if ($media) {
        $upload_result = uploadToExternalServer($media, [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'purpose' => 'post_edit'
        ]);
        
        if (!$upload_result['success']) {
            throw new Exception('Media upload failed: ' . ($upload_result['message'] ?? ''));
        }
        
        $media_name = $upload_result['media_name'][0]; // Get the first (and only) media file
        $extension = strtolower(pathinfo($media_name, PATHINFO_EXTENSION));
        $is_image = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $is_video = in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm']);
        
        if ($is_image) {
            // Update post with new image and clear video
            $stmt = $conn->prepare("UPDATE posts SET image = :image, video = NULL WHERE id = :post_id");
            $stmt->bindParam(':image', $media_name);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
        } elseif ($is_video) {
            // Update post with new video and clear image
            $stmt = $conn->prepare("UPDATE posts SET video = :video, image = NULL WHERE id = :post_id");
            $stmt->bindParam(':video', $media_name);
            $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    
    // Update post content and privacy if provided
    $update_fields = [];
    $update_values = [];
    
    if ($content !== null) {
        $update_fields[] = 'content = :content';
        $update_values[':content'] = $content;
    }
    
    if ($privacy_level_id !== null) {
        $update_fields[] = 'privacy_level_id = :privacy_level_id';
        $update_values[':privacy_level_id'] = $privacy_level_id;
    }
    
    if (!empty($update_fields)) {
        $update_fields[] = 'updated_at = NOW()';
        
        $sql = "UPDATE posts SET " . implode(', ', $update_fields) . " WHERE id = :post_id";
        $stmt = $conn->prepare($sql);
        
        foreach ($update_values as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $conn->commit();
    
    // Get updated post data
    $stmt = $conn->prepare("
        SELECT p.*
        FROM posts p
        WHERE p.id = :post_id
    ");
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $response['success'] = true;
    $response['message'] = 'Post updated successfully';
    $response['data'] = $post_data;
    
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
exit;