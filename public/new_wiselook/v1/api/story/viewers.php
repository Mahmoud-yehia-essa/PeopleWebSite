<?php
// story/viewers.php

ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['story_id']) || empty(trim($input['story_id']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Story ID is required'
    ]);
    exit;
}

$story_id = (int) $input['story_id'];
$db = new Database();
$conn = $db->getConnection();

// Get story viewers
$query = "
    SELECT 
        u.id as user_id, 
        u.first_name, 
        u.last_name, 
        u.profile_picture,
        ss.viewed_at
    FROM story_seen ss
    JOIN users u ON ss.user_id = u.id
    JOIN stories s ON ss.story_id = s.id
    WHERE ss.story_id = :story_id
      AND u.id != s.user_id  -- يمنع صاحب القصة من الظهور في المشاهدين
    ORDER BY ss.viewed_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':story_id', $story_id, PDO::PARAM_INT);
$stmt->execute();
$viewers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format profile picture URLs
foreach ($viewers as &$viewer) {
    if (!empty($viewer['profile_picture'])) {
        $viewer['profile_picture'] = $uploadsPath . $viewer['profile_picture'];
    } else {
        $viewer['profile_picture'] = $uploadsPath . 'default-profile.jpg';
    }
    
    // Format viewed_at time
    $viewer['viewed_at'] = $db->timeAgo($viewer['viewed_at']);
}

echo json_encode([
    'success' => true,
    'message' => 'Viewers fetched successfully',
    'data' => $viewers
]);
exit;