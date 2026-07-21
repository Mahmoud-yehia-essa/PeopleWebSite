<?php
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

// Include upload functions if needed
require_once __DIR__ . '/../../../upload_function.php';

$baseUrl = 'https://worldwisepeople.net/new_wiselook/v1/api/chat/uploads/';

$response = [
    'success' => false,
    'message' => '',
    'media_name' => [],
    'errors' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Invalid request method';
        echo json_encode($response);
        exit;
    }

    if (empty($_FILES['media'])) {
        $response['message'] = 'No media file uploaded';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['media'];

    $allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv',
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain', 'application/octet-stream'
    ];

    $maxFileSize = 50 * 1024 * 1024; // 50MB

    $validFiles = [
        'name' => [],
        'type' => [],
        'tmp_name' => [],
        'error' => [],
        'size' => []
    ];

    if (!is_array($file['name'])) {
        $file = [
            'name' => [$file['name']],
            'type' => [$file['type']],
            'tmp_name' => [$file['tmp_name']],
            'error' => [$file['error']],
            'size' => [$file['size']]
        ];
    }

    foreach ($file['name'] as $key => $name) {
        $fileType = $file['type'][$key];
        $fileSize = $file['size'][$key];
        $fileError = $file['error'][$key];

        if ($fileError !== UPLOAD_ERR_OK) {
            $response['errors'][] = ['filename' => $name, 'error' => 'Upload error code: ' . $fileError];
            continue;
        }

        if (!in_array($fileType, $allowedTypes)) {
            $response['errors'][] = ['filename' => $name, 'error' => 'File type not allowed: ' . $fileType];
            continue;
        }

        if ($fileSize > $maxFileSize) {
            $response['errors'][] = ['filename' => $name, 'error' => 'File too large (max 50MB)'];
            continue;
        }

        $validFiles['name'][] = $name;
        $validFiles['type'][] = $fileType;
        $validFiles['tmp_name'][] = $file['tmp_name'][$key];
        $validFiles['error'][] = $fileError;
        $validFiles['size'][] = $fileSize;
    }

    if (empty($validFiles['name'])) {
        $response['message'] = 'No valid files to upload';
        echo json_encode($response);
        exit;
    }

    $uploadedMedia = [];

    foreach ($validFiles['name'] as $key => $name) {
        $tmpName = $validFiles['tmp_name'][$key];
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $isImage = in_array($extension, ['jpg','jpeg','png','gif','webp']);
        $isVideo = in_array($extension, ['mp4','mov','avi','mkv','webm']);
        $isFile = !$isImage && !$isVideo;

        if ($isImage) $uploadDir = __DIR__ . '/uploads/images/';
        elseif ($isVideo) $uploadDir = __DIR__ . '/uploads/videos/';
        else $uploadDir = __DIR__ . '/uploads/files/';

        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $targetPath = $uploadDir . basename($name);

        if (move_uploaded_file($tmpName, $targetPath)) {
            // full URL
            $urlPath = $baseUrl;
            if ($isImage) $urlPath .= 'images/';
            elseif ($isVideo) $urlPath .= 'videos/';
            else $urlPath .= 'files/';

            $uploadedMedia[] = [
                'name' => $urlPath . basename($name),
                'type' => $isImage ? 'image' : ($isVideo ? 'video' : 'file')
            ];
        } else {
            $response['errors'][] = ['filename' => $name, 'error' => 'Failed to move uploaded file'];
        }
    }

    if (!empty($uploadedMedia)) {
        $response['success'] = true;
        $response['media'] = $uploadedMedia;
    } else {
        $response['message'] = 'No files uploaded successfully';
    }

    echo json_encode($response);

} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    $response['errors'][] = ['type'=>'server_error','error'=>$e->getMessage()];
    echo json_encode($response);
}
?>
