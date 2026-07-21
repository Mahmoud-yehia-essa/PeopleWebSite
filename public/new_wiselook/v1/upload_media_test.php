<?php
// upload_media.php on amcserver
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['media'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No files were uploaded'
    ]);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create upload directory'
        ]);
        exit;
    }
}

$uploadedFiles = [];
$errors = [];

// Handle both single and multiple file uploads
if (is_array($_FILES['media']['name'])) {
    // Multiple files
    foreach ($_FILES['media']['name'] as $key => $name) {
        processFileUpload(
            $_FILES['media']['name'][$key],
            $_FILES['media']['tmp_name'][$key],
            $_FILES['media']['error'][$key],
            $_FILES['media']['size'][$key],
            $uploadDir,
            $uploadedFiles,
            $errors
        );
    }
} else {
    // Single file
    processFileUpload(
        $_FILES['media']['name'],
        $_FILES['media']['tmp_name'],
        $_FILES['media']['error'],
        $_FILES['media']['size'],
        $uploadDir,
        $uploadedFiles,
        $errors
    );
}

// Check if any files were uploaded successfully
if (empty($uploadedFiles)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No files were uploaded successfully',
        'errors' => $errors
    ]);
    exit;
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => count($uploadedFiles) . ' file(s) uploaded successfully',
    'media_name' => $uploadedFiles,
    'errors' => $errors
]);

/**
 * Processes a single file upload
 */
function processFileUpload($name, $tmpName, $error, $size, $uploadDir, &$uploadedFiles, &$errors) {
    // Skip if there was an upload error
    if ($error !== UPLOAD_ERR_OK) {
        $errors[] = "File {$name} upload error: " . getUploadError($error);
        return;
    }
    
    // Validate file size (example: 10MB max)
    $maxSize = 10 * 1024 * 1024;
    if ($size > $maxSize) {
        $errors[] = "File {$name} is too large (max 10MB allowed)";
        return;
    }
    
    // Sanitize the filename
    $sanitizedName = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $name);
    $sanitizedName = time() . '_' . $sanitizedName; // Add timestamp to prevent overwrites
    
    // Set the destination path
    $destination = $uploadDir . $sanitizedName;
    
    // Move the uploaded file
    if (move_uploaded_file($tmpName, $destination)) {
        $uploadedFiles[] = $sanitizedName;
    } else {
        $errors[] = "Failed to move uploaded file {$name}";
    }
}

// Helper function to get upload error messages
function getUploadError($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
?>