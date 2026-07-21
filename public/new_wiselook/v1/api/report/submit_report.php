<?php
// report/submit_report.php

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

// Validate required fields
$required_fields = ['reported_by_id', 'report_type_id', 'target_id', 'report_reasons_id'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

// Sanitize input
$reported_by_id = (int) $input['reported_by_id'];
$report_type_id = (int) $input['report_type_id'];
$target_id = (int) $input['target_id'];
$report_reasons_id = (int) $input['report_reasons_id'];
$lang = isset($input['lang']) && $input['lang'] === 'ar' ? 'ar' : 'en';

// Connect to DB
$db = new Database();
$conn = $db->getConnection();

// Check for duplicate report
$check_sql = "
    SELECT id FROM reports 
    WHERE reported_by_id = :reported_by_id 
    AND report_type_id = :report_type_id 
    AND target_id = :target_id 
    AND report_reasons_id = :report_reasons_id
    LIMIT 1
";

$check_stmt = $conn->prepare($check_sql);
$check_stmt->execute([
    ':reported_by_id' => $reported_by_id,
    ':report_type_id' => $report_type_id,
    ':target_id' => $target_id,
    ':report_reasons_id' => $report_reasons_id
]);

if ($check_stmt->rowCount() > 0) {
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 'تم تقديم هذا البلاغ مسبقًا' : 'Report already submitted'
    ]);
    exit;
}

// Insert report
$insert_sql = "
    INSERT INTO reports (reported_by_id, report_type_id, target_id, report_reasons_id) 
    VALUES (:reported_by_id, :report_type_id, :target_id, :report_reasons_id)
";

$insert_stmt = $conn->prepare($insert_sql);
$result = $insert_stmt->execute([
    ':reported_by_id' => $reported_by_id,
    ':report_type_id' => $report_type_id,
    ':target_id' => $target_id,
    ':report_reasons_id' => $report_reasons_id,
]);

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => $lang === 'ar' ? 'تم إرسال البلاغ بنجاح' : 'Report submitted successfully',
        'report_id' => $conn->lastInsertId()
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $lang === 'ar' ? 'فشل في إرسال البلاغ' : 'Failed to submit report'
    ]);
}
exit;
