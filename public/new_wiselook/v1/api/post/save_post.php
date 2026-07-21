<?php
// post/save_post.php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

// Required fields
$required = ['user_id', 'post_id'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => ($input['lang'] ?? 'en') === 'ar' ? 
                "حقل مطلوب مفقود: $field" : 
                "Missing required field: $field"
        ]);
        exit;
    }
}

$user_id = (int)$input['user_id'];
$post_id = (int)$input['post_id'];
$lang = isset($input['lang']) ? $input['lang'] : 'ar';

$db = new Database();
$conn = $db->getConnection();

// Check if user and post exists and is active
try {
    $db->checkUserExists($user_id);
    $db->checkPostExists($post_id);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 
            "المستخدم أو المنشور غير موجود" : 
            "User or post not found"
    ]);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Check if the post is already saved by this user
    $stmt = $conn->prepare("SELECT id FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Post is already saved - unsave it
        $stmt = $conn->prepare("DELETE FROM saved_posts WHERE user_id = :user_id AND post_id = :post_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $lang === 'ar' ? 
                'تم إلغاء حفظ المنشور بنجاح' : 
                'Post unsaved successfully',
            'data' => [
                'user_id' => $user_id,
                'post_id' => $post_id,
                'saved' => false,
                'action' => 'unsave'
            ]
        ]);
    } else {
        // Post not saved - save it
        $stmt = $conn->prepare("INSERT INTO saved_posts (user_id, post_id) VALUES (:user_id, :post_id)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => $lang === 'ar' ? 
                'تم حفظ المنشور بنجاح' : 
                'Post saved successfully',
            'data' => [
                'user_id' => $user_id,
                'post_id' => $post_id,
                'saved' => true,
                'action' => 'save'
            ]
        ]);
    }
} catch (PDOException $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 
            'خطأ في قاعدة البيانات' : 
            'Database error',
        'error' => $e->getMessage()
    ]);
}