<?php
// services/dictionary.php
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include_once __DIR__ . '/../db.php';
include_once __DIR__ . '/../config.php';

// Read and decode input JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['lang']) || empty(trim($input['lang']))) {
    echo json_encode([
        'success' => false,
        'message' => 'Language code is required'
    ]);
    exit;
}

$lang = strtolower(trim($input['lang'])); // sanitize
$allowed_langs = ['en', 'ar'];

if (!in_array($lang, $allowed_langs)) {
    echo json_encode([
        'success' => false,
        'message' => 'Unsupported language'
    ]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch all dictionary entries for the requested language
$query = "SELECT `key`, `$lang` AS value FROM dictionary";

$stmt = $conn->prepare($query);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dictionary = [];

foreach ($rows as $row) {
    $dictionary[$row['key']] = $row['value'];
}

echo json_encode([
    'success' => true,
    'message' => 'Dictionary loaded successfully',
    'language' => $lang,
    'dictionary' => $dictionary
]);
