<?php
$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

$notification_page = "";
$unread_count = 0;

if ($role == "Admin") {
    $notification_page = "admin_notifications.php";  
} elseif ($role == "Client") {
    $notification_page = "client_notifications.php";
} elseif ($role == "Staff") {
    $notification_page = "staff_notifications.php";
}

if ($role && $user_id) {
    include('includes/dbh.php');
    $table = '';
    if ($role == 'Admin') {
        $table = 'tbladmin_notifications';
    } elseif ($role == 'Client') {
        $table = 'tblclient_notifications';
    } elseif ($role == 'Staff') {
        $table = 'tblstaff_notifications';
    }

    $stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM $table WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $unread_count = $row['unread_count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/header.css">
</head>
<body>
    <div class="header">
        <button class="menu-btn" id="sidebarToggle" onclick="toggleSidebar()">
            <i class="bx bx-menu"></i>
        </button>

        <div class="logo">
            <img src="pictures/logo.png" alt="Logo"> 
        </div>

        <div class="company-name">Menagel SJ Internet Services</div>

        <div class="notification-container">
            <a href="javascript:void(0);" class="notification-btn" id="notificationBell">
                <i class='bx bx-bell'></i>
                <span id="notification-count" class="notification-count"><?= $unread_count ?></span>
            </a>

            <div id="notification-dropdown" class="notification-dropdown" style="display: none;">
                <div id="notification-list">
                </div>
                <div style="text-align: center; margin-top: 10px;">
                    <button id="mark-all-read-btn" class="mark-all-read-btn">Mark All as Read</button>
                </div>
            </div>
        </div>

        <a href="#" class="logout-btn" onclick="openLogoutModal(event)">
            <i class="bx bx-log-out"></i>
        </a>

    </div>

    <div id="logoutModal" class="modal">
  <div class="modal-content" style="text-align: center;">
    <p>Are you sure you want to logout?</p>
    <div style="margin: 15px 0;">
      <button onclick="confirmLogout()">Yes, Logout</button>
      <button onclick="closeLogoutModal()">Cancel</button>
    </div>

    <hr style="margin: 20px 0;">

    <p>How was your experience today?</p>

    <div id="starRating" style="font-size: 24px; margin-bottom: 10px;">
      <span class="star" data-value="1">&#9733;</span>
      <span class="star" data-value="2">&#9733;</span>
      <span class="star" data-value="3">&#9733;</span>
      <span class="star" data-value="4">&#9733;</span>
      <span class="star" data-value="5">&#9733;</span>
    </div>

    <textarea id="feedbackComment" placeholder="Leave a comment (optional)" rows="3" style="width: 100%; max-width: 300px;"></textarea>

    <br><br>
    <button onclick="submitFeedback()">Submit Feedback</button>
  </div>
</div>

<script>
let selectedRating = 0;

function toggleFeedback() {
    const section = document.getElementById("feedbackSection");
    section.style.display = (section.style.display === "none" || section.style.display === "") ? "block" : "none";
}

document.querySelectorAll('.star').forEach(star => {
    star.addEventListener('click', function () {
        selectedRating = this.dataset.value;
        document.querySelectorAll('.star').forEach(s => {
            s.style.color = (s.dataset.value <= selectedRating) ? '#f39c12' : '#ccc';
        });
    });
});

function submitFeedback() {
    const comment = document.getElementById("feedbackComment").value;

    if (selectedRating === 0) {
        alert("Please select a star rating.");
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "includes/submit_feedback.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (xhr.status === 200) {
            alert("Thanks for your feedback!");
            document.getElementById("feedbackComment").value = "";
            document.querySelectorAll('.star').forEach(s => s.style.color = "#ccc");
            selectedRating = 0;
            document.getElementById("feedbackSection").style.display = "none";
        }
    };
    xhr.send("rating=" + selectedRating + "&comment=" + encodeURIComponent(comment));
}

function openLogoutModal(event) {
    event.preventDefault();
    document.getElementById('logoutModal').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
}

function confirmLogout() {
    window.location.href = "includes/logout.php";
}
</script>
</body>
</html>