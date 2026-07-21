<?php
// report/list_reasons.php

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

$report_type_id = isset($input['report_type_id']) ? (int)$input['report_type_id'] : null;
$lang = isset($input['lang']) && strtolower($input['lang']) === 'ar' ? 'ar' : 'en';

if (!$report_type_id) {
    echo json_encode(['success' => false, 'message' => 'report_type_id is required']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$sql = "
    SELECT 
        id, 
        " . ($lang === 'ar' ? 'name_ar AS name, description_ar AS description' : 'name, description') . "
    FROM report_reasons 
    WHERE report_type_id = :report_type_id
";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':report_type_id', $report_type_id, PDO::PARAM_INT);
$stmt->execute();
$reasons = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'message' => 'Report reasons fetched successfully',
    'data' => $reasons
]);
exit;
