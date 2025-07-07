<?php
session_start();
include('includes/dbh.php');
include('includes/encryption.php');
require_once('includes/notification_helper.php');

define('DEFAULT_IMG', '/images/default.png');

if (!isset($_SESSION['user_email'])) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header("Location: login.php");
    exit();
}

$email = $_SESSION['user_email'];
$query = "SELECT * FROM tblusers WHERE email = ? AND role = 'admin'";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing query: ' . $conn->error];
    header("Location: login.php");
    exit();
}
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Unauthorized access!'];
    header("Location: login.php");
    exit();
}

$query = "
    SELECT a.*, 
           ap.staff_id, ap.scheduled_date, ap.scheduled_time, ap.approval_status,
           s.fname AS staff_fname, s.mname AS staff_mname, s.lname AS staff_lname,
           i.install_date
    FROM tblapplication a
    LEFT JOIN tblapprove ap ON a.id = ap.application_id
    LEFT JOIN tblstafflist s ON ap.staff_id = s.id
    LEFT JOIN tblinstallations i ON a.id = i.client_id
    ORDER BY a.id DESC
";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing fetch query: ' . $conn->error];
    header("Location: new_application.php");
    exit();
}
$stmt->execute();
$result = $stmt->get_result();

$applications = [];
while ($application = $result->fetch_assoc()) {
    $application['fname'] = safeDecrypt($application['fname']);
    $application['mname'] = !empty($application['mname']) ? safeDecrypt($application['mname']) : '';
    $application['lname'] = safeDecrypt($application['lname']);
    $application['address'] = safeDecrypt($application['address']);
    $application['mobile'] = safeDecrypt($application['mobile']);
    $application['resident_type'] = $application['residenttype'];
    $application['promo'] = $application['promo_name'];

    if (!empty($application['staff_id'])) {
        $application['staff_fname'] = safeDecrypt($application['staff_fname']);
        $application['staff_mname'] = !empty($application['staff_mname']) ? safeDecrypt($application['staff_mname']) : '';
        $application['staff_lname'] = safeDecrypt($application['staff_lname']);
    }

    $application['billing_proof_path'] = $application['billing_proof'];
    $application['valid_id_path'] = $application['valid_id'];

    $applications[] = $application;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['approve_application_id'], $_POST['staff_id'], $_POST['schedule_date'], $_POST['schedule_time'])) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Form data is incomplete. Please make sure all fields are filled out.'];
        header("Location: new_application.php");
        exit();
    }

    $application_id = intval($_POST['approve_application_id']);
    $staff_id = intval($_POST['staff_id']);
    $schedule_date = $_POST['schedule_date'];
    $schedule_time = $_POST['schedule_time'];

    if (empty($schedule_date) || empty($schedule_time)) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Schedule date and time are required.'];
        header("Location: new_application.php");
        exit();
    }

    $client_query = "SELECT client_id FROM tblclientlist WHERE email = (SELECT email FROM tblapplication WHERE id = ?)";
    $client_stmt = $conn->prepare($client_query);
    if ($client_stmt === false) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing client query: ' . $conn->error];
        header("Location: new_application.php");
        exit();
    }
    $client_stmt->bind_param("i", $application_id);
    $client_stmt->execute();
    $client_result = $client_stmt->get_result();

    if ($client_result && $client_result->num_rows > 0) {
        $client_data = $client_result->fetch_assoc();
        $client_id = $client_data['client_id'];
    } else {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Client not found for this application.'];
        header("Location: new_application.php");
        exit();
    }

    $check_stmt = $conn->prepare("SELECT approval_status FROM tblapprove WHERE application_id = ?");
    if ($check_stmt === false) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing approval check: ' . $conn->error];
        header("Location: new_application.php");
        exit();
    }
    $check_stmt->bind_param("i", $application_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing_approval = $check_result->fetch_assoc();

    if ($existing_approval) {
        $update_stmt = $conn->prepare("UPDATE tblapprove SET staff_id = ?, scheduled_date = ?, scheduled_time = ?, approval_status = 'Approved', client_id = ? WHERE application_id = ?");
        if ($update_stmt === false) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing update statement: ' . $conn->error];
            header("Location: new_application.php");
            exit();
        }
        $update_stmt->bind_param("issi", $staff_id, $schedule_date, $schedule_time, $client_id, $application_id);
        if (!$update_stmt->execute()) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating approval: ' . $update_stmt->error];
            header("Location: new_application.php");
            exit();
        }
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO tblapprove (application_id, staff_id, scheduled_date, scheduled_time, client_id, approval_status) VALUES (?, ?, ?, ?, ?, 'Approved')");
        if ($insert_stmt === false) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing insert statement: ' . $conn->error];
            header("Location: new_application.php");
            exit();
        }
        $insert_stmt->bind_param("iisss", $application_id, $staff_id, $schedule_date, $schedule_time, $client_id);
        if (!$insert_stmt->execute()) {
            $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error inserting approval: ' . $insert_stmt->error];
            header("Location: new_application.php");
            exit();
        }
    }

    $update_app_stmt = $conn->prepare("UPDATE tblapplication SET status = 'Approved' WHERE id = ?");
    if ($update_app_stmt === false) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error preparing application update: ' . $conn->error];
        header("Location: new_application.php");
        exit();
    }
    $update_app_stmt->bind_param("i", $application_id);
    if (!$update_app_stmt->execute()) {
        $_SESSION['popup'] = ['type' => 'error', 'msg' => 'Error updating application status: ' . $update_app_stmt->error];
        header("Location: new_application.php");
        exit();
    }

    addNotification($conn, 'client', $client_id, '✅ Your application has been approved and scheduled!');

    $_SESSION['popup'] = ['type' => 'success', 'msg' => 'Application approved and scheduled successfully.'];
    header("Location: new_application.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - New Application</title>
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet' />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/popup.css" />
  <script defer src="javascript/popup.js"></script>
  <script defer src="javascript/script.js"></script>
</head>
<body>

<?php if (isset($_SESSION['popup'])): ?>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    showPopup('<?= htmlspecialchars($_SESSION['popup']['type'], ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($_SESSION['popup']['msg'], ENT_QUOTES, 'UTF-8') ?>');
  });
</script>
<?php unset($_SESSION['popup']); endif; ?>

<div id="popup-container" class="popup-container"></div>

<?php include('header.php'); ?>
<div class="main-container">
  <?php include('admin_sidebar.html'); ?>
</div>
<div class="container">
  <div class="content">
    <h1>All Applications</h1>
    <?php if (!empty($applications)): ?>
      <table>
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Address</th>
            <th>Resident Type</th>
            <th>Mobile</th>
            <th>Promo</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($applications as $application): ?>
            <tr>
              <td><?= htmlspecialchars("{$application['fname']} {$application['mname']} {$application['lname']}") ?></td>
              <td><?= htmlspecialchars($application['address']) ?></td>
              <td><?= htmlspecialchars($application['resident_type']) ?></td>
              <td><?= htmlspecialchars($application['mobile']) ?></td>
              <td><?= htmlspecialchars($application['promo']) ?></td>
              <td>
                <?php if (!empty($application['install_date'])): ?>
                  <span style="color: green; font-weight: bold;">
                    Installed: <?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($application['install_date']))) ?>
                  </span>
                <?php elseif (!empty($application['scheduled_date'])): ?>
                  <span style="color: purple; font-weight: bold;">
                    Scheduled: <?= htmlspecialchars($application['scheduled_date']) ?> at <?= htmlspecialchars($application['scheduled_time']) ?>
                  </span>
                <?php elseif (!empty($application['staff_id'])): ?>
                  <span style="color: green; font-weight: bold;">
                    Assigned to <?= htmlspecialchars($application['staff_fname']) ?>
                    <?= !empty($application['staff_mname']) ? htmlspecialchars(" " . $application['staff_mname']) : "" ?>
                    <?= htmlspecialchars($application['staff_lname']) ?>
                  </span>
                <?php else: ?>
                  <?php if (empty($application['approval_status']) || $application['approval_status'] !== 'Approved'): ?>
                    <form method="POST">
                      <input type="hidden" name="approve_application_id" value="<?= htmlspecialchars($application['id']) ?>" />
                      <select name="staff_id" required>
                        <?php
                        $staff_query = "SELECT id, fname, lname FROM tblstafflist";
                        $staff_result = $conn->query($staff_query);
                        while ($staff = $staff_result->fetch_assoc()):
                            $staff['fname'] = safeDecrypt($staff['fname']);
                            $staff['lname'] = safeDecrypt($staff['lname']);
                        ?>
                          <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['fname'] . ' ' . $staff['lname']) ?></option>
                        <?php endwhile; ?>
                      </select>
                      <input type="date" name="schedule_date" required />
                      <input type="time" name="schedule_time" required />
                      <button type="submit">Approve & Schedule</button>
                    </form>
                  <?php else: ?>
                    <span style="color: red; font-weight: bold;">Approval already completed.</span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No applications found.</p>
    <?php endif; ?>
  </div>
</div>

<script src="javascript/sidebar-toggle.js"></script>

</body>
</html>

<?php $conn->close(); ?>