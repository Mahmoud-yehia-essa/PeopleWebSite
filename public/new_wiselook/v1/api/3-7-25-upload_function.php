<?php
// upload_function.php
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

include_once __DIR__ . '/config.php';

/**
 * Handles file uploads to external server
 * @param array $files Files to upload (can be single file or multiple files)
 * @param array $additionalFields Additional POST fields to send
 * @return array Result of the upload attempt
 */
function uploadToExternalServer($files, $additionalFields = []) {
    global $uploadFunction;
    
    $response = [
        'success' => false,
        'message' => '',
        'media_name' => [],
        'errors' => []
    ];

    if (empty($files)) {
        $response['message'] = 'No files provided for upload';
        return $response;
    }

    // Prepare files for upload
    $postFields = $additionalFields;
    $fileCount = 0;

    // Handle single file
    if (!is_array($files['name'])) {
        $files = [
            'name' => [$files['name']],
            'type' => [$files['type']],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']]
        ];
    }

    // Process each file
    foreach ($files['name'] as $key => $name) {
        if ($files['error'][$key] !== UPLOAD_ERR_OK) {
            $response['errors'][] = [
                'filename' => $name,
                'error' => getUploadError($files['error'][$key])
            ];
            continue;
        }

        $fileKey = (count($files['name']) > 1) ? 'media[]' : 'media';
        $postFields[$fileKey] = new CURLFile(
            $files['tmp_name'][$key],
            $files['type'][$key],
            $name
        );
        $fileCount++;
    }

    if ($fileCount === 0) {
        $response['message'] = 'No valid files to upload';
        return $response;
    }

    // Initialize cURL for external upload
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uploadFunction);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 seconds

    $uploadResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200) {
        $response['message'] = 'Failed to upload media to external server';
        $response['errors'][] = [
            'http_code' => $httpCode,
            'curl_error' => $curlError
        ];
        return $response;
    }

    $uploadData = json_decode($uploadResponse, true);

    if (!$uploadData || !isset($uploadData['success'])) {
        $response['message'] = 'Invalid response from upload server';
        return $response;
    }

    if (!$uploadData['success']) {
        $response['message'] = $uploadData['message'] ?? 'Media upload failed';
        $response['errors'] = $uploadData['errors'] ?? [];
        return $response;
    }

    // Return successful response
    $response['success'] = true;
    $response['message'] = $uploadData['message'] ?? 'Files uploaded successfully';
    $response['media_name'] = $uploadData['media_name'] ?? [];
    return $response;
}

/**
 * Translates upload error codes to human-readable messages
 */
function getUploadError($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds MAX_FILE_SIZE directive in HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by PHP extension';
        default:
            return 'Unknown upload error';
    }
}

// If this file is called directly (for testing)
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $files = $_FILES['media'] ?? [];
        $additionalFields = $_POST;
        
        $result = uploadToExternalServer($files, $additionalFields);
        echo json_encode($result);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
    }
    exit;
}