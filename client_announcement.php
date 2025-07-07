<?php
    include('includes/dbh.php');

    $result = $conn->query("SELECT * FROM tblannouncements WHERE is_visible = 1 ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="pictures/logo.png" type= "image">
    <link rel="stylesheet" href="css/announcement.css">
    <title>Announcements</title>
</head>
<body>
    <div class="container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="post">
                <p><strong><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?> </strong></p>
                <p><i>"<?php echo nl2br(htmlspecialchars($row['announcement_text'])); ?>"</i></p>

                <?php if (!empty($row['announcement_image']) && file_exists("uploads/" . $row['announcement_image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['announcement_image']); ?>" alt="Announcement Image" class="announcement-img">
                <?php endif; ?>

                <hr>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>