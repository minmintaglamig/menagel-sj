<?php
$uploadDir = 'uploads/test/';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['test_image']['tmp_name'];
        $fileName = basename($_FILES['test_image']['name']);
        $destPath = $uploadDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $message = "✅ Image uploaded successfully!";
        } else {
            $message = "❌ Error moving uploaded file.";
        }
    } else {
        $message = "❌ Error: No file uploaded or upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Image Upload</title>
</head>
<body>
    <h2>Upload a Test Image</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="test_image" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>

    <p><?= htmlspecialchars($message) ?></p>

    <h3>Uploaded Images:</h3>
    <div>
        <?php
        $images = glob($uploadDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
        if ($images):
            foreach ($images as $imgPath):
                $imgUrl = htmlspecialchars($imgPath);
        ?>
            <div style="margin:10px 0;">
                <img src="<?= $imgUrl ?>" width="200" alt="Uploaded Image">
                <p><?= $imgUrl ?></p>
            </div>
        <?php
            endforeach;
        else:
            echo "<p>No images found.</p>";
        endif;
        ?>
    </div>
</body>
</html>