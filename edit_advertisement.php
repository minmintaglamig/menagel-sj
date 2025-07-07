<?php
session_start();
include('includes/dbh.php');

if (!isset($_SESSION['user_email'])) {
    die("Unauthorized access!");
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No advertisement selected.");
}

$ad_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT title, content, image FROM tbladvertisement WHERE id = ?");
$stmt->bind_param("i", $ad_id);
$stmt->execute();
$stmt->bind_result($title, $content, $existing_image);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_ad'])) {
    $new_title = $_POST['title'];
    $new_content = $_POST['content'];
    $new_image = $_FILES['image'];

    $image_name = $existing_image;

    if (!empty($new_image['name'])) {
        $upload_dir = "uploads/advertisement/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $image_name = time() . "_" . basename($new_image['name']);
        $target_path = $upload_dir . $image_name;

        if (move_uploaded_file($new_image['tmp_name'], $target_path)) {
            if (!empty($existing_image) && file_exists($upload_dir . $existing_image)) {
                unlink($upload_dir . $existing_image);
            }
        } else {
            die("Error: Failed to upload new image.");
        }
    }

    $stmt = $conn->prepare("UPDATE tbladvertisement SET title = ?, content = ?, image = ? WHERE id = ?");
    $stmt->bind_param("sssi", $new_title, $new_content, $image_name, $ad_id);

    if ($stmt->execute()) {
        $_SESSION['popup'] = [
            'type' => 'success',
            'msg' => 'Advertisement updated successfully.'
        ];
        header("Location: admin_advertisement.php");
        exit();
    } else {
        die("Error updating advertisement: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <title>Edit Advertisement</title>
    <style>
        form {
            max-width: 500px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            margin-bottom: 10px;
            padding: 8px;
        }
        button {
            padding: 10px 20px;
        }
        #updateBtn:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<?php
if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}
if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

<?php include('header.php'); ?>
<div class="main-container">
    <?php include('admin_sidebar.html'); ?>
</div>

<h1>Edit Advertisement</h1>

<form action="" method="POST" enctype="multipart/form-data">
    <label>Title:</label>
    <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" required>

    <label>Content:</label>
    <textarea name="content" required><?= htmlspecialchars($content) ?></textarea>

    <label>Current Image:</label><br>
    <?php if (!empty($existing_image)): ?>
        <img src="uploads/advertisement/<?= htmlspecialchars($existing_image) ?>" style="max-width:200px;"><br>
    <?php else: ?>
        <p>No image available.</p>
    <?php endif; ?>

    <label>Upload New Image (optional):</label>
    <input type="file" name="image" accept="image/*">

    <button type="submit" name="edit_ad">Update Advertisement</button>
</form>

</body>
</html>