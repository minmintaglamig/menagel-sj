<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');

if (!isset($_SESSION['user_email']) || $_SESSION['role'] !== 'Client') {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header('Location: login.php');
    exit();
}

$email = $_SESSION['user_email'];
    
    $client_check_query = "SELECT client_id FROM tblclientlist WHERE email = ?";
    $stmt = $conn->prepare($client_check_query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $client_result = $stmt->get_result();
    $client = $client_result->fetch_assoc();
    
    $show_apply_button = !$client;
    
    if (!$show_apply_button) {
        $client_query = "SELECT c.client_id, c.fname, c.mname, c.lname, c.routernumber 
                         FROM tblclientlist c
                         WHERE c.email = ?";
        $stmt = $conn->prepare($client_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $client_result = $stmt->get_result();
        $client = $client_result->fetch_assoc();
    
        if (!$client) {
            echo "Client not found.";
            exit();
        }
    
        $fname = decryptData($client['fname']);
        $mname = decryptData($client['mname']);
        $lname = decryptData($client['lname']);
        $router_number = decryptData($client['routernumber'] ?? '');
        $full_name = $fname . ' ' . ($mname ? $mname . ' ' : '') . $lname;
        $client_id = $client['client_id'] ?? null;
    
        $install_query = "SELECT install_date FROM tblinstallations WHERE client_id = ?";
        $stmt = $conn->prepare($install_query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $install_result = $stmt->get_result();
        $install = $install_result->fetch_assoc();
        $install_date = $install['install_date'] ?? 'Not Available';
        $install_date_display = ($install_date && $install_date !== 'Not Available') 
            ? date("M d, Y", strtotime($install_date)) 
            : 'Not Available';
    
        $billing_query = "SELECT p.promo_name, p.amount, b.due_date, b.status 
                          FROM tblpromo_subscribers ps
                          JOIN tblpromo p ON ps.promo_id = p.promo_id
                          JOIN tblclientlist c ON ps.client_id = c.client_id
                          LEFT JOIN tblbilling b ON b.routernumber = c.routernumber
                          WHERE c.client_id = ? 
                          ORDER BY b.due_date DESC LIMIT 1";
        $stmt = $conn->prepare($billing_query);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $billing_result = $stmt->get_result();
        $billing = $billing_result->fetch_assoc();
    
        $appointment_query = "SELECT schedule_date, schedule_time, 
                              (SELECT CONCAT(fname, ' ', lname) FROM tblstafflist WHERE id = t.assigned_staff) AS staff_name
                              FROM tbltickets t
                              WHERE routernumber = ? AND status = 'Scheduled' 
                              ORDER BY schedule_date ASC";
        $stmt = $conn->prepare($appointment_query);
        $stmt->bind_param("s", $router_number);
        $stmt->execute();
        $appointment_result = $stmt->get_result();
    
        $appointments = [];
        while ($row = $appointment_result->fetch_assoc()) {
            $appointments[] = $row;
        }
    
        $promo_name = $billing['promo_name'] ?? 'N/A';
        $due_date = $billing['due_date'] ?? 'Not Set';
        $amount_due = $billing['amount'] ?? '0';
    
        $appointment_display = !empty($appointments) 
            ? $appointments[0]['schedule_date'] . " at " . $appointments[0]['schedule_time'] 
            : "No upcoming appointment";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/popup.css">
    <link rel="stylesheet" href="css/client.css">
    <script defer src="javascript/popup.js"></script>
    <script src="javascript/calendar_client.js"></script>
    <script>
        const appointments = <?php echo json_encode($appointments); ?>;
    </script>
    <title>Client Dashboard</title>
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
    <?php include('client_sidebar.html'); ?>
    <div class="dashboard-wrapper">
        <div class="content">
            <div class="dashboard-section">
                <h2>Account Details</h2>
                <p><strong>Full Name:</strong> <?= htmlspecialchars($full_name) ?></p>
                <p><strong>Router Number:</strong> <?= !empty($router_number) ? htmlspecialchars($router_number) : 'Not Assigned Yet' ?></p>
                <p><strong>Installation Date:</strong> <?= htmlspecialchars($install_date_display) ?></p>
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
                        <h2>Upcoming Appointments</h2>
                        <div id="custom-calendar">
                            <div id="calendar-header">
                                <button id="prev-month">❮</button>
                                <h3 id="month-year"></h3>
                                <button id="next-month">❯</button>
                            </div>
                            <div id="calendar-body"></div>
                        </div>
                    </div>
                    <div class="dashboard-section">
                        <h2>Billing Details</h2>
                        <p><strong>Promo Plan:</strong> <?= htmlspecialchars($promo_name) ?></p>
                        <p><strong>Amount Due:</strong> <?= htmlspecialchars($amount_due) ?> PHP</p>
                        <p><strong>Due Date:</strong> <?= htmlspecialchars($due_date) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('faq_widget.php'); ?>

<script src="javascript/sidebar-toggle.js"></script>
</body>
</html>

<?php $conn->close(); ?>