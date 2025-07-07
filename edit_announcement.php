<?php
    session_start();
    include('includes/dbh.php');
    
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        header("Location: admin_announcements.php");
        exit();
    }
    
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM tblannouncements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $announcement = $result->fetch_assoc();
    
    if (!$announcement) {
        header("Location: admin_announcements.php");
        exit();
    }
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $announcement_text = $conn->real_escape_string($_POST['announcement_text']);
        $announcement_image = $announcement['announcement_image'];
    
        if (!empty($_FILES['announcement_image']['name'])) {
            $target_dir = "uploads/";
            $image_name = basename($_FILES["announcement_image"]["name"]);
            $target_file = $target_dir . time() . "_" . $image_name;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
            $allowed_types = ["jpg", "jpeg", "png", "gif"];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["announcement_image"]["tmp_name"], $target_file)) {
                    $announcement_image = time() . "_" . $image_name;
                }
            }
        }
    
        $update_sql = "UPDATE tblannouncements SET announcement_text = ?, announcement_image = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $announcement_text, $announcement_image, $id);
    
        if ($update_stmt->execute()) {
            $_SESSION['popup'] = [
                'type' => 'success',
                'msg' => 'Announcement updated successfully.'
            ];
            header("Location: admin_announcement.php");
            exit();
        } else {
            $_SESSION['popup'] = [
                'type' => 'error',
                'msg' => 'Failed to update announcement.'
            ];
            header("Location: admin_announcement.php");
            exit();
        }
        
    }
    
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <link rel="stylesheet" href="css/announcement.css">
    <script defer src="javascript/script.js"></script>
    <title>Edit Announcement</title>
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

    <div class="container">
        <form action="" method="POST" enctype="multipart/form-data" class="edit-announcement-form">
            <label for="announcement_text">Announcement Text:</label>
            <textarea id="announcement_text" name="announcement_text" required><?php echo htmlspecialchars($announcement['announcement_text']); ?></textarea>

            <label for="announcement_image">Upload Image (Optional):</label>
            <input type="file" id="announcement_image" name="announcement_image" accept="image/*">

            <?php if (!empty($announcement['announcement_image'])): ?>
                <p>Current Image:</p>
                <img src="uploads/<?php echo htmlspecialchars($announcement['announcement_image']); ?>" alt="Current Image" class="edit-announcement-img-preview">
            <?php endif; ?>

            <button type="submit">Update Announcement</button>
        </form>
    </div>
    <script src="javascript/sidebar-toggle.js"></script>
</body>
</html>