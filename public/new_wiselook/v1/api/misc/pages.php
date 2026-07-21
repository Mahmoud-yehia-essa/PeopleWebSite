<?php
// pages/get_page.php

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

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$page_id = isset($input['id']) ? (int)$input['id'] : null;
$lang = isset($input['lang']) && strtolower($input['lang']) === 'ar' ? 'ar' : 'en';

if (!$page_id) {
    echo json_encode(['success' => false, 'message' => 'Page ID is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$sql = "
    SELECT 
        id,
        " . ($lang === 'ar' ? 'title_ar AS title, content_ar AS content' : 'title, content') . "
    FROM pages
    WHERE id = :id
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $page_id, PDO::PARAM_INT);
$stmt->execute();
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if ($page) {
    echo json_encode([
        'success' => true,
        'message' => 'Page fetched successfully',
        'data' => $page
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Page not found'
    ]);
}
exit;
