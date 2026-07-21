<?php
// post/upload_chunk.php
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

$response = [
    'success' => false,
    'message' => ''
];

try {
    // Get chunk data
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $fileName = isset($_POST['file_name']) ? $_POST['file_name'] : null;
    $chunkIndex = isset($_POST['chunk_index']) ? (int)$_POST['chunk_index'] : null;
    $totalChunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : null;
    $totalSize = isset($_POST['total_size']) ? (int)$_POST['total_size'] : null;
    
    if (!$userId || !$fileName || $chunkIndex === null || !$totalChunks) {
        throw new Exception('Missing required parameters');
    }
    
    if (!isset($_FILES['chunk']) || $_FILES['chunk']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Chunk upload failed');
    }
    
    // Create temp directory for chunks
    $tempDir = __DIR__ . '/../uploads/temp/';
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0755, true);
    }
    
    // Create user-specific temp directory
    $userTempDir = $tempDir . $userId . '/';
    if (!file_exists($userTempDir)) {
        mkdir($userTempDir, 0755, true);
    }
    
    // Save chunk
    $chunkPath = $userTempDir . $fileName . '.part' . $chunkIndex;
    if (!move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath)) {
        throw new Exception('Failed to save chunk');
    }
    
    // Check if all chunks are uploaded
    $uploadedChunks = 0;
    for ($i = 0; $i < $totalChunks; $i++) {
        if (file_exists($userTempDir . $fileName . '.part' . $i)) {
            $uploadedChunks++;
        }
    }
    
    // If all chunks uploaded, merge them
    if ($uploadedChunks === $totalChunks) {
        $finalDir = __DIR__ . '/../uploads/';
        if (!file_exists($finalDir)) {
            mkdir($finalDir, 0755, true);
        }
        
        $finalPath = $finalDir . $fileName;
        $finalFile = fopen($finalPath, 'wb');
        
        if (!$finalFile) {
            throw new Exception('Failed to create final file');
        }
        
        // Merge all chunks
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkFile = $userTempDir . $fileName . '.part' . $i;
            $chunk = fopen($chunkFile, 'rb');
            
            if (!$chunk) {
                fclose($finalFile);
                throw new Exception('Failed to read chunk ' . $i);
            }
            
            stream_copy_to_stream($chunk, $finalFile);
            fclose($chunk);
            
            // Delete chunk after merging
            unlink($chunkFile);
        }
        
        fclose($finalFile);
        
        // Clean up temp directory
        rmdir($userTempDir);
        
        $response['success'] = true;
        $response['message'] = 'File uploaded and merged successfully';
        $response['file_name'] = $fileName;
        $response['file_size'] = filesize($finalPath);
        $response['is_complete'] = true;
    } else {
        $response['success'] = true;
        $response['message'] = 'Chunk ' . ($chunkIndex + 1) . '/' . $totalChunks . ' uploaded';
        $response['is_complete'] = false;
        $response['uploaded_chunks'] = $uploadedChunks;
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
exit;