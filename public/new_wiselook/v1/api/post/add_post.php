<?php
// post/add_post.php
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
include_once __DIR__ . '/../notifications/notification_class.php';

$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

$db = new Database();
$conn = $db->getConnection();
$notification_class = new NotificationClass();

// Sanitize input
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$privacy_level_id = isset($_POST['privacy_level_id']) ? (int)$_POST['privacy_level_id'] : 1;
$post_type_id = isset($_POST['post_type_id']) ? (int)$_POST['post_type_id'] : 1;
$shared_id = isset($_POST['shared_id']) ? (int)$_POST['shared_id'] : null;
$hasMedia = !empty($_FILES['media']);

// Validate required fields
if (!$user_id || !$privacy_level_id || (empty($content) && !$hasMedia && $post_type_id != 2 && !$shared_id)) {
    $response['message'] = 'Missing required fields';
    echo json_encode($response);
    exit;
}

// If it's a shared post, force post_type_id to 1 (regular post)
if ($shared_id) {
    $post_type_id = 1;
    
    // Check if the post being shared exists
    $stmtCheckShared = $conn->prepare("SELECT id FROM posts WHERE id = :shared_id");
    $stmtCheckShared->bindParam(':shared_id', $shared_id);
    $stmtCheckShared->execute();
    
    if ($stmtCheckShared->rowCount() === 0) {
        $response['message'] = 'The post you are trying to share does not exist';
        echo json_encode($response);
        exit;
    }
}

// Check if user exists and is active
$db->checkUserExists($user_id);

// Process media uploads if any (skip if it's a poll or shared post)
$uploadedMedia = [];
if ($hasMedia && $post_type_id != 2 && !$shared_id) {
    $additionalFields = [
        'user_id' => $user_id,
        'content' => $content,
        'privacy_level_id' => $privacy_level_id,
        'post_type_id' => $post_type_id
    ];
    
    // $uploadResult = uploadToExternalServer($_FILES['media'], $additionalFields);
    $uploadResult = uploadToLocalServer($_FILES['media']);
    
    if (!$uploadResult['success']) {
        $response['message'] = $uploadResult['message'];
        if (isset($uploadResult['errors'])) {
            $response['errors'] = $uploadResult['errors'];
        }
        echo json_encode($response);
        exit;
    }
    
    // Process uploaded media names and determine their types
    foreach ($uploadResult['media_name'] as $mediaName) {
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

// Insert post or poll
try {
    $conn->beginTransaction();
    $isPoll = ($post_type_id == 2);

    if ($isPoll) {
        // Handle poll creation
        $pollQuestion = $content;
        $pollOptions = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];
        $expiresAt = isset($_POST['expires_at']) ? $_POST['expires_at'] : null;

        // Validate poll data
        if (empty($pollQuestion)) {
            throw new Exception('Poll question cannot be empty');
        }

        if (count($pollOptions) < 2) {
            throw new Exception('A poll must have at least 2 options');
        }

        // Insert minimal post record
        $stmt = $conn->prepare("INSERT INTO posts (user_id, post_type_id, privacy_level_id, created_at) 
                               VALUES (:user_id, :post_type_id, :privacy_level_id, NOW())");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':post_type_id', $post_type_id);
        $stmt->bindParam(':privacy_level_id', $privacy_level_id);

        if (!$stmt->execute()) {
            throw new Exception('Failed to create poll post');
        }

        $post_id = $conn->lastInsertId();
        
        

        // Insert the poll
        $stmtPoll = $conn->prepare("INSERT INTO polls 
                                   (post_id, question, expires_at, created_at) 
                                   VALUES (:post_id, :question, :expires_at, NOW())");
        $stmtPoll->bindParam(':post_id', $post_id);
        $stmtPoll->bindParam(':question', $pollQuestion);
        $stmtPoll->bindParam(':expires_at', $expiresAt);

        if (!$stmtPoll->execute()) {
            throw new Exception('Failed to create poll');
        }

        $poll_id = $conn->lastInsertId();

        // Insert poll options
        foreach ($pollOptions as $option) {
            $optionContent = trim($option);
            if (!empty($optionContent)) {
                $stmtOption = $conn->prepare("INSERT INTO poll_options (poll_id, content) 
                                              VALUES (:poll_id, :content)");
                $stmtOption->bindParam(':poll_id', $poll_id);
                $stmtOption->bindParam(':content', $optionContent);

                if (!$stmtOption->execute()) {
                    throw new Exception('Failed to add poll option: ' . $optionContent);
                }
            }
        }

        $response['data'] = [
            'poll' => [
                'poll_id' => $poll_id,
                'question' => $pollQuestion,
                'options_count' => count($pollOptions),
                'expires_at' => $expiresAt
            ]
        ];
    } else {
        // Handle regular post creation (including shared posts)
        $image = null;
        $video = null;

        // Only process media if it's not a shared post
        if (!$shared_id && count($uploadedMedia) > 0) {
            $mainMedia = $uploadedMedia[0];
            if ($mainMedia['type'] === 'image') {
                $image = $mainMedia['name'];
            } else {
                $video = $mainMedia['name'];
            }
        }

        // Prepare the SQL query based on whether it's a shared post or not
        if ($shared_id) {
            $stmt = $conn->prepare("INSERT INTO posts 
                                   (user_id, content, privacy_level_id, post_type_id, parent_id, created_at) 
                                   VALUES (:user_id, :content, :privacy_level_id, :post_type_id, :parent_id, NOW())");
            $stmt->bindParam(':parent_id', $shared_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO posts 
                                   (user_id, content, image, video, privacy_level_id, post_type_id, created_at) 
                                   VALUES (:user_id, :content, :image, :video, :privacy_level_id, :post_type_id, NOW())");
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':video', $video);
        }

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':content', $content);
        $stmt->bindParam(':privacy_level_id', $privacy_level_id);
        $stmt->bindParam(':post_type_id', $post_type_id);

        if ($stmt->execute()) {
            $post_id = $conn->lastInsertId();
            
            
            // ---------------- HASHTAGS HANDLING ----------------
// preg_match_all('/#(\w+)/u', $content, $matches); // استخراج كل الكلمات يلي بتبدأ بـ #
// $hashtags = array_unique($matches[1]); // إزالة التكرار

// if (!empty($hashtags)) {
    
//     $content_type_id = 1; 

//     foreach ($hashtags as $tag) {
//         $tag = strtolower(trim($tag)); // نحولها لحروف صغيرة للتوحيد

//         // تحقق إذا الهاشتاغ موجود أو لأ
//         $stmtHash = $conn->prepare("SELECT id FROM hashtags WHERE name = :name");
//         $stmtHash->bindParam(':name', $tag);
//         $stmtHash->execute();

//         if ($stmtHash->rowCount() > 0) {
//             $hashtag = $stmtHash->fetch(PDO::FETCH_ASSOC);
//             $hashtag_id = $hashtag['id'];
//         } else {
//             // أضف الهاشتاغ الجديد
//             $stmtInsertHash = $conn->prepare("INSERT INTO hashtags (name, created_at) VALUES (:name, NOW())");
//             $stmtInsertHash->bindParam(':name', $tag);
//             $stmtInsertHash->execute();
//             $hashtag_id = $conn->lastInsertId();
//         }

//         // اربط الهاشتاغ مع البوست
//         $stmtLink = $conn->prepare("INSERT INTO hashtag_links (hashtag_id, content_id, content_type_id, created_at)
//                                     VALUES (:hashtag_id, :content_id, :content_type_id, NOW())");
//         $stmtLink->bindParam(':hashtag_id', $hashtag_id);
//         $stmtLink->bindParam(':content_id', $post_id);
//         $stmtLink->bindParam(':content_type_id', $content_type_id);
//         $stmtLink->execute();
//     }
// }



            // Insert remaining media into post_media (only if not a shared post)
            if (!$shared_id && count($uploadedMedia) > 1) {
                for ($i = 1; $i < count($uploadedMedia); $i++) {
                    $media = $uploadedMedia[$i];
                    $image = $media['type'] === 'image' ? $media['name'] : null;
                    $video = $media['type'] === 'video' ? $media['name'] : null;

                    $stmtMedia = $conn->prepare("INSERT INTO post_media 
                                               (post_id, image, video, created_at) 
                                               VALUES (:post_id, :image, :video, NOW())");
                    $stmtMedia->bindParam(':post_id', $post_id);
                    $stmtMedia->bindParam(':image', $image);
                    $stmtMedia->bindParam(':video', $video);
                    $stmtMedia->execute();
                }
            }

            // If it's a shared post, update the share count of the original post
            if ($shared_id) {
                $stmtUpdateShares = $conn->prepare("UPDATE posts SET share_count = share_count + 1 WHERE id = :shared_id");
                $stmtUpdateShares->bindParam(':shared_id', $shared_id);
                $stmtUpdateShares->execute();
                
                $response['data']['shared_post_id'] = $shared_id;
            } else {
                $response['data']['media'] = [
                    'media_uploaded' => count($uploadedMedia),
                    'media_info' => $uploadedMedia
                ];
            }
        } else {
            throw new Exception('Failed to insert post');
        }
    }

    // Common success operations
    $post_id = $post_id ?? $conn->lastInsertId();

    // Increment post_count in users table
    $stmtUpdateUser = $conn->prepare("UPDATE users SET post_count = post_count + 1 WHERE id = :user_id");
    $stmtUpdateUser->bindParam(':user_id', $user_id);
    $stmtUpdateUser->execute();

    $conn->commit();

    // NOTIFICATION FOR SHARED POSTS ONLY
    if ($shared_id) {
        // Get original post owner details
        $original_post_query = "SELECT 
                                u.id as owner_id, 
                                u.token, 
                                CONCAT(u.first_name, ' ', u.last_name) as owner_name
                               FROM posts p
                               JOIN users u ON u.id = p.user_id
                               WHERE p.id = ?";
        $stmt = $conn->prepare($original_post_query);
        $stmt->execute([$shared_id]);
        $original_post_owner = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sharer's name
        $sharer_query = "SELECT CONCAT(first_name, ' ', last_name) as full_name FROM users WHERE id = ?";
        $stmt = $conn->prepare($sharer_query);
        $stmt->execute([$user_id]);
        $sharer = $stmt->fetch(PDO::FETCH_ASSOC);
        $sharer_name = $sharer['full_name'] ?? 'Someone';

        // Send notification only if:
        // - Not sharing own post
        // - Original post owner has FCM token
        if ($original_post_owner && 
            $original_post_owner['owner_id'] != $user_id && 
            !empty($original_post_owner['token'])) {
            
            $notification_message = "$sharer_name shared your post";
            
            $notification_class->sendStaticNotification(
                $notification_message,
                $original_post_owner['token'],
                "post" , $post_id
            );
        }
    }

    $response['success'] = true;
    $response['message'] = $isPoll ? 'Poll created successfully' : 
                          ($shared_id ? 'Post shared successfully' : 'Post created successfully');
    $response['data']['post_id'] = $post_id;
    $response['data']['post_type_id'] = $post_type_id;
    if ($shared_id) {
        $response['data']['is_shared'] = true;
    }
} catch (PDOException $e) {
    $conn->rollBack();
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['data'] = null;
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = 'Error: ' . $e->getMessage();
    $response['data'] = null;
}

echo json_encode($response);
exit;