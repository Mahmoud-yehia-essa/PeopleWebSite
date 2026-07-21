<?php
// story/seen.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
if (!isset($input['user_id']) || !isset($input['story_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Credentials required'
    ]);
    exit;
}

$user_id = (int) $input['user_id'];
$story_id = (int) $input['story_id'];

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();
    
    // 1. Check if the story exists and is active
    $checkStory = $conn->prepare("SELECT id , user_id FROM stories WHERE id = :story_id AND is_active = 1");
    $checkStory->bindParam(':story_id', $story_id, PDO::PARAM_INT);
    $checkStory->execute();
    $story = $checkStory->fetch(PDO::FETCH_ASSOC);

    if ($checkStory->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Story not found or inactive'
        ]);
        exit;
    }
    
    // 2. Check if user has already seen this story (prevent duplicate views)
    $checkSeen = $conn->prepare("SELECT id FROM story_seen WHERE story_id = :story_id AND user_id = :user_id");
    $checkSeen->bindParam(':story_id', $story_id, PDO::PARAM_INT);
    $checkSeen->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $checkSeen->execute();
    
    if ($checkSeen->rowCount() === 0) {
        // 3. Insert into story_seen table
        $insertSeen = $conn->prepare("
            INSERT INTO story_seen (story_id, user_id, viewed_at) 
            VALUES (:story_id, :user_id, NOW())
        ");
        $insertSeen->bindParam(':story_id', $story_id, PDO::PARAM_INT);
        $insertSeen->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $insertSeen->execute();
        
        // 4. Increment view_count in stories table
    if ($story["user_id"] != $user_id) {
        $updateViews = $conn->prepare("
            UPDATE stories 
            SET view_count = view_count + 1 
            WHERE id = :story_id
        ");
        $updateViews->bindParam(':story_id', $story_id, PDO::PARAM_INT);
        $updateViews->execute();
    }
    }
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Story view recorded successfully'
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}

exit;