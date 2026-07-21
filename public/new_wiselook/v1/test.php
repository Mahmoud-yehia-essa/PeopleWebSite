<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = dirname(__DIR__) . '/code/wise/uploads/'; // one level up to /code then into /wise/uploads/
    $targetFile = $targetDir . basename($_FILES['image']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Create the uploads directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Check if image file is a actual image
    $check = getimagesize($_FILES['image']['tmp_name']);
    if ($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Upload file if everything is ok
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            echo "The file " . htmlspecialchars(basename($_FILES['image']['name'])) . " has been uploaded.";
            echo "<br>Access it at: <a href='/wise/uploads/" . htmlspecialchars(basename($_FILES['image']['name'])) . "'>View File</a>";
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Upload Test</title>
</head>
<body>
    <h2>Upload Image Test</h2>
    <form action="test.php" method="post" enctype="multipart/form-data">
        Select image to upload:
        <input type="file" name="image" id="image" accept="image/*">
        <input type="submit" value="Upload Image" name="submit">
    </form>
</body>
</html>
