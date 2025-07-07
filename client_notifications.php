<?php
    session_start();
    include('includes/dbh.php');
    include('includes/get_notifications.php');
    include('includes/mark_notification_read.php');
    include('includes/mark_all_notifications_read.php');
    
    if (!isset($_SESSION['role']) || !isset($_SESSION['client_id'])) {
        header('Location: login.php');
        exit();
    }
    
    $role = $_SESSION['role'];  
    $client_id = $_SESSION['client_id'];
    
    $table = '';
    if ($role === 'Admin') {
        $table = 'tbladmin_notifications';
    } elseif ($role === 'Client') {
        $table = 'tblclient_notifications';
    } elseif ($role === 'Staff') {
        $table = 'tblstaff_notifications';
    } else {
        header('Location: login.php');
        exit();
    }
    
    if ($role === 'client') {
        $stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM $table WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->bind_param('i', $client_id);
    } else {
        $stmt = $conn->prepare("SELECT id, message, is_read, created_at FROM $table WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param('i', $client_id);
    }
    
    $stmt->execute();
    $notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notifications</title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="javascript/notification.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            width: 90%;
            max-width: 700px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
        }

        h2 {
            text-align: center;
            color: #D84040;
            margin-bottom: 20px;
        }

        .notification-card {
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            background-color: #fafafa;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.05);
            position: relative;
            transition: background-color 0.3s ease;
        }

        .notification-card.unread {
            background-color: #ffe9e9;
        }

        .notification-message {
            font-size: 16px;
            color: #333;
        }

        .notification-time {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }

        .notification-badge {
            position: absolute;
            top: 15px;
            right: 20px;
            background-color: #D84040;
            color: white;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>

<?php include('header.php'); ?>

<div class="container">
    <h2>🔔 My Notifications</h2>
    <?php if ($notifications->num_rows > 0): ?>
        <?php while($row = $notifications->fetch_assoc()): ?>
            <div class="notification-card <?= $row['is_read'] == 0 ? 'unread' : '' ?>">
                <div class="notification-message"><?= htmlspecialchars($row['message']) ?></div>
                <div class="notification-time"><?= timeAgo($row['created_at']) ?></div>
                <?php if ($row['is_read'] == 0): ?>
                    <div class="notification-badge">New</div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align: center; color: #888;">No notifications yet. 📭</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
    function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
    
        if ($difference < 60) {
            return "Just now";
        } elseif ($difference < 3600) {
            return floor($difference / 60) . " minutes ago";
        } elseif ($difference < 86400) {
            return floor($difference / 3600) . " hours ago";
        } elseif ($difference < 604800) {
            return floor($difference / 86400) . " days ago";
        } else {
            return date('F j, Y', $timestamp);
        }
    }
    
    $conn->close();
?>