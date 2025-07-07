<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];

$staff_query = "SELECT * FROM tblstafflist WHERE email = ?";
$stmt = $conn->prepare($staff_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$staff_result = $stmt->get_result();
$staff = $staff_result->fetch_assoc();

if (!$staff) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Staff not found. Please contact admin.'];
    header("Location: login.php");
    exit();
}

$id = $staff['id'];

$decrypted_fname = decryptData($staff['fname']);
$decrypted_lname = decryptData($staff['lname']);
$decrypted_email = decryptData($staff['email']);

if ($staff['status'] !== 'Active') {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Your staff status is inactive. Please contact admin.'];
    header("Location: login.php");
    exit();
}

$install_query = "
    SELECT a.application_id, a.client_id, c.fname, c.lname, a.approved_at
    FROM tblapprove a
    JOIN tblclientlist c ON a.client_id = c.client_id
    WHERE a.staff_id = ? 
    AND a.approval_status = 'Approved' 
    ORDER BY a.approved_at DESC";
$stmt = $conn->prepare($install_query);
$stmt->bind_param("i", $id);
$stmt->execute();
$install_result = $stmt->get_result();

$installations = [];
while ($row = $install_result->fetch_assoc()) {
    $row['client_name'] = decryptData($row['fname']) . ' ' . decryptData($row['lname']);
    $installations[] = $row;
}

$appt_query = "
    SELECT schedule_date, schedule_time, fname, lname, concern_type
    FROM tbltickets 
    WHERE assigned_staff = ? 
    AND status = 'Scheduled' 
    ORDER BY schedule_date ASC";
$stmt = $conn->prepare($appt_query);
$stmt->bind_param("i", $id);
$stmt->execute();
$appt_result = $stmt->get_result();

$appointments = [];
while ($row = $appt_result->fetch_assoc()) {
    $row['fname'] = decryptData($row['fname']);
    $row['lname'] = decryptData($row['lname']);
    $appointments[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/cards.css">
    <link rel="stylesheet" href="css/popup.css">
    <script src="javascript/popup.js"></script>
    <title>Staff Dashboard</title>
    <style>
.dashboard-grid {
  display: flex !important;
  justify-content: space-between !important;
  gap: 20px !important;
}

.dashboard-left,
.dashboard-right {
  flex: 1 !important;
  background-color: #fff !important;
  padding: 20px !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

.dashboard-section {
  padding: 20px !important;
  margin-bottom: 20px !important;
  background-color: #f9f9f9 !important;
  border-radius: 8px !important;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
  box-sizing: border-box;
  width: 100% !important;
}

@media (max-width: 768px) {
  .dashboard-grid {
    flex-direction: column !important;
    align-items: center !important; 
    width: 100% !important;
    padding: 20px !important; 
    margin: 0 auto !important;
    box-sizing: border-box;
  }

  .dashboard-left,
  .dashboard-right {
    width: 100% !important; 
    margin-bottom: 20px !important;
  }

  .dashboard-right {
    margin-bottom: 0 !important;
  }

  .dashboard-section {
    padding: 0 !important;
    margin: 0 !important; 
    width: 100% !important; 
    max-width: 100% !important; 
  }

  #custom-calendar {
    width: 100% !important; 
    padding: 0 !important;
    margin: 0 !important;  
    box-sizing: border-box;
  }

  #calendar-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 20px !important;
    padding: 0 10px !important;  
  }

  #calendar-body {
    max-height: none !important;  
    overflow: auto !important;  
    width: 100% !important;  
  }


  .client-announcement {
    width: 100% !important; 
    padding: 0 !important;  
    margin: 0 !important;   
    box-sizing: border-box;
  }

  .client-announcement ul {
    list-style-type: none !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .client-announcement li {
    padding: 10px 0 !important;
    border-bottom: 1px solid #ddd !important;
  }

  .client-announcement a {
    color: #6D2323 !important;
    text-decoration: none !important;
  }

  .client-announcement a:hover {
    text-decoration: underline !important;
  }
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
        <?php include('staff_sidebar.html'); ?>

        <div class="content">
            <div class="dashboard-section">
                <h2>Staff Details</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($decrypted_fname . ' ' . $decrypted_lname) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($decrypted_email) ?></p>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-left">
                    <div class="dashboard-section">
                        <h2>Announcements</h2>
                        <?php include('client_announcement.php'); ?>
                    </div>
                </div>

                <div class="dashboard-right">
                    <div class="dashboard-section">
                        <h2>Assigned Installations</h2>
                        <div class="application-container">
                            <?php if (!empty($installations)): ?>
                                <?php foreach ($installations as $install): ?>
                                    <div class="application-card">
                                        <h3><?= htmlspecialchars($install['client_name']) ?></h3>
                                        <span class="app-status">Approved on <?= date("M d, Y", strtotime($install['approved_at'])) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No assigned installations.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dashboard-section">
                        <h2>Upcoming Appointments</h2>
                        <div class="ticket-container">
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $appt): ?>
                                    <div class="ticket">
                                        <div class="ticket-date"><?= date("M d, Y", strtotime($appt['schedule_date'])) ?></div>
                                        <div class="ticket-time"><?= date("h:i A", strtotime($appt['schedule_time'])) ?></div>
                                        <p>Client: <?= htmlspecialchars($appt['fname'] . ' ' . $appt['lname']) ?></p>
                                        <span class="concern-type">Concern: <?= htmlspecialchars($appt['concern_type']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No upcoming appointments.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>