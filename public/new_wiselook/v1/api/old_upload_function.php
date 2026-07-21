<?php
// upload_function.php
function uploadImage($inputName = 'image', $uploadDir = 'uploads') {
  ini_set('display_errors', 1);
  error_reporting(E_ALL);

  // Corrected upload directory path
  $uploadDirectory = dirname(__DIR__) . '/' . $uploadDir;

  if (!is_dir($uploadDirectory)) {
      mkdir($uploadDirectory, 0777, true);
  }

  if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
      $fileTmpPath = $_FILES[$inputName]['tmp_name'];
      $originalName = $_FILES[$inputName]['name'];

      $fileInfo = pathinfo($originalName);
      $baseName = $fileInfo['filename'];
      $extension = isset($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '';

      $newFileName = $baseName . $extension;
      $destinationPath = $uploadDirectory . "/" . $newFileName;

      $counter = 1;
      while (file_exists($destinationPath)) {
          $newFileName = $baseName . "($counter)" . $extension;
          $destinationPath = $uploadDirectory . "/" . $newFileName;
          $counter++;
      }

      if (move_uploaded_file($fileTmpPath, $destinationPath)) {
          return [
              'status' => 'success',
              'message' => 'Image uploaded successfully.',
              'file_name' => $newFileName,
              'file_path' => $uploadDir . '/' . $newFileName,
          ];
      } else {
          return [
              'status' => 'error',
              'message' => 'Failed to move uploaded file.',
          ];
      }
  } else {
      return [
          'status' => 'error',
          'message' => 'No image file uploaded or file upload error.',
      ];
  }
}

