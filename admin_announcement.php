<?php
    session_start();
    include('includes/dbh.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'alert', 'msg' => 'Unauthorized access! Please log in.'];
    header("Location: login.php");
    exit();
} 

$sql = "SELECT id, announcement_text, announcement_image, created_at, is_visible FROM tblannouncements ORDER BY created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    echo "Error: " . $conn->error;
} else {
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/announcement.css">
    <link rel="stylesheet" href="css/popup.css">
    <script defer src="javascript/popup.js"></script>
    <title>Admin Announcements</title>
</head>
<body>
<?php if (isset($_SESSION['popup'])): ?>
<script>
    window.onload = function() {
        showPopup('<?= $_SESSION['popup']['type'] ?>', '<?= $_SESSION['popup']['msg'] ?>');
    };
</script>
<?php unset($_SESSION['popup']); endif; ?>
<div id="popup-container" class="popup-container"></div>

    <?php include('header.php'); ?>
    <div class="main-container">
        <?php include('admin_sidebar.html'); ?>
    </div>
    <div class="container">
        <h2>📢 Admin Announcements</h2>

        <form action="includes/save_announcement.php" method="POST" enctype="multipart/form-data">
            <h3>Add New Announcement</h3>
            <textarea name="announcement_text" required placeholder="Enter announcement here..."></textarea><br>
            <input type="file" name="announcement_image" accept="image/*"><br>
            <button type="submit">Post Announcement</button>
        </form>

        <table class="announcement-table">
    <thead>
        <tr>
            <th>Announcement</th>
            <th>Image</th>
            <th>Date</th>
            <th>Visibility</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="announcement-text">helloooooo</td>
            <td>
                <img src='https://i.pinimg.com/736x/80/23/48/8023488a5b2223e0744e8e8a4a9f2060.jpg' alt='Announcement Image' class='announcement-img' style="max-width: 100px; max-height: 100px;">
            </td>
            <td><?php echo date("F j, Y, g:i a"); ?></td>
            <td>
                <form method="POST" action="includes/toggle_announcement.php">
                    <input type="hidden" name="announcement_id" value="1">
                    <button type="submit" name="toggle_visibility" class="toggle-btn" style="background: #28a745; color: white;">
                        Visible
                    </button>
                </form>
            </td>
            <td class="announcement-actions">
                <a href='edit_announcement.php?id=1' class='edit-btn'>Edit</a>
                <a href='includes/delete_announcement.php?id=1'>Delete</a>
            </td>
        </tr>
        <tr>
            <td class="announcement-text">DEFENSE NAAAA!!!</td>
            <td>
                <span class="no-image">No Image</span>
            </td>
            <td><?php echo date("F j, Y, g:i a"); ?></td>
            <td>
                <form method="POST" action="includes/toggle_announcement.php">
                    <input type="hidden" name="announcement_id" value="2">
                    <button type="submit" name="toggle_visibility" class="toggle-btn" style="background: #dc3545; color: white;">
                        Hidden
                    </button>
                </form>
            </td>
            <td class="announcement-actions">
                <a href='edit_announcement.php?id=2' class='edit-btn'>Edit</a>
                <a href='includes/delete_announcement.php?id=2'>Delete</a>
            </td>
        </tr>
    </tbody>
</table>
    </div>
    <script src="javascript/sidebar-toggle.js"></script>
</body>
</html>